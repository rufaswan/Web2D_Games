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
declare( strict_types=1 );
//////////////////////////////
class psx
{
	public static function bpp4to8( string &$pix ) : string
	{
		$new = '';
		$len = strlen($pix);
		for ( $i=0; $i < $len; $i++ )
		{
			$b = ord( $pix[$i] );
			$b1 = ($b >> 0) & BIT4;
			$b2 = ($b >> 4) & BIT4;
			$new .= chr($b1) . chr($b2);
		}
		return $new;
	}

	public static function rgb555( string $str ) : string
	{
		if ( ! isset($str[1]) )
			return '';

		// 15-bit ABGR
		// RGB = R/c6       G/52       B/52
		//     = 11-- -11-  -1-1 --1-  -1-1 --1-
		//     = 11---      -1-1-      -1-1-
		// 15-bit = -(A) -1-1-(B) -1-1-(G) 11---(R)
		//        = --1- 1--1 -1-1 1---
		//        = 2958 = 58 29
		$pal = tool::ord($str);

		// fedc ba98 7654 3210
		// abbb bbgg gggr rrrr
		$b = ($pal >> 7) & 0xf8; // >> 10 << 3
		$g = ($pal >> 2) & 0xf8; // >>  5 << 3
		$r = ($pal << 3) & 0xf8; // >>  0 << 3

		$str = chr($r) . chr($g) . chr($b) . BYTE;
		return $str;
	}

