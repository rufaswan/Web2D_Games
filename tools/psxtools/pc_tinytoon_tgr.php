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

function dir_loop( $fp, $pos, $dir )
{
	$b1  = fp2str($fp, $pos, 4);
	$cnt = ordint($b1);
		$pos += 4;

	$func = __FUNCTION__;
	$sect = fp2str($fp, $pos, $cnt*0xf);
	$txt  = '';
	for ( $si=0; $si < $cnt; $si++ )
	{
		$sub = substr($sect, $si*0xf, 0xf);

		// 0   1 2 3 4  5 6  7 8 9 a  b c d e
		// ty  set      id   off      siz
		$ty = str2int($sub,  0, 1);
		$st = str2int($sub,  1, 4);
		$id = str2int($sub,  5, 2);
		$ps = str2int($sub,  7, 4);
		$sz = str2int($sub, 11, 4);
		$fn = sprintf('%s/%04d-%x-%d', $dir, $si, $st, $id);

		$log = sprintf('%8x , %8x , %8x , %4x , %s.%x', $ps, $sz, $st, $id, $fn, $ty);
		$txt .= "$log\n";
		echo "$log\n";

		switch ( $ty )
		{
			case 0: // Nothing / DIR
				$txt .= $func($fp, $ps, $fn);
				break;

			case 1: // Palette , 256 * rgba
				$pal = fp2str($fp, $ps, $sz);
				for ( $pi=0; $pi < $sz; $pi += 4 )
				{
					$r = $pal[$pi+2];
					$g = $pal[$pi+1];
					$b = $pal[$pi+0];
					$pal[$pi+0] = $r;
					$pal[$pi+1] = $g;
					$pal[$pi+2] = $b;
					$pal[$pi+3] = BYTE;
				}
				save_file("$fn.pal", $pal);
				break;

			case 2: // Image
				$pix = fp2str($fp, $ps, $sz);
				save_file("$fn.img", $pix);
				break;

			case 3: // Sound
				$wav = fp2str($fp, $ps, $sz);
				save_file("$fn.snd", $wav);
				break;

			case 4: // HotSpot , n * 9
				$unk = fp2str($fp, $ps, $sz);
				save_file("$fn.hot", $unk);
				break;

			default:
				return php_error('unknown type %x', $ty);
		} // switch ( $ty )
	} // for ( $si=0; $si < $cnt; $si++ )
	return $txt;
}

function tinytoon( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);

	$txt = dir_loop($fp, 0, $dir);
	save_file("$dir/tgrlist.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tinytoon( $argv[$i] );
