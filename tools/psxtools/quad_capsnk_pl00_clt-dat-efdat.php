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
require 'capsnk.inc';

function sectquad( &$quad, &$atlas, &$keys, &$anim, &$hit )
{
	$quad['keyframe'] = array();
	foreach ( $keys as $kk => $kv )
	{
		list($x,$y,$w,$h) = $atlas->getxywh( $kv['atlas'] );

		$layer = array(
			array(
				'dstquad' => array(
					$kv['dx']   ,$kv['dy']   ,
					$kv['dx']+$w,$kv['dy']   ,
					$kv['dx']+$w,$kv['dy']+$h,
					$kv['dx']   ,$kv['dy']+$h,
				),
				'srcquad' => array(
					$x   ,$y   ,
					$x+$w,$y   ,
					$x+$w,$y+$h,
					$x   ,$y+$h,
				),
				'tex_id'   => 0,
				'blend_id' => 0,
			),
		);

		$kent = array(
			'name'  => "keyframe $kk",
			'layer' => $layer,
		);
		list_add($quad['keyframe'], $kk, $kent);
	} // foreach ( $keys as $kk => $kv )

	$quad['slot'] = array();
	$quad['hitbox'] = array();
	$b04 = str2int($hit,  4, 3);
	$b08 = str2int($hit,  8, 3);
	$b0c = str2int($hit, 12, 3);
	$b08p = array(
		str2int($hit, $b08 +  0, 3),
		str2int($hit, $b08 +  4, 3),
		str2int($hit, $b08 +  8, 3),
		str2int($hit, $b08 + 12, 3),
	);
	foreach ( $anim[0] as $sk => $sv )
	{
		list($kid,$bx1,$bx2) = explode(',', $sv);

		$layer = array();
		$s04 = substr($hit, $b04 + ($bx1 * 8), 8);
		for ( $i=0; $i < 4; $i++ )
		{
			$b = ord($s04[$i]);
			$s08p = substr($hit, $b08p[$i] + ($b * 8), 8);

			$w = str2int($s08p, 4, 2);
			$h = str2int($s08p, 6, 2);
			if ( $w < 1 || $h < 1 )
				continue;
			$x = str2int($s08p, 0, 2, true);
			$y = str2int($s08p, 2, 2, true);
				$x =  $x - $w;
				$y = -$y - $h;

			$lent = array(
				'debug' => "damage $bx1 $i",
				'hitquad' => array(
					$x   ,$y   ,
					$x+$w,$y   ,
					$x+$w,$y+$h,
					$x   ,$y+$h,
				),
			);
			$layer[] = $lent;
		} // for ( $i=0; $i < 4; $i++ )

		$s0c = substr($hit, $b0c + ($bx2 * 0x20), 0x20);
		$w = str2int($s0c, 4, 2);
		$h = str2int($s0c, 6, 2);
		if ( $w < 1 || $h < 1 )
		{ }
		else
		{
			$x = str2int($s0c, 0, 2, true);
			$y = str2int($s0c, 2, 2, true);
				$x =  $x - $w;
				$y = -$y - $h;

			$lent = array(
				'debug' => "attack $bx2",
				'hitquad' => array(
					$x   ,$y   ,
					$x+$w,$y   ,
					$x+$w,$y+$h,
					$x   ,$y+$h,
				),
			);
			$layer[] = $lent;
		}

		$hent = array(
			'name'  => "hitbox $sk",
			'layer' => $layer,
		);
		list_add($quad['hitbox'], $sk, $hent);

		$sent = array(
			array('type'=>'keyframe' , 'id'=>$kid),
			array('type'=>'hitbox'   , 'id'=>$sk ),
		);
		list_add($quad['slot'], $sk, $sent);
	} // foreach ( $anim[0] as $sk => $sv )

	$quad['animation'] = array();
	foreach ( $anim[1] as $ak => $av )
	{
		$time = array();
		foreach ( $av['time'] as $tv )
		{
			$tent = array(
				'time'   => $tv['fps'],
				'attach' => array('type'=>'slot' , 'id'=>$tv['slot']),
			);
			$time[] = $tent;
		} // foreach ( $av['time'] as $tv )

		$aent = array(
			'name'     => $av['name'],
			'timeline' => $time,
			'loop_id'  => $av['loop'],
		);
		list_add($quad['animation'], $ak, $aent);
	} // foreach ( $anim[1] as $ak => $av )
	return;
}
//////////////////////////////
function sectanim_set( &$anm, $pos, $name, &$anim, &$slot )
{
	$id = -1;
	$done = array();
	while (1)
	{
		$off = str2int($anm, $pos, 3);
			$pos += 4;
			$id++;
		if ( $off === 0 )
			break;
		if ( isset($done[$off]) )
			continue;
		$done[$off] = 1;

		$line = array();
		$time = array();
		$loop = -1;
		while (1)
		{
			if ( isset($line[$off]) )
			{
				$loop = $line[$off];
				break;
			}
			$line[$off] = count($time);

			$fps = str2int($anm, $off + 0, 1);
			$flg = str2int($anm, $off + 1, 1);
			$kid = str2int($anm, $off + 4, 2);
			$bx1 = str2int($anm, $off + 8, 1);
			$bx2 = str2int($anm, $off + 9, 1);
				$off += 0x14;

			$sv  = sprintf('%d,%d,%d', $kid, $bx1, $bx2);
			$sid = array_search($sv, $slot);
			if ( $sid === false )
			{
				$sid = count($slot);
				$slot[] = $sv;
			}

			$ent = array(
				'fps'  => $fps,
				'slot' => $sid,
			);
			$time[] = $ent;

			if ( $flg & 0x80 )
			{
				$b = str2int($anm, $off, 3);
				$off = $b;
				continue;
			}
			if ( $flg & 0x40 )
				break;
		} // while (1)

		$ent = array(
			'name' => sprintf('%s %d', $name, $id),
			'time' => $time,
			'loop' => $loop,
		);
		$anim[] = $ent;
	} // while (1)
	return;
}

