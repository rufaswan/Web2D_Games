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

function mana( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$ps = str2int($file, $p, 4);

		if ( $file[$ps] == ZERO ) // allface.dat
		{
			$clut = "CLUT";
			$clut .= chrint(16,4); // no clut
			$clut .= chrint(48,4); // width
			$clut .= chrint(48,4); // height
			$clut .= strpal555($file, $ps, 16);
				$ps += 0x20;

			$sz = 0x18 * 0x30;
			while ( $sz )
			{
				$b = ord( $file[$ps] );

				$b1 = ($b >> 0) & BIT4;
				$b2 = ($b >> 4) & BIT4;
				$clut .= chr($b1) . chr($b2);

				$ps++;
				$sz--;
			}
			$fn = sprintf("$dir/%04d.clut", $i);
			save_file($fn, $clut);
			continue;
		}

		if ( $file[$ps] != chr(0x10) ) // TIM file
			continue;

		// TIM with CLUT
		if ( ord( $file[$ps+4] ) & 8 )
		{
			$str = substr($file, $ps);
			$tim = psxtim($str);

			foreach ( $tim['pal'] as $k => $v )
			{
				if ( trim($v, ZERO.BYTE) == "" )
					continue;
				$clut = "CLUT";
				$clut .= chrint($tim['cc'], 4); // no clut
				$clut .= chrint($tim['w'], 4); // width
				$clut .= chrint($tim['h'], 4); // height
				$clut .= $v;
				$clut .= $tim['pix'];

				$fn = sprintf("$dir/%04d_%d.clut", $i, $k);
				save_file($fn, $clut);
			} // foreach ( $tim['pal'] as $k => $v )
			continue;
		}

		// TIM with 16-bit RGB555 pixels
		$w = str2int($file, $ps+0x10, 2);
		$h = str2int($file, $ps+0x12, 2);
			$ps += 0x14;

		$rgba = "RGBA";
		$rgba .= chrint($w, 4); // width
		$rgba .= chrint($h, 4); // height

		$sz = $w * $h;
		while ( $sz > 0 )
		{
			$rgba .= rgb555( $file[$ps+0] . $file[$ps+1] );
			$ps += 2;
			$sz--;
		}
		$fn = sprintf("$dir/%04d.rgba", $i);
		save_file($fn, $rgba);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

