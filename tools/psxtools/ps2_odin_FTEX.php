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
 *   ezSwizzle @ PS2Linux
 *   http://ps2linux.no-ip.info/playstation2-linux.com/project/ezSwizzle
 *     Victor Suba
 *     Lionel Lemarie
 */

require 'common.inc';
require 'common-guest.inc';

//define('DRY_RUN', true);

//////////////////////////////
function unswizz8( &$sub, $inv, &$upper, &$lower )
{
	// SIMPLIFIED
	// original @ size 32x32 => 16x16 swizzled
	//   0   0/A1   80  90/B1  100 100/A1  180 190/B1
	//  20  40/A1   a0  d0/B1  120 140/A1  1a0 1d0/B1
	//  40  11/A2   c0  81/B2  140 111/A2  1c0 181/B2
	//  60  51/A2   e0  c1/B2  160 151/A2  1e0 1c1/B2
	//
	// 200 200/A1  280 290/B1  300 300/A1  380 390/B1
	// 220 240/A1  2a0 2d0/B1  320 340/A1  3a0 3d0/B1
	// 240 211/A2  2c0 281/B2  340 311/A2  3c0 381/B2
	// 260 251/A2  2e0 2c1/B2  360 351/A2  3e0 3c1/B2
	//
	// TYPE A
	//    0  4  8  c  10 14 18 1c   2  6  a  e  12 16 1a 1e
	//   20 24 28 2c  30 34 38 3c  22 26 2a 2e  32 36 3a 3e [A1]
	//   11 15 19 1d   1  5  9  d  13 17 1b 1f   3  7  b  f
	//   31 35 39 3d  21 25 29 2d  33 37 3b 3f  23 27 2b 2f [A2]
	// rearranged
	//    0 24  8 2c   1 25  9 2d   2 26  a 2e   3 27  b 2f
	//    4 20  c 28   5 21  d 29   6 22  e 2a   7 23  f 2b
	//   10 34 18 3c  11 35 19 3d  12 36 1a 3e  13 37 1b 3f
	//   14 30 1c 38  15 31 1d 39  16 32 1e 3a  17 33 1f 3b [A1+A2]
	//
	// TYPE B
	//   10 14 18 1c   0  4  8  c  12 16 1a 1e   2  6  a  e
	//   30 34 38 3c  20 24 28 2c  32 36 3a 3e  22 26 2a 2e [B1]
	//    1  5  9  d  11 15 19 1d   3  7  b  f  13 17 1b 1f
	//   21 25 29 2d  31 35 39 3d  23 27 2b 2f  33 37 3b 3f [B2]
	// rearranged
	//    4 20  c 28   5 21  d 29   6 22  e 2a   7 23  f 2b
	//    0 24  8 2c   1 25  9 2d   2 26  a 2e   3 27  b 2f
	//   14 30 1c 38  15 31 1d 39  16 32 1e 3a  17 33 1f 3b
	//   10 34 18 3c  11 35 19 3d  12 36 1a 3e  13 37 1b 3f [B1+B2]
	//
	$b1a = $sub[0x00] . $sub[0x04] . $sub[0x08] . $sub[0x0c]; // 0-3
	$b1b = $sub[0x01] . $sub[0x05] . $sub[0x09] . $sub[0x0d]; // 14-17
	$b1c = $sub[0x02] . $sub[0x06] . $sub[0x0a] . $sub[0x0e]; // 8-b
	$b1d = $sub[0x03] . $sub[0x07] . $sub[0x0b] . $sub[0x0f]; // 1c-1f

	$b2a = $sub[0x10] . $sub[0x14] . $sub[0x18] . $sub[0x1c]; // 4-7
	$b2b = $sub[0x11] . $sub[0x15] . $sub[0x19] . $sub[0x1d]; // 10-13
	$b2c = $sub[0x12] . $sub[0x16] . $sub[0x1a] . $sub[0x1e]; // c-f
	$b2d = $sub[0x13] . $sub[0x17] . $sub[0x1b] . $sub[0x1f]; // 18-1b

	if ( ! $inv )
	{
		$upper .= $b1a . $b2a . $b1c . $b2c;
		$lower .= $b2b . $b1b . $b2d . $b1d;
	}
	else
	{
		$upper .= $b2a . $b1a . $b2c . $b1c;
		$lower .= $b1b . $b2b . $b1d . $b2d;
	}
	return;
}

