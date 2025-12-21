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

function godwing_sort_order( $a, $b )
{
	$d = $a['o'] - $b['o'];
	if ( $d !== 0 )
		return $d;
	return $b['i'] - $a['i'];
}

function sectkeypose( &$quad, &$keys, &$m7 )
{
	$cnt_key   = count($keys);
	$cnt_layer = str2int($m7, 0, 2);
	$cnt_pose  = str2int($m7, 2, 2);
	$base_pos  = str2int($m7, 4, 2);

	$quad['animation'] = array();
	$time = array();
	for ( $ipose=0; $ipose < $cnt_pose; $ipose++ )
	{
		$key_layer = array();
		$hit_layer = array();
		$orders  = array();
		$inherit = array();
		$is_done = false;
		while ( ! $is_done )
		{
			$is_done = true;
			for ( $ilayer=0; $ilayer < $cnt_layer; $ilayer++ )
			{
				$pos_layer = $base_pos + ($ilayer * 0x1c);
				$sub_layer = substr($m7, $pos_layer, 0x1c);
					$parent = str2int($sub_layer, 0, 2, true);
					$cnt1 = str2int($sub_layer, 0x02, 2); // sets
					$cnt2 = str2int($sub_layer, 0x04, 2); // frames/set

					$pos1 = str2int($sub_layer, 0x0c, 4);
					$pos2 = str2int($sub_layer, 0x10, 4);
					$pos3 = str2int($sub_layer, 0x14, 4);
					$pos4 = str2int($sub_layer, 0x18, 4);
				// has parent but data not available yet, try again on next loop
				if ( $parent >= 0 && ! isset($inherit[$parent]) )
				{
					$is_done = false;
					continue;
				}
				// already done
				if ( isset($inherit[$ilayer]) )
					continue;

				// 01  23  45  67   89  ab
				// f1  --  f2  rot  dx  dy
				$id2  = str2int($m7, $pos1 + ($ipose * 2), 2);
				$sub2 = substr ($m7, $pos2 + ($id2 * 0xc), 0xc);
					$vis = str2int($sub2,  0, 2);
					$id3 = str2int($sub2,  2, 2);
					$id4 = str2int($sub2,  4, 2);
					$rot = str2int($sub2,  6, 2, true);
					$dx  = str2int($sub2,  8, 2, true);
					$dy  = str2int($sub2, 10, 2, true);

				// 1000 = 360 degree
				$radian = ($rot / 0x800) * pi();
				$cur_inherit = qmat3_dxdy($radian, $dx, $dy);
				if ( $parent >= 0 )
					$cur_inherit = matrix_multi33($inherit[$parent], $cur_inherit);
				$inherit[$ilayer] = $cur_inherit;

				// 01  23
				// --  ord
				$sub3 = substr($m7, $pos3 + ($id3 * 4), 4);
				//$b00 = str2int($sub3, 0, 2);
				$b02 = str2int($sub3, 2, 2);
				$orders[] = array('p'=>$parent , 'i'=>$ilayer , 'o'=>$b02);

				$cur_key = 0;
				$cur_hit = 0;
				if ( $vis === 0 )
					goto end_layer;

				$id34 = $id3 * $cnt2 + $id4;
				$sub4 = substr($m7, $pos4 + ($id34 * 0x1e), 0x1e);
				if ( $keys[$sub4] === 0 )
					goto end_layer;

				$kid = $keys[$sub4]['id'];
				$cur_key = $quad['keyframe'][$kid]['layer'][0];
				qmat3_mult($cur_inherit, $cur_key['dstquad']);
					$cur_key['debug'] = sprintf('order %x', $b02);

				if ( isset($quad['hitbox'][$kid]) && $quad['hitbox'][$kid] !== 0 )
				{
					$cur_hit = $quad['hitbox'][$kid]['layer'][0];
					qmat3_mult($cur_inherit, $cur_hit['hitquad']);
				}

end_layer:
				list_add($key_layer, $ilayer, $cur_key);
				list_add($hit_layer, $ilayer, $cur_hit);
			} // for ( $ilayer=0; $ilayer < $cnt_layer; $ilayer++ )
		} // while ( ! $is_done )

		// add by order
		usort($orders, 'godwing_sort_order');
		$ord_vis = array();
		foreach ( $orders as $k => $v )
			$ord_vis[] = $v['i'];

		$kid = $cnt_key + $ipose;
		$kent = array(
			'name'  => "keyframe $ipose",
			'layer' => $key_layer,
			'order' => $ord_vis,
		);
		list_add($quad['keyframe'], $kid, $kent);

		$hent = array(
			'name'  => "hitbox $ipose",
			'layer' => $hit_layer,
		);
		list_add($quad['hitbox'], $kid, $hent);

		$sent = array(
			quad_attach('keyframe', $kid),
			quad_attach('hitbox'  , $kid),
		);
		list_add($quad['slot'], $kid, $sent);

		$tent = array(
			'time'           => 10,
			'dstquad_mix_id' => 1,
			'hitquad_mix_id' => 1,
			'attach'         => quad_attach('slot', $kid),
		);
		$time[] = $tent;
	} // for ( $ipose=0; $ipose < $cnt_pose; $ipose++ )

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

	$cnt = count($keys);
	$dmy = 0;
	list_add($quad['keyframe'], $cnt, $dmy);
	list_add($quad['hitbox'  ], $cnt, $dmy);
	list_add($quad['slot'    ], $cnt, $dmy);

	$id = -1;
	foreach ( $keys as $kk => $kv )
	{
		if ( $kv === 0 )
			continue;
		$id++;
		$keys[$kk]['id'] = $id;

		list($x,$y,$w,$h) = $atlas->getxywh( $kv['atlas'] );
		$src = xywh_quad($w, $h);
		xywh_move($src, $x, $y);

		list($dx,$dy,$dw,$dh) = $kv['dst'];
		$dst = xywh_quad($dw, $dh);
		xywh_move($dst, $dx, $dy);

		$kent = array(
			'name'  => 'key ' . $kv['name'],
			'layer' => array(
				array(
					'dstquad'  => $dst,
					'srcquad'  => $src,
					'blend_id' => 0,
					'tex_id'   => 0,
					'_xywh'    => array($x,$y,$w,$h),
				),
			),
		);
		list_add($quad['keyframe'], $id, $kent);

		if ( $kv['hit'] === 0 )
			continue;

		list($hx,$hy,$hw,$hh) = $kv['hit'];
		$hit = xywh_quad($hw, $hh);
		xywh_move($hit, $hx, $hy);

		$hent = array(
			'name'  => 'hit ' . $kv['name'],
			'layer' => array(
				array(
					'hitquad' => $hit,
				),
			),
		);
		$sent = array(
			quad_attach('keyframe', $id),
			quad_attach('hitbox'  , $id),
		);
		list_add($quad['hitbox'], $id, $hent);
		list_add($quad['slot'  ], $id, $sent);
	} // foreach ( $keys as $kk => $kv )
	return;
}