	public static function pal555( string $str, int $bgzero=-1 ) : string
	{
		$pal = '';
		$siz = strlen($str) & ~1; // floor to 2s
		for ( $i=0; $i < $siz; $i += 2 )
		{
			$rgb = self::rgb555($str[$i+0] . $str[$i+1]);
			if ( ($i>>1) === $bgzero )
				$rgb[3] = ZERO;
			$pal .= $rgb;
		}
		return $pal;
	}
	//////////////////////////////
	public static function tim( string &$file, int $pos=0, bool $check=true ) : array
	{
		$bak = $pos;

		// TIM header
		//   0 = 4-bpp grayscale
		//   1 = 8-bpp grayscale
		//   2 = rgb555 true color
		//   3 = rgb888 true color
		//   8 = 4-bpp pseudo-color
		//   9 = 8-bpp pseudo-color
		$type = tool::ordstr($file, $pos+4, 1);
			$pos += 8;

		$pix = '';
		$pal = '';
		$cc  = 0;
		$w   = 0;
		$h   = 0;
		switch ( $type )
		{
			//////////////////////////////
			case 8:
				// VERY COMMON
				// palette header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$cc  = tool::ordstr($file, $pos+ 8, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;
				$pal = self::pal555($sub);
				if ( $cc > 16 )
					$cc = 16;
			case 0:
				// pixel header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$w   = tool::ordstr($file, $pos+ 8, 2) * 4;
				$h   = tool::ordstr($file, $pos+10, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;

				self::bpp4to8($sub);
				$pix = $sub;
				if ( $cc === 0 )
					$cc = 0x10;
				$type = 'CLUT';
				break;
			//////////////////////////////
			case 9:
				// VERY COMMON
				// palette header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$cc  = tool::ordstr($file, $pos+ 8, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;
				$pal = self::pal555($sub);
				if ( $cc > 256 )
					$cc = 256;
			case 1:
				// TokiMemo 2 substories *.tml
				// pixel header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$w   = tool::ordstr($file, $pos+ 8, 2) * 2;
				$h   = tool::ordstr($file, $pos+10, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;

				$pix = $sub;
				if ( $cc === 0 )
					$cc = 0x100;
				$type = 'CLUT';
				break;
			//////////////////////////////
			case 2:
				// Legend of Mana /ana/etc_etc/title00.dat/1.tim
				// Xenogears 2464/*.tim
				// Lunar 2 Eternal Blue data/isok/*.tim
				// pixel header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$w   = tool::ordstr($file, $pos+ 8, 2);
				$h   = tool::ordstr($file, $pos+10, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;

				$pix = self::pal555($sub);
				$type = 'RGBA';
				break;
			case 3:
				// Legend of Mana /ana/zukan_p/z00/zukan1p.dat
				// pixel header
				$siz = tool::ordstr($file, $pos+ 0, 4);
				$w   = tool::ordstr($file, $pos+ 8, 2);
				$h   = tool::ordstr($file, $pos+10, 2);
				$sub = tool::substr ($file, $pos+12, $siz-12);
					$pos += $siz;

				$siz -= 12;
				for ( $i=0; $i < $siz; $i += 3 )
					$pix .= $sub[$i+0] . $sub[$i+1] . $sub[$i+2] . BYTE;
				$type = 'RGBA';
				break;
			//////////////////////////////
			default:
				tool::warning('unknown type', $type);
				return [];
		} // switch ( $type )

		// VRAM size check = 400 x 200
		//   for false positive
		tool::trace(__FUNCTION__, $type, 'img', $w, $h, $pal, $pix);
		if ( $check )
		{
			if ( $w > 0x400 || $w < 1 )  return null;
			if ( $h > 0x200 || $h < 1 )  return null;
		}

		$tim = [
			'w'   => $w,
			'h'   => $h,
			'pix' => $pix,
			'siz' => $pos - $bak,
			't'   => $type,
		];

		if ( $cc > 0 )
		{
			$tim['pal'] = $pal;
			$tim['cc' ] = $cc;
		}
		return $tim;
	}

	public static function blend( int $f=100, int $b=100 ) : array
	{
		// (SRC * f) + (DST * b)
		$blend = [
			'name'  => sprintf('psx blend = (SRC * %d%%) + (DST * %d%%)', $f, $b),
			'mode_rgb'   => 0,
			'mode_alpha' => ['FUNC_ADD' , 'ONE' , 'ONE'],
		];
		tool::trace($blend['name']);

		$mode = sprintf('%d,%d', $f, $b);
		switch ( $mode )
		{
			case '50,50': // 0.5 , 0.5 / average
				$blend['mode_rgb'] = ['FUNC_ADD' , 'CONSTANT_ALPHA' , 'CONSTANT_ALPHA'];
				$blend['color']    = '#ffffff80';
				return $blend;
			case '100,100': // 1.0 , 1.0 / addition
				$blend['mode_rgb'] = ['FUNC_ADD' , 'ONE' , 'ONE'];
				return $blend;
			case '25,100': // 0.25 , 1.0 / partial addition
				$blend['mode_rgb'] = ['FUNC_ADD' , 'CONSTANT_ALPHA' , 'ONE'];
				$blend['color']    = '#ffffff40';
				return $blend;
			case '-100,100': // -1.0 , 1.0 / subtraction
				$blend['mode_rgb'] = ['FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE'];
				return $blend;
		} // switch ( $mode )

		tool::warning('unknown mode', $mode);
		return [];
	}
} // class psx
//////////////////////////////
/*
 * Special Thanks
 *   ezSwizzle @ PS2Linux
 *   http://ps2linux.no-ip.info/playstation2-linux.com/project/ezSwizzle
 *     Victor Suba
 *     Lionel Lemarie
 */
class ps2
{
	public static function alpha2x( string &$pal ) : int
	{
		$len = strlen($pal);
		for ( $i=0; $i < $len; $i += 4 )
		{
			$a = ord( $pal[$i+3] );
			$a = tool::int_clamp($a*2, 0, BIT8);
			$pal[$i+3] = chr($a);
		}
		return 0;
	}
	//////////////////////////////
	public static function unswizz8( string &$sub, bool $inv, string &$upper, string &$lower ) : void
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
	}

	public static function pix8( string &$pix, int $ow, int $oh ) : void
	{
		// minimum size 128x128 for swizzled image
		$new = '';
		$inv = false;
		for ( $y=0; $y < $oh; $y += 4 )
		{
			$sub = tool::substr($pix, $y*$ow, $ow*4);

			$upper = '';
			$lower = '';
			$len = strlen($sub);
			for ( $i=0; $i < $len; $i += 0x20 )
			{
				$str = tool::substr($sub, $i, 0x20);
				self::unswizz8($str, $inv, $upper, $lower);
			} // for ( $i=0; $i < $len; $i += 0x20 )

			$new .= $upper . $lower;
			$inv = ! $inv;
		} // for ( $y=0; $y < $oh; $y += 4 )

		$pix = $new;
	}
	//////////////////////////////
	public static function unswizz4( string &$sub, bool $inv, string &$upper, string &$lower ) : void
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
	}

	public static function pix4square( string &$pix, int $ow, int $oh ) : int
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
		$upper = [];
		$lower = [];
		for ( $zx=0; $zx < $zw; $zx++ )
		{
			for ( $ix=0; $ix < 4; $ix++ )
			{
				for ( $iy=0; $iy < 2; $iy++ )
				{
					for ( $zy=0; $zy < $zh; $zy++ )
					{
						//printf("zx[%x] ix[%x] iy[%x] zy[%x] = pos[%x]\n", $zx, $ix, $iy, $zy, $pos);
						$sub = substr($pix, $pos, 0x40);
							$pos += 0x40;

						if ( ! isset( $upper[$ix][$iy][$zy] ) )
							$upper[$ix][$iy][$zy] = '';
						if ( ! isset( $lower[$ix][$iy][$zy] ) )
							$lower[$ix][$iy][$zy] = '';

						self::unswizz4($sub, $inv, $upper[$ix][$iy][$zy], $lower[$ix][$iy][$zy] );
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
		if ( $ow & 0x7f || $oh & 0x7f )
			return 0;
		$zw = $ow >> 7; // div 0x80
		$zh = $oh >> 7; // div 0x80
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
					$sub = tool::substr($new, $zxx+$p, 0x80);
					tool::str_update($pix, $dxx+$p, $sub);
				} // for ( $y=0; $y < 0x80; $y++ )

			} // for ( $zx=0; $zx < $zw; $zx++ )
		} // for ( $zy=0; $zy < $zh; $zy++ )
		return 0;
	}

	public static function pix4( string &$pix, int $ow, int $oh ) : int
	{
		// $pix is converted from 4-bpp to 8-bpp
		//      in little endian order
		// 12 34 56 78 => 02 01 04 03 06 05 08 07
		//
		// minimum size 128x128 for swizzled image

		// square image = 128x128
		if ( $ow == $oh )
			return self::pix4square($pix, $ow, $oh);

		// landscape image = 512x128
		// pad zeroes to make it into 512x512 square
		if ( $ow > $oh )
		{
			$pad = ($ow * $ow) - ($ow * $oh);
			$pix .= str_repeat(ZERO, $pad);
			self::pix4square($pix, $ow, $ow);

			$pix = tool::substr($pix, 0, $ow*$oh);
			return 0;
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
				$sub = tool::substr($pix, $i*$siz, $siz);
				self::pix4square($sub, $ow, $ow);
				$new .= $sub;
			}
			$pix = $new;
		}
		return 0;
	}
	//////////////////////////////
	public static function pal( string &$pal, bool $swizzle ) : int
	{
		if ( ! $swizzle )
			return self::alpha2x($pal);

		// swizzled in RGBA blocks
		//  0 1 2 3  4 5 6 7  10 11 12 13  14 15 16 17
		//  8 9 a b  c d e f  18 19 1a 1b  1c 1d 1e 1f
		//  ...
		$new = '';
		for ( $i=0; $i < 0x400; $i += 0x80 )
		{
			$b1 = tool::substr($pal, $i+0x00, 0x20);
			$b2 = tool::substr($pal, $i+0x20, 0x20);
			$b3 = tool::substr($pal, $i+0x40, 0x20);
			$b4 = tool::substr($pal, $i+0x60, 0x20);
			$new .= $b1 . $b3 . $b2 . $b4;
		} // for ( $i=0; $i < 0x400; $i += 0x80 )

		self::alpha2x($new);
		$pal = $new;
		return 0;
	}

