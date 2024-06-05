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
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';

$gp_def_anim = array(
	0 => 'idle',   // default
	1 => 'jump',   // press up
	2 => 'crouch', // press down
	3 => 'walk',   // press left/right
);

function sectpix( &$meta )
{
	$pal = substr($meta, 0, 0x200);
		$pal = pal555($pal);

	$pix = substr($meta, 0x200);
	while ( strlen($pix) & 0x7fff )
		$pix .= ZERO;
	$dec = '';

	$len = strlen($pix);
	$pos = 0;
	$sh  = 0;
	while ( $pos < $len )
	{
		$cvs = str_repeat(ZERO, 0x8000);
		for ( $y=0; $y < 0x100; $y += 0x20 )
		{
			for ( $x=0; $x < 0x80; $x += 0x10 )
			{
				for ( $ty=0; $ty < 0x20; $ty++ )
				{
					$dyy = ($y + $ty) * 0x80;
					$dxx = $dyy + $x;

					$b1 = substr($pix, $pos, 0x10);
						$pos += 0x10;
					str_update($cvs, $dxx, $b1);
				}
			} // for ( $x=0; $x < 0x80; $x += 0x10 )
		} // for ( $y=0; $y < 0x100; $y += 0x20 )
		$dec .= $cvs;
		$sh  += 0x100;
	} // while ( $pos < $len )

	bpp4to8($dec);
	$meta = array(
		'pal' => $pal,
		'pix' => $dec,
		'w'   => 0x80*2,
		'h'   => $sh,
	);
	return;
}

function sectatlas( &$atlas, $meta, $tex )
{
	$cnt = str2int($meta,  8, 4);
	$pos = str2int($meta, 12, 4);
	$key = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$off = str2int($meta, $pos, 4);
			$pos += 4;
		if ( $off === 0 )
			continue;
		printf("key %x\n", $i);

		$num = str2int($meta, $off, 4);
			$off += 4;
		$ent = array();
		for ( $j=0; $j < $num; $j++ )
		{
			$sub = substr($meta, $off, 0x10);
				$off += 0x10;
			printf("  %2x : %s\n", $j, printhex($sub));

			$sx = ord($sub[0]);
			$sy = ord($sub[1]);
			$dx = ord($sub[2]) - 0x80;
			$dy = ord($sub[3]) - 0x80;

			// fedcba98 76543210 fedcba98 76543210
			// 1111---- -44----- 3-----22 22------
			$b04 = str2int($sub, 4, 4);
			$p06 = ($b04 >> 0x6 ) & 0xf;
			$p0f = ($b04 >> 0xf ) & 0x1;
			$p15 = ($b04 >> 0x15) & 0x3;
			$p1c = ($b04 >> 0x1c) & 0xf;
				$cid  = $p06;
				$alph = array($p0f , $p15);
				$tile = ($p1c === 2) ? 0x10 : 0x20; // 2=10 , 4=20

			// fedcba98 76543210 fedcba98 76543210
			// 1122..-- 3333333. ...----- --------
			$b08 = str2int($sub, 8, 4);
			$p0d = ($b08 >> 0xd ) & 0x7f0;
			$p1a = ($b08 >> 0x1a) & 0xc;
			$p1e = ($b08 >> 0x1e) & 0x3;
				$sy += ($p1e * 0x100);

			$b0d = ord($sub[13]);
			$b0f = ord($sub[15]);
			$flipx = ($b0d === 0xf0); // 10=0 , f0=1
			$flipy = ($b0f === 0xf0); // 10=0 , f0=1

			$pal = substr ($tex['pal'], $cid*0x40, 0x40);
				$pal[3] = ZERO;
			$src = rippix8($tex['pix'], $sx, $sy, $tile, $tile, $tex['w'], $tex['h']);
			$aid = $atlas->putclut($tile, $tile, $pal, $src);

			$t = array(
				'dst'   => array($dx, $dy, $tile),
				'flip'  => array($flipx, $flipy),
				'alpha' => $alph,
				'atlas' => $aid,
			);
			list_add($ent, $j, $t);
		} // for ( $j=0; $j < $num; $j++ )

		list_add($key, $i, $ent);
	} // for ( $i=0; $i < $cnt; $i++ )
	return $key;
}
//////////////////////////////
function sectkeys( &$quad, &$atlas, &$keys )
{
	$quad['keyframe'] = array();
	foreach ( $keys as $kk => $kv )
	{
		if ( empty($kv) )
			continue;

		$layer = array();
		foreach ( $kv as $lk => $lv )
		{
			if ( empty($lv) )
				continue;

			list($x,$y,$w,$h) = $atlas->getxywh( $lv['atlas'] );
			$src = xywh_quad($w, $h);
			xywh_move($src, $x, $y);

			list($dx,$dy,$dz) = $lv['dst'];
			$dst = xywh_quad($dz, $dz, $lv['flip'][0], $lv['flip'][1]);
			xywh_move($dst, $dx, $dy);

			$lent = array(
				'dstquad'  => $dst,
				'srcquad'  => $src,
				'blend_id' => 0,
				'tex_id'   => 0,
				'_xywh'    => array($x,$y,$w,$h),
			);

			if ( $lv['alpha'][0] )
				$lent['blend_id'] = $lv['alpha'][1] + 1;

			list_add($layer, $lk, $lent);
		} // foreach ( $kv as $lk => $lv )

		$kent = array(
			'name'  => 'keyframe ' . $kk,
			'layer' => $layer,
		);
		list_add($quad['keyframe'], $kk, $kent);
	} // foreach ( $keys as $kk => $kv )
	return;
}