function tm2pix8( &$pix, $ow, $oh )
{
	if ( defined("DRY_RUN") )
		return;

	// minimum size 128x128 for swizzled image
	$new = '';
	$inv = false;
	for ( $y=0; $y < $oh; $y += 4 )
	{
		$sub = substr($pix, $y*$ow, $ow*4);

		$upper = '';
		$lower = '';
		$len = strlen($sub);
		for ( $i=0; $i < $len; $i += 0x20 )
		{
			$str = substr($sub, $i, 0x20);
			unswizz8($str, $inv, $upper, $lower);
		} // for ( $i=0; $i < $len; $i += 0x20 )

		$new .= $upper . $lower;
		$inv = ! $inv;
	} // for ( $y=0; $y < $oh; $y += 4 )

	$pix = $new;
	return;
}
//////////////////////////////
function unswizz4( &$sub, $inv, &$upper, &$lower )
{
	// SIMPLIFIED
	// original @ size 32x32 => 16x8 swizzled
	//   0  0/A1   80 120/B1  100 200/A1  180 320/B1
	//  20 80/A1   a0 1a0/B1  120 280/A1  1a0 3a0/B1
	//  40 21/A2   c0 101/B2  140 221/A2  1c0 301/B2
	//  60 a1/A2   e0 181/B2  160 2a1/A2  1e0 381/B2
	//
	// 200 40/A1  280 160/B1  300 240/A1  380 360/B1
	// 220 c0/A1  2a0 1e0/B1  320 2c0/A1  3a0 3e0/B1
	// 240 61/A2  2c0 141/B2  340 261/A2  3c0 341/B2
	// 260 e1/A2  2e0 1c1/B2  360 2e1/A2  3e0 3c1/B2
	//
	// TYPE A
	//    0  8 10 18  20 28 30 38   2  a 12 1a  22 2a 32 3a
	//    4  c 14 1c  24 2c 34 3c   6  e 16 1e  26 2e 36 3e [A1]
	//   21 29 31 39   1  9 11 19  23 2b 33 3b   3  b 13 1b
	//   25 2d 35 3d   5  d 15 1d  27 2f 37 3f   7  f 17 1f [A2]
	// rearranged
	//    0 24  8 2c  10 34 18 3c   1 25  9 2d  11 35 19 3d
	//    2 26  a 2e  12 37 1a 3e   3 27  b 2f  13 37 1b 3f
	//    4 20  c 28  14 30 1c 38   5 21  d 29  15 31 1d 39
	//    6 22  e 2a  16 32 1e 3a   7 23  f 2b  17 33 1f 3b [A1+A2]
	//
	// TYPE B
	//   20 28 30 38   0  8 10 18  22 2a 32 3a   2  a 12 1a
	//   24 2c 34 3c   4  c 14 1c  26 2e 36 3e   6  e 16 1e [B1]
	//    1  9 11 19  21 29 31 39   3  b 13 1b  23 2b 33 3b
	//    5  d 15 1d  25 2d 35 3d   7  f 17 1f  27 2f 37 3f [B2]
	// rearranged
	//    4 20  c 28  14 30 1c 38   5 21  d 29  15 31 1d 39
	//    6 22  e 2a  16 32 1e 3a   7 23  f 2b  17 33 1f 3b
	//    0 24  8 2c  10 34 18 3c   1 25  9 2d  11 35 19 3d
	//    2 26  a 2e  12 37 1a 3e   3 27  b 2f  13 37 1b 3f [B1+B2]
	//
	$b1a = $sub[0x00] . $sub[0x08] . $sub[0x10] . $sub[0x18]; // 0-3
	$b1b = $sub[0x01] . $sub[0x09] . $sub[0x11] . $sub[0x19]; // 24-27
	$b1c = $sub[0x02] . $sub[0x0a] . $sub[0x12] . $sub[0x1a]; // 8-b
	$b1d = $sub[0x03] . $sub[0x0b] . $sub[0x13] . $sub[0x1b]; // 2c-2f
	$b1e = $sub[0x04] . $sub[0x0c] . $sub[0x14] . $sub[0x1c]; // 10-13
	$b1f = $sub[0x05] . $sub[0x0d] . $sub[0x15] . $sub[0x1d]; // 34-37
	$b1g = $sub[0x06] . $sub[0x0e] . $sub[0x16] . $sub[0x1e]; // 18-1b
	$b1h = $sub[0x07] . $sub[0x0f] . $sub[0x17] . $sub[0x1f]; // 3c-3f

	$b2a = $sub[0x20] . $sub[0x28] . $sub[0x30] . $sub[0x38]; // 4-7
	$b2b = $sub[0x21] . $sub[0x29] . $sub[0x31] . $sub[0x39]; // 20-23
	$b2c = $sub[0x22] . $sub[0x2a] . $sub[0x32] . $sub[0x3a]; // c-f
	$b2d = $sub[0x23] . $sub[0x2b] . $sub[0x33] . $sub[0x3b]; // 28-2b
	$b2e = $sub[0x24] . $sub[0x2c] . $sub[0x34] . $sub[0x3c]; // 14-17
	$b2f = $sub[0x25] . $sub[0x2d] . $sub[0x35] . $sub[0x3d]; // 30-33
	$b2g = $sub[0x26] . $sub[0x2e] . $sub[0x36] . $sub[0x3e]; // 1c-1f
	$b2h = $sub[0x27] . $sub[0x2f] . $sub[0x37] . $sub[0x3f]; // 38-3b

	if ( ! $inv )
	{
		$upper .= $b1a . $b2a . $b1c . $b2c . $b1e . $b2e . $b1g . $b2g;
		$lower .= $b2b . $b1b . $b2d . $b1d . $b2f . $b1f . $b2h . $b1h;
	}
	else
	{
		$upper .= $b2a . $b1a . $b2c . $b1c . $b2e . $b1e . $b2g . $b1g;
		$lower .= $b1b . $b2b . $b1d . $b2d . $b1f . $b2f . $b1h . $b2h;
	}
	return;
}