function sectkeys( &$atlas, &$m7, &$tim )
{
	$keys = array();
	$cnt_layer = str2int($m7, 0, 2);
	$pos = str2int($m7, 4, 2);
	for ( $ilayer=0; $ilayer < $cnt_layer; $ilayer++ )
	{
		$s = substr($m7, $pos, 0x1c);
			$pos += 0x1c;

		$b02 = str2int($s, 2, 2);
		$b04 = str2int($s, 4, 2);
		$b18 = str2int($s, 0x18, 4);
		$cnt = $b02 * $b04;

		for ( $i=0; $i < $cnt; $i++ )
		{
			$sub = substr($m7, $b18, 0x1e);
				$b18 += 0x1e;

			if ( isset($keys[$sub]) )
				continue;

			// 01  23  45  67  89  ab  cd  ef  01  23  45  67  89  ab   cd
			// dx  dy  dw  dh  sx  sy  sw  sh  hx  hy  hw  hh  --  tid  cid
			$dx = str2int($sub, 0x00, 2, true);
			$dy = str2int($sub, 0x02, 2, true);
			$dw = str2int($sub, 0x04, 2);
			$dh = str2int($sub, 0x06, 2);

			$sx = str2int($sub, 0x08, 2);
			$sy = str2int($sub, 0x0a, 2);
			$sw = str2int($sub, 0x0c, 2);
			$sh = str2int($sub, 0x0e, 2);

			$hx = str2int($sub, 0x10, 2, true);
			$hy = str2int($sub, 0x12, 2, true);
			$hw = str2int($sub, 0x14, 2);
			$hh = str2int($sub, 0x16, 2);

			//$unk = str2int($sub, 0x18, 2);
			$tid = str2int($sub, 0x1a, 2);
			$b1c = str2int($sub, 0x1c, 2);
			$cid = $b1c >> 5; // div 20

			// ms17 layer  7-e  = tid 23
			// ms1a layer  c    = tid 23
			// ms1c layer  4-5  = tid 23
			// ms1f layer 12-15 = tid 20
			if ( ! isset($tim[$tid]) )
			{
				$keys[$sub] = 0;
				continue;
			}

			$pal = substr ($tim[0]['pal'], $cid*0x40, 0x40);
				$pal[3] = ZERO;

			$tex = $tim[$tid];
			$src = rippix8($tex['pix'], $sx, $sy, $sw, $sh, $tex['w'], $tex['h']);
			$aid = $atlas->putclut($sw, $sh, $pal, $src);

			$ent = array(
				'dst'   => array($dx, $dy, $dw, $dh),
				'hit'   => 0,
				'atlas' => $aid,
				'name'  => "layer $ilayer id $i",
			);
			if ( $hx|$hy|$hw|$hh )
				$ent['hit'] = array($hx, $hy, $hw, $hh);
			$keys[$sub] = $ent;
		} // for ( $i=0; $i < $cnt; $i++ )
	} // for ( $ilayer=0; $ilayer < $cnt_layer; $ilayer++ )
	return $keys;
}