function sectslot( &$meta )
{
	$slot = array();
	$ed = str2big($meta, 0, 4);
	for ( $i=0; $i < $ed; $i += 4 )
	{
		$pos = str2big($meta, $i, 4);
		if ( $pos === 0 )
			continue;

		$head = substr($meta, $pos, 0x11);
			$pos += 0x11;
		printf("slot %x = %s\n", $i >> 2, printhex($head));

		$body = array( array() , array() , array() , array() );
		for ( $j=0; $j < 4; $j++ )
		{
			$cnt = ord($head[$j]);
			for ( $k=0; $k < $cnt; $k++ )
			{
				$sub = substr($meta, $pos, 4);
					$pos += 4;
				printf("  %2x-%2x : %s\n", $j, $k, printhex($sub));

				$body[$j][$k] = $sub;
			} // for ( $k=0; $k < $cnt; $k++ )
		} // for ( $j=0; $j < 4; $j++ )

		$sent = array(
			'head' => $head,
			'body' => $body,
		);
		list_add($slot, $i >> 2, $sent);
	} // for ( $i=0; $i < $ed; $i += 4 )
	return $slot;
}

function slot_keyhit( &$quad, &$slot, $slot_id, $flag )
{
	if ( ! isset($slot[$slot_id]) )
		return php_error('slot[%x] not found', $slot_id);

	$slhead = $slot[$slot_id]['head'];
	$slbody = $slot[$slot_id]['body'];
	$debug  = sprintf('%x,%x', $slot_id, $flag);
	$hit_id = -1;
	foreach ( $quad['hitbox'] as $hk => $hv )
	{
		if ( empty($hv) )
			continue;
		if ( $hv['debug'] === $debug )
		{
			$hit_id = $hk;
			goto donehit;
		}
	} // foreach ( $quad['hitbox'] as $hk => $hv )

	// $hit_id === -1
	$basex = sint8( $slhead[15] );
	$basey = sint8( $slhead[16] );
	$layer = array();
	for ( $i=0; $i < 4; $i++ )
	{
		if ( empty($slbody[$i]) )
			continue;
		if ( $flag & (1 << (3 + $i)) ) // 8 10 20 40
			continue;

		foreach ( $slbody[$i] as $k => $v )
		{
			$hx = sint8($v[0]) + $basex;
			$hy = sint8($v[1]) + $basey;
			$hw = ord($v[2]);
			$hh = ord($v[3]);

			$hit = xywh_quad($hw*2, $hh*2);
			xywh_move($hit, $hx-$hw, $hy-$hh);

			$ent = array(
				'debug'     => $i,
				'hitquad'   => $hit,
				'attribute' => 'hitbox ' . $i,
			);
			$layer[] = $ent;
		} // foreach ( $slbody[$i] as $k => $v )
	} // for ( $i=0; $i < 4; $i++ )

	$hit_id = count($quad['hitbox']);
	$hent = array(
		'debug' => $debug,
		'layer' => $layer,
	);
	$quad['hitbox'][] = $hent;

donehit:
	$key_id = ord($slhead[4]);
	if ( ! isset($quad['keyframe'][$key_id]) )
		return php_error('key[%x] not found', $key_id);

	$slot_id = count($quad['slot']);
	$sent = array(
		quad_attach('keyframe', $key_id),
		quad_attach('hitbox'  , $hit_id),
	);
	$quad['slot'][] = $sent;
	return quad_attach('slot', $slot_id);
}