function pix4square( &$pix, $ow, $oh )
{
	// SIMPLIFIED
	// superswizz 32x32
	//    0  9 10 19   2  b 12 1b
	//    4  d 14 1d   6  f 16 1f
	//    1  8 11 18   3  a 13 1a
	//    5  c 15 1c   7  e 17 1e
	//
	// superswizz 64x64
	//    0 11 20 31   2 13 22 33   4 15 24 35   6 17 26 37 [h/10]
	//   40 51 60 71  42 53 62 73  44 55 64 75  46 57 66 77
	//    8 19 28 39   a 1b 2a 3b   c 1d 2c 3d   e 1f 2e 3f
	//   48 59 68 79  4a 5b 6a 7b  4c 5d 6c 7d  4e 5f 6e 7f
	//    1 10 21 30   3 12 23 32   5 14 25 34   7 16 27 36
	//   41 50 61 70  43 52 63 72  45 54 65 74  47 56 67 76
	//    9 18 29 38   b 1a 2b 3a   d 1c 2d 3c   f 1e 2f 3e
	//   49 58 69 78  4b 5a 6b 7a  4d 5c 6d 7c  4f 5e 6f 7e
	//   [w/20]
	//
	$zw = $ow / 0x20;
	$zh = $oh / 0x10;
	$pos = 0;
	$inv = false;
	$upper = array();
	$lower = array();
	for ( $zx=0; $zx < $zw; $zx++ )
	{
		for ( $ix=0; $ix < 4; $ix++ )
		{
			for ( $iy=0; $iy < 2; $iy++ )
			{
				for ( $zy=0; $zy < $zh; $zy++ )
				{
					printf("zx[%x] ix[%x] iy[%x] zy[%x] = pos[%x]\n", $zx, $ix, $iy, $zy, $pos);
					$sub = substr($pix, $pos, 0x40);
						$pos += 0x40;

					if ( ! isset( $upper[$ix][$iy][$zy] ) )
						$upper[$ix][$iy][$zy] = '';
					if ( ! isset( $lower[$ix][$iy][$zy] ) )
						$lower[$ix][$iy][$zy] = '';

					unswizz4($sub, $inv, $upper[$ix][$iy][$zy], $lower[$ix][$iy][$zy] );
				} // for ( $zy=0; $zy < $zh; $zy++ )
			} // for ( $iy=0; $iy < 2; $iy++ )

			$inv = ! $inv;
		} // for ( $ix=0; $ix < 4; $ix++ )
	} // for ( $zx=0; $zx < $zw; $zx++ )

	$pix = '';
	for ( $zy=0; $zy < $zh; $zy++ )
	{
		for ( $ix=0; $ix < 4; $ix++ )
		{
			$pix .= $upper[$ix][0][$zy];
			$pix .= $upper[$ix][1][$zy];
			$pix .= $lower[$ix][0][$zy];
			$pix .= $lower[$ix][1][$zy];
		} // for ( $ix=0; $ix < 4; $ix++ )
	} // for ( $zy=0; $zy < $zh; $zy++ )

	// swizzled in 128x128 block
	//  1 2 3    1 4 7
	//  4 5 6 => 2 5 8
	//  7 8 9    3 6 9
	if ( $ow % 0x80 || $oh % 0x80 )
		return;
	$zw = $ow / 0x80;
	$zh = $oh / 0x80;
	$new = $pix;
	for ( $zy=0; $zy < $zh; $zy++ )
	{
		for ( $zx=0; $zx < $zw; $zx++ )
		{
			$zxx = (($zy * $zw) * 0x80 + $zx) * 0x80;
			$dxx = (($zx * $zw) * 0x80 + $zy) * 0x80;
			if ( $zxx == $dxx ) // no change
				continue;

			for ( $y=0; $y < 0x80; $y++ )
			{
				$p = $y * $ow;
				$sub = substr($new, $zxx+$p, 0x80);
				str_update($pix, $dxx+$p, $sub);
			} // for ( $y=0; $y < 0x80; $y++ )

		} // for ( $zx=0; $zx < $zw; $zx++ )
	} // for ( $zy=0; $zy < $zh; $zy++ )

	return;
}