function godwing( $dir )
{
	printf("== godwing( %s )\n", $dir);

	$m = array();
	$m[1] = load_file("$dir/0001.tim8");
	$m[2] = load_file("$dir/0002.tim8");
	$m[3] = load_file("$dir/0003.tim8");
	$m[4] = load_file("$dir/0004.tim8");
		$tim = array();
		$tim[0] = psxtim($m[1]);
		$tim[1] = psxtim($m[2]);
		$tim[2] = psxtim($m[3]);
		$tim[3] = psxtim($m[4]);
	$m[7] = load_file("$dir/0007.unk");

	$atlas = new atlas_tex;
	$atlas->init();
	$keys = sectkeys($atlas, $m[7], $tim);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('psx gundam battle master gw');
	$quad['blend'] = array( blend_modes('normal') );
	$quad['mix'] = array(0,'LINEAR');

	sectlayer($quad, $atlas, $keys);
	sectkeypose($quad, $keys, $m[7]);
	save_quadfile($dir, $quad);
	return;
}

function batmas( $dir )
{
	$dir = rtrim($dir, '\\/');
	if ( ! is_dir($dir) )
		return;

	$list = array();
	$batgw = array(
		'0001.tim8' , '0002.tim8' , '0003.tim8' , '0004.tim8' ,
		'0006.unk' ,
		'0007.unk' , // src + dst
		'0008.unk' , // opcodes
	);
	if ( dir_file_exists($list, $dir, $batgw) )
		return godwing($dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	batmas( $argv[$i] );

/*
Gundam Wing
	1p wings
		6.unk = RAM 801aab4c +4dc
		7.unk = RAM 801ab028 +143b4     -> RAM 801775a4
		8.unk = RAM 801bf3dc +4766  + 2

	BREAK ON 8.unk TOC = [801bf778..801bfa78]?

	80069830 -> xor v0,v0 = forced ALL -> IDLE animation
*/
