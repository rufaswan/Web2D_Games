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
require 'common-quad.inc';
require 'class-atlas.inc';
require 'quad.inc';

function gbatmas_sort_order( $a, $b )
{
	$d = $a['o'] - $b['o'];
	if ( $d !== 0 )
		return $d;
	return $b['i'] - $a['i'];
}

function sectkeypose( &$quad, &$pose )
{
	$cnt_key = count($quad['keyframe']);

	$quad['animation'] = array();
	$time = array();
	foreach ( $pose as $pk => $pv )
	{
		// psycho.dat
		// 0 - 27 -  1
		//      | -  2 - 3 -  4
		//      |        | -  5
		//      |        | -  6
		//      |        | - 17 - 18 - 19 - 21 - 20
		//      |        | - 22 - 23 - 24 - 26 - 25
		//      | -  7 - 8 - 9 - 10
		//      | - 11
		//      | - 12 - 13 - 14 - 15
		//      | - 16
		$key_layer = array();
		$hit_layer = array();
		$orders  = array();
		$inherit = array();
		$is_done = false;
		while ( ! $is_done )
		{
			$is_done = true;
			foreach ( $pv as $lk => $lv )
			{
				$par = $lv['parent'];
				// should be -1
				if ( $par === $lk )
					$par--;
				// has parent but data not available yet, try again on next loop
				if ( $par >= 0 && ! isset($inherit[$par]) )
				{
					$is_done = false;
					continue;
				}
				// already done
				if ( isset($inherit[$lk]) )
					continue;

				// 1000 = 360 degree
				$radian = ($lv['rot'] / 0x800) * pi();
				$cur_inherit = qmat3_dxdy($radian, $lv['dst'][0], $lv['dst'][1]);
				if ( $par >= 0 )
					$cur_inherit = matrix_multi33($inherit[$par], $cur_inherit);
				$inherit[$lk] = $cur_inherit;

				$orders[] = array('p'=>$par , 'i'=>$lk , 'o'=>$lv['order']);

				$cur_key = 0;
				$cur_hit = 0;
				if ( $lv['disp'] !== 0 )
				{
					$kid = $lv['skel'];
					$cur_key = $quad['keyframe'][$kid]['layer'][0];
					qmat3_mult($cur_inherit, $cur_key['dstquad']);
						$cur_key['debug'] = sprintf('order %x', $lv['order']);

					if ( isset($quad['hitbox'][$kid]) && $quad['hitbox'][$kid] !== 0 )
					{
						$cur_hit = $quad['hitbox'][$kid]['layer'][0];
						qmat3_mult($cur_inherit, $cur_hit['hitquad']);
					}
				}

				list_add($key_layer, $lk, $cur_key);
				list_add($hit_layer, $lk, $cur_hit);
			} // foreach ( $pv as $lk => $lv )
		} // while ( ! $is_done )

		// add by order
		usort($orders, 'gbatmas_sort_order');
		$ord_vis = array();
		foreach ( $orders as $k => $v )
			$ord_vis[] = $v['i'];

		$kid = $cnt_key + $pk;
		$kent = array(
			'name'  => "keyframe $pk",
			'layer' => $key_layer,
			'order' => $ord_vis,
		);
		list_add($quad['keyframe'], $kid, $kent);

		$hent = array(
			'name'  => "hitbox $pk",
			'layer' => $hit_layer,
		);
		list_add($quad['hitbox'], $kid, $hent);

		$sent = array(
			array('type'=>'keyframe' , 'id'=>$kid),
			array('type'=>'hitbox'   , 'id'=>$kid),
		);
		list_add($quad['slot'], $kid, $sent);

		$tent = array(
			'time'         => 10,
			'keyframe_mix' => 1,
			'hitbox_mix'   => 1,
			'attach'       => array('type'=>'slot' , 'id'=>$kid),
		);
		$time[] = $tent;
	} // foreach ( $pose as $pk => $pv )

	$ent = array(
		'name'     => "ALL KEYPOSES",
		'timeline' => $time,
		'loop_id'  => 0,
	);
	$quad['animation'][] = $ent;
	return;
}