function tm2pix4( &$pix, $ow, $oh )
{
	if ( defined("DRY_RUN") )
		return;

	// $pix is converted from 4-bpp to 8-bpp
	//      in little endian order
	// 12 34 56 78 => 02 01 04 03 06 05 08 07
	//
	// minimum size 128x128 for swizzled image

	// square image = 128x128
	if ( $ow == $oh )
		return pix4square($pix, $ow, $oh);

	// landscape image = 512x128
	// pad zeroes to make it into 512x512 square
	if ( $ow > $oh )
	{
		$pad = ($ow - $oh) * $ow;
		$pix .= str_repeat(ZERO, $pad);
		return pix4square($pix, $ow, $ow);
	}

	// portrait image = 128x512
	// split it into 4 parts of 128x128 square
	if ( $oh > $ow )
	{
		$siz = $ow * $ow;
		$cnt = $oh / $ow;
		$new = '';
		for ( $i=0; $i < $cnt; $i++ )
		{
			$sub = substr($pix, $i*$siz, $siz);
			pix4square($sub, $ow, $ow);
			$new .= $sub;
		}
		$pix = $new;
	}
	return;
}
//////////////////////////////
function tm2pal( &$pal, $swizzle )
{
	if ( defined("DRY_RUN") )
		return;
	if ( ! $swizzle )
		return ps2_alpha2x($pal);

	// swizzled in RGBA blocks
	//  0 1 2 3  4 5 6 7  10 11 12 13  14 15 16 17
	//  8 9 a b  c d e f  18 19 1a 1b  1c 1d 1e 1f
	//  ...
	$new = '';
	for ( $i=0; $i < 0x400; $i += 0x80 )
	{
		$b1 = substr($pal, $i+0x00, 0x20);
		$b2 = substr($pal, $i+0x20, 0x20);
		$b3 = substr($pal, $i+0x40, 0x20);
		$b4 = substr($pal, $i+0x60, 0x20);
		$new .= $b1 . $b3 . $b2 . $b4;
	} // for ( $i=0; $i < 0x400; $i += 0x80 )

	ps2_alpha2x($new);
	$pal = $new;
	return;
}
//////////////////////////////
function sect_fgst( &$file, $pos, $pfx )
{
	printf("== sect_fgst( %x , %s )\n", $pos, $pfx);
	$fgsz = str2int($file, $pos+4, 4);
	$hdsz = str2int($file, $pos+8, 4);

	// SLUS 215.77 , sub_177370
	$ver = str2int($file, $pos+0x10, 4); // lw
	if ( $ver < 0x66 )
		return php_error('Loaded data is not lower version');

	$dp = ord( $file[$pos+0x18] );
	$a0 = ord( $file[$pos+0x19] ); // ???
	if ( $dp == 32 || $dp == 24 || $dp == 16 )
		$v1 = 0;
	else
	if ( $dp == 8 || $dp == 4 )
		$v1 = $a0 >> 3;

	// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
	// TextureSwizzling.pdf
	//   256x128 texture
	//   - 8-bit = swizzle 128x64
	//   - 4-bit = swizzle 128x32
	//   texture size (in bytes) remain the same
	$ow = str2int($file, $pos+0x14, 2);
	$oh = str2int($file, $pos+0x16, 2);
	$zw = str2int($file, $pos+0x2e, 2);
	$zh = str2int($file, $pos+0x30, 2);
	$tm = substr0($file, $pos+0x44);
		$pos += $hdsz;
	printf("%8x , %x[%x]x%x[%x] , %d-bpp , %s\n", $pos, $ow, $zw, $oh, $zh, $dp, $tm);

	$swizzle = ( $zw != 0 || $zh != 0 );
	if ( $swizzle )
		echo "SWIZZLED\n";

	$img = '';
	switch ( $dp )
	{
		case 32:
			$pix = substr($file, $pos+0x400, $ow*$oh*4);
			ps2_alpha2x($pix);

			$img = 'RGBA';
			$img .= chrint($ow, 4);
			$img .= chrint($oh, 4);
			$img .= $pix;
			break;

		case 24:
			return php_warning('24-bpp %s', $pfx);
			break;
		case 16:
			return php_warning('16-bpp %s', $pfx);
			break;

		// if ( $zw*2 == $ow && $zh*2 == $oh )
		case 8:
			$pal = substr($file, $pos, 0x400);
			$pix = substr($file, $pos+0x400, $ow*$oh);
			//save_file("$pfx.pal", $pal);
			//save_file("$pfx.pix", $pix);

			tm2pal($pal, $swizzle);
			if ( $swizzle )
				tm2pix8($pix, $ow, $oh);

			$img = 'CLUT';
			$img .= chrint(0x100, 4);
			$img .= chrint($ow, 4);
			$img .= chrint($oh, 4);
			$img .= $pal;
			$img .= $pix;
			break;

		// if ( $zw*2 == $ow && $zh*4 == $oh )
		case 4:
			$pal = substr($file, $pos, 0x40);
			$pix = substr($file, $pos+0x400, $ow/2*$oh);
			//save_file("$pfx.pal", $pal);
			//save_file("$pfx.pix", $pix);

			bpp4to8($pix);
			//save_file("$pfx.4bpp", $pix);

			if ( $swizzle )
				tm2pix4($pix, $ow, $oh);
			//save_file("$pfx.swz", $pix);

			$img = 'CLUT';
			$img .= chrint(0x10, 4);
			$img .= chrint($ow, 4);
			$img .= chrint($oh, 4);
			$img .= $pal;
			$img .= $pix;
			break;

		default:
			return php_error('Unknown texture depth');
	}

	save_file("$pfx.tm2", $img);
	return;
}