function sectanim( &$anm )
{
	$anim = array();
	$slot = array();

	$b00 = str2int($anm,  0, 3);
	$b04 = str2int($anm,  4, 3);
	$b08 = str2int($anm,  8, 3);
	//$b0c = str2int($anm, 12, 3);

	$done = array();
	for ( $i=0; $i < 12; $i++ )
	{
		$pos = $b00 + ($i * 4);
		$off = str2int($anm, $pos, 3);
		if ( array_search($off, $done) !== false )
			continue;
		$done[] = $off;

		sectanim_set($anm, $off, "anim 0 set $i", $anim, $slot);
	} // for ( $i=0; $i < 12; $i++ )

	sectanim_set($anm, $b04, 'anim 4', $anim, $slot);
	//sectanim_set($anm, $b08, 'anim 8', $anim, $slot);
	//sectanim_set($anm, $b0c, 'anim c', $anim, $slot);
	return array($slot,$anim);
}
//////////////////////////////
function sectkeys( &$atlas, &$spr )
{
	$keys = array();
	foreach ( $spr as $sk => $sv )
	{
		$aid = $atlas->putrgba($sv['w'], $sv['h'], $sv['pix']);

		$ent = array(
			'dx'    => -$sv['cx'],
			'dy'    => -$sv['cy'],
			'atlas' => $aid,
		);
		$keys[$sk] = $ent;
	} // foreach ( $spr as $sk => $sv )
	return $keys;
}

function capsnk( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$file = capsnk_load($pfx);
	if ( $file === -1 )
		return;

	$spr = capsnk_sprite($file['dat'][0], $file['dat'][1], $file['efdat'][0], $file['clt']);

	$atlas = new atlas_tex;
	$atlas->init();
	$keys = sectkeys($atlas, $spr);
	$anim = sectanim($file['dat'][3]);

	$atlas->sort();
	$atlas->save("$pfx.0");

	$quad = load_idtagfile('psx capcom vs snk pro');
	$quad['blend'] = array( blend_modes('normal') );
	sectquad($quad, $atlas, $keys, $anim, $file['dat'][2]);

	save_quadfile($pfx, $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	capsnk( $argv[$i] );

/*
12c[6d220] = s2/11
324[6d220] = v1/15b10c
3d8[6d220] = a1/0
a0/24 = [v1]

v0/0 = [sp/1ffe18 + a1/0 + 10]
	= [8007c8bc + a1/0] = module02.bin[78]
a0/15b130 = a0/24 + v1/15b10c

v0/15b130 = (v0/0 << 2) + a0/15b130
v0/54 = [v0]

s2/15b1a4 = (s2/11 << 2) + (v0/54 + v1/15b10c)
s0/7a8 = [s2]

[8015b130..8015b15f]?
	[80162e24..80162e53]?
[801e5f70..801e5f9f]?

anim data
	[0/lbu] timer
	[1/lbu] flag
	[2/lbu] move x
	[3/lbu]
	[4/lh ] *  c = 0/keys id
	[6/] sfx/voice id
	[8/lbu] *  8 = 2[1][id]  push/damage hitbox
		<< 3 + 1a4(a2) = RAM 80158a54 = 4(m2) -> 9c(m2) + n*8
		-> 1a8(a2) = RAM 8006d3c8
		= RAM 8006d3c4
	[9/lbu] * 20 = 2[+600][id]  attack hitbox + recoil value
		<< 5 + 1a0(a2) = RAM 80158fb0 = 8(m2) -> 29c(m2) + 4*4 -> 5f8(m2) + n*20
	[a/lbu] *  4 = 2[0][id]
		<< 2 + 338(a2) = RAM 80158a3c = 0(m2) -> 84(m2) + n*4
		-> 328(a2) = RAM 8006d548
	[b/]
	[c/]
	[d/]
	[e/lbu]
		-> 126(a2) = RAM 8006d346
	[f/]
	[10/]
	[11/]
	[12/lbu]
		v1 = 22(a2[800c9020])
		if ( v1 == v0 )
		else
			22(a2) = v0
	[13/]

hitbox attack
	[0/lhu]  x  -1 to left    +1 to right
	[2/lhu]  y  -1 to bottom  +1 to top
	[4/lhu]  width
	[6/lhu]  height
	[8/lbu]  damage  (& 7f) * 64
	[9/lbu]
	[a/lbu]
	[b/lb]  opp +x
	[c/lb]  dizzy
	[d/lb]  down
	[e/lbu]  attack  (& 7f)
	[f/lb]
	[10/lb]  wait recover
	[11/]
	[12/]
	[13/lb]
	[14/lb]
	[15/lbu]
	[16/]
	[17/]
	[18/]
	[19/lbu]
	[1a/]
	[1b/lb]
	[1c/lbu]
	[1d/]
	[1e/]
	[1f/]
 */
