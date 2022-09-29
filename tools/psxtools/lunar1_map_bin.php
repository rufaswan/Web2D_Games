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
require 'lunar1.inc';

define('NO_TRACE', 1);
//define('DRY_RUN', 1);

function map56_decode( &$file, $blk )
{
	$dec = '';
	$buf = array();

	$gp11fc = str2int($file, 0x200, 2); // lz_offset
	$gp1200 = str2int($file, 0x202, 2); // lz_end
	printf("map %x - %x\n", $gp11fc, $gp1200);

	$ed = 0x200;
	$st = 0;
	while ( $st < $ed )
	{
		$id = str2int($file, $st, 2);
			$st += 2;

		if ( ! isset($buf[$id]) )
		{
			// 5 =  80 * 2
			// 6 = 100 * 2
			$by = '';
			if ( $id === 0 )
				$by = str_repeat(ZERO.ZERO, $blk >> 1);
			else
			if ( $id < $gp11fc )
			{
				$v0 = substr($file, $id, 2);
				$by = str_repeat($v0, $blk >> 1);
			}
			else
			if ( $id < $gp1200 )
			{
				$v0 = substr($file, $id);
				$by = lunar_decode2($v0);
			}
			else
				$by = substr($file, $id, $blk);

			printf("map[%4x] = %4x\n", $id, strlen($by));
			$buf[$id] = $by;
		}

		$dec .= $buf[$id];
	} // while ( $st < $ed )
	return $dec;
}

function save_map5( &$data, $dir )
{
	printf("== save_map5( %s )\n", $dir);
	// map   = 10x10 blocks = 800x800 pixels
	// block =  8x8  tiles  =  80x80  pixels
	// tile  = 10x10 pixels
	$pix = COPYPIX_DEF(0x800,0x800);
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	$pos = 0;
	for ( $my=0; $my < 0x800; $my += 0x80 )
	{
		for ( $mx=0; $mx < 0x800; $mx += 0x80 )
		{
			// 8*8*2 = 40*2 = 80 bytes
			$sub = substr($data[5], $pos, 0x80);
				$pos += 0x80;

			if ( trim($sub,ZERO) === '' )
				continue;

			$p = 0;
			for ( $by=0; $by < 0x80; $by += 0x10 )
			{
				for ( $bx=0; $bx < 0x80; $bx += 0x10 )
				{
					$id = str2int($sub, $p, 2);
						$p += 2;

					// fedc ba98 7654 3210
					// vh-c tttt tttt tttt
					$tid = $id & 0xfff;
					$cid = ($id >> 12) & 1;
					$pix['hflip'] = $id & 0x4000;
					$pix['vflip'] = $id & 0x8000;
					// $id & 0x2000 === interact object on top

					$pix['src']['pal'] = substr($data[4], $cid*0x400, 0x400);

					$sx = $tid & BIT4;
					$sy = $tid >> 4;
					$pix['src']['pix'] = rippix8($data[3], $sx*0x10, $sy*0x10, 0x10, 0x10, 0x100, 0x400);

					$pix['dx'] = $mx + $bx;
					$pix['dy'] = $my + $by;
					copypix_fast($pix, 1);
				} // for ( $bx=0; $bx < 0x80; $bx += 0x10 )
			} // for ( $by=0; $by < 0x80; $by += 0x10 )
		} // for ( $mx=0; $mx < 0x800; $mx += 0x80 )
	} // for ( $my=0; $my < 0x800; $my += 0x80 )

	savepix("$dir/map5", $pix, false, false);
	return;
}

function save_map6( &$data, $dir )
{
	// map   = 10x10 blocks
	// block = 10x10 tiles
	// collusion?
	return;
}

function save_map7( &$data, $dir )
{
	// map   = 40x80 tiles = 200x400 pixels
	// tile  =  8x8  pixels
	$pix = COPYPIX_DEF(0x200,0x400);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$src = substr($data[3], 0x30000);
		bpp4to8($src);
	$pal = substr($data[4], 0x400, 0x400);

	$cmd = -1;
	$pos = 0;
	for ( $my=0; $my < 0x400; $my += 8 )
	{
		for ( $mx=0; $mx < 0x200; $mx += 8 )
		{
			$id = str2int($data[7], $pos, 2);
				$pos += 2;

			if ( $cmd !== -1 )
			{
				$id  = $cmd & (~0x200);
				$cmd = -1;
			}

			// fedc ba98 7654 3210
			// cccc --pt tttt tttt
			$tid = $id & 0x1ff;
			$cid = $id >> 12;

			$cmd = ( $id & 0x200 ) ? $id + 1 : -1;
			$pix['hflip'] = $id & 0x400;
			$pix['vflip'] = $id & 0x800;

			$pix['src']['pal'] = substr($pal, $cid*0x40, 0x40);

			$sx = $tid & 0x1f;
			$sy = $tid >> 5;
			$pix['src']['pix'] = rippix8($src, $sx*8, $sy*8, 8, 8, 0x100, 0x200);

			$pix['dx'] = $mx;
			$pix['dy'] = $my;
			copypix_fast($pix, 1);
		} // for ( $mx=0; $mx < 0x400; $mx += 8 )
	} // for ( $my=0; $my < 0x400; $my += 8 )

	savepix("$dir/map7", $pix, false, false);
	return;
}
//////////////////////////////
function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$id = 0;

	$data = array();

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$typ = str2int($file, $st+0, 4);
		$siz = str2int($file, $st+4, 4);
			$st += 8;
		if ( $siz === 0 )
			goto endsect;

		$fn = sprintf('%s/%04d.%x', $dir, $id, $typ);
			$id++;
		printf("%8x , %8x , %s\n", $st, $siz, $fn);

		$sub = substr($file, $st, $siz);
			$sub .= ZERO . ZERO . ZERO . ZERO;
		switch ( $typ )
		{
			case 3: // dict ok
			case 0x103: // dict ok
			case 0x203: // dict ok
			case 0x303: // dict ok
				$b1 = $typ & BIT8;
				$b2 = $typ >> 8;
				$sub = lunar_decode($sub);
				while ( strlen($sub) < 0x10000 )
					$sub .= ZERO;
				$data[$b1][$b2] = $sub;
				break;

			case 4: // dict ok
			case 0x104: // dict ok
				$b1 = $typ & BIT8;
				$b2 = $typ >> 8;
				$sub = lunar_decode($sub);
				while ( strlen($sub) < 0x400 )
					$sub .= ZERO;
				$data[$b1][$b2] = $sub;
				break;

			case 5: // error
				$sub = map56_decode($sub, 0x80);
				$data[$typ] = $sub;
				break;
			case 6: // error
				$sub = map56_decode($sub, 0x200);
				$data[$typ] = $sub;
				break;

			case 7: // dict ok
				$sub = lunar_decode($sub);
				$data[$typ] = $sub;
				break;

			case 9: // error
			case 0x10: // error
				$data[$typ] = $sub;
				break;

			default:
				return php_error('UNKNOWN type %x', $typ);
		} // switch ( $typ & BIT8 )
		save_file($fn, $sub);

		$st += $siz;
	} // while ( $st < $ed )

endsect:
	$data[3] = $data[3][0] . $data[3][1] . $data[3][2] . $data[3][3];
	$data[4] = pal555($data[4][0] . $data[4][1]);

	save_map5($data, $dir);
	//save_map6($data, $dir);
	save_map7($data, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );

/*
map_034.bin = Burg cliff , Dragon Master Grave
map_021.bin = Burg
	5,6  80016864  lhu  s1, 0(v1[8012c7ac])
 */
