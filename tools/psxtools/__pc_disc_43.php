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
 *   https://github.com/scummvm/scummvm/tree/master/engines/tinsel/graphics.cpp
 */
require "common.inc";
require "pc_disc.inc";

//////////////////////////////
function dwn_pal565( &$pal )
{
	$clr = '';
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$c = str2int($pal, $i, 2);

		// fedcba9876543210
		// rrrrrggggggbbbbb
		$r = ($c >> 8) & 0xf8; // >> 11 << 3
		$g = ($c >> 3) & 0xfc; // >>  5 << 2
		$b = ($c << 3) & 0xf8; // >>  0 << 3
		$clr .= chr($r) . chr($g) . chr($b) . BYTE;
	}
	return $clr;
}

function dwn_scn( &$file, &$sect, $dir )
{
	echo "== dwn_scn( $dir )\n";

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = int_ceil($b1, 4);
			$h = int_ceil($b2, 4);

		$b1 = str2int($sub6,  8, 4);
		$b2 = str2int($sub6, 12, 4);
			$st19 = $b1 & 0x01ffffff;
			$rle  = ( $b2 & 1 ); // 0 raw , 1 rle , 40000 raw+locale

		if ( $rle === 0 )
		{
			$src = substr($file, $st19, $w*$h*2);
			$img = array(
				'w' => $w,
				'h' => $h,
				'pix' => dwn_pal565($src),
			);
			save_clutfile("$fn.rgba", $img);
			continue;
		}
	} // while ( $st6 < $ed6 )

	return;
}
//////////////////////////////
function dw2_scn( &$file, &$sect, $dir )
{
	echo "== dw2_scn( $dir )\n";

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = int_ceil($b1 & 0x3fff, 4);
			$h = int_ceil($b2 & 0x3fff, 4);
			$pak = $b2 >> 14;

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st19 = $b1;
			$st5  = $b2;

		$img = array('w' => $w , 'h' => $h);
		if ( $st5 !== 0 )
		{
			$pal = substr($file, $st5, 0x400);
			palbyte($pal);
			$img['cc' ] = 0x100;
			$img['pal'] = $pal;
			$img['pix'] = substr($file, $st19, $w*$h);
			save_clutfile("$fn.clut", $img);
			continue;
		}
		//else
			//dw2_pak($file, $st19, $w, $h, $pak, $fn);
	} // while ( $st6 < $ed6 )

	return;
}
//////////////////////////////
function dw1_scn( &$file, &$sect, $dir )
{
	echo "== dw1_scn( $dir )\n";

	// tile = 4 x 4 = 0x10
	$st48 = str2int($file, $sect[3]+0, 4);
	$b1   = str2int($file, $sect[3]+4, 4);
	$st44 = $st48 + $b1 * 16;

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = int_ceil($b1, 4);
			$h = int_ceil($b2, 4);

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st3 = $b1 & 0xfffff;
			$st5 = $b2 & 0xfffff;

		$pal = substr($file, $st5, 0x400);
		palbyte($pal);

		$pix = COPYPIX_DEF($w,$h);
		$pix['src']['w'] = 4;
		$pix['src']['h'] = 4;
		$pix['src']['pal'] = $pal;

		for ( $y=0; $y < $h; $y += 4 )
		{
			for ( $x=0; $x < $w; $x += 4 )
			{
				$b1 = str2int($file, $st3, 2);
					$st3 += 2;

				$bpp4 = $b1 & 0x8000;
				$tid  = $b1 & 0x7fff;
				if ( $bpp4 )
					$src = substr($file, $st44+$tid*16, 16);
				else
					$src = substr($file, $st48+$tid*16, 16);

				$pix['dx'] = $x;
				$pix['dy'] = $y;
				$pix['src']['pix'] = $src;

				copypix_fast($pix);
			} // for ( $x=0; $x < $w; $x += 4 )
		} // for ( $y=0; $y < $h; $y += 4 )

		savepix($fn, $pix, false);
	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function disc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir  = str_replace('.', '_', $fname);
	$sect = scnsect($file);

	// http://rewiki.regengedanken.de/wiki/.SCN
	//   DW1 3 4 5 - c d -  -  -  -  -  -  -  -  -  -  -
	//   DW2 - - 5 - c d f 12 13  - 19 1b 1c 1d 1e  -  -
	//   DWN - - - 9 - - f  -  - 18 19 1b 1c 1d  - 20 31
	// 3 = tile map   data
	// 4 = tile pixel data
	// 5 = palette data
	//save_txt($file, $sect, "{$dir}_txt");
	if ( ! isset( $sect[6] ) ) // no graphic
		return;

	if ( isset( $sect[3] ) )
		return dw1_scn($file, $sect, "{$dir}_gfx");
	if ( isset( $sect[5] ) )
		return dw2_scn($file, $sect, "{$dir}_gfx");
	dwn_scn($file, $sect, "{$dir}_gfx");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc( $argv[$i] );

/*
dwn_scn/62 = 4 x 4  b1aade5-b1aadf5  rle
	02 -- 21 --  1f f8
	02 80 1f f8
	02 -- 1f f8  21 --
		80 = 2
		00 = 4
		-= 10
dwn_scn/55 = 8 x 8  b1bf1c3-b1bf211  rle
	02 --  1f f8  20 --
	03 80  1f f8
	02 80  20 --
	03 80  1f f8
	03 80  20 --
	02 80  1f f8
	02 80  20 --
	01 --  21 --
	02 80  1f f8
	02 80  20 --
	03 --  21 --  42 --  1f f8
	02 80  1f f8
	03 --  41 --  42 --  1f f8
	03 80  1f f8
	02 80  42 --
	03 80  1f f8
	02 80  42 --
		80 = 31
		00 =  9
		-= 24
 */