function sectanim( &$quad, &$meta, &$slot )
{
	global $gp_def_anim;
	$quad['animation'] = array();
	$quad['hitbox']    = array();
	$quad['slot']      = array();

	$ed = str2big($meta, 0, 4);
	for ( $i=0; $i < $ed; $i += 4 )
	{
		$pos = str2big($meta, $i, 4);
		if ( $pos === 0 )
			continue;

		$b00 = ord( $meta[$pos+0] );
		$b01 = ord( $meta[$pos+1] );
			$pos += 2;

		if ( $b00 !== 0 )
			php_notice('anim b00 not zero = %x', $b00);
		$cnt = $b01;
		printf("anim %x\n", $i >> 2);

		$time = array();
		for ( $j=0; $j < $cnt; $j++ )
		{
			$sub = substr($meta, $pos, 8);
				$pos += 8;
			printf("  %2x : %s\n", $j, printhex($sub));

			$sid = ord($sub[0]);
			$fps = ord($sub[1]);
			$flg = ord($sub[7]);

			$tent = array(
				'time'   => $fps,
				'attach' => slot_keyhit($quad, $slot, $sid, $flg),
			);
			$time[] = $tent;
		} // for ( $j=0; $j < $cnt; $j++ )

		$aid = $i >> 2;
		if ( isset($gp_def_anim[$aid]) )
			$name = $gp_def_anim[$aid];
		else
			$name = 'animation ' . $aid;

		$aent = array(
			'name'     => $name,
			'timeline' => $time,
			'loop_id'  => 0,
		);
		list_add($quad['animation'], $aid, $aent);
	} // for ( $i=0; $i < $ed0; $i += 4 )
	return;
}

//////////////////////////////
function panzer( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$off = array();
	for ( $i=0; $i < 0x20; $i += 4 )
		$off[] = str2int($file, $i, 4);

	$len = strlen($file);
	if ( $off[0] !== 0x24 )
		return;
	if ( ($off[6]+$off[7]) !== $len )
		return;

	$dir  = str_replace('.', '_', $fname);
	$meta = array(
		substr($file, $off[0], $off[1]),
		substr($file, $off[2], $off[3]),
		substr($file, $off[4], $off[5]),
		substr($file, $off[6], $off[7]),
	);
	foreach ( $meta as $mk => $mv )
		save_file("$dir/meta.$mk", $mv);

	sectpix($meta[3]);
	save_palfile("$dir.pal.rgba", $meta[3]['pal'], 0x10);

	$atlas = new atlas_tex;
	$atlas->init();
	$keys = sectatlas($atlas, $meta[2], $meta[3]);
	$slot = sectslot ($meta[1]);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('psx panzer bandit');
	// if alpha on
	//   0  SRC/2 + DST/2
	//   1  SRC   + DST
	//   2  -SRC  + DST
	//   3  SRC/4 + DST
	$quad['blend'] = array(
		blend_modes('normal'),
		psx_blend_mode( 0.5 , 0.5),
		psx_blend_mode(   1 ,   1),
		psx_blend_mode(  -1 ,   1),
		psx_blend_mode(0.25 ,   1),
	);
	sectkeys($quad, $atlas, $keys);
	sectanim($quad, $meta[0], $slot);

	save_quadfile($dir, $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	panzer( $argv[$i] );

/*
blending
	koh 77,166
		BG 392018 + FG 6a83ee
			80  525283 or (BG +  FG) / 2
			82  a4a4ff or  BG +  FG
			84  000000 or  BG -  FG
			86  524152 or  BG + (FG  / 4)

	headlight error if without blending
		zako06
		zako12

anim 3
   0 :  7  2  1 --  1  f  7 --
   1 :  7  1 -- --  1  f  7 --
key 7
   0 : f0 c0 68 58 -- 78 1c 20 -- --  1 40 -- 10 -- 10
   1 : 60 -- 6d 55 -- 78 8c 44 -- -- -- -- -- f0 -- 10
   2 : 60 20 60 5f -- 78 8c 40 -- -- -- -- -- 10 -- 10
   3 : d0 c0 80 5f -- 78 9c 20 -- -- -- 40 -- 10 -- 10
   4 : e0 c0 80 6f -- 78 9c 20 -- -- -- 40 -- 10 -- 10
   5 : 80 20 5f 4d -- 78 8c 40 -- -- -- -- -- 10 -- 10
   6 : 20 20 61 44 -- 78 8c 40 -- -- -- -- -- 10 -- 10
hit 7 = -- --  1  1  7 -- -- -- -- -- -- -- -- -- --  8  2
   2- 0 : f8 ed 11 11
   3- 0 : f8 ea 14 14
 */
