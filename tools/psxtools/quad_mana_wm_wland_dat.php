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
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';
require 'quad_mana.inc';

function wmland_keys( &$dat, &$atlas, &$pal, &$pix )
{
	$keys = array();
	$hits = array();
	$id   = -1;
	for ( $i = 0x40; $i < 0x80; $i += 2 )
	{
		$pos = str2int($dat, $i, 2);
			$id++;
		if ( $pos === 0 )
			continue;

		$cnt = ord( $dat[$pos] );
			$pos++;

		$kent = array();
		for ( $j=0; $j < $cnt; $j++ )
		{
			$s = substr($dat, $pos, 0xc);
				$pos += 0xc;

			// 0  1  2  3  4 5 6  7  8 9  a b
			// dx dy sx sy w h cn fg - bl - -
			$dx = sint8( $s[0] );
			$dy = sint8( $s[1] );

			$sx  = ord( $s[2] );
			$sy  = ord( $s[3] );
			$w   = ord( $s[4] );
			$h   = ord( $s[5] );
			$cid = ord( $s[6] );

			$blend = ord( $s[9] );

			$srcpix = rippix8($pix, $sx, $sy, $w, $h, 0x100, 0x3e);
			$srcpal = substr ($pal, $cid*0x40, 0x40);
				$srcpal[3] = ZERO;
			$aid = $atlas->putclut($w, $h, $srcpal, $srcpix);

			// default order is top-to-bottom
			$en = array(
				'dx'  => $dx,
				'dy'  => $dy,
				'rot' => 0,
				'bit' => 0,
				'blend' => $blend,
				'atlas' => $aid,
			);
			array_unshift($kent, $en); // add in reverse
		} // for ( $j=0; $j < $cnt; $j++ )

		$keys[$id] = $kent;
	} // for ( $i = 0x40; $i < 0x80; $i++ )

	return array($keys,$hits);
}

function wmland_anim( &$dat, &$quad )
{
	$quad['animation'] = array();
	$id   = -1;
	for ( $i=0; $i < 0x40; $i += 2 )
	{
		$pos = str2int($dat, $i, 2);
			$id++;
		if ( $pos === 0 )
			continue;

		$time = array();
		while (1)
		{
			$key = ord( $dat[$pos+0] );
			$fps = ord( $dat[$pos+1] );
			// +2
			// +3
				$pos += 4;
			if ( $key === BIT8 || $fps === BIT8 )
				break;

			$ent = array(
				'time' => $fps,
				'attach' => array(
					'type' => 'keyframe',
					'id'   => $key,
				),
			);
			$time[] = $ent;
		} // while (1)

		$anim = array(
			'name'     => "animation $id",
			'timeline' => $time,
			'loop_id'  => 0,
		);
		list_add($quad['animation'], $id, $anim);
	} // for ( $i=0; $i < 0x40; $i += 2 )
	return;
}

function mana( $fname )
{
	// for *.dat only
	if ( stripos($fname, '.dat') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0x2000,4) !== '####' )
		return;

	$pix = substr($file, 0     , 0x1f00);
	$pal = substr($file, 0x1f00, 0x100 );
	$dat = substr($file, 0x2004);

	bpp4to8($pix);
	$pal = pal555($pal);

	$atlas = new AtlasTex;
	$atlas->init();

	list($keys,$hits) = wmland_keys($dat, $atlas, $pal, $pix);

	$atlas->sort();
	$atlas->save("$fname.0");

	$quad = load_idtagfile('ps1 legend of mana');
	$quad['blend'] = mana_blend();
	sectquad($atlas, $keys, $hits, $quad);
	wmland_anim($dat, $quad);

	save_quadfile($fname, $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
if ( strlen($file) > 0x4000 ) // for /wm/test/landdata.dat

v9 == 0
	bone.dat
	gato.dat
	gomiyama.dat
	jungle.dat
	kiruma.dat
	naraku.dat
	orcha.dat
	rusheim.dat
	rushei2.dat
	ryuon.dat
	sand.dat
	urukan.dat
	wmori.dat
v9 != 1
	fiegu.dat
	kirameki.dat
	norun.dat
	roa.dat

// image masking
// Legend of Mana - /wm/wmland/roa.dat
//  38,46  0-4  fg#502808 + bg#784048
//
// screenshot
//  2 #d76f50 =+603008 =0 1,3
//  6 #c76750 =+502808 =1 1
//  1 #a75748 =+301800
//  5 #974f48 =+201000
//  4 #874848 =+100800
//
//  97,93 += 135,139 / +7,+11
//  0  1,3
//  1  1
//  2  3,3,3
//  3  3,3
//  4  3
 */
