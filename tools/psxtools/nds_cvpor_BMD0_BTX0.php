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
require "common.inc";

function cvpor( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$mgc = substr($file, $st, 4);
		switch ( $mgc )
		{
			case "BMD0":
			case "BTX0":
				$siz = str2int($file, $st+0x10, 4);
				printf("%8x , %8x , %s/%s\n", $st, $siz, $fname, $mgc);
				$st += $siz;
				break;
			case "MDL0":
				$siz = str2int($file, $st+4, 4);
				printf("%8x , %8x , %s/%s\n", $st, $siz, $fname, $mgc);
				$st += $siz;
				break;
			case "TEX0":
				$siz = str2int($file, $st+4, 4);
				printf("%8x , %8x , %s/%s\n", $st, $siz, $fname, $mgc);

				$file = substr($file, $st);

				$off1 = str2int($file, 0x04, 3);
				$off2 = str2int($file, 0x14, 3);
				$off3 = str2int($file, 0x24, 3);

				$cns = $off1 - $off3;
				if ( $cns < 0x200 ) // 4-bit
				{
					$cn = $cns / 0x20;
					$clut = mstrpal555($file, $off3, 0x10, $cn);

					$pix = "";
					for ( $i=$off2; $i < $off3; $i++ )
					{
						$p = ord( $file[$i] );
						$p1 = ($p >> 0) & BIT4;
						$p2 = ($p >> 4) & BIT4;
						$pix .= chr($p1) . chr($p2);
					}

					$h = strlen($pix) / 0x80;
					foreach ( $clut as $k => $v )
					{
						$clut = "CLUT";
						$clut .= chrint(0x10, 4);
						$clut .= chrint(0x80, 4);
						$clut .= chrint($h, 4);
						$clut .= $v;
						$clut .= $pix;
						save_file("$dir/$k.clut", $clut);
					}
				}
				else // 8-bit
				{
					$cn = $cns / 0x200;
					$clut = mstrpal555($file, $off3, 0x100, $cn);
					$pix  = substr($file, $off2, $off3-$off2);

					$h = strlen($pix) / 0x80;
					foreach ( $clut as $k => $v )
					{
						$clut = "CLUT";
						$clut .= chrint(0x100, 4);
						$clut .= chrint(0x80, 4);
						$clut .= chrint($h, 4);
						$clut .= $v;
						$clut .= $pix;
						save_file("$dir/$k.clut", $clut);
					}
				}
				return;
			default:
				printf("%8x , %s , UNKNOWN\n", $st, $fname);
				return;
		} // switch ( $mgc )
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );
