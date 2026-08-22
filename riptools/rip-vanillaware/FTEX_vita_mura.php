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
 *   http://playstationdev.wiki/psvitadevwiki/index.php?title=GXT
 *   http://forum.xentax.com/viewtopic.php?f=18&t=16171&sid=00e26d4f119d2985bbc8137c42e3a10d
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-s3tc');
tool::require('class-clutfile');
tool::require('func-hexnum');

//define('DRY_RUN', true);

function dxt_swizzled( string &$pix, int $bw, int $bh, array $draw ) : void
{
	// 1 pixel = 4*4 bc tile
	// unswizzled tiles
	//   0 2  8  a
	//   1 3  9  b
	//   4 6  c  e
	//   5 7  d  f
	// bitmask
	//  1x1  *start
	//  1x2  down
	//  2x2  right
	//  2x4  down
	//  4x4  right
	//  4x8  down
	//  8x8  right
	// pattern = rdrd rdrd
	//         x/1-1- 1-1- = aa
	//         y/-1-1 -1-1 = 55
	tool::trace(__FUNCTION__, $bw, $bh);
	$map = [];
	$min = ( $bw < $bh ) ? $bw : $bh;

	$ttl = $bw  * $bh;
	$blk = $min * $min;
	$p   = 0;
	for ( $y=0; $y < $bh; $y += $min )
	{
		for ( $x=0; $x < $bw; $x += $min )
		{
			for ( $i=0; $i < $blk; $i++ )
			{
				list($bx,$by) = hexnum::swizzle_id2xy($i, 0xaaaaaa, 0x555555);
					$bx += $x;
					$by += $y;
				$id = ($by * $bw) + $bx;
				$map[$id] = $p + $i;
			}
			$p += $blk;
		} // for ( $x=0; $x < $bw; $x += $min )
	} // for ( $y=0; $y < $bh; $y += 32 )

	//hexnum::swizzle_map($map, $bw, $bh, true);
	hexnum::swizzle_map($map, $bw, $bh);
	$pix = $draw($pix, $bw, $bh, $map);
}
//////////////////////////////
function im_dxt1( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);

	$s3   = new s3tc_texture;
	$pix  = $s3->dxt1($pix);
	$draw = [$s3, 's3tc_draw'];
	//$pix  = $draw($pix, $w>>2, $h>>2);

	dxt_swizzled($pix, $w>>2, $h>>2, $draw);
	return $pix;
}

function im_dxt3( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);

	$s3   = new s3tc_Texture;
	$pix  = $s3->dxt3($pix);
	$draw = [$s3, 's3tc_draw'];
	//$pix  = $draw($pix, $w>>2, $h>>2);

	dxt_swizzled($pix, $w>>2, $h>>2, $draw);
	return $pix;
}

