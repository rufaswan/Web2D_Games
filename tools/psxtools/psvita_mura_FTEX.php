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
require 'common.inc';
require 'common-guest.inc';
require 'class-s3tc.inc';

//define('DRY_RUN', true);

function dxt_swizzled( &$pix, $ow, $oh )
{
	// 1 tile = 4*4 pixels
	// unswizzled tiles
	//   0 2  8 10
	//   1 3  9 11
	//   4 6 12 14
	//   5 7 13 15
	// bitmask
	//          0 -> 1        = down
	//         01 -> 23       = right
	//       0123 -> 4567     = down
	//   01234567 -> 89abcdef = right
	// pattern = rdrd rdrd
	//         = x/aa  y/55
	printf("== dxt_swizzled( %x , %x )\n", $ow, $oh);

	// 1 pixel = 4*4 bc tile
	$bc = array(
		'pix' => $pix,
		'dec' => str_repeat(ZERO, strlen($pix)),
		'pos' => 0,
		'w'   => $ow >> 2, // div 4
		'h'   => $oh >> 2, // div 4
		'bpp' => 4,
	);
	$min = ( $bc['w'] > $bc['h'] ) ? $bc['h'] : $bc['w'];

	for ( $y=0; $y < $bc['h']; $y += $min )
	{
		for ( $x=0; $x < $bc['w']; $x += $min )
		{
			$blk = $min * $min;
			for ( $i=0; $i < $blk; $i++ )
			{
				$sx = swizzle_bitmask($i, 0xaaaaaa);
				$sy = swizzle_bitmask($i, 0x555555);
				pixdec_copy44($bc, $x+$sx, $y+$sy);
			}
		} // for ( $x=0; $x < $bc['w']; $x += $min )
	} // for ( $y=0; $y < $bc['h']; $y += 32 )

	$pix = $bc['dec'];
	return;
}
//////////////////////////////
function im_dxt1( &$file, $pos, $w, $h )
{
	printf("== im_dxt1( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$s3  = new s3tc_texture;
	$pix = $s3->dxt1($pix);
	//$pix = $s3->s3tc_debug($pix, $w, $h);

	dxt_swizzled($pix, $w, $h);
	return $pix;
}

function im_dxt3( &$file, $pos, $w, $h )
{
	printf("== im_dxt3( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$s3  = new s3tc_Texture;
	$pix = $s3->dxt3($pix);
	//$pix = $s3->s3tc_debug($pix, $w, $h);

	dxt_swizzled($pix, $w, $h);
	return $pix;
}

function im_dxt5( &$file, $pos, $w, $h )
{
	printf("== im_dxt5( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$s3  = new s3tc_texture;
	$pix = $s3->dxt5($pix);
	//$pix = $s3->s3tc_debug($pix, $w, $h);

	dxt_swizzled($pix, $w, $h);
	return $pix;
}
//////////////////////////////
function bgra_swizzled( &$pix, $ow, $oh )
{
	// unswizzle pixels
	//   0 2
	//   1 3
	// bitmask
	//          0 -> 1  = down
	//         01 -> 23 = right
	// pattern = rdrd rdrd
	//         = x/aa  y/55
	printf("== bgra_swizzled( %x , %x )\n", $ow, $oh);
	$dec = $pix;
	$pos = 0;
	$min = ( $ow > $oh ) ? $oh : $ow;

	for ( $y=0; $y < $oh; $y += $min )
	{
		for ( $x=0; $x < $ow; $x += $min )
		{
			$blk = $min * $min;
			for ( $i=0; $i < $blk; $i++ )
			{
				$sx = swizzle_bitmask($i, 0xaaaaaa);
				$sy = swizzle_bitmask($i, 0x555555);

				$dyy = ($y  + $sy) * $ow;
				$dxx = $dyy + $x + $sx;
				$s = substr($pix, $pos, 4); // 1 RGBA pixel
						$pos += 4;
				str_update($dec, $dxx*4, $s);
			}
		} // for ( $x=0; $x < $ow; $x += $min )
	} // for ( $y=0; $y < $oh; $y += 32 )

	$pix = $dec;
	return;
}
//////////////////////////////
function im_bgra8888( &$file, $pos, $w, $h )
{
	printf("== im_bgra8888( %x , %x , %x )\n", $pos, $w, $h);

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
function vitagxt( &$file, $base, $pfx, $id )
{
	printf("== vitagxt( %x , $pfx , $id )\n", $base);
	if ( substr($file, $base+0, 4) !== 'GXT'.ZERO )
		return;

	$cnt = str2int($file, $base+8, 4);
	if ( $cnt != 1 )
		return php_error('%s/%04d is multi-GXT [%d]', $pfx, $id, $cnt);

	$fmt = substr ($file, $base+0x34, 4);
	$w   = str2int($file, $base+0x38, 2);
	$h   = str2int($file, $base+0x3a, 2);
		$w = int_ceil_pow2($w);
		$h = int_ceil_pow2($h);

	$list_fmt = array(
		"\x00\x10\x00\x0c" => 'im_bgra8888',
		"\x00\x00\x00\x85" => 'im_dxt1',
		"\x00\x00\x00\x86" => 'im_dxt3',
		"\x00\x00\x00\x87" => 'im_dxt5',
	);
	if ( ! isset( $list_fmt [$fmt] ) )
		return php_error('UNKNOWN im fmt  %s', debug($fmt));
	printf("DETECT  fmt %s\n", $list_fmt[$fmt]);

	$off = str2int($file, $base+0x20, 4);
	$siz = str2int($file, $base+0x24, 4);
	$fn  = sprintf('%s.%d.gxt', $pfx, $id);
	printf("%4x x %4x  %s\n", $w, $h, $fn);

	if ( defined('DRY_RUN') )
		return;

	$func = $list_fmt[$fmt];
	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => $func($file, $base+$off, $w, $h),
	);
	save_clutfile($fn, $img);
	return;
}
//////////////////////////////
function mura( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FTEX' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = str2int($file,  8, 4);
	$cnt  = str2int($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) !== 'FTX0' )
			return php_error('%s 0x%x not FTX0', $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("GXT  %x , %x , %s\n", $st, $sz1, $fn);

		vitagxt($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

argv_loopfile($argv, 'mura');

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
