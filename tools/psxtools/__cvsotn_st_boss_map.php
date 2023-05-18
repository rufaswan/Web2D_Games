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
 *   Zone File Technical Documentation (Dec 26, 2010)
 *   http://romhacking.net/documents/528/
 *     Nyxojaele
 */
require 'common.inc';
require 'cvsotn.inc';

function sectfbin( &$fbin )
{
	// f_xxx.bin is sets of 4-bit 80x80 pix data
	// arranged like this
	//   0 1  4 5  8 9  c d  10 11  14 15  18 19  1c 1d
	//   2 3  6 7  a b  e f  12 13  16 17  1a 1b  1e 1f
	// clut is on 2 3 6 7 a b e f
	//   in left-to-right , then top-to-bottom order
	//   2,0  2,1  3,0  3,1  6,0  6,1  7,0  7,1 ... e,e  e,f  f,e  f,f
	//
	//   0, 0      0  0,  0
	//  80, 0   2000  0, 80
	//   0,80   4000  0,100  pal
	//  80,80   6000  0,180  pal
	// 100, 0   8000  0,200
	// 180, 0   a000  0,280
	// 100,80   c000  0,300  pal
	// 180,80   e000  0,380  pal
	// ...
	// 700, 0  38000  0,e00
	// 780, 0  3a000  0,e80
	// 700,80  3c000  0,f00
	// 780,80  3e000  0,f80
	$pix = $fbin;
	bpp4to8($pix);

	$pal = '';
	for ( $c=0; $c < 16; $c++ )
	{
		for ( $s=0; $s < 4; $s++ )
		{
			$pos = ($s * 0x8000) + ($c * 0x40);
			$p1 = $pos + 0x5c00;
			$p2 = $pos + 0x7c00;

			$pal .= substr($fbin, $p1+0   , 0x20);
			$pal .= substr($fbin, $p1+0x20, 0x20);
			$pal .= substr($fbin, $p2+0   , 0x20);
			$pal .= substr($fbin, $p2+0x20, 0x20);
		} // for ( $s=0; $s < 4; $s++ )
	} // for ( $c=0; $c < 16; $c++ )

	$fbin = array(
		'pix' => $pix,
		'pal' => pal555($pal),
	);
	return;
}
//////////////////////////////
function sectmap( &$bin, &$fbin, $off )
{
	$s = substr($bin, $off, 0x10);

	$b00 = bossoff($s, 0); // tile layout
	$b04 = bossoff($s, 4); // tile def
	if ( $b00 < 0 || $b04 < 0 )
		return;

	$b0400 = bossoff($bin, $b04 +  0); // tile set
	$b0404 = bossoff($bin, $b04 +  4); // tile pos
	$b0408 = bossoff($bin, $b04 +  8); // clut
	$b040c = bossoff($bin, $b04 + 12); // collusion

	$b08 = str2int($s, 8, 3);
		$x1 = ($b08 >>  0) & 0x3f;
		$y1 = ($b08 >>  6) & 0x3f;
		$x2 = ($b08 >> 12) & 0x3f;
		$y2 = ($b08 >> 18) & 0x3f;

	$map_w = ($x2 + 1) - $x1;
	$map_h = ($y2 + 1) - $y1;
		$x1 <<= 8;
		$y1 <<= 8;
		$map_w <<= 8;
		$map_h <<= 8;

	$b0b = str2int($s, 11, 1);
	$b0c = str2int($s, 12, 2);
	$b0e = str2int($s, 14, 2);

	$pix = copypix_def($map_w,$map_h);
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	$txt = '';
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			$tid = str2int($bin, $b00, 2);
				$b00 += 2;

			$d00 = str2int($bin, $b0400 + $tid, 1); // tile pos 100x100
			$d04 = str2int($bin, $b0404 + $tid, 1); // tile pos  10x10
			$d08 = str2int($bin, $b0408 + $tid, 1); // clut
			$d0c = str2int($bin, $b040c + $tid, 1); // collusion
			$txt .= sprintf('%02x%02x%02x%02x  ', $d00, $d04, $d08, $d0c);

			$pal = substr($fbin['pal'], $d08*0x40, 0x40);
				$pal[3] = ZERO;
			$pix['src']['pal'] = $pal;

			$x100 = ($d00 >> 0) & BIT4;
			$y100 = ($d00 >> 4) & BIT4;
			$x10  = ($d04 >> 0) & BIT4;
			$y10  = ($d04 >> 4) & BIT4;

			if ( $y100 > 0 ) // y is 0-ff only , not 100+
				continue;

			// locate the 8000 slot
			$tpos = $x100 * 0x10000;

			// locate the 2000 slot
			if ( $y10 & 8 )  { $tpos += 0x8000; $y10 &= 7; }
			if ( $x10 & 8 )  { $tpos += 0x4000; $x10 &= 7; }

			$sub = substr($fbin['pix'], $tpos, 0x4000);
			$pix['src']['pix'] = rippix8($sub, $x10*0x10, $y10*0x10, 0x10, 0x10, 0x80, 0x80);
			copypix_fast($pix, 1);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )
		$txt .= "\n";

	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	$img = array(
		'x' => $x1,
		'y' => $y1,
		'pix' => $pix['rgba'],
		'txt' => str_replace('0', '-', $txt),
	);
	return $img;
}
//////////////////////////////
function psxsotn( $dir )
{
	$dir = rtrim($dir, '/\\');
	$bin  = load_file("$dir/$dir.bin");
	$fbin = load_file("$dir/f_$dir.bin");
	if ( empty($bin) || empty($fbin) )
		return;

	if ( strlen($fbin) !== 0x40000 )
		return;
	sectfbin($fbin);

	$b00 = bossoff($bin, 0x00); // mips func
	$b04 = bossoff($bin, 0x04); // mips func
	$b08 = bossoff($bin, 0x08); // mips func
	$b0c = bossoff($bin, 0x0c); // mips func
	if ( $b00 < 0 || $b04 < 0 || $b08 < 0 || $b0c < 0 )
		return;

	$b10 = bossoff($bin, 0x10);
	$b14 = bossoff($bin, 0x14); // spr
	$b18 = bossoff($bin, 0x18); // palettes
	$b1c = bossoff($bin, 0x1c);
	$b20 = bossoff($bin, 0x20); // map gfx
	$b24 = bossoff($bin, 0x24); // spr gfx
	$b28 = bossoff($bin, 0x28); // mips func

	printf("== %s\n", $dir);
	printf("fbin  pix %x  pal %x\n", strlen($fbin['pix']), strlen($fbin['pal']));
	$mapgfx = array();
	$mapdat = array();
	for ( $i=0;; $i++ )
	{
		$fg_off = bossoff($bin, $b20+0);
		$bg_off = bossoff($bin, $b20+4);
			$b20 += 8;
		if ( $fg_off < 0 || $bg_off < 0 )
			break;

		$mapdat[$i] = array($fg_off, $bg_off);
		if ( ! isset($mapgfx[$fg_off]) )
			$mapgfx[$fg_off] = sectmap($bin, $fbin, $fg_off);
		if ( ! isset($mapgfx[$bg_off]) )
			$mapgfx[$bg_off] = sectmap($bin, $fbin, $bg_off);
	} // for ( $i=0;; $i++ )

	foreach ( $mapgfx as $mk => $mv )
	{
		$fn = sprintf('%s/map/%06x', $dir, $mk);
		save_clutfile("$fn.rgba", $mv['pix']);
		save_file    ("$fn.txt" , $mv['txt']);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxsotn( $argv[$i] );

/*
 * dra.bin       is loaded @ 800a0000
 * f_map.bin     is loaded @ 801e0000
 * st/xxx.bin    is loaded @ 80180000 -> 8003c77c
 * bin/arc_f.bin is loaded @ 8013c000
 * bin/ric.bin   is loaded @
 *
 * lbaini.php -lba  dra.bin 3cac-4a6c cb3c-cd1c  st/sel/sel.bin b0dc-b0fc
 *
 */