function sectlayer( &$quad, &$atlas, &$keys )
{
	$quad['keyframe'] = array();
	$quad['hitbox'  ] = array();
	$quad['slot'    ] = array();
	foreach ( $keys as $kk => $kv )
	{
		list($x,$y,$w,$h) = $atlas->getxywh( $kv['atlas'] );
		list($dx,$dy) = $kv['dst'];

		$kent = array(
			'name'  => "part $kk",
			'layer' => array(
				array(
					'dstquad'  => array(
						$dx   ,$dy   ,
						$dx+$w,$dy   ,
						$dx+$w,$dy+$h,
						$dx   ,$dy+$h,
					),
					'srcquad'  => array(
						$x   ,$y   ,
						$x+$w,$y   ,
						$x+$w,$y+$h,
						$x   ,$y+$h,
					),
					'blend_id' => 0,
					'tex_id'   => 0,
					'_xywh'    => array($x,$y,$w,$h),
				),
			),
		);
		list_add($quad['keyframe'], $kk, $kent);

		if ( $kv['hit'] === 0 )
			continue;
		list($hx,$hy,$hw,$hh) = $kv['hit'];

		$hent = array(
			'name'  => "hitbox $kk",
			'layer' => array(
				array(
					'hitquad' => array(
						$hx    ,$hy    ,
						$hx+$hw,$hy    ,
						$hx+$hw,$hy+$hh,
						$hx    ,$hy+$hh,
					),
				),
			),
		);
		$sent = array(
			array('type'=>'keyframe' , 'id'=>$kk),
			array('type'=>'hitbox'   , 'id'=>$kk),
		);
		list_add($quad['hitbox'], $kk, $hent);
		list_add($quad['slot'  ], $kk, $sent);
	} // foreach ( $keys as $kk => $kv )
	return;
}
//////////////////////////////
function sectkeys_c( &$atlas, &$meta, &$tim, $bm )
{
	$len  = strlen($meta);
	$keys = array();
	for ( $p=0; $p < $len; $p += 12 )
	{
		$s = substr($meta, $p, 12);
		// 0    1    2  3  4  5   6  7   8  9  a  b
		// cid  tid  x1 y1 x2 y2  dx dy  hx hy hw hh
		$cid = str2int($s,  0, 1);
		$tid = str2int($s,  1, 1);
		$x1  = str2int($s,  2, 1);
		$y1  = str2int($s,  3, 1);
		$x2  = str2int($s,  4, 1);
		$y2  = str2int($s,  5, 1);
		$dx  = str2int($s,  6, 1, true);
		$dy  = str2int($s,  7, 1, true);
		$hx  = str2int($s,  8, 1, true);
		$hy  = str2int($s,  9, 1, true);
		$hw  = str2int($s, 10, 1);
		$hh  = str2int($s, 11, 1);
			$w = $x2 - $x1;
			$h = $y2 - $y1;

		$pal = substr ($tim[12]['pal'], $cid*0x40, 0x40);
			$pal[3] = ZERO;

		$tex = $tim[$tid];
		$src = rippix8($tex['pix'], $x1, $y1, $w, $h, $tex['w'], $tex['h']);
		$aid = $atlas->putclut($w, $h, $pal, $src);

		$ent = array(
			'dst'   => array($dx, $dy),
			'hit'   => 0,
			'atlas' => $aid,
		);
		if ( $hx|$hy|$hw|$hh )
			$ent['hit'] = array($hx, $hy, $hw, $hh);
		$keys[] = $ent;
	} // for ( $p=0; $p < $len; $p += 12 )

	return $keys;
}