function odin( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FTEX' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$cnt = str2int($file, 12, 4);

	$ed = strlen($file);
	$st = str2int($file, 8, 4);
	$id = 0;
	while ( $st < $ed )
	{
		$mgc = substr($file, $st, 4);
		$fn = sprintf('%s.%d', $pfx, $id);
		switch ( $mgc )
		{
			case "FGST":
				$siz = str2int($file, $st+0x04, 4);
				sect_fgst($file, $st, $fn);

				$st = int_ceil($st+0x80+$siz, 0x10);
				$id++;
				break;
			case "FEOC":
				return;
			default:
				php_error('UNKNOWN mgc @ %x', $st);
				return;
		} // switch ( $mgc )
	} // while ( $st < $ed )
	return;
}

argv_loopfile($argv, 'odin');

/*
4-bpp GrimGrimoire
	 80x40   [un] chara_effect_arranged000.tm2
	400x200  circle_arranged011.tm2
	200x200  circle_arranged021.tm2
	 80x40   [un] cursor_arranged000.tm2
	200x100  effect_arranged011.tm2
	100x80   effect_arranged021.tm2
	200x100  interface_arranged000.tm2
	100x80   opening3_arranged032.tm2
	200x200  font1
	200x200  font2

4-bpp Odin Sphere
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	100x100  on_memory_icon_arranged000.tm2
	100x200  axeknight_icon_arranged000.tm2
	 40x20   [un] axeknight_icon_arranged010.tm2
	 80x80   beehive_icon_arranged000.tm2
	 40x40   [un] beehive_icon_arranged010.tm2
	100x100  berserker_icon_arranged000.tm2
	 80x80   berserker_icon_arranged010.tm2
	 80x40   [un] berserker_icon_arranged020.tm2
	100x100  brigand_icon_arranged000.tm2
	100x100  brigand_icon_arranged010.tm2
	 80x80   brigand_icon_arranged020.tm2
	 40x40   [un] brigand_icon_arranged030.tm2
	100x100  bugbear_icon_arranged000.tm2
	 80x80   bugbear_icon_arranged010.tm2
	 80x80   bugbear_icon_arranged020.tm2
	200x100  darkover_icon_arranged000.tm2
	200x100  dragon_g_icon_arranged000.tm2
	100x100  dragon_g_icon_arranged010.tm2
	100x80   dragon_g_icon_arranged020.tm2
	 80x100  dwarf_icon_arranged000.tm2
	 80x40   [un] dwarf_icon_arranged010.tm2
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	200x100  elfhunter_icon_arranged000.tm2
	200x100  elfknight_icon_arranged000.tm2
	 40x40   [un] filter_arranged000.tm2
	100x80   fin2_arranged070.tm2
	100x80   gargoyle_icon_arranged000.tm2
	200x100  geist_icon_arranged000.tm2
	100x100  ghouls_icon_arranged000.tm2
	 80x100  goblin_icon_arranged000.tm2
	 80x80   goblin_icon_arranged010.tm2
	200x80   griffon_icon_arranged000.tm2
	100x80   griffon_icon_arranged010.tm2
	 80x80   griffon_icon_arranged020.tm2
	 80x80   grizzly_icon_arranged000.tm2
	 40x40   [un] grizzly_icon_arranged010.tm2
	 40x20   [un] grizzly_icon_arranged020.tm2
	200x100  gwendlyn_icon_arranged000.tm2
	100x100  gwendlyn_icon_arranged010.tm2
	100x100  gwendlyn_icon_arranged020.tm2
	200x100  kitchin_arranged011.tm2
	100x100  mage_icon_arranged000.tm2
	100x100  manticora_icon_arranged000.tm2
	 40x40   [un] manticora_icon_arranged010.tm2
	 20x20   [un] mercedes_eneme_arranged011.tm2
	100x200  mercedes_eneme_icon_arranged000.tm2
	 20x20   [un] mercedes_arranged011.tm2
	100x200  mercedes_icon_arranged000.tm2
	 20x20   [un] npc_icon_arranged000.tm2
	200x200  odet_icon_arranged000.tm2
	100x100  on_memory_icon_arranged000.tm2
	100x200  onyx_icon_arranged000.tm2
	100x100  onyx_icon_arranged010.tm2
	100x80   onyx_icon_arranged020.tm2
	200x80   ordyne_icon_arranged000.tm2
	200x80   ordyne_icon_arranged010.tm2
	200x80   ordyne_icon_arranged020.tm2
	100x80   ordyne_icon_arranged030.tm2
	 40x40   [un] ordyne_icon_arranged040.tm2
	200x200  oswald_eneme_icon_arranged000.tm2
	100x100  oswald_eneme_icon_arranged010.tm2
	100x100  oswald_eneme_icon_arranged020.tm2
	200x200  oswald_icon_arranged000.tm2
	100x100  oswald_icon_arranged010.tm2
	100x100  oswald_icon_arranged020.tm2
	 80x80   penitente_icon_arranged000.tm2
	 80x80   penitente_icon_arranged010.tm2
	 40x40   [un] penitente_icon_arranged020.tm2
	200x100  pooka01_icon_arranged000.tm2
	100x100  pooka01_icon_arranged010.tm2
	100x80   pooka01_icon_arranged020.tm2
	 80x100  salamander_elder_icon_arranged000.tm2
	 40x40   [un] salamander_elder_icon_arranged010.tm2
	 80x100  salamander_icon_arranged000.tm2
	 40x40   [un] salamander_icon_arranged010.tm2
	100x100  sorsal_icon_arranged000.tm2
	100x100  troll_icon_arranged000.tm2
	 80x80   trolls_icon_arranged000.tm2
	 40x40   [un] trolls_icon_arranged010.tm2
	100x100  unicornknight_icon_arranged000.tm2
	 80x80   unicornknight_icon_arranged010.tm2
	 80x80   unicornknight_icon_arranged020.tm2
	200x100  valkyie_another_icon_arranged000.tm2
	100x100  valkyie_cheapedition_icon_arranged000.tm2
	100x100  valkyie_cheapedition_icon_arranged010.tm2
	 40x20   [un] valkyie_cheapedition_icon_arranged020.tm2
	100x100  valkyie_icon_arranged000.tm2
	100x100  valkyie_icon_arranged010.tm2
	 40x20   [un] valkyie_icon_arranged020.tm2
	 20x20   [un] velbet_eneme_arranged011.tm2
	200x200  velbet_eneme_icon_arranged000.tm2
	 20x20   [un] velbet_arranged011.tm2
	200x200  velbet_icon_arranged000.tm2
	 20x20   [un] vender_icon_arranged000.tm2
	 80x80   volcane_icon_arranged000.tm2
	200x100  vulcan00_icon_arranged000.tm2
	200x200  wagner_icon_arranged000.tm2
	100x100  warrior_icon_arranged000.tm2
	 80x80   warrior_icon_arranged010.tm2
	 80x80   warrior_icon_arranged020.tm2
	 80x80   wizerdeye_icon_arranged000.tm2
	200x200  wraith_icon_arranged000.tm2
	 40x20   [un] wraith_icon_arranged010.tm2
	100x100  font1
	400x400  font2
*/
