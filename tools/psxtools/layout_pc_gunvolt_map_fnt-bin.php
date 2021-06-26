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

$gp_pix = array();

/*
//////////////////////////////
function srcfnt8( &$pix, $int, $x, $y )
{
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;
	$pix['dx'] = $x * 8;
	$pix['dy'] = $y * 8;

	// fedcba98 76543210 fedcba98  7  6543210
	// -------- -------- --------  -  col
	$b1 = ($int >>  0) & BIT8;
	$b2 = ($int >>  8) & BIT8;
	$b3 = ($int >> 16) & BIT8;
	$b4 = ($int >> 24) & BIT8;

	//$pix['hflip'] = $int & 0x80;
	//$pix['vflip'] = $int & 0x40;

	$col = ($b1 & 0x7f) * 8;
	$row = ($b2 & 0x7f) * 8;
	$tid = 0;

	global $gp_pix;
	$pix['src']['pix'] = '';
	for ( $y=0; $y < 8; $y++ )
	{
		$syy = ($row + $y) * $gp_pix[$tid]['w'];
		$sxx = $syy + $col;

		$pix['src']['pix'] .= substr($gp_pix[$tid]['pix'], $sxx*4, 8*4);
	} // for ( $y=0; $y < 8; $y++ )

	return;
}

function sectmap( &$bin, $pfx, $p1, $p2, $p3, $off, $map_w, $map_h, $tile_s )
{
	printf("== sectmap( %s , %x , %x , %x , %x , %x , %x , %x )\n", $pfx, $p1, $p2, $p3, $off, $map_w, $map_h, $tile_s);
	$id = 0;
	while ( $p3 < $p1 )
	{
		printf("%x , map %04d\n", $p3, $id);
		$pix = COPYPIX_DEF();
		$pix['rgba']['w'] = $map_w * $tile_s;
		$pix['rgba']['h'] = $map_h * $tile_s;
		$pix['rgba']['pix'] = canvpix($pix['rgba']['w'] , $pix['rgba']['h']);

		$map1 = '';
		$map2 = '';
		for ( $y=0; $y < $map_h; $y++ )
		{
			for ( $x=0; $x < $map_w; $x++ )
			{
				$b1 = str2int($bin, $p3, 2);
					$p3 += 2;
				$b2 = str2int($bin, $off+$b1*4, 4);
				$map1 .= sprintf("%4x ", $b1);
				$map2 .= sprintf("%8x ", $b2);

				mgv_src($pix, $b2, $x, $y);
				copypix_fast($pix, 4);
			} // for ( $x=0; $x < $map_w; $x++ )

			$map1 .= "\n";
			$map2 .= "\n";
		} // for ( $y=0; $y < $map_h; $y++ )

		echo "$map1\n";
		echo "$map2\n";

		$fn = sprintf("%s/map_%04d", $pfx, $id);
		savepix($fn, $pix, false);
		$id++;
	} // while ( $p3 < $p1 )

	return;
}
//////////////////////////////
function gva_bin( &$bin, $pfx )
{
	echo "== gva_bin( $pfx )\n";
	$p1 = str2int($bin, 0x10, 4);
	$p2 = str2int($bin, 0x34, 4) + 0x30;
	$p3 = str2int($bin, 0x38, 4) + 0x30;
	$off = 0x3c;

	// max fnt = 400x400
	listmapid($bin, $p3 , $p1, 2);
	listmapid($bin, $off, $p2, 4);
	//sectmap($bin, $pfx, $p1, $p2, $p3, $off, 27, 15, 8);
	return;
}
//////////////////////////////
function gv2_src( &$pix, $int, $x, $y, $ix, $iy )
{
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;
	$pix['dx'] = ($x * 16) + $ix;
	$pix['dy'] = ($y * 16) + $iy;

	// fedcba98 76543210  fedcba98  7   6   543210
	// -------- --------  row       hf  vf  col
	$b1 = ($int >>  0) & BIT8;
	$b2 = ($int >>  8) & BIT8;
	//$b3 = ($int >> 16) & BIT8;
	//$b4 = ($int >> 24) & BIT8;
	flag_watch('gv2_src', $int & 0xffff0000);

	//$pix['hflip'] = $b1 & 0x80;
	//$pix['vflip'] = $b1 & 0x40;

	$col = ($b1 & 0x7f) * 8;
	$row =  $b2 * 8;
	$tid = 1;

	global $gp_pix;
	$pix['src']['pix'] = riprgba($gp_pix[$tid]['pix'], $col, $row, 8, 8, $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	return;
}

function gv2_tile( &$bin, $pfx, $st, $ed )
{
	$cnt = ($ed - $st) / 0x10;
	$map_w = 16;
	$map_h = ceil( $cnt/$map_w );

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w * 16;
	$pix['rgba']['h'] = $map_h * 16;
	$pix['rgba']['pix'] = canvpix($pix['rgba']['w'] , $pix['rgba']['h']);

	for ( $y=0; $y < $map_h; $y++ )
	{
		for ( $x=0; $x < $map_w; $x++ )
		{
			$b1 = str2int($bin, $st+ 0, 4);
			$b2 = str2int($bin, $st+ 4, 4);
			$b3 = str2int($bin, $st+ 8, 4);
			$b4 = str2int($bin, $st+12, 4);
				$st += 16;

			gv2_src($pix, $b1, $x, $y, 0, 0);
			copypix_fast($pix, 4);
			gv2_src($pix, $b2, $x, $y, 8, 0);
			copypix_fast($pix, 4);
			gv2_src($pix, $b3, $x, $y, 0, 8);
			copypix_fast($pix, 4);
			gv2_src($pix, $b4, $x, $y, 8, 8);
			copypix_fast($pix, 4);
		} // for ( $x=0; $x < $map_w; $x++ )
	} // for ( $y=0; $y < $map_h; $y++ )

	savepix("$pfx/tile", $pix, false);
	return;
}

function gv2_bin( &$bin, $pfx )
{
	echo "== gv2_bin( $pfx )\n";
	$p1 = str2int($bin, 8, 4);
	$p2 = str2int($bin, 0x1c, 4) + 0x18;
	$p3 = str2int($bin, 0x20, 4) + 0x18;
	$off = 0x24;

	// max fnt = 400x200
	listmapid($bin, $p3 , $p1, 2);
	listmapid($bin, $off, $p2, 4);
	//sectmap($bin, $pfx, $p1, $p2, $p3, $off, 25, 15, 8);
	gv2_tile($bin, $pfx, $off, $p2);
	return;
}
//////////////////////////////
function mgv_src( &$pix, $int, $x, $y )
{
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;
	$pix['dx'] = $x * 16;
	$pix['dy'] = $y * 16;

	// fedcba98 76543210  fedcba98  7   6   543210
	// -------- --------  row       hf  vf  col
	$b1 = ($int >>  0) & BIT8;
	$b2 = ($int >>  8) & BIT8;
	//$b3 = ($int >> 16) & BIT8;
	//$b4 = ($int >> 24) & BIT8;
	flag_watch('mgv_src', $int & 0xffff0000);

	$pix['hflip'] = $b1 & 0x80;
	$pix['vflip'] = $b1 & 0x40;

	$col = ($b1 & 0x3f) * 16;
	$row =  $b2 * 16;
	$tid = 0;

	global $gp_pix;
	$pix['src']['pix'] = riprgba($gp_pix[$tid]['pix'], $col, $row, 16, 16, $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	return;
}
function mgv_room( &$bin, $rps, $off, &$pix, $ax, $ay )
{
	for ( $ry=0; $ry < 15; $ry++ )
	{
		for ( $rx=0; $rx < 16; $rx++ )
		{
			$lid = str2int($bin, $rps, 2);
				$rps += 2;
			$int = str2int($bin, $off+$lid*4, 4);

			mgv_src($pix, $int, $ax+$rx, $ay+$ry);
			copypix_fast($pix, 4);
		} // for ( $rx=0; $rx < 16; $rx++ )
	} // for ( $ry=0; $ry < 15; $ry++ )

	return;
}
function mgv_area( &$bin, $pfx, $p1, $p2, $p3, $off )
{
	// 0    4    8    c    10   14   18   1c
	// sz1  ps1  sz2  ps2  sz3  ps3  sz4  ps4
	$sz  = str2int($bin, $p1+0, 4);
	$st  = str2int($bin, $p1+4, 4);
	$mgc = substr ($bin, $p1+$st, 8);
		$p1 += $st;

	$aw = str2int($mgc, 0, 2);
	$ah = str2int($mgc, 6, 2);

	$id = 0;
	while (1)
	{
		$sub = substr($bin, $p1, $sz);
			$p1 += $sz;
		if ( substr($sub,0,8) !== $mgc )
			break;

		$st = 8;
		$pix = COPYPIX_DEF();
		$pix['rgba']['w'] = $aw * 16 * 16;
		$pix['rgba']['h'] = $ah * 15 * 16;
		$pix['rgba']['pix'] = canvpix($pix['rgba']['w'] , $pix['rgba']['h']);

		for ( $ay=0; $ay < $ah; $ay++ )
		{
			for ( $ax=0; $ax < $aw; $ax++ )
			{
				$rid = str2int($sub, $st, 2);
					$st += 2;

				$rps = $p3 + ($rid * 16 * 15 * 2);
				mgv_room($bin, $rps, $off, $pix, $ax*16, $ay*15);
			} // for ( $ax=0; $ax < $aw; $ax++ )
		} // for ( $ay=0; $ay < $ah; $ay++ )

		$fn = sprintf("%s/map-%04d", $pfx, $id);
		savepix($fn, $pix, false);
		$id++;
	} // while (1)

	return;
}
function mgv_tile( &$bin, $pfx, $st, $ed )
{
	$cnt = ($ed - $st) / 4;
	$map_w = 16;
	$map_h = ceil( $cnt/$map_w );

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w * 16;
	$pix['rgba']['h'] = $map_h * 16;
	$pix['rgba']['pix'] = canvpix($pix['rgba']['w'] , $pix['rgba']['h']);

	for ( $y=0; $y < $map_h; $y++ )
	{
		for ( $x=0; $x < $map_w; $x++ )
		{
			$b1 = str2int($bin, $st, 4);
				$st += 4;

			mgv_src($pix, $b1, $x, $y);
			copypix_fast($pix, 4);
		} // for ( $x=0; $x < $map_w; $x++ )
	} // for ( $y=0; $y < $map_h; $y++ )

	savepix("$pfx/tile", $pix, false);
	return;
}
function mgv_bin( &$bin, $pfx )
{
	echo "== mgv_bin( $pfx )\n";
	$p1 = str2int($bin, 8, 4);
	$p2 = str2int($bin, 0x20, 4) + 0x18;
	$p3 = str2int($bin, 0x24, 4) + 0x18;
	$off = 0x28;

	// max fnt = 400x80
	listmapid($bin, $p3 , $p1, 2);
	listmapid($bin, $off, $p2, 4);
	mgv_tile($bin, $pfx, $off, $p2);
	mgv_area($bin, $pfx, $p1 , $p2, $p3, $off);
	return;
}
//////////////////////////////
function listmapid( &$bin, $st, $ed, $by )
{
	$list = array();
	for ( $i=$st; $i < $ed; $i += $by )
	{
		$b = str2int($bin, $i, $by);
		if ( ! isset($list[$b]) )
			$list[$b] = 0;
		$list[$b]++;
	}
	ksort($list);
	printf("== %s( %d ) , %x = %x\n", __FUNCTION__, $by, count($list), ($ed-$st)/$by);
	foreach ( $list as $k => $v )
		printf("  %8x = %4x\n", $k, $v);
	return;
}

function sect_bin( &$bin, $pfx )
{
	if ( $bin[4] == "\x18" && $bin[0x18] == "\x01" && $bin[0x1c] == "\x10" )
		return mgv_bin($bin, $pfx);
	if ( $bin[4] == "\x18" && $bin[0x18] == "\x0c" )
		return gv2_bin($bin, $pfx);
	if ( $bin[4] == "\x00" && $bin[0x30] == "\x0c" )
		return gva_bin($bin, $pfx);

	php_error("UNKNOWN %s.bin", $pfx);
	return;
}
*/
//////////////////////////////
function origtile( &$bin, $pfx )
{
	$len = strlen( $bin['data'][0] );

	return;
}

