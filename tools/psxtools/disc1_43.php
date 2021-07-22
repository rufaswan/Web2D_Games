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
require "disc.inc";

function sect_map( &$file, $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle )
{
	printf("== sect_map( %s , %x , %x , %x , %x , %x , %d , %d )\n", $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = canvpix($w,$h);
	$pix['src']['w'] = 4;
	$pix['src']['h'] = 4;

	// set pallete data
	if ( $bpp == 8 )  $pix['src']['pal'] = substr($file, $st5, 0x400);
	if ( $bpp == 4 )  $pix['src']['pal'] = substr($file, $st5, 0x40);
	palbyte( $pix['src']['pal'] );

	// set pixel data
	$pcnt = 0;
	$ptyp = 0;
	$pind = 0;
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$ssid = 0;

			if ( $rle ) // 0xcc
			{
				if ( $pcnt <= 0 )
				{
					$b1 = str2int($file, $st3, 2);
						$st3 += 2;

					$pcnt = $b1 & 0x3fff;
					$ptyp = 0;
					if ( $b1 & 0x8000 )  $ptyp = 1; // DUP
					if ( $b1 & 0x4000 )  $ptyp = 2; // ID++

					if ( $ptyp != 0 )
					{
						$pind = str2int($file, $st3, 2);
							$st3 += 2;
					}
				} // if ( $pcnt == 0 )

				switch ( $ptyp )
				{
					case 0:
						$ssid = str2int($file, $st3, 2);
							$st3 += 2;
						break;
					case 1:
						$ssid = $pind;
						break;
					case 2:
						$ssid = $pind;
						$pind++;
						break;
				} // switch ( $ptyp )

				$pcnt--;
			}
			else // 0xdd
			{
				$ssid = str2int($file, $st3, 2);
					$st3 += 2;
			}

			if ( $bpp == 8 )
				$pix['src']['pix'] = substr($file, $st4+$ssid*16, 16);
			else
			if ( $bpp == 4 )
			{
				$pix['src']['pix'] = substr($file, $st4+$ssid*8, 8);
				bpp4to8( $pix['src']['pix'] );
			}

			copypix_fast($pix);
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	savepix($fn, $pix, false);
	return;
}
//////////////////////////////
function save_gfx( &$file, &$sect, $dir )
{
	if ( ! isset($sect[3]) )  return; // tile data   -> 4
	if ( ! isset($sect[4]) )  return; // 4x4 pixel data
	if ( ! isset($sect[5]) )  return; // palette
	if ( ! isset($sect[6]) )  return; // sprite data -> 3,5
	if ( ! isset($sect[7]) )  return; // anim data   -> 6
	printf("== save_gfx( $dir )\n");

	$st4 = str2int($file, $sect[3], 4);

	$ed7 = str2int($file, $sect[7]-4, 4);
	$st7 = $sect[7];
	$id7 = 0;
	while ( $st7 < $ed7 )
	{
		$b1 = str2int($file, $st7, 3);
			$st7 += 8;

		$st6 = $b1 & 0xfffff;
		$b1 = str2int($file, $st6+0, 2);
		$b2 = str2int($file, $st6+2, 2);
			$w = int_ceil($b1 & 0x7fff, 4);
			$h = int_ceil($b2 & 0x7fff, 4);

		$b1 = str2int($file, $st6+ 8, 3);
		$b2 = str2int($file, $st6+12, 3);
			$st3 = $b1 & 0xfffff;
			$st5 = $b2 & 0xfffff;

		// from SCUS_946.00 , sub_8001588c
		$bpp = -1;
		if ( $file[$st3+0] == "\x88" )  $bpp = 8;
		if ( $file[$st3+0] == "\x44" )  $bpp = 4;

		$rle = -1;
		if ( $file[$st3+1] == "\xcc" )  $rle = 1;
		if ( $file[$st3+1] == "\xdd" )  $rle = 0;

		if ( $bpp < 0 || $rle < 0 )
			return php_error("UNKNOWN st3 type %d , %d", $bpp, $rle);

		$fn = sprintf("%s/%06d", $dir, $id7);
			$id7++;

		if ( $bpp == 8 )  $st3 += 2;
		if ( $bpp == 4 )  $st3 += (2 + 32);
		sect_map($file, $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle);
	} // while ( $st7 < $ed7 )
	return;
}
//////////////////////////////
function disc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir  = str_replace('.', '_', $fname);
	$sect = scnsect($file);

	# http://rewiki.regengedanken.de/wiki/.SCN
	#   DW1 3 4 5 - c d -  -  -  -  -  -  -  -  -  -  -
	#   DW2 - - 5 - c d f 12 13  - 19 1b 1c 1d 1e  -  -
	#   DWN - - - 9 - - f  -  - 18 19 1b 1c 1d  - 20 31
	save_txt($file, $sect, "$dir/txt");
	save_gfx($file, $sect, "$dir/gfx");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc( $argv[$i] );
