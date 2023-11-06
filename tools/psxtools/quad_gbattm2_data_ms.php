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

function sectquad( &$quad, &$atlas, &$keys, &$skel )
{
	$quad['keyframe'] = array();
	$quad['hitbox'  ] = array();
	$quad['slot'    ] = array();
	foreach ( $keys as $kk => $kv )
	{
		list($x,$y,$w,$h) = $atlas->getxywh( $kv['atlas'] );
		list($dx,$dy) = $kv['dst'];

		$kent = array(
			'name'  => "keyframe $kk",
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

		list($hx,$hy,$hw,$hh) = $kv['hit'];
		if ( $hw === 0 || $hh === 0 )
			continue;

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

	$quad['animation'] = array();
	$quad['skeleton' ] = array();

	$skel_cnt = count($skel[0]);
	$anim_cnt = count($skel);

	$bone = array();
	$aid  = 0;
	for ( $sk=0; $sk < $skel_cnt; $sk++ )
	{
		$time = array();
		$parent = -1;
		$order  = -1;
		for ( $ak=0; $ak < $anim_cnt; $ak++ )
		{
			$skel_ent = $skel[$ak][$sk];
			$parent = $skel_ent['parent'];
			$order  = $skel_ent['order'];

			$mat4 = matrix(4);

			$radian = $skel_ent['rot'] / 0x8000 * pi();
			$t = matrix_rotate_z(4, $radian);
			if ( $t !== -1 )
				$mat4 = matrix_multi44($mat4, $t);

			$mat4[0+3] += $skel_ent['dst'][0];
			$mat4[4+3] += $skel_ent['dst'][1];

			$tent = array(
				'time'   => 10,
				'matrix' => $mat4,
				'matrix_mix' => 1,
			);
			if ( $skel_ent['disp'] )
			{
				$kid = $skel_ent['skel'];
				if ( ! empty( $quad['slot'][$kid] ) )
					$tent['attach'] = array('type'=>'slot' , 'id'=>$kid);
				else
					$tent['attach'] = array('type'=>'keyframe' , 'id'=>$kid);
			}

			$time[] = $tent;
		} // for ( $ak=0; $ak < $anim_cnt; $ak++ )

		$aent = array(
			'name'     => "bone $sk",
			'timeline' => $time,
			'loop_id'  => 0,
		);
		list_add($quad['animation'], $aid, $aent);

		$bent = array(
			//name
			'attach'    => array('type'=>'animation' , 'id'=>$aid),
			'parent_id' => $parent,
			'order'     => $order,
		);
		$bone[] = $bent;

		$aid++;
	} // for ( $sk=0; $sk < $skel_cnt; $sk )

	$sent = array(
		'name' => 'all animation',
		'bone' => $bone,
	);
	$quad['skeleton'][] = $sent;

	return;
}
//////////////////////////////
function sectkeys_c( &$atlas, &$meta, &$tim )
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

		$tex = $tim[$tid];
		$src = rippix8($tex['pix'], $x1, $y1, $w, $h, $tex['w'], $tex['h']);
		$pal = substr ($tex['pal'], $cid*0x40, 0x40);
			$pal[3] = ZERO;
		$aid = $atlas->putclut($w, $h, $pal, $src);

		$ent = array(
			'dst'   => array($dx, $dy),
			'hit'   => array($hx, $hy, $hw, $hh),
			'atlas' => $aid,
		);
		$keys[] = $ent;
	} // for ( $p=0; $p < $len; $p += 12 )

	return $keys;
}

function sectskel_182( &$m20, &$m21 )
{
	$skel_cnt = str2int($m20, 0, 3);
	$anim_cnt = str2int($m20, 4, 3);

	$skel = array();
	for ( $ak=0; $ak < $anim_cnt; $ak++ )
	{
		$m20p = 8 + ($ak * 0x182);
		if ( $m20[$m20p] === ZERO )
			continue;

		$m20p += 2;
		$pose = array();
		for ( $sk=0; $sk < $skel_cnt; $sk++ )
		{
			$m20s = substr($m20, $m20p, 8);
				$m20p += 8;
			$m21p1 = 4 + ($sk * 0x12c);
			//echo debug($m20s, "$ak $sk");

			// 0  1  2 3  4  5   6 7
			// k  d  rot  dx dy  ? ?
			$k   = str2int($m20s, 0, 1);
			$d   = str2int($m20s, 1, 1);
			$rot = str2int($m20s, 2, 2);
			$dx  = str2int($m20s, 4, 1, true);
			$dy  = str2int($m20s, 5, 1, true);

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
			$pose[] = $ent;
		} // for ( $sk=0; $sk < $skel_cnt; $sk++ )

		$skel[] = $pose;
	} // for ( $ak=0; $ak < $anim_cnt; $ak++ )
	return $skel;
}

function sectskel_62( &$m30, &$m31, &$m28 )
{
	$skel_cnt = str2int($m30, 0, 3);
	$anim_cnt = str2int($m30, 4, 3);

	$skel = array();
	for ( $ak=0; $ak < $anim_cnt; $ak++ )
	{
		$m30p = 8 + ($ak * 0x62);
		if ( $m30[$m30p] === ZERO )
			continue;

		$m30p += 2;
		$pose = array();
		for ( $sk=0; $sk < $skel_cnt; $sk++ )
		{
			$m31k = str2int($m30, $m30p, 2);
				$m30p += 2;
			$m31s = substr($m31, $m31k*8, 8);
			$m28p1 = 4 + ($sk * 0x12c);
			//echo debug($m31s, "$ak $sk");

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
			$pose[] = $ent;
		} // for ( $sk=0; $sk < $skel_cnt; $sk++ )

		$skel[] = $pose;
	} // for ( $ak=0; $ak < $anim_cnt; $ak++ )
	return $skel;
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

	$atlas = new AtlasTex;
	$atlas->init();
	$keys = sectkeys_c($atlas, $m[22], $tim);
	$skel = sectskel_182($m[20], $m[21]);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('ps1 gundam battle master 1');
	$quad['blend'] = array( blend_modes('normal') );

	sectquad($quad, $atlas, $keys, $skel);
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

	$atlas = new AtlasTex;
	$atlas->init();
	$keys = sectkeys_c($atlas, $m[29], $tim);
	$skel = sectskel_62($m[30], $m[31], $m[28]);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile('psx gundam battle master 2');
	$quad['blend'] = array( blend_modes('normal') );

	sectquad($quad, $atlas, $keys, $skel);
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

 */
