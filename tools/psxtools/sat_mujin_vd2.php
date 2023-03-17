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

function sect_map32( &$src, &$tile, &$pal, $fn )
{
	printf("== sect_map32( %s )\n", $fn);

	$len = strlen($tile) / 2;
	$mw = 0x40;
	$mh = $len / 0x40;

	$w = $mw * 8;
	$h = $mh * 8;

	$pix = copypix_def($w,$h);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$blk = 8 * 8;
	$pos = 0;
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($tile, $pos, 2);
				$pos += 2;

			$cid = $b1 >> 12;
			$tid = $b1 & 0xfff;
			$pix['src']['pal'] = substr($pal, $cid*0x40, 0x40);

			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$pix['src']['pix'] = substr($src, $tid*$blk, $blk);

			copypix_fast($pix, 1);
		} // for ( $x=0; $x < $w; $x += 8 )
	} // for ( $y=0; $y < $h; $y += 8 )

	savepix($fn, $pix, false);
	return;
}

function sect_map8( &$src, &$tile, &$pal, $fn )
{
	printf("== sect_map8( %s )\n", $fn);

	$len = strlen($tile) / 2;
	$mw = 0x40;
	$mh = $len / 0x40;

	$w = $mw * 8;
	$h = $mh * 8;

	$pix = copypix_def($w,$h);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;
	$pix['src']['pal'] = $pal;

	$blk = 8 * 8;
	$pos = 0;
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($tile, $pos, 2);
				$pos += 2;
			$tid = $b1 >> 1;

			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$pix['src']['pix'] = substr($src, $tid*$blk, $blk);

			copypix_fast($pix, 1);
		} // for ( $x=0; $x < $w; $x += 8 )
	} // for ( $y=0; $y < $h; $y += 8 )

	savepix($fn, $pix, false);
	return;
}

function sect_map0( &$src, &$tile, $fn )
{
	printf("== sect_map0( %s )\n", $fn);

	$len = strlen($tile) / 4;
	$mw = 0x40;
	$mh = $len / 0x40;

	$w = $mw * 8;
	$h = $mh * 8;

	$pix = copypix_def($w,$h);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$blk = 8 * 8 * 4;
	$pos = 0;
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($tile, $pos, 4);
				$pos += 4;
			$tid = $b1 >> 2;

			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$pix['src']['pix'] = substr($src, $tid*$blk, $blk);

			copypix_fast($pix, 4);
		} // for ( $x=0; $x < $w; $x += 8 )
	} // for ( $y=0; $y < $h; $y += 8 )

	savepix($fn, $pix, false);
	return;
}
//////////////////////////////
function mujin( $fname )
{
	// for *.vd2 only
	if ( stripos($fname, '.vd2') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$off1 = str2big($file, 0, 4);

	$id = 0;
	while (1)
	{
		$fn = sprintf('%s/%04d', $dir, $id);
			$id++;
		$sub = substr($file, $off1, 0x24);
			$off1 += 0x24;

		echo debug($sub, 'off1');
		$pixoff = str2big($sub,    0, 4);
		$pixsiz = str2big($sub, 0x08, 4);
		$tiloff = str2big($sub, 0x0c, 4);
		$tilsiz = str2big($sub, 0x14, 4);
		$palbpp = str2big($sub, 0x1a, 2);
		$paloff = str2big($sub, 0x1c, 4);

		if ( ! isset( $file[$off1+0x23] ) )
			break;
		if ( $pixoff == 0 )
			continue;

		$src  = substr($file, $pixoff, $pixsiz);
		$tile = substr($file, $tiloff, $tilsiz);
		$pal  = '';
		if ( $palbpp == 0 )
		{
			$src = pal555( big2little16($src) );
			sect_map0($src, $tile, $fn);
		}
		else
		if ( $palbpp == 4 || $palbpp == 8 )
		{
			$pal = substr($file, $paloff, 0x200);
			$pal = pal555( big2little16($pal) );
			sect_map8($src, $tile, $pal, $fn);
		}
		else
		if ( $palbpp == 32 )
		{
			$pal = substr($file, $paloff, 0x200);
			$pal = pal555( big2little16($pal) );
			bpp4to8($src);
			$src = big2little16($src);
			sect_map32($src, $tile, $pal, $fn);
		}
		else
			php_notice("UNKNOWN bpp %x", $palbpp);

	} // while (1)

	return;
}

for ( $i=1; $i < $argc; $i++ )
	mujin( $argv[$i] );
