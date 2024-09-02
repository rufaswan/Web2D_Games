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
 *   GFD Studio
 *   https://github.com/TGEnigma/GFD-Studio/blob/master/GFDLibrary/Textures/GNF/GNFTexture.cs
 */
require 'common.inc';
require 'common-guest.inc';
require 'class-bptc.inc';
require 'class-s3tc.inc';

//define('DRY_RUN', true);

function gnf_swizzled_bc( &$pix, $ow, $oh )
{
	// 1 tile  = 4*4 pixels
	// 1 block = 8*8 tiles = 32*32 pixels
	// unswizzled tiles
	//    0  1  4  5 16 17 20 21
	//    2  3  6  7 18 19 22 23
	//    8  9 12 13 24 25 28 29
	//   10 11 14 15 26 27 30 31
	//   32 33 36 37 48 49 52 53
	//   34 35 38 39 50 51 54 55
	//   40 41 44 45 56 57 60 61
	//   42 43 46 47 58 59 62 63
	// bitmask
	//          0 -> 1        = right
	//         01 -> 23       = down
	//       0123 -> 4567     = right
	//   01234567 -> 89abcdef = down
	// pattern = -rdr drdr
	//         = x/55  y/2a
	printf("== gnf_swizzled_bc( %x , %x )\n", $ow, $oh);

	// 1 pixel = 4*4 bc tile
	$bc = array(
		'pix' => $pix,
		'dec' => str_repeat(ZERO, strlen($pix)),
		'pos' => 0,
		'w'   => $ow >> 2, // div 4
		'h'   => $oh >> 2, // div 4
		'bpp' => 4,
	);

	// morton swizzle for every 8x8 tiles
	for ( $y=0; $y < $bc['h']; $y += 8 )
	{
		for ( $x=0; $x < $bc['w']; $x += 8 )
		{
			for ( $i=0; $i < 0x40; $i++ )
			{
				$sx = swizzle_bitmask($i, 0x55);
				$sy = swizzle_bitmask($i, 0x2a);
				pixdec_copy44($bc, $x+$sx, $y+$sy);
			}
		} // for ( $x=0; $x < $bc['w']; $x += 8 )
	} // for ( $y=0; $y < $bc['h']; $y += 8 )

	$pix = $bc['dec'];
	return;
}
//////////////////////////////
function im_bc3( &$file, $pos, $w, $h )
{
	printf("== im_bc3( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$bc3 = new s3tc_texture;
	$pix = $bc3->bc3($pix);
	//$pix = $bc3->s3tc_debug($pix, $w, $h);

	gnf_swizzled_bc($pix, $w, $h);
	return $pix;
}

function im_bc7( &$file, $pos, $w, $h )
{
	printf("== im_bc7( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $w*$h);

	$bc7 = new bptc_texture;
	$pix = $bc7->bc7($pix);
	//$pix = $bc7->bptc_debug($pix, $w, $h);

	gnf_swizzled_bc($pix, $w, $h);
	return $pix;
}
//////////////////////////////
function ps4gnf( &$file, $base, $pfx, $id )
{
	printf("== ps4gnf( %x , %s , %d )\n", $base, $pfx, $id);
	if ( substr($file,$base,4) != 'GNF ' )
		return;

	$off = str2int($file, $base+4, 4);
		$base += 8;

	//$ver = ord( $file[$base+0] );
	$cnt = ord( $file[$base+1] );
	if ( $cnt != 1 )
		return php_error('%s/%04d is multi-GNF [%d]', $pfx, $id, $cnt);

	// fedcba98 76543210 fedcba98 76543210
	// -------- -------- -------- --------
	//$b0 = str2int($file, $base+ 8, 4);

	// fedcba98 76543210 fedcba98 76543210
	// ccccccss ssssmmmm mmmmmmmm --------
	// m = min lod clamp
	// s = surface format
	// c = channel type
	$b1 = str2int($file, $base+12, 4);
	$fmt = ($b1 >> 20) & 0x3f;

	// fedcba98 76543210 fedcba98 76543210
	// -ssshhhh hhhhhhhh hhwwwwww wwwwwwww
	// w = width
	// h = height
	// s = sampler modulation factor
	$b2 = str2int($file, $base+16, 4);
	$w = ($b2 >>  0) & 0x3fff;
	$h = ($b2 >> 14) & 0x3fff;

	// fedcba98 76543210 fedcba98 76543210
	// tttt--pt ttttllll bbbbwwwz zzyyyxxx
	// x = channel order x
	// y = channel order y
	// z = channel order z
	// w = channel order w
	// b = base mip level
	// l = last mip level
	// t = tile mode
	// p = is padded to pow2
	// t = texture type
	//$b3 = str2int($file, $base+20, 4);

	// fedcba98 76543210 fedcba98 76543210
	// -----ppp pppppppp pppddddd dddddddd
	// d = depth
	// p = pitch
	//$b4 = str2int($file, $base+24, 4);

	// fedcba98 76543210 fedcba98 76543210
	// ------ll llllllll lllbbbbb bbbbbbbb
	// b = base array slice index
	// l = last array slice index
	//$b5 = str2int($file, $base+28, 4);

	// fedcba98 76543210 fedcba98 76543210
	// -------u tadecccc ccccwwww wwwwwwww
	// w = min lod warning
	// c = mip stats counter index
	// e = mips stats enabled
	// d = metadata compression enabled
	// a = dcc alpha on msb
	// t = dcc color transform
	// u = use alth tile mode
	//$b6 = str2int($file, $base+32, 4);

	$w = int_ceil($w, 32);
	$h = int_ceil($h, 32);

	$list_fmt = array(
		0x25 => 'im_bc3',
		0x29 => 'im_bc7',
	);
	if ( ! isset($list_fmt[$fmt]) )
		return php_error('UNKNOWN im fmt  %x', $fmt);
	printf("DETECT  fmt %s , %x x %x \n", $list_fmt[$fmt], $w, $h);

	if ( defined('DRY_RUN') )
		return;

	$fn = sprintf('%s.%d.gnf', $pfx, $id);
	printf("%4x x %4x , %s\n", $w, $h, $fn);

	$func = $list_fmt[$fmt];
	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $func($file, $base+$off, $w, $h),
	);
	save_clutfile($fn, $img);
	return;
}

function aegis( $fname )
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
		printf("GNF  %x , %x , %s\n", $st, $sz1, $fn);

		ps4gnf($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

argv_loopfile($argv, 'aegis');

/*
odin sphere leifthsar
	25  im_bc3
	29  im_bc7
dragon crown pro
	29  im_bc7
grim grimoire once more
	25  im_bc3
13 sentinels
	29  im_bc7

odin sphere leifthsar
	29  im_bc7
		bg32a.ftx
		bg32b.ftx
		Interface_DE.ftx
		Interface_ES.ftx
		Interface_FR.ftx
		Interface.ftx
		Interface_IT.ftx
		Interface_UK.ftx
		Interface_US.ftx
		Menu_DE.ftx
		Menu_ES.ftx
		Menu_FR.ftx
		Menu.ftx
		Menu_IT.ftx
		Menu_UK.ftx
		Menu_US.ftx
		SKILLCARD00.ftx
		SKILLCARD01.ftx
		SKILLCARD02.ftx
		SKILLCARD03.ftx
		SKILLCARD04.ftx
		SKILLCARD05.ftx
		SKILLCARD06.ftx
		title_DE.ftx
		title_ES.ftx
		title_FR.ftx
		title.ftx
		title_IT.ftx
		title_UK.ftx
		title_US.ftx
 */