function sectpose_182( &$m20, &$m21 )
{
	$layer_cnt = str2int($m20, 0, 3);
	$pose_cnt  = str2int($m20, 4, 3);

	$pose = array();
	for ( $pk=0; $pk < $pose_cnt; $pk++ )
	{
		$m20p = 8 + ($pk * 0x182);
		$m20s = substr($m20, $m20p, 0x182);
		if ( str2int($m20s,0,2) !== 1 )
			continue;
		//printf("[%4x]%2x = %s\n", $m20p, $pk, printhex($m20s));

		$m20p  = 2;
		$layer = array();
		for ( $lk=0; $lk < $layer_cnt; $lk++ )
		{
			$m20ps = substr($m20s, $m20p, 8);
			//printf("  [%4x]%2x = %s\n", $m20p, $lk, printhex($m20ps));
				$m20p += 8;
			$m21p1 = 4 + ($lk * 0x12c);

			// 0  1  2 3  4  5   6 7
			// k  d  rot  dx dy  ? ?
			$k   = str2int($m20ps, 0, 1);
			$d   = str2int($m20ps, 1, 1);
			$rot = str2int($m20ps, 2, 2);
			$dx  = str2int($m20ps, 4, 1, true);
			$dy  = str2int($m20ps, 5, 1, true);

			$m21k = ($k & 0x7f) * 2;
			$m21p2 = $m21p1 + 0x2c + $m21k;
			$sid = str2int($m21, $m21p2, 2);

			$parent = str2int($m21, $m21p1 + 1, 1);
			$order  = str2int($m21, $m21p1 + 3, 1);

			$ent = array(
				'order'  => $order,
				'parent' => $parent,
				'skel' => $sid,
				'disp' => $d,
				'rot'  => $rot,
				'dst'  => array($dx,$dy),
			);
			$layer[] = $ent;
		} // for ( $lk=0; $lk < $layer_cnt; $lk++ )

		$pose[] = $layer;
	} // for ( $pk=0; $pk < $pose_cnt; $pk++ )
	return $pose;
}

function sectpose_62( &$m30, &$m31, &$m28 )
{
	$layer_cnt = str2int($m30, 0, 3);
	$pose_cnt  = str2int($m30, 4, 3);

	$pose = array();
	for ( $pk=0; $pk < $pose_cnt; $pk++ )
	{
		$m30p = 8 + ($pk * 0x62);
		$m30s = substr($m30, $m30p, 0x62);
		if ( str2int($m30s,0,2) !== 1 )
			continue;
		//printf("[%4x]%2x = %s\n", $m30p, $pk, printhex($m30s));

		$m30p  = 2;
		$layer = array();
		for ( $lk=0; $lk < $layer_cnt; $lk++ )
		{
			$m31k = str2int($m30s, $m30p, 2);
				$m30p += 2;
			$m31s = substr($m31, $m31k*8, 8);
			$m28p1 = 4 + ($lk * 0x12c);
			//printf("  [%4x]%2x = %s\n", $m31k*8, $lk, printhex($m31s));

			// 0  1  2 3  4  5   6 7
			// k  d  rot  dx dy  ? ?
			$k   = str2int($m31s, 0, 1);
			$d   = str2int($m31s, 1, 1);
			$rot = str2int($m31s, 2, 2);
			$dx  = str2int($m31s, 4, 1, true);
			$dy  = str2int($m31s, 5, 1, true);

			$m28k = ($k & 0x7f) * 2;
			$m28p2 = $m28p1 + 0x2c + $m28k;
			$sid = str2int($m28, $m28p2, 2);

			$parent = str2int($m28, $m28p1 + 1, 1);
			$order  = str2int($m28, $m28p1 + 3, 1);

			$ent = array(
				'order'  => $order,
				'parent' => $parent,
				'skel' => $sid,
				'disp' => $d,
				'rot'  => $rot,
				'dst'  => array($dx,$dy),
			);
			$layer[] = $ent;
		} // for ( $lk=0; $lk < $layer_cnt; $lk++ )

		$pose[] = $layer;
	} // for ( $pk=0; $pk < $pose_cnt; $pk++ )
	return $pose;
}
//////////////////////////////
function gbatmas1( $dir )
{
	printf("== gbatmas1( %s )\n", $dir);

	$m = array();
	$m[16] = load_file("$dir/16.tim8");
	$m[17] = load_file("$dir/17.tim8");
	$m[18] = load_file("$dir/18.tim8");
	$m[19] = load_file("$dir/19.tim8");
		$tim = array();
		$tim[12] = psxtim($m[16]);
		$tim[13] = psxtim($m[17]);
		$tim[14] = psxtim($m[18]);
		$tim[15] = psxtim($m[19]);
	$m[20] = load_file("$dir/meta.20"); // 182  dst list + data
	$m[21] = load_file("$dir/meta.21"); // 12c  src skeleton
	$m[22] = load_file("$dir/meta.22"); //   c  src x1,y1,x2,y2

	$atlas = new atlas_tex;
	$atlas->init();
	$keys = sectkeys_c($atlas, $m[22], $tim, 1);
	$pose = sectpose_182($m[20], $m[21]);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('ps1 gundam battle master 1');
	$quad['blend'] = array( blend_modes('normal') );

	sectlayer  ($quad, $atlas, $keys);
	sectkeypose($quad, $pose);
	save_quadfile($dir, $quad);
	return;
}

