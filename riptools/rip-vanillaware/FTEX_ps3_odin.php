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

require 'tool.inc';
tool::require('class-s3tc');
tool::require('class-ieee754');
tool::require('class-clutfile');

//define('DRY_RUN', true);

function im_dxt1( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);
	$w = tool::int_ceil_pow2($w);
	$h = tool::int_ceil_pow2($h);

	$dxt1 = new s3tc_texture;
	$pix  = $dxt1->dxt1($pix);
	$pix  = $dxt1->s3tc_draw($pix, $w>>2, $h>>2);
	return $pix;
}

function im_dxt3( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);
	$w = tool::int_ceil_pow2($w);
	$h = tool::int_ceil_pow2($h);

	$dxt3 = new s3tc_texture;
	$pix  = $dxt3->dxt3($pix);
	$pix  = $dxt3->s3tc_draw($pix, $w>>2, $h>>2);
	return $pix;
}

function im_dxt5( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);
	$w = tool::int_ceil_pow2($w);
	$h = tool::int_ceil_pow2($h);

	$dxt5 = new s3tc_texture;
	$pix  = $dxt5->dxt5($pix);
	$pix  = $dxt5->s3tc_draw($pix, $w>>2, $h>>2);
	return $pix;
}

function im_dxt1p2( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h/2);
	$w = tool::int_ceil($w, 4);
	$h = tool::int_ceil($h, 4);

	$dxt1 = new s3tc_texture;
	$pix  = $dxt1->dxt1($pix);
	$pix  = $dxt1->s3tc_draw($pix, $w>>2, $h>>2);
	return $pix;
}

function im_dxt5p2( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);
	$w = tool::int_ceil($w, 4);
	$h = tool::int_ceil($h, 4);

	$dxt5 = new s3tc_texture;
	$pix  = $dxt5->dxt5($pix);
	$pix  = $dxt5->s3tc_draw($pix, $w>>2, $h>>2);
	return $pix;
}

//////////////////////////////
function argb_swizzled( string &$pix, int $ow, int $oh ) : void
{
	tool::trace(__FUNCTION__, $ow, $oh);

	// unswizzle pixels
	//   0 1
	//   2 3
	// bitmask
	//          0 -> 1  = right
	//         01 -> 23 = down
	// pattern = drdr drdr
	//         = x/55  y/aa
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
				list($dx,$dy) = hexnum::swizzle_id2xy($i, 0x555555, 0xaaaaaa);
					$dx += $x;
					$dy += $y;
				$s = substr($pix, $pos, 4); // 1 RGBA pixel
					$pos += 4;
				$dst = ($dy * $ow) + $dx;
				tool::str_update($dec, $dst*4, $s);
			} // for ( $i=0; $i < $blk; $i++ )
		} // for ( $x=0; $x < $ow; $x += $min )
	} // for ( $y=0; $y < $oh; $y += 32 )

	$pix = $dec;
}
//////////////////////////////
function im_argb( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = '';
	$w = tool::int_ceil_pow2($w);
	$h = tool::int_ceil_pow2($h);
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
function odin_gtf( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	// NO MAGIC

	//$ver = ieee754::ordstr($file, 0, 4);
	$cnt = ieee754::ordstr($file, 8, 4);
	if ( $cnt !== 1 )
		tool::error('is multi-GTF', $fname, $cnt);

	$off = ieee754::ordstr($file, 0x10, 4);
	$fmt = ieee754::ordstr($file, 0x18, 1);
	$w = ieee754::ordstr($file, 0x20, 2);
	$h = ieee754::ordstr($file, 0x22, 2);

	$list_fmt = [
		0x85 => 'im_argb',
		0x86 => 'im_dxt1',
		0x87 => 'im_dxt3',
		0x88 => 'im_dxt5',
		0xa6 => 'im_dxt1p2',
		0xa8 => 'im_dxt5p2',
	];
	if ( ! isset($list_fmt[$fmt]) )
		tool::error('UNKNOWN im fmt', $fmt);
	tool::trace('DETECT fmt', $list_fmt[$fmt]);

	$fn = sprintf('%s.gtf', $fname);
	tool::trace('gtf size', $w, $h, $fn);

	if ( defined('DRY_RUN') )
		return true;

	$func = $list_fmt[$fmt];
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pix = $func($file, $off, $w, $h);
	clutfile::save($fn, $img);
	return true;
}

function odin_ftex( string &$file, string $fname ) : bool
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

		if ( substr($file, $st, 4) !== "gtf\x00" )
			tool::error('not gtf', $fname, $st);

		$sz1 = tool::ordstr($file, $st+4, 4);
		$sz2 = tool::ordstr($file, $st+8, 4);
		tool::trace('gtf', $st, $sz1, $fn);

		$sub = tool::substr($file, $st+$sz2, $sz1);
		odin_gtf($sub, "$pfx.$i");

		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function odin( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = odin_ftex($file, $fname);
	if ( $r )  return;

	// no MAGIC = disabled for false positives
	//$r = odin_gtf($file, $fname);
	//if ( $r )  return;
}

tool::argv_callback($argv, 'odin');

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
