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

$gp_pix  = array();
$gp_clut = array();

function gimbuf( &$buf, &$sub, $dx, $dy, $ow, $oh, $blk)
{
	for ( $y=0; $y < 8; $y++ )
	{
		$syy = $y * $blk;
		$dyy = ($dy + $y) * $ow;
		$dxx = $dyy + $dx;

		$s = substr($sub, $syy, $blk);
		str_update($buf, $dxx, $s);
	}
	return;
}

function gimpix( &$pix, $ow, $oh, $blk )
{
	//return;
	// blocks of 16x8 for 8-bpp
	// blocks of 32x8 for 4-bpp
	$bw = $ow / $blk;
	$bh = $oh / 8;
	if ( (int)$bw == 0 || (int)$bh == 0 )
		return;

	$buf = $pix;
	$pos = 0;
	$bs  = $blk * 8;
	for ( $y=0; $y < $bh; $y++ )
	{
		for ( $x=0; $x < $bw; $x++ )
		{
			$sub = substr($pix, $pos, $bs);
				$pos += $bs;

			gimbuf($buf, $sub, $x*$blk, $y*8, $ow, $oh, $blk);
		} // for ( $x=0; $x < $bw; $x++ )
	} // for ( $y=0; $y < $bh; $y++ )

	$pix = $buf;
	return;
}
//////////////////////////////
function pspgim_pix( &$file, $base, &$data )
{
	$size = str2int($file, $base+0, 2);
	$type = str2int($file, $base+4, 2);
	//$swiz = str2int($file, $base+6, 2);
	$w = str2int($file, $base+ 8, 2);
	$h = str2int($file, $base+10, 2);

	$base += ($size + 0x10);
	switch ( $type )
	{
		case 3:
			printf("TYPE %x RGBA32 , %x x %x\n", $type, $w, $h);
			$size = 4 * $w * $h;
			$data['byte'] = 4;
			$data['w'] = $w;
			$data['h'] = $h;
			$data['pix'] = substr($file, $base, $size);
			// all RGBA32 are palette
			break;

		case 4:
			printf("TYPE %x 4-bpp , %x x %x\n", $type, $w, $h);
			$size = $w / 2 * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;

			$data['pix'] = substr($file, $base, $size);
			bpp4to8($data['pix']);
			gimpix($data['pix'], $w, $h, 32);
			break;

		case 5:
			printf("TYPE %x 8-bpp , %x x %x\n", $type, $w, $h);
			$size = $w * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;
			$data['pix'] = substr($file, $base, $size);
			gimpix($data['pix'], $w, $h, 16);
			break;

		default:
			return php_error("TYPE %x UNKNOWN", $type);
	} // switch ( $type )
	return;
}

function pspgim( &$file, $base, $pfx, $id )
{
	printf("== pspgim( %x , $pfx , $id )\n", $base);
	if ( substr($file, $base, 11) !== "MIG.00.1PSP" )
		return php_error("not GIM");

	global $gp_pix, $gp_clut;
	$gp_pix  = '';
	$gp_clut = '';

	$pos = $base + 0x10;
	while (1)
	{
		$blk   = str2int($file, $pos+ 0, 2);
		$bsize = str2int($file, $pos+ 4, 4);
		$bnext = str2int($file, $pos+ 8, 4);
		$bdata = str2int($file, $pos+12, 4);
		switch ( $blk )
		{
			case 2:
				printf("%8x , %s/%d/root\n", $pos, $pfx, $id);
				$pos += $bnext;
				break;
			case 3:
				printf("%8x , %s/%d/picture\n", $pos, $pfx, $id);
				$pos += $bnext;
				break;
			case 4:
				printf("%8x , %s/%d/image\n", $pos, $pfx, $id);
				if ( ! empty($gp_pix) )
					return php_error("multiple image blocks");

				pspgim_pix($file, $pos+$bdata, $gp_pix);
				$pos += $bnext;
				break;
			case 5:
				printf("%8x , %s/%d/palette\n", $pos, $pfx, $id);
				if ( ! empty($gp_clut) )
					return php_error("multiple palette blocks");

				pspgim_pix($file, $pos+$bdata, $gp_clut);
				$pos += $bnext;
				break;
			case 0:
				break 2;
			default:
				return php_error("%8x UNKNOWN", $pos);
		}
	} // while (1)

	//return;
	$fn = sprintf("%s.%d.gim", $pfx, $id);
	if ( empty($gp_clut) )
		save_clutfile($fn, $gp_pix);
	else
	{
		$gp_pix['pal'] = $gp_clut['pix'];
		$gp_pix['cc']  = $gp_clut['w'];

		save_clutfile($fn, $gp_pix);
	}
	return;
}

function grand( $fname )
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

		if ( substr($file, $st, 4) != "FTX0" )
			return php_error("%s 0x%x not FTX0\n", $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("GIM  %x , %x , %s\n", $st, $sz1, $fn);

		pspgim($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	grand( $argv[$i] );