function gbatmas2( $dir )
{
	printf("== gbatmas2( %s )\n", $dir);

	$m = array();
	$m[16] = load_file("$dir/16.tim8");
	$m[17] = load_file("$dir/17.tim8");
	$m[18] = load_file("$dir/18.tim8");
	$m[19] = load_file("$dir/19.tim8");
		$tim = array();
		$tim[12] = psxtim($m[16]);
		$tim[13] = psxtim($m[17]);
		$tim[14] = psxtim($m[18]);
		$tim[15] = psxtim($m[19]);
	$m[28] = load_file("$dir/meta.28"); // 12c  src skeleton
	$m[29] = load_file("$dir/meta.29"); //   c  src x1,y1,x2,y2
	$m[30] = load_file("$dir/meta.30"); //  62  dst list
	$m[31] = load_file("$dir/meta.31"); //   8  dst data

	$atlas = new atlas_tex;
	$atlas->init();
	$keys = sectkeys_c($atlas, $m[29], $tim, 2);
	$pose = sectpose_62($m[30], $m[31], $m[28]);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('psx gundam battle master 2');
	$quad['blend'] = array( blend_modes('normal') );

	sectlayer  ($quad, $atlas, $keys);
	sectkeypose($quad, $pose);
	save_quadfile($dir, $quad);
	return;
}

//////////////////////////////
function batmas( $dir )
{
	$dir = rtrim($dir, '\\/');
	if ( ! is_dir($dir) )
		return;

	$list = array();
	$bat1 = array(
		'12.vh' , '13.vb' ,
		'16.tim8' , '17.tim8' , '18.tim8' , '19.tim8' ,
		'meta.20' , // dst list + data
		'meta.21' , // src skeleton
		'meta.22' , // src x1,y1,x2,y2
		'meta.23' , // opcodes
		'meta.24' ,
		'meta.25' ,
	);
	if ( dir_file_exists($list, $dir, $bat1) )
		return gbatmas1($dir);

	$bat2 = array(
		'12.vh' , '13.vb' ,
		'16.tim8' , '17.tim8' , '18.tim8' , '19.tim8' ,
		'meta.24' ,
		'meta.25' ,
		'meta.26' , // opcodes
		'meta.28' , // src skeleton
		'meta.29' , // src x1,y1,x2,y2
		'meta.30' , // dst list
		'meta.31' , // dst data
	);
	if ( dir_file_exists($list, $dir, $bat2) )
		return gbatmas2($dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	batmas( $argv[$i] );

/*
bm1
	20=8+182  21=4+12c->22  22=c  23  24  25=h=4+14
bm2
	24  25=h=4+14  26  28=4+12c->29  29=c  30=8+62->31  31=8

zeta jump 1e = 16 17 16*3 17 16*18
	25=19  29=88  31=d4a
zaku idle 1a = 16*1a
//////////////////////////////
Gundam Battle Master 2
	1p zeta
		meta.24 = RAM 801c4d0c +30e
		meta.25 = RAM 80171d20 +3a0
		meta.26 = RAM 8016bbf8 +2414
		meta.28 = RAM 80147ca0 +2b60
		meta.29 = RAM 8014ba24 +660
		meta.30 = RAM 8014ede8 +c408
		meta.31 = RAM 8015b1f0 +6a50

	BREAK ON meta.26 TOC = [8016bed8..8016c118]?
		80044ff0  lw   v0, 40(a1)
			a0 = (a0 << 1) + v0
		80044ffc  lhu  a0,  0(a0)

	80044ff4 -> xor a0,a0 = forced ALL -> IDLE animation
 */
