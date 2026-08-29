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
 *   Tegra X1 block linear swizzling algorithm
 *   https://github.com/ScanMountGoat/tegra_swizzle/blob/2c0b15f887278258f9cabecd9560b81730512cd7/tegra_swizzle/src/swizzle.rs
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-bptc');
tool::require('class-s3tc');
tool::require('class-clutfile');
tool::require('func-hexnum');

//define('DRY_RUN', true);

// Tegra TRM page 1188
// gob_offset
//     ((x % 64) / 32) * 256  | ((x & 3f) >> 5) << 8 = --x. .... << 3 = ---x .... .---
//   + ((y %  8) /  2) * 64   | ((y &  7) >> 1) << 6 = ---- -yy. << 5 = ---- yy.- ----
//   + ((x % 32) / 16) * 32   | ((x & 1f) >> 4) << 5 = ---x .... << 1 = ---- --x. ...-
//   +  (y %  2) * 16         |  (y &  1)       << 4 = ---- ---y << 4 = ---- ---y ----
//   +  (x % 16)              |  (x &  f)                             = ---- ---- xxxx
//
// pattern                 bc4               bc7
// = ---x yyxy xxxx        = --xy yxyx       = ---x yyxy
// x/---1 --1- 1111 = 12f  x/--1- -1-1 = 25  x/---1 --1- = 12
// y/---- 11-1 ---- =  d0  y/---1 1-1- = 1a  y/---- 11-1 =  d
//
// gob = 200 byte
//             bc4  bc7
//    1   1x1  -    -    *start
//    2   2x1  -    -    right
//    4   4x1  -    -    right
//    8   8x1  1x1  -    right
//   10  10x1  2x1  1x1  right
//   20  10x2  2x2  1x2  down
//   40  20x2  4x2  2x2  right
//   80  20x4  4x4  2x4  down
//  100  20x8  4x8  2x8  down
//  200  40x8  8x8  4x8  right
//

function tegra_x1_swizzled_8_bits( string &$pix, int $bw, int $bh, array $draw ) : void
{
	if ( ($bw & 7) || ($bh & 7) )
		tool::error('tegra not in 8x8 tile', $bw, $bh);

	// 1 pixel      = 4*4 bc tile
	// bc4          =  8 byte
	// in 8*8 pixel = 40 * 8 = 200 byte
	// unswizzled tiles
	//   0  1  4  5 20 21 24 25
	//   2  3  6  7 22 23 26 27
	//   8  9  c  d 28 29 2c 2d
	//   a  b  e  f 2a 2b 2e 2f
	//  10 11 14 15 30 31 34 35
	//  12 13 16 17 32 33 36 37
	//  18 19 1c 1d 38 39 3c 3d
	//  1a 1b 1e 1f 3a 3b 3e 3f
	// bc4 pattern = --xy yxyx
	//             x/--1- -1-1 = 25
	//             y/---1 1-1- = 1a
	tool::trace(__FUNCTION__, $bw, $bh);
	$map = [];

	// block_height = 10 , y += (10 * 8)
	$blk = 8 * 8;
	$p   = 0;
	for ( $sy=0; $sy < $bh; $sy += 0x80 )
	{
		for ( $x=0; $x < $bw; $x += 8 )
		{
			for ( $y=0; $y < 0x80; $y += 8 )
			{
				if ( ($sy+$y) >= $bh )
					continue;
				// DO NOT increase $p when over $bh
				for ( $i=0; $i < $blk; $i++ )
				{
					list($bx,$by) = hexnum::swizzle_id2xy($i, 0x25, 0x1a);
						$bx +=  $x;
						$by += ($y + $sy);
					$id = ($by * $bw) + $bx;
					$map[$id] = $p + $i;
				}
				$p += $blk;
			} // for ( $y=0; $y < 0x80; $y += 8 )
		} // for ( $x=0; $x < $bw; $x += 8 )
	} // for ( $sy=0; $sy < $bh; $sy += 0x80 )

	//hexnum::swizzle_map($map, $bw, $bh, true);
	hexnum::swizzle_map($map, $bw, $bh);
	$pix = $draw($pix, $bw, $bh, $map);
}

