<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'common.inc';
require 'common-guest.inc';

// amf3-file-format-spec.pdf
$gp_amf3 = [
	0x00 => 'undefined-marker',
	0x01 => 'null-marker',
	0x02 => 'false-marker',
	0x03 => 'true-marker',

	0x04 => 'integer-marker',
	0x05 => 'double-marker',
	0x06 => 'string-marker',
	0x07 => 'xml-doc-marker',

	0x08 => 'date-marker',
	0x09 => 'array-marker',
	0x0a => 'object-marker',
	0x0b => 'xml-marker',

	0x0c => 'byte-array-marker',
	0x0d => 'vector-int-marker',
	0x0e => 'vector-uint-marker',
	0x0f => 'vector-double-marker',

	0x10 => 'vector-object-marker',
	0x11 => 'dictionary-marker',
];

function sol2json( &$file, $fname )
{
	global $gp_amf3;
	$len = strlen($file);
	$pos = 0x1e;
	while ( $pos < $len )
	{
		$bak  = $pos;
		$size = ord( $file[$pos+0] );
			$size >>= 1;
		$name = substr($file, $pos+1, $size);
			$pos += ($size + 1);

		$type = ord( $file[$pos] );
			$pos++;
		if ( ! isset($gp_amf3[$type]) )
			return php_error('%x = type %x', $pos-1, $type);

		switch ( $type )
		{
			case 4:
				$int  = 0;
				$cont = 1;
				while ( $cont )
				{
					$b = ord( $file[$pos] );
						$pos++;
					$int |= ($b & 0x7f);
					$cont =  $b & 0x80;

					if ( $cont )
						$int <<= 7;
				} // while ( $cont )

				printf("%8x : (%s) %s = %d\n", $bak, $gp_amf3[$type], $name, $int);
				break;
			case 6:
				$size = ord( $file[$pos] );
					$size >>= 1;
				$val = substr($file, $pos+1, $size);

				$pos += ($size + 1);
				printf("%8x : (%s) %s = %s\n", $bak, $gp_amf3[$type], $name, $val);
				break;
			default:
				printf("%8x : (%s) %s\n", $bak, $gp_amf3[$type], $name);
				break;
		} // switch ( $type )

		$pos++; // skip 00
	} // while ( $pos < $len )
	return;
}

function is_solfile( &$file )
{
	if ( substr($file,0,2) !== "\x00\xbf" )
		return false;

	$siz = str2big($file, 2, 4);
	$len = strlen($file);
	if ( ($siz+6) !== $len )
		return false;

	if ( substr($file,6,4) !== 'TCSO' )
		return false;

	return true;
}

function soljsonfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( is_solfile($file) )
		return sol2json($file, $fname);

	return;
}

argv_loopfile($argv, 'soljsonfile');
