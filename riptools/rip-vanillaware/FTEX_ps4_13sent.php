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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-bptc');
tool::require('class-s3tc');
tool::require('class-clutfile');
tool::require('func-hexnum');

//define('DRY_RUN', true);

function gnf_swizzled_bc( string &$pix, int $bw, int $bh, array $draw ) : void
{
	if ( ($bw & 7) || ($bh & 7) )
		tool::error('gnf not in 8x8 tile', $bw, $bh);

	// 1 pixel      = 4*4 bc tile
	// in 8*8 pixel = 40 byte
	// unswizzled tiles
	//    0  1  4  5 10 11 14 15
	//    2  3  6  7 12 13 16 17
	//    8  9  c  d 18 19 1c 1d
	//    a  b  e  f 1a 1b 1e 1f
	//   20 21 24 25 30 31 34 35
	//   22 23 26 27 32 33 36 37
	//   28 29 2c 2d 38 39 3c 3d
	//   2a 2b 2e 2f 3a 3b 3e 3f
	// bitmask
	//  1x1  *start
	//  2x1  right
	//  2x2  down
	//  4x2  right
	//  4x4  down
	//  8x4  right
	//  8x8  down
	// pattern = drdr drdr
	//         x/-1-1 -1-1 = 55
	//         y/1-1- 1-1- = aa
	tool::trace(__FUNCTION__, $bw, $bh);
	$map = [];

	// morton swizzle for every 8x8 tiles
	$blk = 8 * 8;
	$p   = 0;
	for ( $y=0; $y < $bh; $y += 8 )
	{
		for ( $x=0; $x < $bw; $x += 8 )
		{
			for ( $i=0; $i < $blk; $i++ )
			{
				list($bx,$by) = hexnum::swizzle_id2xy($i, 0x55, 0xaa);
					$bx += $x;
					$by += $y;
				$id = ($by * $bw) + $bx;
				$map[$id] = $p + $i;
			}
			$p += $blk;
		} // for ( $x=0; $x < $bw; $x += 8 )
	} // for ( $y=0; $y < $bh; $y += 8 )

	//hexnum::swizzle_map($map, $bw, $bh, true);
	hexnum::swizzle_map($map, $bw, $bh);
	$pix = $draw($pix, $bw, $bh, $map);
}
//////////////////////////////
function im_bc3( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);

	$bc3 = new s3tc_texture;
	$pix = $bc3->bc3($pix);
	//$pix = $bc3->s3tc_draw($pix, $w>>2, $h>>2);

	gnf_swizzled_bc($pix, $w>>2, $h>>2, [$bc3,'s3tc_draw']);
	return $pix;
}

function im_bc4( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	// BC4 uses 8 bytes per 4x4 block (half of BC3/BC7)
	$pix = tool::substr($file, $pos, ($w * $h) >> 1);

	$bc4 = new s3tc_texture;
	$pix = $bc4->bc4($pix);
	//$pix = $bc4->s3tc_draw($pix, $w>>2, $h>>2);

	// BC4 outputs single-channel grayscale, convert to RGBA
	$rgba = '';
	$len = strlen($pix);
	for ($i = 0; $i < $len; $i++) {
		$g = $pix[$i];
		$rgba .= $g . $g . $g . BYTE; // R=G=B=gray, A=255
	}
	$pix = $rgba;

	gnf_swizzled_bc($pix, $w>>2, $h>>2, [$bc4,'s3tc_draw']);
	return $pix;
}

function im_bc7( string &$file, int $pos, int $w, int $h ) : string
{
	tool::trace(__FUNCTION__, $pos, $w, $h);
	$pix = tool::substr($file, $pos, $w*$h);

	$bc7 = new bptc_texture;
	$pix = $bc7->bc7($pix);
	//$pix = $bc7->bptc_draw($pix, $w>>2, $h>>2);

	gnf_swizzled_bc($pix, $w>>2, $h>>2, [$bc7,'bptc_draw']);
	return $pix;
}
//////////////////////////////
function aegis_gnf( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file,0,4) != 'GNF ' )
		return false;

	$off = tool::ordstr($file, 4, 4);
		$pos = 8;

	//$ver = ord( $file[$pos+0] );
	$cnt = ord( $file[$pos+1] );
	if ( $cnt !== 1 )
		tool::error('is multi-GNF', $fname, $cnt);

	// fedcba98 76543210 fedcba98 76543210
	// -------- -------- -------- --------
	//$b0 = tool::ordstr($file, $pos+ 8, 4);

	// fedcba98 76543210 fedcba98 76543210
	// ccccccss ssssmmmm mmmmmmmm --------
	// m = min lod clamp
	// s = surface format
	// c = channel type
	$b1 = tool::ordstr($file, $pos+12, 4);
	$fmt = ($b1 >> 20) & 0x3f;

	// fedcba98 76543210 fedcba98 76543210
	// -ssshhhh hhhhhhhh hhwwwwww wwwwwwww
	// w = width
	// h = height
	// s = sampler modulation factor
	$b2 = tool::ordstr($file, $pos+16, 4);
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
	//$b3 = tool::ordstr($file, $pos+20, 4);

	// fedcba98 76543210 fedcba98 76543210
	// -----ppp pppppppp pppddddd dddddddd
	// d = depth
	// p = pitch
	//$b4 = tool::ordstr($file, $pos+24, 4);

	// fedcba98 76543210 fedcba98 76543210
	// ------ll llllllll lllbbbbb bbbbbbbb
	// b = base array slice index
	// l = last array slice index
	//$b5 = tool::ordstr($file, $pos+28, 4);

	// fedcba98 76543210 fedcba98 76543210
	// -------u tadecccc ccccwwww wwwwwwww
	// w = min lod warning
	// c = mip stats counter index
	// e = mips stats enabled
	// d = metadata compression enabled
	// a = dcc alpha on msb
	// t = dcc color transform
	// u = use alth tile mode
	//$b6 = tool::ordstr($file, $pos+32, 4);

	$w = tool::int_ceil($w, 32);
	$h = tool::int_ceil($h, 32);

	$list_fmt = [
		0x25 => 'im_bc3',
		0x26 => 'im_bc4',
		0x29 => 'im_bc7',
	];
	if ( ! isset($list_fmt[$fmt]) )
		tool::error('UNKNOWN im fmt', $fmt);
	tool::trace('DETECT  fmt', $list_fmt[$fmt], $w, $h);

	if ( defined('DRY_RUN') )
		return true;

	$fn = sprintf('%s.gnf', $fname);
	tool::trace('gnf size', $w, $h, $fn);

	$func = $list_fmt[$fmt];
	$img = new clutdata;
		$img->w = $w;
		$img->h = $h;
		$img->pix = $func($file, $pos+$off, $w, $h);
	clutfile::save($fn, $img);
	return true;
}