	public static function blend( string $a, string $b, string $c, string $d ) : array
	{
		// (A - B) * C + D
		// ABCD are 2 bits
		//   0=SRC  1=DST  2=0  3=reserved
		// ABD are color , C is alpha
		$blend = [
			'name' => sprintf('ps2 blend = (%s.rgb - %s.rgb) * %s.a + %s.rgb', $a, $b, $c, $d),
			'mode_rgb'   => 0,
			'mode_alpha' => ['FUNC_ADD' , 'ONE' , 'ONE'],
		];
		tool::trace($blend['name']);

		$mode = "$a,$b,$c,$d";
		switch ( $mode )
		{
			case 'SRC,DST,SRC,DST':
				// ((SRC.rgb - DST.rgb) * SRC.a) + DST.rgb
				// = SRC.rgb * SRC.a + (-DST.rgb * SRC.a + DST.rgb)
				// = SRC.rgb * SRC.a + DST.rgb * (-1 * SRC.a + 1)
				// = SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)
				$blend['mode_rgb'] = ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'];
				return $blend;
			case 'SRC,0,SRC,DST':
				// ((SRC.rgb - 0) * SRC.a) + DST.rgb
				$blend['mode_rgb'] = ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE'];
				return $blend;
			case '0,SRC,SRC,DST':
				// ((0 - SRC.rgb) * SRC.a) + DST.rgb
				$blend['mode_rgb'] = ['FUNC_REVERSE_SUBTRACT' , 'SRC_ALPHA' , 'ONE'];
				return $blend;
			case 'SRC,DST,DST,DST':
				// ((SRC.rgb - DST.rgb) * DST.a) + DST.rgb
				// = SRC.rgb * DST.a + (-DST.rgb * DST.a + DST.rgb)
				// = SRC.rgb * DST.a + DST.rgb * (-1 * DST.a + 1)
				// = SRC.rgb * DST.a + DST.rgb * (1 - DST.a)
				$blend['mode_rgb'] = ['FUNC_ADD' , 'DST_ALPHA' , 'ONE_MINUS_DST_ALPHA'];
				return $blend;
			case 'SRC,0,DST,DST':
				// ((SRC.rgb - 0) * DST.a) + DST.rgb
				$blend['mode_rgb'] = ['FUNC_ADD' , 'DST_ALPHA' , 'ONE'];
				return $blend;
			case '0,SRC,DST,DST':
				// ((0 - SRC.rgb) * DST.a) + DST.rgb
				$blend['mode_rgb'] = ['FUNC_REVERSE_SUBTRACT' , 'DST_ALPHA' , 'ONE'];
				return $blend;
		} // switch ( $mode )

		tool::warning('unknown mode', $mode);
		return [];
	}
} // class ps2
//////////////////////////////
class saturn
{
	public static function rgb555( string $str ) : string
	{
		$b = $str[1] . $str[0];
		return psx::rgb555($b);
	}

