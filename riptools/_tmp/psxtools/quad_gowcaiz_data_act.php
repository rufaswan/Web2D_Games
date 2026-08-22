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
require 'gowcaiz.inc';

function anim_meta( &$meta, &$tmg )
{
	$anim = [];
	$keys = [];
	foreach ( $meta as $mk => $mv )
	{
		$mv  = explode("\n", $mv);
		$cnt = (int)array_shift($mv);

		$alist = [
			'time' => [],
			'loop' => -1,
		];
		for ( $i=0; $i < $cnt; $i++ )
		{
			$klayer = [];
			$hlayer = [];

			$b = array_shift($mv);
			list($tmgk,$time,$cx,$cy,$u1,$u2) = explode(',', $b);

			if ( isset($tmg[$tmgk]) )
			{
				$b = $tmg[$tmgk];
				$klayer[] = [$tmgk , -$cx , -$cy];
			}

			$hcnt = (int)array_shift($mv);
			for ( $hi=0; $hi < $hcnt; $hi++ )
			{
				$b = array_shift($mv);
				list($hx,$hy,$hh,$hw,$htype) = explode(',', $b);

				$hlayer[] = [$hx - $cx , -$hy - $hh , 0+$hw , 0+$hh , $htype];
			} // for ( $hi=0; $hi < $hcnt; $hi++ )

			$acnt = (int)array_shift($mv);
			for ( $ai=0; $ai < $acnt; $ai++ )
			{
				$b = array_shift($mv);
				list($ax,$ay,$atmgk,$u1) = explode(',', $b);

				if ( isset($tmg[$atmgk]) )
					$klayer[] = [$atmgk , 0+$ax , -$ay];
			} // for ( $ai=0; $ai < $acnt; $ai++ )

			$json = json_encode( [$klayer,$hlayer] );
			$aid  = array_search($json, $keys);
			if ( $aid === false )
			{
				$aid = count($keys);
				$keys[] = $json;
			}

			$alist['time'][$i] = [$aid , 0+$time];
		} // for ( $i=0; $i < $cnt; $i++ )

		$anim[$mk] = $alist;
	} // foreach ( $meta as $mk => $mv )

	$meta = [$anim , $keys];
	return;
}

function sect_quad( &$atlas, &$tmg, &$meta, &$quad )
{
	$quad['keyframe'] = [];
	$quad['hitbox'  ] = [];
	$quad['slot'    ] = [];
	foreach ( $meta[1] as $kk => $kv )
	{
		list($klayer,$hlayer) = json_decode($kv, true);

		if ( ! empty($klayer) )
		{
			$layer = [];
			foreach ( $klayer as $lk => $lv )
			{
				list($tk,$kx,$ky) = $lv;
				$b = $tmg[$tk];

				list($x,$y,$w,$h) = $atlas->getxywh( $b['atlas'] );
				$src = xywh_quad($w, $h);
				xywh_move($src, $x, $y);

				$dst = xywh_quad($w, $h);
				xywh_move($dst, $kx, $ky);

				$k = [
					'dstquad'  => $dst,
					'srcquad'  => $src,
					'blend_id' => 0,
					'tex_id'   => 0,
				];
				$layer[] = $k;
			} // foreach ( $klayer as $lk => $lv )

			$kent = [
				'name'  => "keyframe $kk",
				'layer' => $layer,
			];
			list_add($quad['keyframe'], $kk, $kent);
		}

		if ( ! empty($hlayer) )
		{
			$layer = [];
			foreach ( $hlayer as $lk => $lv )
			{
				list($hx,$hy,$hw,$hh,$hty) = $lv;
				$h = [
					'debug'   => $hty,
					'hitquad' => [
						$hx    , $hy    ,
						$hx+$hw, $hy    ,
						$hx+$hw, $hy+$hh,
						$hx    , $hy+$hh,
					],
				];
				$layer[] = $h;
			} // foreach ( $hlayer as $lk => $lv )

			$hent = [
				'name'  => "hitbox $kk",
				'layer' => $layer,
			];
			list_add($quad['hitbox'], $kk, $hent);
		}

		if ( ! empty($klayer) && ! empty($hlayer) )
		{
			$sent = [
				quad_attach('keyframe', $kk),
				quad_attach('hitbox'  , $kk),
			];
			list_add($quad['slot'], $kk, $sent);
		}
	} // foreach ( $meta[1] as $kk => $kv )

	$quad['animation'] = [];
	foreach ( $meta[0] as $ak => $av )
	{
		$time = [];
		foreach ( $av['time'] as $tk => $tv )
		{
			list($id,$fps) = $tv;

			$a = ['time' => $fps];
			if ( ! empty($quad['slot'][$id]) )
				$a['attach'] = quad_attach('slot', $id);
			else
			if ( ! empty($quad['keyframe'][$id]) )
				$a['attach'] = quad_attach('keyframe', $id);
			else
			if ( ! empty($quad['hitbox'][$id]) )
				$a['attach'] = quad_attach('hitbox', $id);

			$time[] = $a;
		} // foreach ( $av as $tk => $tv )

		$anim = [
			'name'     => "animation $ak",
			'timeline' => $time,
			'loop_id'  => 0,
		];
		list_add($quad['animation'], $ak, $anim);
	} // foreach ( $meta[0] as $ak => $av )
	return;
}

function gowcaiz( $fname )
{
	// for *.act only
	if ( stripos($fname, '.act') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$meta = sectmeta($file);

	$file = substr ($file, 0xc000);
	$tmg  = secttmg($file);

	$atlas = new atlas_tex;
	$atlas->init();
	foreach ( $tmg as $tk => $tv )
	{
		$aid = $atlas->putclut($tv['w'], $tv['h'], $tv['pal'], $tv['pix']);
		$tmg[$tk]['atlas'] = $aid;
	}
	$atlas->sort();
	$atlas->save("$fname.0");

	anim_meta($meta, $tmg);

	$quad = load_idtagfile('psx gowcaizer');
	$quad['blend'] = [ blend_modes('normal') ];
	sect_quad($atlas, $tmg, $meta, $quad);

	save_quadfile($fname, $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gowcaiz( $argv[$i] );
