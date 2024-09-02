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
require 'common.inc';
require 'common-guest.inc';
require 'class-s3tc.inc';

//define('DRY_RUN', true);

function im_dxt1( &$file, $pos, $w, $h )
{
	printf("== im_dxt1( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	$dxt1 = new s3tc_texture;
	$pix  = $dxt1->dxt1($pix);
	$pix  = $dxt1->s3tc_debug($pix, $w, $h);
	return $pix;
}

function im_dxt3( &$file, $pos, $w, $h )
{
	printf("== im_dxt3( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	$dxt3 = new s3tc_texture;
	$pix  = $dxt3->dxt3($pix);
	$pix  = $dxt3->s3tc_debug($pix, $w, $h);
	return $pix;
}

function im_dxt5( &$file, $pos, $w, $h )
{
	printf("== im_dxt5( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	$dxt5 = new s3tc_texture;
	$pix  = $dxt5->dxt5($pix);
	$pix  = $dxt5->s3tc_debug($pix, $w, $h);
	return $pix;
}

function im_dxt1p2( &$file, $pos, $w, $h )
{
	printf("== im_dxt1( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h/2);
	$w = int_ceil($w, 4);
	$h = int_ceil($h, 4);

	$dxt1 = new s3tc_texture;
	$pix  = $dxt1->dxt1($pix);
	$pix  = $dxt1->s3tc_debug($pix, $w, $h);
	return $pix;
}

function im_dxt5p2( &$file, $pos, $w, $h )
{
	printf("== im_dxt5( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);
	$w = int_ceil($w, 4);
	$h = int_ceil($h, 4);

	$dxt5 = new s3tc_texture;
	$pix  = $dxt5->dxt5($pix);
	$pix  = $dxt5->s3tc_debug($pix, $w, $h);
	return $pix;
}

//////////////////////////////
function argb_swizzled( &$pix, $ow, $oh )
{
	// unswizzle pixels
	//   0 1
	//   2 3
	// bitmask
	//          0 -> 1  = right
	//         01 -> 23 = down
	// pattern = drdr drdr
	//         = x/55  y/aa
	printf("== argb_swizzled( %x , %x )\n", $ow, $oh);
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
				$sx = swizzle_bitmask($i, 0x555555);
				$sy = swizzle_bitmask($i, 0xaaaaaa);

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
function im_argb( &$file, $pos, $w, $h )
{
	printf("== im_argb( %x , %x , %x )\n", $pos, $w, $h);
	$pix = '';
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);
	$siz = $w * $h;

	for ( $i=0; $i < $siz; $i++ )
	{
		$pix .= $file[$pos+1]; // r
		$pix .= $file[$pos+2]; // g
		$pix .= $file[$pos+3]; // b
		$pix .= $file[$pos+0]; // a
			$pos += 4;
	} // for ( $i=0; $i < $siz; $i++ )

	argb_swizzled($pix, $w, $h);
	return $pix;
}
//////////////////////////////
function ps3gtf( &$file, $base, $pfx, $id )
{
	printf("== ps3gtf( %x , %s , %d )\n", $base, $pfx, $id);

	//$ver = str2big($file, $base+0, 4);
	$cnt = str2big($file, $base+8, 4);
	if ( $cnt != 1 )
		return php_error('%s/%04d is multi-GTF [%d]', $pfx, $id, $cnt);

	$off = str2big($file, $base+0x10, 4);
	$fmt = str2big($file, $base+0x18, 1);
	$w = str2big($file, $base+0x20, 2);
	$h = str2big($file, $base+0x22, 2);

	$list_fmt = array(
		0x85 => 'im_argb',
		0x86 => 'im_dxt1',
		0x87 => 'im_dxt3',
		0x88 => 'im_dxt5',
		0xa6 => 'im_dxt1p2',
		0xa8 => 'im_dxt5p2',
	);
	if ( ! isset($list_fmt[$fmt]) )
		return php_error('UNKNOWN im fmt  %x', $fmt);
	printf("DETECT fmt %s\n", $list_fmt[$fmt]);

	$fn = sprintf('%s.%d.gtf', $pfx, $id);
	printf("%4x x %4x , %s\n", $w, $h, $fn);

	if ( defined('DRY_RUN') )
		return;

	$func = $list_fmt[$fmt];
	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $func($file, $base+$off, $w, $h),
	);
	save_clutfile($fn, $img);
	return;
}

function odin( $fname )
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

		if ( substr($file, $st, 4) !== "gtf\x00" )
			return php_error('%s 0x%x not gtf', $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("gtf  %x , %x , %s\n", $st, $sz1, $fn);

		ps3gtf($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

argv_loopfile($argv, 'odin');

/*
dragon crown
	86 im_dxt1
	87 im_dxt3
	88 im_dxt5
odin sphere leifthsar
	85 im_argb
	87 im_dxt3
	88 im_dxt5
	a6 im_dxt1p2
	a8 im_dxt5p2

odin sphere leifthsar
	85 im_argb
		HD_Cook03.ftx
	a6 im_dxt1p2
	w/non-pow-2 size
		HD_HIDE_[00/01].ftx
	a8 im_dxt5p2
	w/non-pow-2 size
		HD_HIDE_[02/03/04/05/06].ftx
 */
