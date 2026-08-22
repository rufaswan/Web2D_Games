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
tool::require('class-clutfile');

function gimpix( string &$pix, int $w, int $h ) : void
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
				$sub = tool::substr($pix, $pos, 16);
					$pos += 16;
				$dyy = ($by + $y) * $w;
				$dxx = $dyy + $bx;
				tool::str_update($buf, $dxx, $sub);
			} // for ( $y=0; $y < 8; $y++ )
		} // for ( $bx=0; $bx < $w; $bx += 16 )
	} // for ( $by=0; $by < $h; $by += 8 )

	$pix = $buf;
}
//////////////////////////////
function pspgim_pix( string &$file ) : array
{
	$size = tool::ordstr($file, 0, 2);
	$type = tool::ordstr($file, 4, 2);
	//$swiz = tool::ordstr($file, 6, 2);
	$w = tool::ordstr($file,  8, 2);
	$h = tool::ordstr($file, 10, 2);

	$pos  = $size + 0x10;
	$data = [];
	switch ( $type )
	{
		case 3:
			printf("TYPE %x RGBA32 , %x x %x\n", $type, $w, $h);
			$size = 4 * $w * $h;
			$data['byte'] = 4;
			$data['w'] = $w;
			$data['h'] = $h;
			$data['pix'] = tool::substr($file, $pos, $size);
			// all RGBA32 are palette
			return $data;

		case 4:
			printf("TYPE %x 4-bpp , %x x %x\n", $type, $w, $h);
			// dummy 1x1 texture
			if ( $w === 1 && $h === 1 )
				return [];
			$size = $w / 2 * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;

			$data['pix'] = tool::substr($file, $pos, $size);
			gimpix($data['pix'], $w, $h);
			psx::bpp4to8($data['pix']);
			return $data;

		case 5:
			printf("TYPE %x 8-bpp , %x x %x\n", $type, $w, $h);
			$size = $w * $h;
			$data['byte'] = 1;
			$data['w'] = $w;
			$data['h'] = $h;
			$data['pix'] = tool::substr($file, $pos, $size);
			gimpix($data['pix'], $w, $h);
			return $data;

		default:
			tool::error('TYPE UNKNOWN', $type);
	} // switch ( $type )
	return $data;
}

function grand_gim( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 11) !== 'MIG.00.1PSP' )
		tool::error('not GIM');

	$pix = '';
	$pal = '';

	$pos = 0x10;
	while (1)
	{
		$blk   = tool::ordstr($file, $pos+ 0, 2);
		$bsize = tool::ordstr($file, $pos+ 4, 4);
		$bnext = tool::ordstr($file, $pos+ 8, 4);
		$bdata = tool::ordstr($file, $pos+12, 4);
		switch ( $blk )
		{
			case 2:
				printf("%8x , %s/root\n", $pos, $fname);
				$pos += $bnext;
				break;
			case 3:
				printf("%8x , %s/picture\n", $pos, $fname);
				$pos += $bnext;
				break;
			case 4:
				printf("%8x , %s/image\n", $pos, $fname);
				if ( ! empty($pix) )
					tool::error('multiple image blocks');

				$sub = tool::substr($file, $pos+$bdata, $bnext);
				$pix = pspgim_pix($sub);
				$pos += $bnext;
				break;
			case 5:
				printf("%8x , %s/palette\n", $pos, $fname);
				if ( ! empty($pal) )
					tool::error('multiple palette blocks');

				$sub = tool::substr($file, $pos+$bdata, $bnext);
				$pal = pspgim_pix($sub);
				$pos += $bnext;
				break;
			case 0:
				break 2;
			default:
				tool::error('UNKNOWN', $pos);
		}
	} // while (1)

	// dummy 1x1 texture
	if ( empty($pix) )
		return (bool)tool::warning('empty pix');

	$fn = sprintf('%s.gim', $fname);
	$img = new clutdata;
		$img->w = $pix['w'];
		$img->h = $pix['h'];
		$img->pix = $pix['pix'];
	if ( ! empty($pal) )
		$img->pal = $pal['pix'];
	clutfile::save($fn, $img);
	return true;
}

function grand_ftex( string &$file, string $fname ) : bool
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
		tool::trace('GIM', $st, $sz1, $fn);

		$sub = tool::substr($file, $st+$sz2, $sz1);
		grand_gim($sub, "$pfx.$i");

		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function grand( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = grand_ftex($file, $fname);
	if ( $r )  return;

	$r = grand_gim($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'grand');