function aegis_ftex( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	// FTEX Format:
	// 0x00: "FTEX" magic
	// 0x04: version (usually 0x00010000)
	// 0x08: hdsz = header size (where first FTX0 chunk starts)
	// 0x0C: cnt = number of textures
	// 0x10: padding to 0x20
	// 0x20+: filename entries (0x30 bytes each: 0x20 name + 0x10 padding)
	// hdsz+: FTX0 chunks (texture data)
	if ( substr($file, 0, 4) !== 'FTEX' )
		return false;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$ver  = tool::ordstr($file,  4, 4);
	$hdsz = tool::ordstr($file,  8, 4);
	$cnt  = tool::ordstr($file, 12, 4);

	printf("======================================\n");
	printf("FTEX File: %s\n", basename($fname));
	printf("  Version:      0x%08x\n", $ver);
	printf("  Header Size:  0x%x (%d bytes)\n", $hdsz, $hdsz);
	printf("  Texture Count: %d\n", $cnt);
	printf("  File Size:    0x%x (%d bytes)\n", strlen($file), strlen($file));
	printf("======================================\n\n");

	// Parse filename entries
	printf("Texture Names:\n");
	for ($i = 0; $i < $cnt; $i++) {
		$p1 = 0x20 + ($i * 0x30);
		$fn = tool::substr($file, $p1, 0x20);
		$fn = rtrim($fn, ZERO);
		printf("  [%d] 0x%04x: %s\n", $i, $p1, $fn);
	}
	printf("\n");

	// Process each FTX0 chunk
	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		// Get texture name from filename table
		$p1 = 0x20 + ($i * 0x30);
		$fn = tool::substr($file, $p1, 0x20);
		$fn = rtrim($fn, ZERO);

		printf("------ Texture %d: %s ------\n", $i, $fn);
		printf("FTX0 Chunk Start: 0x%x\n", $st);

		// FTX0 chunk structure:
		// 0x00: "FTX0" magic
		// 0x04: sz1 = size of GNF header + pixel data
		// 0x08: sz2 = offset from FTX0 start to GNF header
		// 0x0C+: padding to sz2
		// sz2+: GNF header + pixel data
		if ( substr($file, $st, 4) !== 'FTX0' )
			tool::error('not FTX0', $fname, $st);

		$sz1 = tool::ordstr($file, $st+4, 4);
		$sz2 = tool::ordstr($file, $st+8, 4);

		printf("  FTX0 Header:\n");
		printf("    sz1 (data size):    0x%x (%d bytes)\n", $sz1, $sz1);
		printf("    sz2 (GNF offset):   0x%x (%d bytes)\n", $sz2, $sz2);
		printf("    Total chunk size:   0x%x (%d bytes)\n", $sz1 + $sz2, $sz1 + $sz2);
		printf("  GNF Position: 0x%x\n", $st + $sz2);

		// Extract GNF texture
		$sub = tool::substr($file, $st+$sz2, $sz1);
		aegis_gnf($sub, "$pfx.$i");

		// Move to next FTX0 chunk
		// Total size = FTX0 header (sz2) + GNF data (sz1)
		$st += ($sz1 + $sz2);
		printf("  Next Chunk: 0x%x\n\n", $st);
	} // for ( $i=0; $i < $cnt; $i++ )

	printf("======================================\n");
	printf("Extraction complete!\n");
	printf("======================================\n");
	return true;
}

function aegis( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = aegis_ftex($file, $fname);
	if ( $r )  return;

	$r = aegis_gnf($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'aegis');

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
