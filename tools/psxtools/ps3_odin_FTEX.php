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
require "class-s3tc.inc";

//define("DRY_RUN", true);

function im_dxt3( &$file, $pos, $w, $h )
{
	printf("== im_dxt3( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$dxt3 = new S3TC_Texture;
	$pix  = $dxt3->DXT3($pix);
	$pix  = $dxt3->S3TC_debug($pix, $w, $h);

	return $pix;
}

function im_dxt5( &$file, $pos, $w, $h )
{
	printf("== im_dxt5( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$dxt5 = new S3TC_Texture;
	$pix  = $dxt5->DXT5($pix);
	$pix  = $dxt5->S3TC_debug($pix, $w, $h);

	return $pix;
}
//////////////////////////////
function ps3gtf( &$file, $base, $pfx, $id )
{
	printf("== ps3gtf( %x , %s , %d )\n", $base, $pfx, $id);

	//$ver = str2big($file, $base+0, 4);
	$cnt = str2big($file, $base+8, 4);
	if ( $cnt != 1 )
		return php_error("%s/%04d is multi-GTF [%d]", $pfx, $id, $cnt);

	$off = str2big($file, $base+0x10, 4);
	$fmt = str2big($file, $base+0x18, 1);
	$w = str2big($file, $base+0x20, 2);
	$h = str2big($file, $base+0x22, 2);
		$w = int_ceil_pow2($w);
		$h = int_ceil_pow2($h);

	$list_fmt = array(
		0x87 => 'im_dxt3',
		0x88 => 'im_dxt5',
	);
	$fn = sprintf("%s.%d.gtf", $pfx, $id);
	printf("DETECT fmt %s\n", $list_fmt[$fmt]);
	printf("%4x x %4x , %s\n", $w, $h, $fn);

	if ( defined("DRY_RUN") )
		return;

	$func = $list_fmt[$fmt];
	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $func($file, $base+$off, $w, $h),
	);

	if ( empty($img['pix']) )
		return;

	save_clutfile($fn, $img);
	return;
}

function odin( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
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

		if ( substr($file, $st, 4) != "gtf\x00" )
			return php_error("%s 0x%x not gtf\n", $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("gtf  %x , %x , %s\n", $st, $sz1, $fn);

		ps3gtf($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	odin( $argv[$i] );