function tegra_x1_swizzled_16_bits( string &$pix, int $bw, int $bh, array $draw ) : void
{
	if ( ($bw & 3) || ($bh & 7) )
		tool::error('tegra not in 4x8 tile', $bw, $bh);

	// 1 pixel      = 4*4 bc tile
	// bc7          = 10 byte
	// in 4*8 pixel = 20 * 10 = 200 byte
	// unswizzled tiles
	//   0  2 10 12
	//   1  3 11 13
	//   4  6 14 16
	//   5  7 15 17
	//   8  a 18 1a
	//   9  b 19 1b
	//   c  e 1c 1e
	//   d  f 1d 1f
	// bc7 pattern = ---x yyxy
	//             x/---1 --1- = 12
	//             y/---- 11-1 =  d
	tool::trace(__FUNCTION__, $bw, $bh);
	$map = [];

	// block_height = 10 , y += (10 * 8)
	$blk = 4 * 8;
	$p   = 0;
	for ( $sy=0; $sy < $bh; $sy += 0x80 )
	{
		for ( $x=0; $x < $bw; $x += 4 )
		{
			for ( $y=0; $y < 0x80; $y += 8 )
			{
				if ( ($sy+$y) >= $bh )
					continue;
				// DO NOT increase $p when over $bh
				for ( $i=0; $i < $blk; $i++ )
				{
					list($bx,$by) = hexnum::swizzle_id2xy($i, 0x12, 0xd);
						$bx +=  $x;
						$by += ($y + $sy);
					$id = ($by * $bw) + $bx;
					$map[$id] = $p + $i;
				}
				$p += $blk;
			} // for ( $y=0; $y < 0x80; $y += 8 )
		} // for ( $x=0; $x < $bw; $x += 4 )
	} // for ( $sy=0; $sy < $bh; $sy += 0x80 )

	//hexnum::swizzle_map($map, $bw, $bh, true);
	hexnum::swizzle_map($map, $bw, $bh);
	$pix = $draw($pix, $bw, $bh, $map);
}
//////////////////////////////
function im_bc3( string &$file, int $pos, int $w, int $h, int $size ) : clutdata
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $size);

	$bc3 = new s3tc_Texture;
	$pix = $bc3->bc3($pix);
	//$pix = $bc3->s3tc_draw($pix, $w>>2, $h>>2);

	$powh = tool::int_ceil_pow2($h);
	tegra_x1_swizzled_16_bits($pix, $w>>2, $powh>>2, [$bc3,'s3tc_draw']);
	if ( $powh !== $h )
		$pix = tool::substr($pix, 0, $w*$h*4);
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pix = $pix;
	return $img;
}

function im_bc4( string &$file, int $pos, int $w, int $h, int $size ) : clutdata
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $size);

	$bc4 = new s3tc_Texture;
	$pix = $bc4->bc4($pix);
	//$pix = $bc4->bc4_draw($pix, $w>>2, $h>>2);

	$powh = tool::int_ceil_pow2($h);
	tegra_x1_swizzled_8_bits($pix, $w>>2, $powh>>2, [$bc4,'bc4_draw']);
	if ( $powh !== $h )
		$pix = tool::substr($pix, 0, $w*$h);
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pal = clutfile::graypal(0x100);
		$img->pix = $pix;
	return $img;
}

