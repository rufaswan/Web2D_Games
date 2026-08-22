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

function jinguji( $fname )
{
	// for *.dat only
	if ( stripos($fname, '.dat') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	$pos = 0;

	$id = 0;
	while ( $pos < $len )
	{
		$type = str2int($file, $pos, 4);
		switch ( $type )
		{
			case 0x10:
				$tim = psxtim ($file, $pos);
				$fn  = sprintf('%s/%04d.clut', $dir, $id);
				save_clutfile($fn, $tim);

				$id++;
				$pos += $tim['siz'];
				break;
			case 0:
				$pos = int_ceil($pos + 1, 0x800);
				break;
			default:
				$sub = substr($file, $pos);
				$fn  = sprintf('%s/%04d.bin', $dir, $id);
				save_file($fn, $sub);

				$id++;
				$pos += $len;
				break;
		} // switch ( $type )
	} // while ( $pos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jinguji( $argv[$i] );