function bin2sect( &$bin, $h1, $h2, $h3, $p1, $p2, $p3 )
{
	$b1 = str2int($bin, $h1, 4);
	$b2 = str2int($bin, $h2, 4);
	$b3 = str2int($bin, $h3, 4);
	$s1 = substr ($bin, $b1, $b2-$b1);
	$s2 = substr ($bin, $b2, $b3-$b2);

	$b1 = str2int($s1, $p1, 4);
	$b2 = str2int($s1, $p2, 4);
	$b3 = str2int($s1, $p3, 4);
	$s3 = substr ($s1, $b1, $b2-$b1);
	$s4 = substr ($s1, $b2, $b3-$b2);
	$s5 = substr ($s1, $b3);
	return array($s3, $s4, $s5, $s2);
}

function sect_bin( &$bin, $pfx )
{
	if ( $bin[4] == "\x18" && $bin[0x18] == "\x01" && $bin[0x1c] == "\x10" )
	{
		$bin = array(
			'type'   => 'mgv',
			'data'   => bin2sect($bin, 4,8,12 , 4,8,12),
			'tile_s' => 16, // size 16x16
			'tile_b' =>  4, // 1 * 16x16 = 4 bytes
			'tile_d' =>  2, // data
			'map_w'  => 16,
			'map_h'  => 15,
		);
	}
	else
	if ( $bin[4] == "\x18" && $bin[0x18] == "\x0c" )
	{
		$bin = array(
			'type'   => 'gv2',
			'data'   => bin2sect($bin, 4,8,12 , 0,4,8),
			'tile_s' =>  8, // size 8x8
			'tile_b' => 16, // 4 * 8x8 = 16 bytes
			'tile_d' =>  4, // data
			'map_w'  => 25,
			'map_h'  => 15,
		);
	}
	else
	if ( $bin[4] == "\x00" && $bin[0x30] == "\x0c" )
	{
		$bin = array(
			'type'   => 'gva',
			'data'   => bin2sect($bin, 8,16,24 , 0,4,8),
			'tile_s' =>  8, // size 8x8
			'tile_b' => 16, // 4 * 8x8 = 16 bytes
			'tile_d' =>  4, // data
			'map_w'  => 27,
			'map_h'  => 15,
		);
	}
	else
		return php_error("UNKNOWN %s.bin", $pfx);

	foreach ( $bin['data'] as $k => $v )
		save_file("$pfx/meta/$k.meta", $v);

	origtile($bin, $pfx);
	//
	return;
}
//////////////////////////////
function sect_pix( &$fnt, $off, $w, $h )
{
	$pix = '';
	$y = $h;
	while ( $y > 0 )
	{
		$y--;
		$p = $off + ($y * $w * 4);
		for ( $x=0; $x < $w; $x++ )
		{
			$pix .= $fnt[$p+2];
			$pix .= $fnt[$p+1];
			$pix .= $fnt[$p+0];
			$pix .= $fnt[$p+3];
				$p += 4;
		} // for ( $x=0; $x < $w; $x++ )
	} // while ( $y > 0 )

	return $pix;
}

