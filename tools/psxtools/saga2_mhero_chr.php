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
/*
 * 00 4  CHR size
 * 04 4  ??? offset
 * 08 4  ??? offset
 * 0c 4  CLUT offset [16 color]
 * 10 4  sprite meta
 *
 * sprite meta
 * 00 width  & 0x80
 * 01 height & 0x80
 * 02 ???
 * 03 ???
 *
 * 80 86 =  4 * 56 ( e0/2 + 70)
 * 81 86 =  6 * 56 (150/2 + a8)
 * 82 86 =  8 * 56 (1c0/2 + e0)
 * 86 86 = 16 * 56 (380/2 +1c0)
 * 8a 86 = 24 * 56 (540/2 +2a0)
 * 8b 86 = 26 * 56 (5b0/2 +2d8)
 * 8c 86 = 28 * 56 (620/2 +310)
 * 8d 86 = 30 * 56 (690/2 +348)
 * 8e 86 = 32 * 56 (700/2 +380)
 * 92 86 = 40 * 56 (8c0/2 +460)
 * => x++ = 4+(i*2)
 *
 * 8e 80 = 32 *  8 (100/2 + 80)
 * 8e 81 = 32 * 16 (200/2 +100)
 * 8e 82 = 32 * 24 (300/2 +180)
 * 8e 83 = 32 * 32 (400/2 +200)
 * 8e 84 = 32 * 40 (500/2 +280)
 * 8e 85 = 32 * 48 (600/2 +300)
 * 8e 86 = 32 * 56 (700/2 +380)
 * => y++ = 8+(i*8)
 */

require "common.inc";

function spr_wh( &$file, $st )
{
	$ret = array();
	while (1)
	{
		$b1 = ord( $file[$st+0] );
		$b2 = ord( $file[$st+1] );
		if ( ($b1 & 0x80) == 0 )
			return $ret;

		$w = 4 + (($b1 & 0x7f) * 2);
		$h = 8 + (($b2 & 0x7f) * 8);

		$ret[] = array($w,$h);
		$st += 4;
	}
	return $ret;
}

function saga2( $fname )
{
	// only *.CHR files
	if ( stripos($fname, ".CHR") == false )
		return;

	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$spr_wh = spr_wh($file, 0x10);

	$clut = str2int ($file, 0x0c,  4);
	$clut = strpal555($file, $clut, 0x10);
		// first color is always alpha
		$clut[3] = ZERO;

	$st = 0x10 + count($spr_wh) * 4;
	foreach ( $spr_wh as $k => $wh )
	{
		$out = sprintf("$dir/%04d.clut", $k);
		list($w,$h) = $wh;

		$data = "CLUT";
		$data .= chrint(0x10, 4); // no clut
		$data .= chrint($w, 4); // width
		$data .= chrint($h, 4); // height
		$data .= $clut;

		$len = $w * $h / 2;
		$pix = substr($file, $st, $len);
		for ( $i=0; $i < $len; $i++ )
		{
			$b = ord( $pix[$i] );
			$b1 = ($b >> 0) & BIT4;
			$b2 = ($b >> 4) & BIT4;
			$data .= chr($b1) . chr($b2);
		}
		save_file($out, $data);
		$st += $len;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
