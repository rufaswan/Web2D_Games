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

define('NO_TRACE', true);

function LZ00_decode( &$sub )
{
	trace("== LZ00_decode\n");
	$dec = '';
	$len = strlen($sub);
	$pos = 8;

	while ( $pos < $len )
	{
		trace("%6x  %6x  ", $pos, strlen($dec));
		$b1 = ord( $sub[$pos] );
			$pos++;

		if ( $b1 < 0x10 )
		{
			$dlen = $b1 + 1;
			trace("COPY LEN %x\n", $dlen);
			$dec .= substr($sub, $pos, $dlen);
			$pos += $dlen;
		}
		else
		{
			$b2 = ord( $sub[$pos] );
				$pos++;
			$dpos = $b1 - 0x10;
			$dlen = $b2 + 3;
			trace("REF  POS -%x LEN %x\n", $dpos, $dlen);

			for ( $i=0; $i < $dlen; $i++ )
			{
				$p = strlen($dec) - 1 - $dpos;
				$dec .= $dec[$p];
			}
		}
	} // while ( $pos < $len )

	$sub = $dec;
	return;
}
//////////////////////////////
function sect_map( &$spt, &$scp, &$spl, $fn, $w, $h, $cid, $bpp )
{
	printf("== sect_map( %s , %x , %x , %x , %x )\n", $fn, $w, $h, $cid, $bpp);

	$pix = copypix_def($w,$h);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$pos = 0;
	$map = '';
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($spt, $pos+0, 2);
			$b2 = str2big($spt, $pos+2, 2);
				$pos += 4;
			$map .= sprintf('%4x ', $b2);

			$pix['dx'] = $x;
			$pix['dy'] = $y;

			flag_watch('spt', $b1 & 0x3f00);
			$pix['hflip'] = $b1 & 0x4000;
			$pix['vflip'] = $b1 & 0x8000;

			$nid = $b1 & BIT8; // override default palette
			if ( $nid === 0 )
				$nid = $cid;

			if ( $bpp == 4 )
			{
				$tid = $b2;
				$pix['src']['pal'] = substr($spl, $nid*0x40, 0x40);

				$b2 = substr($scp, $tid*0x20, 0x20);
				bpp4to8($b2);
				$src = big2little16($b2);
			}
			if ( $bpp == 8 )
			{
				$tid = $b2 >> 1;
				$pix['src']['pal'] = substr($spl, $nid*0x40, 0x400);

				$src = substr($scp, $tid*0x40, 0x40);
			}
			$pix['src']['pix'] = $src;

			copypix_fast($pix);
		} // for ( $x=0; $x < $w; $x += 8 )
		$map .= "\n";

	} // for ( $y=0; $y < $h; $y += 8 )
	echo "$map\n";

	savepix($fn, $pix, false);
	return;
}

