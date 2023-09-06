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
 *
 * Special Thanks
 *   ScummVM
 *   https://github.com/scummvm/scummvm/tree/master/engines/sci/resource.cpp
 *   https://github.com/scummvm/scummvm/tree/master/engines/sci/decompressor.cpp
 */
require 'common.inc';

function getbits( &$bits, &$file, &$pos, $c )
{
	while ( count($bits) < $c )
	{
		$by = ord( $file[$pos] );
			$pos++;
		$i = 8;
		while ( $i > 0 )
		{
			$i--;
			$bits[] = ($by >> $i) & 1;
		}
	} // while ( count($bits) < $c )

	$int = 0;
	for ( $i=0; $i < $c; $i++ )
	{
		$int <<= 1;
		$int |= array_shift($bits);
	}
	return $int;
}

function kq7_dlen20( &$bits, &$file, &$pos )
{
	$b = getbits($bits, $file, $pos, 2);
	switch( $b )
	{
		case 0:  return 2;
		case 1:  return 3;
		case 2:  return 4;
	}

	$b = getbits($bits, $file, $pos, 2);
	switch( $b )
	{
		case 0:  return 5;
		case 1:  return 6;
		case 2:  return 7;
	}

	$len = 8;
	while (1)
	{
		$b = getbits($bits, $file, $pos, 4);
		$len += $b;
		if ( $b !== 0xf )
			break;
	}
	return $len;
}

function kq7_decode( &$file, $cty )
{
	$dec = '';
	$len = strlen($file);
	$pos = 0;
	$bits = array();
	switch ( $cty )
	{
		case 0x20: // kCompSTACpack => new DecompressorLZS
			echo "kCompSTACpack\n";
			while ( $pos < $len )
			{
				$flg = getbits($bits, $file, $pos, 1);
				if ( $flg )
				{
					$flg = getbits($bits, $file, $pos, 1);
					if ( $flg )
					{
						$dpos = getbits($bits, $file, $pos, 7);
						if ( $dpos === 0 )
							goto done;
					}
					else
						$dpos = getbits($bits, $file, $pos, 11);

					$dlen = kq7_dlen20($bits, $file, $pos);
					for ( $i=0; $i < $dlen; $i++ )
					{
						$dp = strlen($dec) - $dpos;
						$dec .= $dec[$dp];
					}
				}
				else
				{
					$b = getbits($bits, $file, $pos, 8);
					$dec .= chr($b);
				}
			} // while ( $pos < $len )
			break;
		default:
			return php_error('UNKNOWN  kq7_decode( %x )', $cty);
	} // switch ( $cty )

done:
	$file = $dec;
	return;
}
//////////////////////////////
function kingquest7( $fname )
{
	// for *.000 only
	if ( stripos($fname, '.000') === false )
		return;
	$res = fopen_file($fname);
	if ( ! $res )  return;

	$dir = str_replace('.', '_', $fname);
	$fsz = filesize($fname);

	$done = array();
	// kq7 resource.000  0 1 2 4 6 7 9 b 10 11
	// kq7   altres.000  f
	$sci = array(
		'view'  , 'pic'  , 'script', 'animation', // 0x00-0x03
		'sound' , 'etc'  , 'vocab' , 'font'     , // 0x04-0x07
		'cursor', 'patch', 'bitmap', 'palette'  , // 0x08-0x0b
		'wave'  , 'audio', 'sync'  , 'message'  , // 0x0c-0x0f
		'map'   , 'heap' , 'chunk' , 'audio36'  , // 0x10-0x13
	);
	$pos = 0;
	$txt = '';
	while ( $pos < $fsz )
	{
		// ResVersion ResourceManager::detectVolVersion()
		//   SCI32 volume format:   {bResType wResNumber dwPacked dwUnpacked wCompression} = 13 bytes
		$head = fp2str($res, $pos, 0xd);

		// 0   1 2  3 4 5 6  7 8 9 a  b c
		// ty  id   sz       sz       cp
		$ty  = str2int($head,  0, 1);
		$id  = str2int($head,  1, 2);
		$sz1 = str2int($head,  3, 4); // compressed size
		$sz2 = str2int($head,  7, 4); // decompressed size
		$cty = str2int($head, 11, 2);

		$fn = sprintf('%s/%s/%05d.%s', $dir, $sci[$ty], $id, $sci[$ty]);
		$dt = fp2str ($res, $pos + 0xd, $sz1);
		if ( $sz1 !== $sz2 || $cty > 0 )
		{
			$fn .= sprintf('.%x.dec', $cty);
			kq7_decode($dt, $cty);
		}

		$log = sprintf('%8x  %8x  %8x  %s', $pos, $sz1, $sz2, $fn);
			$pos += (0xd + $sz1);

		if ( isset($done[$fn]) )
			return php_error('same name = %s', $fn);
		$done[$fn] = 1;

		echo "$log\n";
		$txt .= "$log\n";
		save_file($fn, $dt);
	} // while ( $pos < $fsz )

	save_file("$dir/list.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kingquest7( $argv[$i] );

/*
// TODO: 12 should be "Wave", but SCI seems to just store it in Audio resources
static const ResourceType s_resTypeMapSci21[] = {
	View  , Pic        , Script, Animation, // 0x00-0x03
	Sound , Etc        , Vocab , Font     , // 0x04-0x07
	Cursor, Patch      , Bitmap, Palette  , // 0x08-0x0B
	Audio , Audio      , Sync  , Message  , // 0x0C-0x0F
	Map   , Heap       , Chunk , Audio36  , // 0x10-0x13
	Sync36, Translation, Robot , VMD      , // 0x14-0x17
	Duck  , Clut       , TGA   , ZZZ        // 0x18-0x1B
};
*/