	public static function pal555( string $str, int $bgzero=-1 ) : string
	{
		$pal = '';
		$siz = strlen($str) & ~1; // floor to 2s
		for ( $i=0; $i < $siz; $i += 2 )
		{
			$rgb = psx::rgb555($str[$i+1] . $str[$i+0]);
			if ( ($i>>1) === $bgzero )
				$rgb[3] = ZERO;
			$pal .= $rgb;
		}
		return $pal;
	}
} // class saturn
//////////////////////////////
class wii
{
	public static function wii_blend_mode( string $a, string $b, string $c, string $d ) : array
	{
		// D +/- ( (1 - C) * A + (C * B) )
		$blend = [
			'name' => sprintf('wii blend = %s + ( (1 - %s) * %s + (%s * %s) )', $d, $c, $a, $c, $b),
			'mode_rgb'   => 0,
			'mode_alpha' => ['FUNC_ADD' , 'ZERO' , 'ONE'],
		];
		tool::trace($blend['name']);

		$mode = "$a,$b,$c,$d";
		switch ( $mode )
		{
			case 'SRC.rgb,0,0,DST.rgb': // addition = D + A
				// rgb = SRC.rgb + DST.rgb
				// a   = DST.a
				$blend['mode_rgb'] = ['FUNC_ADD' , 'ONE' , 'ONE'];
				return $blend;
			case '-SRC.rgb,0,0,DST.rgb': // subtraction = D - A
				// rgb = SRC.rgb * -1 + DST.rgb
				// a   = DST.a
				$blend['mode_rgb'] = ['FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE'];
				return $blend;
			case '0,SRC.rgb,DST.rgb,0': // multiplication = C * B
				// rgb = SRC.rgb * DST.rgb
				// a   = DST.a
				$blend['mode_rgb'] = ['FUNC_ADD' , 'DST_COLOR' , 'ZERO'];
				return $blend;
			case '0,SRC.rgb,SRC.a,DST.rgb': // addition and multiplication = D + C * B
				// rgb = SRC.rgb * SRC.a + DST.rgb
				// a   = DST.a
				$blend['mode_rgb'] = ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE'];
				return $blend;
			case 'DST.rgb,SRC.rgb,SRC.a,0': // decal = (1 - C) * A + (C * B)
				// rgb = SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)
				// a   = DST.a
				$blend['mode_rgb'] = ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'];
				return $blend;
			case 'SRC,0,0,DST': // proportional
				// rgba = SRC * (1 - k) + DST * k
				$blend['mode_rgb']   = ['FUNC_ADD' , 'ONE_MINUS_CONSTANT_COLOR' , 'CONSTANT_COLOR'];
				$blend['mode_alpha'] = ['FUNC_ADD' , 'ONE_MINUS_CONSTANT_ALPHA' , 'CONSTANT_ALPHA'];
				$blend['color']      = '#ffffffff';
				return $blend;
		} // switch ( $mode )

		tool::warning('unknown mode', $mode);
		return [];
	}
} // class wii
//////////////////////////////