function sch_spt_scp( &$file, $dir )
{
	$sch = ''; // sets
	$spt = ''; // tile map data
	$scp = ''; // tile pixel data , not used if LZ00
	$spl = ''; // palette data
	foreach ( $file as $k => $v )
	{
		if ( strpos($k, '.sch') )
			$sch = $v;
		else
		if ( strpos($k, '.spt') )
			$spt = $v;
		else
		if ( strpos($k, '.scp') )
			$scp = $v;
		else
		if ( strpos($k, '.spl') )
			$spl = $v;
	} // foreach ( $file as $k => $v )

	if ( empty($sch) || empty($spt) || empty($spl) )
		return;

	$spl = pal555( big2little16($spl) );

	$schps = 0;
	$schid = 0;
	while (1)
	{
		$b1 = str2big($sch, $schps+0, 4, true);
			$schps += 4;
		if ( $b1 < 0 )
			return;
		$sptps = str2big($sch, $b1+0, 4);
		$cid   = str2big($sch, $b1+4, 2);

		$fn = sprintf('%s/%04d', $dir, $schid);
			$schid++;
		printf("%8x , %8x , %4x , %s\n", $b1, $sptps, $cid, $fn);

		$head = substr($spt, $sptps+0, 0x20);
		echo debug($head, 'SPT');

		$type  = str2big($head, 0, 2);
		$sptw  = str2big($head, 4, 2);
		$spth  = str2big($head, 6, 2);
		$sptsz = str2big($head, 8, 4);

		$body = substr($spt, $sptps+0x20, $sptsz);

		if ( substr($body,0,4) === 'LZ00' ) // full image
		{
			$sptsz = str2big($body, 4, 4);
			LZ00_decode($body);
		}

		switch ( $type )
		{
			case 0x0000:
				printf("[4 bpp] %x x %x = %x\n", $sptw, $spth, $sptsz);
				$pal = substr($spl, $cid*0x40, 0x40);
				bpp4to8($body);
				$pix = big2little16($body);

				$img = array(
					'cc'  => 0x10,
					'w'   => $sptw,
					'h'   => $spth,
					'pal' => $pal,
					'pix' => $pix,
				);
				save_clutfile("$fn.clut", $img);
				break;
			case 0x0001:
				printf("[8 bpp] %x x %x = %x\n", $sptw, $spth, $sptsz);
				$pal = substr($spl, $cid*0x40, 0x400);
				$pix = $body;

				$img = array(
					'cc'  => 0x100,
					'w'   => $sptw,
					'h'   => $spth,
					'pal' => $pal,
					'pix' => $pix,
				);
				save_clutfile("$fn.clut", $img);
				break;
			case 0x0100:
				printf("[SCP-4] %x x %x = %x\n", $sptw, $spth, $sptsz);
				sect_map($body, $scp, $spl, $fn, $sptw, $spth, $cid, 4);
				break;
			case 0x0101:
				printf("[SCP-8] %x x %x = %x\n", $sptw, $spth, $sptsz);
				sect_map($body, $scp, $spl, $fn, $sptw, $spth, $cid, 8);
				break;
			default:
				php_error('UNKNOWN type %x', $type);
				break;
		} // switch ( $type )

	} // while (1)
	return;
}
//////////////////////////////
function cottonmf( &$file, $dir )
{
	$data = array();
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$siz = str2big($file, $st+0, 4, true);
		if ( $siz <= 0 )
			break;

		$fnm = substr ($file, $st+4, 16);
			$fnm = rtrim($fnm, ZERO);
			$fnm = strtolower($fnm);
		$rsz = str2big($file, $st+0x14, 4);

		printf("%6x , %6x , %6x , %s\n", $st, $siz, $rsz, $fnm);
		$sub = substr($file, $st+0x20, $rsz);

		$data[$fnm] = $sub;
		save_file("$dir/mf/$fnm", $sub);

		$st += $siz;
	} // while ( $st < $ed )

	$file = $data;
	return;
}

function cotton( $fname )
{
	// for *.mf only
	if ( stripos($fname, '.mf') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	cottonmf($file, $dir);
	sch_spt_scp($file, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cotton( $argv[$i] );

/*
cotton boomerang - chr_ttl.mf/0005.clut

	00 00 10 ff ff ff ff db
	03 [02 22 22 22]
		0*5d
		[2 22*3]
	ff 00 15 03 ff 5c
	08 [22 22 d2 22 20 00 02 2d dd]
		0*3 22*3 0*62
		[22*2 d2 22 20 0 2 2d dd]
	7d 5c e3 00
	0a [02 22 df ff d2 22 00 02 df ff f2]
		22*3 0*5c 2 22*2
		[2 22 df ff d2 22 0 2 df ff f2]
	ea 5b da 00
	04 [22 2d ff ff fd]
		22*3 0*5b 22*3
		[22 2d ff*2 fd]
	7b 00
		22 0 2 = -6b,3
	01 [2f ff]
		[2f ff]
	17 01
		ff*2 fd 22 = -7,4
	00 [20]
		[20]
	e6 58
		0*59 2 22 = -d6,5b
	01 [df d2]
		[df d2]
	e6 00
		22 df ff = -d6,3
	7b 02
		ff fd 22 0 2 = -6b,5
	82 00
		2d ff*2 = -72,3
	02 [ff ff f2]
		[ff*2 f2]
	7b
 */