function im_dxt5( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);

	$s3   = new s3tc_texture;
	$pix  = $s3->dxt5($pix);
	$draw = [$s3, 's3tc_draw'];
	//$pix  = $draw($pix, $w>>2, $h>>2);

	dxt_swizzled($pix, $w>>2, $h>>2, $draw);
	return $pix;
}
//////////////////////////////
function bgra_swizzled( string &$pix, int $ow, int $oh ) : void
{
	tool::trace(__FUNCTION__, $ow, $oh);

	// unswizzle pixels
	//   0 2
	//   1 3
	// bitmask
	//          0 -> 1  = down
	//         01 -> 23 = right
	// pattern = rdrd rdrd
	//         = x/aa  y/55
	$dec = $pix;
	$pos = 0;
	$min = min($ow, $oh);

	for ( $y=0; $y < $oh; $y += $min )
	{
		for ( $x=0; $x < $ow; $x += $min )
		{
			$blk = $min * $min;
			for ( $i=0; $i < $blk; $i++ )
			{
				list($sx,$sy) = hexnum::swizzle_id2xy($i, 0xaaaaaa, 0x555555);
				$dyy = ($y  + $sy) * $ow;
				$dxx = $dyy + $x + $sx;
				$s = tool::substr($pix, $pos, 4); // 1 RGBA pixel
						$pos += 4;
				tool::str_update($dec, $dxx*4, $s);
			}
		} // for ( $x=0; $x < $ow; $x += $min )
	} // for ( $y=0; $y < $oh; $y += 32 )

	$pix = $dec;
}
//////////////////////////////
function im_bgra8888( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);

	$pix = '';
	$siz = $w * $h;
	for ( $i=0; $i < $siz; $i++ )
	{
		$pix .= $file[$pos+2]; // r
		$pix .= $file[$pos+1]; // g
		$pix .= $file[$pos+0]; // b
		$pix .= $file[$pos+3]; // a
			$pos += 4;
	} // for ( $i=0; $i < $siz; $i++ )

	bgra_swizzled($pix, $w, $h);
	return $pix;
}
//////////////////////////////
function mura_gxt( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 4) !== 'GXT'.ZERO )
		return false;

	$cnt = tool::ordstr($file, 8, 4);
	if ( $cnt != 1 )
		tool::error('is multi-GXT', $fname, $cnt);

	$fmt = substr($file, 0x34, 4);
	$w   = tool::ordstr($file, 0x38, 2);
	$h   = tool::ordstr($file, 0x3a, 2);
		$w = tool::int_ceil_pow2($w);
		$h = tool::int_ceil_pow2($h);

	$list_fmt = [
		"\x00\x10\x00\x0c" => 'im_bgra8888',
		"\x00\x00\x00\x85" => 'im_dxt1',
		"\x00\x00\x00\x86" => 'im_dxt3',
		"\x00\x00\x00\x87" => 'im_dxt5',
	];
	if ( ! isset( $list_fmt[$fmt] ) )
		tool::error('UNKNOWN im fmt', bin2hex($fmt));
	tool::trace('DETECT  fmt', $list_fmt[$fmt]);

	$off = tool::ordstr($file, 0x20, 4);
	$siz = tool::ordstr($file, 0x24, 4);
	$fn  = sprintf('%s.gxt', $fname);
	tool::trace('gxt size', $w, $h, $fn);

	if ( defined('DRY_RUN') )
		return true;

	$fmt = $list_fmt[$fmt];
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pix = $fmt($file, $off, $w, $h);
	clutfile::save($fn, $img);
	return true;
}
//////////////////////////////
function mura_ftex( string &$file, string $fname ) : bool
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
		tool::trace('GXT', $st, $sz1, $fn);

		$sub = tool::substr($file, $st+$sz2, $sz1);
		mura_gxt($sub, "$pfx.$i");

		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function mura( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = mura_ftex($file, $fname);
	if ( $r )  return;

	$r = mura_gxt($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'mura');

/*
mura
	app
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	patch
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 1
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 2
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 3
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 4
		-- -- -- --  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
dcrown
	app
		-- -- -- --  gxt_swizzled
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
	patch
		-- -- -- --  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
odin
	bt
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	or
		-- -- -- --  gxt_swizzled
		-- -- -- a0  gxt_
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
		-- 10 -- 0c  im_bgra8888
	re
		-- -- -- --  gxt_swizzled
		-- -- -- a0  gxt_
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
		-- 10 -- 0c  im_bgra8888

vita odin
	gxt_a0  w/non-pow-2 size
		a0,85  Odin2_OR_US_cpk/HIDE_[00/01].ftx
		a0,87  Odin2_OR_US_cpk/HIDE_[02/03/04/05/06].ftx
		a0,87  Odin2_OR_US_cpk/Other.ftx
		a0,85  Odin2_RE_US_cpk/GUI/SD_HIDE_[00/01].ftx
		a0,87  Odin2_RE_US_cpk/GUI/SD_HIDE_[02/03/04/05/06].ftx
		a0,87  Odin2_RE_US_cpk/OnMemory/SD_Other.ftx
	im_10000c
		Odin2_OR_US_cpk/Alice.ftx
		Odin2_OR_US_cpk/Alice_Event01.ftx

NinPriPack1_cpk
	ftx.7z               = 16 MB
	ftx -> gxt.7z        = 28 MB
	ftx -> gxt -> png.7z = 38 MB
 */
