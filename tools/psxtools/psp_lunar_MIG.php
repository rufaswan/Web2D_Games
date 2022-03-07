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

function swizrgba( &$pix, $w, $h )
{
	// blocks of 4x8 for RGBA
	$buf = $pix;
	$pos = 0;
	for ( $by=0; $by < $h; $by += 8 )
	{
		for ( $bx=0; $bx < $w; $bx += 4 )
		{
			for ( $y=0; $y < 8; $y++ )
			{
				$sub = substr($pix, $pos, 4*4);
					$pos += (4*4);
				$dyy = ($by + $y) * $w;
				$dxx = $dyy + $bx;
				str_update($buf, $dxx*4, $sub);
			} // for ( $y=0; $y < 8; $y++ )
		} // for ( $bx=0; $bx < $w; $bx += 16 )
	} // for ( $by=0; $by < $h; $by += 8 )

	$pix = $buf;
	return;
}

function swizbpp( &$pix, $w, $h )
{
	// blocks of 16x8 for 8-bpp
	// blocks of 32x8 for 4-bpp
	$buf = $pix;
	$pos = 0;
	for ( $by=0; $by < $h; $by += 8 )
	{
		for ( $bx=0; $bx < $w; $bx += 16 )
		{
			for ( $y=0; $y < 8; $y++ )
			{
				$sub = substr($pix, $pos, 16);
					$pos += 16;
				$dyy = ($by + $y) * $w;
				$dxx = $dyy + $bx;
				str_update($buf, $dxx, $sub);
			} // for ( $y=0; $y < 8; $y++ )
		} // for ( $bx=0; $bx < $w; $bx += 16 )
	} // for ( $by=0; $by < $h; $by += 8 )

	$pix = $buf;
	return;
}
//////////////////////////////
function pspgim_pix( &$file, $base )
{
	$size = str2int($file, $base+0, 2);
	$type = str2int($file, $base+4, 2);
	$swiz = str2int($file, $base+6, 2);
	$w = str2int($file, $base+ 8, 2);
	$h = str2int($file, $base+10, 2);
	$algx = str2int($file, $base+14, 2);
	$algy = str2int($file, $base+16, 2);
		$w = int_ceil($w, $algx);
		$h = int_ceil($h, $algy);

	$base += ($size + 0x10);
	$data = array();
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
			if ( $swiz )
				swizrgba($data['pix'], $w, $h);
			return $data;

		case 4:
			printf("TYPE %x 4-bpp , %x x %x\n", $type, $w, $h);
			$size = $w / 2 * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;

			$data['pix'] = substr($file, $base, $size);
			if ( $swiz )
				swizbpp($data['pix'], $w, $h);
			bpp4to8($data['pix']);
			return $data;

		case 5:
			printf("TYPE %x 8-bpp , %x x %x\n", $type, $w, $h);
			$size = $w * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;

			$data['pix'] = substr($file, $base, $size);
			if ( $swiz )
				swizbpp($data['pix'], $w, $h);
			return $data;

		default:
			return 0;
	} // switch ( $type )
	return 0;
}
//////////////////////////////
function save_gim( &$pix, &$pal, &$id, $fname )
{
	if ( empty($pix) )
		return;

	if ( ! empty($pal) )
	{
		$pix['pal'] = $pal['pix'];
		$pix['cc']  = $pal['w'];
	}
	save_clutfile("$fname.$id.rgba", $pix);

	$id++;
	$pix = array();
	$pal = array();
	return;
}

function pspgim( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 11) !== 'MIG.00.1PSP' )
		return;

	$pix = array();
	$pal = array();

	$len = strlen($file);
	$pos = 0x10;
	$id = 0;
	while ( $pos < $len )
	{
		$blk   = str2int($file, $pos+ 0, 2);
		$bsize = str2int($file, $pos+ 4, 4);
		$bnext = str2int($file, $pos+ 8, 4);
		$bdata = str2int($file, $pos+12, 4);
		switch ( $blk )
		{
			case 2:
				printf("%8x , %s/root\n", $pos, $fname);
				$pos += $bnext;
				break;
			case 3:
				printf("%8x , %s/picture\n", $pos, $fname);
				save_gim($pix, $pal, $id, $fname);
				$pos += $bnext;
				break;
			case 4:
				printf("%8x , %s/image\n", $pos, $fname);
				if ( ! empty($pix) )
					return php_error('multiple image blocks');

				$pix = pspgim_pix($file, $pos+$bdata);
				$pos += $bnext;
				break;
			case 5:
				printf("%8x , %s/palette\n", $pos, $fname);
				if ( ! empty($pal) )
					return php_error('multiple palette blocks');

				$pal = pspgim_pix($file, $pos+$bdata);
				$pos += $bnext;
				break;
			default:
				break 2;
		}
	} // while (1)

	save_gim($pix, $pal, $id, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pspgim( $argv[$i] );