function sect_fnt( &$fnt, $pfx )
{
	$len = strlen($fnt);
	if ( str2int($fnt, 8,4) == $len ) // mgv gv2
		$pos = str2int($fnt, 4, 4);
	else
	if ( str2int($fnt,16,4) == $len ) // gva
		$pos = str2int($fnt, 8, 4);
	else
		return;

	global $gp_pix;
	$cnt = str2int($fnt, $pos, 4);
	$off = $pos + 4 + ($cnt * 0x20 * 2);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = $pos + 4 + ($i * 0x20);

		$p2 = str2int($fnt, $p1+ 0, 4);
		$sz = str2int($fnt, $p1+ 4, 4);
		$st = str2int($fnt, $p1+ 8, 4);
		$w  = str2int($fnt, $p1+16, 2);
		$h  = str2int($fnt, $p1+18, 2);
		if ( ($w*$h*4) != $sz )
			return php_error("%d  %x x %x != %x", $i, $w, $h, $sz);

		$fn = substr0($fnt, $pos+$p2);
		$img = array(
			'w' => $w,
			'h' => $h,
			'pix' => sect_pix($fnt, $off+$st, $w, $h),
		);
		printf("%4x x %4x , %s/%s\n", $w, $h, $pfx, $fn);

		$gp_pix[$i] = $img;
		save_clutfile("$pfx/$fn.rgba", $img);
	} // for ( $i=0; $i < $cnt; $i++ )

	return;
}
//////////////////////////////
function gunvolt( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$fnt = load_file("$pfx.fnt");
	$bin = load_file("$pfx.bin");
	if ( empty($fnt) || empty($bin) )
		return;

	global $gp_pix;
	$gp_pix = array();

	sect_fnt($fnt, $pfx);
	if ( empty($gp_pix) )
		return;

	sect_bin($bin, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );

/*
mgv
	screen = 16x15 tile
	tile   = 16x16 pixel
	set    = 16x   tile
gv2
	screen = 25x15 tile
	tile   =  8x8  pixel , st01_a 16x16 pixel
	set    = 16x   tile
gva
	screen =   x   tile
	tile   =  8x8  pixel
	set    = 16x   tile


gv2 st1403_a
	0    x288  tga 0 [  0-120]
	288  x112  tga 2 [120- 70]
	400  x486  tga 1 [190-1e6]
 */
