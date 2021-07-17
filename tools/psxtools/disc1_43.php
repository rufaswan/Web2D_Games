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
 *   http://scummvm-2.2.0/engines/tinsel/graphics.cpp
 */
require "common.inc";

function sect_map( &$file, $fn, $st3, $st4, $st5, $w, $h, $bpp, $lzs )
{
	printf("== sect_map( %s , %x , %x , %x , %x , %x , %d , %d )\n", $fn, $st3, $st4, $st5, $w, $h, $bpp, $lzs);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = canvpix($w,$h);
	$pix['src']['w'] = 4;
	$pix['src']['h'] = 4;

	// set pallete data
	if ( $bpp == 8 )  $pix['src']['pal'] = substr($file, $st5, 0x400);
	if ( $bpp == 4 )  $pix['src']['pal'] = substr($file, $st5, 0x40);
	$len = strlen( $pix['src']['pal'] );
	for ( $i=0; $i < $len; $i += 4 )
		$pix['src']['pal'][$i+3] = BYTE;

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

			if ( $lzs ) // 0xcc
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
						$b1 = str2int($file, $st3, 2);
							$st3 += 2;
						break;
					case 1:
						$b1 = $pind;
						break;
					case 2:
						$b1 = $pind;
						$pind++;
						break;
				} // switch ( $ptyp )

				if ( $bpp == 8 )
				{
					$pix['src']['pix'] = substr($file, $st4+$b1*16, 16);
					copy_fast($pix);
				}
				else
				if ( $bpp == 4 )
				{
					$pix['src']['pix'] = substr($file, $st4+$b1*8, 8);
					bpp4to8( $pix['src']['pix'] );
					copy_fast($pix);
				}
				$pcnt--;
			}
			else // 0xdd
			{
			}
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	savepix($fn, $pix, false);
	return;
}

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

		$bpp = -1;
		if ( $file[$st3+0] == "\x88" )  $bpp = 8;
		if ( $file[$st3+0] == "\x44" )  $bpp = 4;

		$lzs = -1;
		if ( $file[$st3+1] == "\xcc" )  $lzs = 1;
		if ( $file[$st3+1] == "\xdd" )  $lzs = 0;

		if ( $bpp < 0 || $lzs < 0 )
			return php_error("UNKNOWN st3 type %d , %d", $bpp, $lzs);

		$fn = sprintf("%s/%06d", $dir, $id7);
			$id7++;
		sect_map($file, $fn, $st3+2, $st4, $st5, $w, $h, $bpp, $lzs);
	} // while ( $st7 < $ed7 )
	return;
}

function save_txt( &$file, &$sect, $dir )
{
	if ( empty($sect[1]) )
		return;
	printf("== save_txt( $dir )\n");

	foreach ( $sect[1] as $k => $v )
	{
		$ed = str2int($file, $v-4, 4);
		$st = $v;
		$txt = '';
		while ( $st < $ed )
		{
			$len = ord($file[$st]);
			$sub = substr($file, $st+1, $len);

			$st += (1 + $len);
			$txt .= "$sub\n";
		} // while ( $st < $ed )

		$fn = sprintf("$dir/%04d.txt", $k);
		save_file($fn, $txt);
	} // foreach ( $sect[1] as $k => $v )

	return;
}

function disc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$sect = array();
	$sect[1] = array();

	$pos = 0;
	while (1)
	{
		if ( substr($file,$pos+1,3) !== "\x0043" )
			return php_error("NOT 43 %s @ %x", $fname, $pos);

		$id = ord( $file[$pos] );
		$nx = str2int($file, $pos+4, 4);

		if ( $id == 1 )
			$sect[1][] = $pos + 8;
		else
		{
			if ( isset( $sect[$id] ) )
				return php_error("DUP 43 %s @ %x = %x", $fname, $pos, $id);
			$sect[$id] = $pos + 8;
		}

		$pos = $nx;
		if ( $pos == 0 )
			break;
	} // while (1)

	save_txt($file, $sect, "$dir/txt");
	save_gfx($file, $sect, "$dir/gfx");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc( $argv[$i] );
