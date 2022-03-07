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

function mcvstack( &$stack, &$sub, $base )
{
	$head = substr($sub, 0, 0x18);
	$sub  = substr($sub, 0x18);
	//echo debug($head);

	$mid = str2big($head, 0x06, 2);
	$mw  = str2big($head, 0x14, 2, true);
	$mh  = str2big($head, 0x16, 2, true);
	if ( $mw <= 0 || $mh <= 0 )
		return;

	// create a default struct
	if ( ! isset( $stack[$mid] ) )
	{
		$stack[$mid] = array(
			'id' => -1,
			'pix' => '',
			'pal' => '',
			'ent' => array(),
		);
	}

	// add new tileset
	$list = array();
	$cnt = $mw * $mh;
	$pos = 0;
	$is_none = true;
	while ( $cnt > 0 )
	{
		$cnt--;
		$b1 = str2big($sub, $pos, 4, true);
			$pos += 4;

		if ( $b1 < 0 )
			continue;

		$is_none = false;
		if ( $b1 > $stack[$mid]['id'] )
		{
			$stack[$mid]['id'] = $b1;
			$list[$b1] = 1;
		}
	} // while ( $cnt > 0 )

	if ( $is_none ) // nothing to draw
		return;

	$ent = array();
	$ent['base'] = $base;
	$ent['head'] = $head;
	$ent['tile'] = substr($sub, 0, $mw*$mh*4);
		$sub = substr($sub, $mw*$mh*4);
	$stack[$mid]['ent'][] = $ent;

	// tile = 8 x 8 pixel
	$cnt = count($list) * 0x40;
	$stack[$mid]['pix'] .= substr($sub, 0, $cnt);
		$sub = substr($sub, $cnt);

	if ( ! empty($sub) )
	{
		if ( strlen($sub) !== 0x200 )
			php_warning('palette not 0x200');
		$stack[$mid]['pal'] .= pal555( big2little16($sub) );
	}
	return;
}

function mvcmap( &$ent, &$pix, &$pal, $fn )
{
	$b1 = str2big($ent['head'], 0x12, 2);
		$dx = ($b1 >> 0) & 0x3f;
		$dy = ($b1 >> 6) & 0x3f;
	$mw = str2big($ent['head'], 0x14, 2);
	$mh = str2big($ent['head'], 0x16, 2);
	printf("%s , %6x , %2x + %2x = %2x , %2x + %2x = %2x\n", $fn, $ent['base'], $dx, $mw, $dx+$mw, $dy, $mh, $dy+$mh);

	$w = $mw * 8;
	$h = $mh * 8;
	$img = array(
		'cc'  => 0x100,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => str_repeat(ZERO, $w*$h),
	);

	$pos = 0;
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($ent['tile'], $pos, 4);
				$pos += 4;

			$b1 = substr($pix, $b1*0x40, 0x40);
			for ( $ty=0; $ty < 8; $ty++ )
			{
				$b2 = substr($b1, $ty*8, 8);
				$dxx = (($y + $ty) * $w) + $x;
				str_update($img['pix'], $dxx, $b2);
			} // for ( $ty=0; $ty < 8; $ty++ )
		} // for ( $x=0; $x < $w; $x += 8 )
	} // for ( $y=0; $y < $h; $y += 8 )

	save_clutfile("$fn.clut", $img);
	return;
}

function cotton( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'MCV0' )
		return;

	$stack = array();
	$ed = strlen($file);
	$st = 0x50;
	while ( $st < $ed )
	{
		$siz = str2big($file, $st, 4);
		$sub = substr ($file, $st, $siz);

		if ( $siz > 0x18 ) // skip dummy
			mcvstack($stack, $sub, $st);
		$st += $siz;
	} // while ( $st < $ed )

	$dir = str_replace('.', '_', $fname);
	foreach ( $stack as $sk => $sv )
	{
		printf("== MAP : %x\n", $sk);
		foreach ( $sv['ent'] as $skk => $svv )
		{
			$b1 = str2big($svv['head'], 6, 2);
			$fn = sprintf('%s/%x/%04d', $dir, $b1, $skk);
			mvcmap( $svv, $sv['pix'], $sv['pal'], $fn);
		} // foreach ( $sv['ent'] as $svv )
	} // foreach ( $stack as $sk => $sv )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cotton( $argv[$i] );

/*
cotton boomerang - mcv_st1.bin
	8     -84-  -- -- 1- -- -1 -- -- --  21 +  0
	16    -87-  -- -- 1- -- -1 11 -- --  21 + 30
	26    -871  -- -- 1- -- -1 11 -- -1  21 + 31
	36    -872  -- -- 1- -- -1 11 -- 1-  21 + 32
	48    -873  -- -- 1- -- -1 11 -- 11  21 + 33

	1009  -678  -- -- -1 1- -1 11 1- --  19 + 38
	1025  -639  -- -- -1 1- -- 11 1- -1  18 + 39
	1035  -5fa  -- -- -1 -1 11 11 1- 1-  17 + 3a
	1045  -5fb  -- -- -1 -1 11 11 1- 11  17 + 3b
	1057  -5bc  -- -- -1 -1 1- 11 11 --  16 + 3c
	1071  -57d  -- -- -1 -1 -1 11 11 -1  15 + 3d
	1081  -57e  -- -- -1 -1 -1 11 11 1-  15 + 3e
	1093  -53f  -- -- -1 -1 -- 11 11 11  14 + 3f
	1099  -4c-  -- -- -1 -- 11 -- -- --  13 +  0
	1107  -4c1  -- -- -1 -- 11 -- -- -1  13 +  1

	1303  -4a2  -- -- -1 -- 1- 1- -- 1-  12 +  2
	1309  -4a3  -- -- -1 -- 1- 1- -- 11  12 +  3
	1313  -4a4  -- -- -1 -- 1- 1- -1 --  12 +  4
 */
