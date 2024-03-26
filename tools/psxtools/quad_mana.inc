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

function sectkeys( &$meta, &$atlas, &$tim, $pos, $i16 )
{
	$key_cnt = str2int($meta, $pos, 2);
		$pos += 2;
	printf("== sectkeys( %x , %d )\n", $pos-2, $i16);

	$keys = array();
	$hits = array();
	for ( $i=0; $i < $key_cnt; $i++ )
	{
		$off = str2int($meta, $pos, 2);
			$pos += 2;

		$cnt = str2int($meta, $off, 1);
			$off++;

		// IMPORTANT : top-to-bottom order
		$kent = array();
		$hent = array();
		$name = sprintf('%x key %d', $off-1, $i);
		for ( $j=0; $j < $cnt; $j++ )
		{
			$b7 = ord( $meta[$off+7] );
			if ( $b7 & 0x20 )
			{
				if ( $i16 )
				{
					$s = substr($meta,  $off, 0x11);
						$off += 0x11;

					// 0 1  2 3  4 5  6 [7] 8  9 a  b c  d e  f 0
					$hit = array(
						sint16( $s[ 0].$s[ 1] ) , sint16( $s[ 2].$s[ 3] ) ,
						sint16( $s[ 4].$s[ 5] ) , sint16( $s[ 6].$s[ 8] ) ,
						sint16( $s[ 9].$s[10] ) , sint16( $s[11].$s[12] ) ,
						sint16( $s[13].$s[14] ) , sint16( $s[15].$s[16] ) ,
					);
				}
				else
				{
					$s = substr($meta,  $off, 9);
						$off += 9;

					// 0 1 2 3 4 5 6 [7] 8
					$hit = array(
						sint8( $s[0] ) , sint8( $s[1] ) ,
						sint8( $s[2] ) , sint8( $s[3] ) ,
						sint8( $s[4] ) , sint8( $s[5] ) ,
						sint8( $s[6] ) , sint8( $s[8] ) ,
					);
				}
				//echo debug($s, $name);

				// default order is top-to-bottom
				$en = array(
					'hit' => $hit,
					'bit' => $b7,
				);
				array_unshift($hent, $en); // add in reverse
			}
			else
			{
				if ( $i16 )
				{
					$s = substr($meta,  $off, 0xb);
						$off += 0xb;

					//0  1   2  3   4 5  6   7   8    9  a
					//dx dy  sx sy  w h  cid flg rot  dx dy
					$dx = sint16( $s[0].$s[ 9] );
					$dy = sint16( $s[1].$s[10] );
				}
				else
				{
					$s = substr($meta,  $off, 9);
						$off += 9;

					//0  1   2  3   4 5  6   7   8
					//dx dy  sx sy  w h  cid flg rot
					$dx = sint8( $s[0] );
					$dy = sint8( $s[1] );
				}
				//echo debug($s, $name);

				$sx  = ord( $s[2] );
				$sy  = ord( $s[3] );
				$w   = ord( $s[4] );
				$h   = ord( $s[5] );
				$cid = ord( $s[6] );
				$tid = $b7 & 0xf;
				$rot = sint8( $s[8] );

				if ( ! isset( $tim[$tid] ) )
					return php_error('tim id not found = %d', $tid);
				$tex = &$tim[$tid];
				$srcpix = rippix8($tex['pix'], $sx, $sy, $w, $h, $tex['w'], $tex['h']);
				$srcpal = substr ($tex['pal'], $cid*$tex['cc']*4, $tex['cc']*4);
					$srcpal[3] = ZERO;
				$aid = $atlas->putclut($w, $h, $srcpal, $srcpix);

				//$blend = ($cid === 11) ? 1 : 0;
				$blend = 0;

				// default order is top-to-bottom
				$en = array(
					'dx'  => $dx,
					'dy'  => $dy,
					'rot' => $rot,
					'bit' => $b7,
					'blend' => $blend,
					'atlas' => $aid,
				);
				array_unshift($kent, $en); // add in reverse
			}
		} // for ( $j=0; $j < $cnt; $j++ )

		$keys[$i] = $kent;
		$hits[$i] = $hent;
	} // for ( $i=0; $i < $cnt; $i++ )
	return array($keys,$hits);
}

