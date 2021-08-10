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
require "common-guest.inc";

function mujin( $fname )
{
	// for *.vd1 only
	if ( stripos($fname, '.vd1') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$off1 = str2big($file,  8, 4);
	$off2 = str2big($file, 12, 4);

	$off21 = str2big($file, $off2, 4);

		$pal = substr($file, $off21);
		$pal = pal555( big2little16($pal) );

	$off11 = str2big($file, $off1+0, 4);
	$off12 = str2big($file, $off1+4, 4);

	$id = 0;
	while ( $off11 < $off12 )
	{
		$fn = sprintf("%s/%04d.clut", $dir, $id);
			$id++;

		$w   = str2big($file, $off11+ 0, 2, true);
		$h   = str2big($file, $off11+ 2, 2, true);
		$siz = str2big($file, $off11+ 4, 4);
		$typ = str2big($file, $off11+ 8, 2);
		$pos = str2big($file, $off11+12, 4);
			$off11 += 0x10;

		if ( $w <= 0 || $h <= 0 )
			continue;

		$pix = substr($file, $off12+$pos, $siz);
		$bpp = -1;
		if ( $typ ==  8 )  $bpp = 4;
		if ( $typ == 32 )  $bpp = 8;
		if ( $bpp < 0 )
			continue;
		printf("%x x %x = %x [%d bpp] , %x , %s\n", $w, $h, $siz, $bpp, $pos, $fn);

		$cc = 0;
		$b1 = '';
		$b2 = '';
		if ( $bpp == 8 )
		{
			$cc = 0x100;
			$b1 = substr($pal, 0, 0x400);
			$b2 = $pix;
		}
		if ( $bpp == 4 )
		{
			$cc = 0x10;
			$b1 = substr($pal, 0, 0x40);
			bpp4to8($pix);
			$b2 = big2little16($pix);
		}

		$img = array(
			'cc'  => $cc,
			'w'   => $w,
			'h'   => $h,
			'pal' => $b1,
			'pix' => $b2,
		);
		save_clutfile($fn, $img);
	} // while ( $off11 < $off12 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mujin( $argv[$i] );