function im_bc7( string &$file, int $pos, int $w, int $h, int $size ) : clutdata
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $size);

	$bc7 = new bptc_texture;
	$pix = $bc7->bc7($pix);
	//$pix = $bc7->bptc_draw($pix, $w>>2, $h>>2);

	// Supporter00.ftx   c0 180 ->  c0 200
	// for_Minimap.ftx  780 438 -> 780 800
	$powh = tool::int_ceil_pow2($h);
	tegra_x1_swizzled_16_bits($pix, $w>>2, $powh>>2, [$bc7,'bptc_draw']);
	if ( $powh !== $h )
		$pix = tool::substr($pix, 0, $w*$h*4);
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pix = $pix;
	return $img;
}
//////////////////////////////
function unicorn_nvt( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file,0,4) != '.tex' )
		return false;

	$fmt = tool::ordstr($file, 0x04, 2);
	$w   = tool::ordstr($file, 0x0c, 4);
	$h   = tool::ordstr($file, 0x10, 4);
	$sz1 = tool::ordstr($file, 0x1c, 4);
	$sz2 = tool::ordstr($file, 0x20, 4);

	$list_fmt = [
		0x44 => 'im_bc3',
		0x49 => 'im_bc4',
		0x4d => 'im_bc7',
	];
	if ( ! isset($list_fmt[$fmt]) )
		tool::error('UNKNOWN im fmt', $fmt);
	tool::trace('DETECT fmt', $list_fmt[$fmt], $w, $h);

	if ( defined('DRY_RUN') )
		return true;

	$fn = sprintf('%s.nvt', $fname);
	tool::trace('nvt size', $w, $h, $fn);

	$func = $list_fmt[$fmt];
	$img = $func($file, $sz1, $w, $h, $sz2);
	clutfile::save($fn, $img);
	return true;
}

function unicorn_ftex( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 4) !== 'FTEX' )
		return false;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = tool::ordstr($file,  8, 4);
	$cnt  = tool::ordstr($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = tool::substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) !== 'FTX0' )
			tool::error('not FTX0', $fname, $st);

		$sz1 = tool::ordstr($file, $st+4, 4);
		$sz2 = tool::ordstr($file, $st+8, 4);
		tool::trace('NVT', $st, $sz1, $fn);

		$sub = tool::substr($file, $st+$sz2, $sz1);
		unicorn_nvt($sub, "$pfx.$i");

		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function unicorn( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = unicorn_ftex($file, $fname);
	if ( $r )  return;

	$r = unicorn_nvt($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'unicorn');

/*
13 sentinels
	44  im_bc3
	49  im_bc4
	4d  im_bc7
grim grimoire once again
	49  im_bc4
	4d  im_bc7
unicorn overlord
	49  im_bc4
	4d  im_bc7

13 sentinels
	44  im_bc3
		SecretFile_000.ftx
		SecretFile_001.ftx
	49  im_bc4
		FontBt.ftx
		FontDigi.ftx
		Font.ftx

grim grimoire once more
	49  im_bc4
		Font.ftx
		Font_Spell.ftx

unicorn overlord
	49  im_bc4
		AiramFontB.ftx
		AiramFont.ftx
		AlbertusNovaFontB.ftx
		AlbertusNovaFont.ftx
		AlbertusNovaFont_LB.ftx
		AlbertusNovaFont_L.ftx
		CongenialFontB.ftx
		CongenialFont.ftx
		KiaroFontB.ftx
		KiaroFont.ftx
		KiaroFont_LB.ftx
		KiaroFont_L.ftx
		KleeFontB.ftx
		KleeFont.ftx
		ManofaFontB.ftx
		ManofaFont.ftx
		MsgFont_CNB.ftx
		MsgFont_CN.ftx
		MsgFont_KOB.ftx
		MsgFont_KO.ftx
		MsgFont_TWB.ftx
		MsgFont_TW.ftx
		SwUserNameFont.ftx
		SysFont_CNB.ftx
		SysFont_CN.ftx
		SysFont_CN_LB.ftx
		SysFont_CN_L.ftx
		SysFont_KOB.ftx
		SysFont_KO.ftx
		SysFont_KO_LB.ftx
		SysFont_KO_L.ftx
		SysFont_TWB.ftx
		SysFont_TW.ftx
		SysFont_TW_LB.ftx
		SysFont_TW_L.ftx
		UnicornFontB.ftx
		UnicornFont.ftx
 */