function sectquad( &$atlas, &$keys, &$hits, &$quad )
{
	printf("== sectquad()\n");
	$max = 0;
	$quad['keyframe'] = array();
	foreach ( $keys as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		if ( $max < $kk )
			$max = $kk;

		$layer = array();
		foreach ( $kv as $kvv )
		{
			list($x,$y,$w,$h) = $atlas->getxywh( $kvv['atlas'] );
			$radian = $kvv['rot'] * pi() / 0x80;

			// w,h to quad -> rotate -> move dx,dy
			$dst = xywh_quad($w, $h, $kvv['bit'] & 0x40, $kvv['bit'] & 0x80);
			xywh_rotate($dst, $radian);
			xywh_move  ($dst, $kvv['dx'], $kvv['dy']);

			$en = array(
				'debug'    => sprintf('0x%x', $kvv['bit']),
				'dstquad'  => $dst,
				'srcquad'  => array(
					$x   ,$y    ,
					$x+$w,$y    ,
					$x+$w,$y+$h ,
					$x   ,$y+$h ,
				),
				'blend_id' => $kvv['blend'],
				'tex_id'   => 0,
				'_xywh'    => array($x,$y,$w,$h),
				'_rotate'  => array($kvv['dx'],$kvv['dy'],$radian),
			);
			$layer[] = $en;
		} // foreach ( $kv as $kvv )

		$en = array(
			'name'  => "keyframe $kk",
			'layer' => $layer,
		);
		list_add($quad['keyframe'], $kk, $en);
	} // foreach ( $keys[0] as $kk => $kv )


	$quad['hitbox'] = array();
	foreach ( $hits as $hk => $hv )
	{
		if ( empty($hv) )
			continue;
		if ( $max < $hk )
			$max = $hk;

		$layer = array();
		foreach ( $hv as $hvv )
		{
			$en = array(
				'debug'   => sprintf('0x%x', $kvv['bit']),
				'hitquad' => $hvv['hit'],
			);
			$layer[] = $en;
		} // foreach ( $hv as $hvv )

		$en = array(
			'name'  => "hitbox $hk",
			'layer' => $layer,
		);
		list_add($quad['hitbox'], $hk, $en);
	} // foreach ( $keys[1] as $hk => $hv )


	$max++;
	$quad['slot'] = array();
	for ( $i=0; $i < $max; $i++ )
	{
		if ( empty( $quad['keyframe'][$i] ) )  continue;
		if ( empty( $quad['hitbox'  ][$i] ) )  continue;
		$en = array(
			array('type'=>'keyframe', 'id'=>$i),
			array('type'=>'hitbox'  , 'id'=>$i),
		);
		list_add($quad['slot'], $i, $en);
	}
	return;
}

function sectanim( &$meta, &$quad )
{
	printf("== sectanim()\n");
	$anim_cnt = str2int($meta, 4, 1);

	$quad['animation'] = array();
	for ( $i=0; $i < $anim_cnt; $i++ )
	{
		$pos = 6 + ($i * 2);
		$off = str2int($meta, $pos, 2);

		$i16 = $off & 0x8000;
		$off &= 0x7fff;

		$tcnt = ord($meta[$off]);
			$off++;
		if ( $tcnt < 1 )
			continue;

		$time = array();
		for ( $j=0; $j < $tcnt; $j++ )
		{
			if ( $i16 )
			{
				$kid = str2int($meta, $off+0, 1);
				$fps = str2int($meta, $off+1, 1);
				// +2  use by previous anim
				// +3  replace meta[5]
					$off += 4;
			}
			else
			{
				$kid = str2int($meta, $off+0, 1);
				$fps = str2int($meta, $off+1, 1);
					$off += 2;
			}

			$attach = array();
			if ( ! empty( $quad['slot'][$kid] ) )
				$attach = array('type' => 'slot' , 'id' => $kid);
			else
			if ( ! empty( $quad['keyframe'][$kid] ) )
				$attach = array('type' => 'keyframe' , 'id' => $kid);
			else
			if ( ! empty( $quad['hitbox'][$kid] ) )
				$attach = array('type' => 'hitbox' , 'id' => $kid);

			$ent = array('time' => $fps);
			if ( ! empty($attach) )
				$ent['attach'] = $attach;

			$time[] = $ent;
		} // for ( $j=0; $j < $tcnt; $j++ )

		$anim = array(
			'name'     => "animation $i",
			'timeline' => $time,
			'loop_id'  => 0,
		);
		list_add($quad['animation'], $i, $anim);
	} // for ( $i=0; $i < $anim_cnt; $i++ )
	return;
}
//////////////////////////////
function mana_blend()
{
	// src = FG , dst = BG
	return array(
		array(
			'name' => '00 = FG * a + BG * (1-a)',
			'mode' => array('FUNC_ADD', 'SRC_ALPHA', 'ONE_MINUS_SRC_ALPHA'),
		),
		array(
			'name' => '01 = FG + BG',
			'mode' => array('FUNC_ADD', 'ONE', 'ONE'),
		),
		array(
			'name' => '02 = -FG + BG',
			'mode' => array('FUNC_REVERSE_SUBTRACT', 'ONE', 'ONE'),
		),
		array(
			'name'  => '03 = FG/4 + BG',
			'color' => '#ffffff40',
			'mode'  => array('FUNC_ADD', 'CONSTANT_ALPHA', 'ONE'),
		),
		array(
			'name'  => '04 = FG/2 + BG/2',
			'color' => '#ffffff80',
			'mode'  => array('FUNC_ADD', 'CONSTANT_ALPHA', 'CONSTANT_ALPHA'),
		),
	);
}

function sectmeta( &$meta, &$tim, $pfx )
{
	$pos = str2int($meta, 2, 2);
	$flg = str2int($meta, 5, 1);

	$atlas = new atlas_tex;
	$atlas->init();
	list($keys,$hits) = sectkeys($meta, $atlas, $tim, $pos, $flg & 1);

	$atlas->sort();
	$atlas->save("$pfx.0");

	$quad = load_idtagfile('ps1 legend of mana');
	$quad['blend'] = mana_blend();
	sectquad($atlas, $keys, $hits, $quad);
	sectanim($meta, $quad);

	save_quadfile($pfx, $quad);
	return;
}
