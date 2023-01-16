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
require 'class-pixlines.inc';
require 'quad.inc';

php_req_extension('json_decode', 'json');

$gp_json = '';
$gp_s4_flag = 0;
$gp_s5_flag = 0;
$gp_s6_flag = 0;
$gp_s8_flag = 0;

function s4_flags( $is )
{
	global $gp_json, $gp_s4_flag;
	switch ( $gp_json['tag'] )
	{
			case 'ps2_grim':
			case 'ps2_odin':
				if ( $is === 'is_skip' )  return ($gp_s4_flag & 0x02);
				if ( $is === 'is_tex'  )  return ($gp_s4_flag & 0x04) === 0;
				return 0;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':

			case 'psp_gran':
			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':
				return 0;
	} // switch ( $tag )
	return 0;
}

function s5_flags( $is )
{
	global $gp_json, $gp_s5_flag;
	switch ( $gp_json['tag'] )
	{
			case 'ps2_grim':
			case 'ps2_odin':
				if ( $is === 'is_attack' )  return ($gp_s5_flag & 0x01);
				if ( $is === 'is_damage' )  return ($gp_s5_flag & 0x02);
				return 0;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':

			case 'psp_gran':
			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':
				return 0;
	} // switch ( $tag )
	return 0;
}

function s6_flags( $is )
{
	global $gp_json, $gp_s6_flag;
	switch ( $gp_json['tag'] )
	{
			case 'ps2_grim':
			case 'ps2_odin':
				if ( $is === 'is_blend' )  return ($gp_s6_flag & 0x04) === 0;
				return 0;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':

			case 'psp_gran':
			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':
				return 0;
	} // switch ( $tag )
	return 0;
}

function s8_flags( $is )
{
	global $gp_json, $gp_s8_flag;
	switch ( $gp_json['tag'] )
	{
			case 'ps2_grim':
			case 'ps2_odin':
				if ( $is === 'is_flipx' )  return ($gp_s8_flag & 0x01);
				if ( $is === 'is_flipy' )  return ($gp_s8_flag & 0x02);
				if ( $is === 'is_loop'  )  return ($gp_s8_flag & 0x04);
				if ( $is === 'is_end'   )  return ($gp_s8_flag & 0x08);
				if ( $is === 'is_skip'  )  return ($gp_s8_flag & 0x40);
				if ( $is === 'is_sfx'   )  return ($gp_s8_flag & 0x80);
				if ( $is === 'is_s4s5'  )  return ($gp_s8_flag & 0x400) === 0;
				return 0;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':

			case 'psp_gran':
			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':
				return 0;
	} // switch ( $tag )
	return 0;
}
//////////////////////////////
function s6s4_lines( $dir )
{
	global $gp_json;
	ob_start();

	$grid = new PixLines;
	foreach ( $gp_json['s6'] as $s6k => $s6v )
	{
		if ( empty($s6v) )
			continue;
		printf("s6[%d].flags  %s\n", $s6k, $s6v['bits']);

		$fn = sprintf('%s/%04d.clut', $dir, $s6k);
		$grid->new();

		for ( $i=0; $i < $s6v['s4'][1]; $i++ )
		{
			$s4k = $s6v['s4'][0] + $i;
			$s4v = $gp_json['s4'][$s4k];
			printf("  s4[%d].flags  %s\n", $s4k, $s4v['bits']);

			$s2k = $s4v['s0s1s2'][2];
			$s2v = $gp_json['s2'][$s2k];

			$grid->addquad($s2v, "\x0e");
		} // for ( $i=0; $i < $s6v['s4'][1]; $i++ )

		for ( $i=0; $i < $s6v['s5'][1]; $i++ )
		{
			$s5k = $s6v['s5'][0] + $i;
			$s5v = $gp_json['s5'][$s5k];
			printf("  s5[%d].flags  %s\n", $s5k, $s5v['bits']);

			$s3k = $s5v['s3'];
			$s3v = $gp_json['s3'][$s3k];

			$grid->addquad($s3v['rect'], "\x0d");
		} // for ( $i=0; $i < $s6v['s5'][1]; $i++ )

		$img = $grid->draw();
		save_clutfile($fn, $img);
	} // foreach ( $gp_json['s6'] as $s6k => $s6v )

	$txt = ob_get_clean();
	save_file("$dir/pixlines.txt", $txt);
	return;
}

function s6_loop( &$quad )
{
	global $gp_json, $gp_s6_flag, $gp_s4_flag, $gp_s5_flag;

	$quad['keyframe'] = array();
	$quad['hitbox']   = array();
	$quad['set']      = array();
	foreach ( $gp_json['s6'] as $s6k => $s6v )
	{
		if ( empty($s6v) )
			continue;

		$gp_s6_flag = hexdec( $s6v['bits'] );
		$is_blend = s6_flags('is_blend');

		$s4 = array();
		if ( $s6v['s4'][1] > 0 )
		{
			$layer = array();
			for ( $i=0; $i < $s6v['s4'][1]; $i++ )
			{
				$s4k = $s6v['s4'][0] + $i;
				$s4v = $gp_json['s4'][$s4k];

				$gp_s4_flag = hexdec( $s4v['bits'] );
				if ( s4_flags('is_skip') )
					continue;

				$s2k = $s4v['s0s1s2'][2];
				$s2v = $gp_json['s2'][$s2k];

				$data = array(
					'_debug'   => $s4v['bits'],
					'dstquad'  => $s2v,
				);

				if ( $is_blend )
					$data['blend_id'] = $s4v['blend'] + 1;

				$s0k = $s4v['s0s1s2'][0];
				$s0v = $gp_json['s0'][$s0k];
				if ( $s0v !== '#ffffffff' )
					$data['fogquad'] = $s0v;

				if ( s4_flags('is_tex') )
				{
					$data['tex_id'] = $s4v['tex']+1;

					$s1k = $s4v['s0s1s2'][1];
					$s1v = $gp_json['s1'][$s1k];
					$data['srcquad'] = $s1v;
				}

				$layer[$i] = $data;
			} // for ( $i=0; $i < $s6v['s4'][1]; $i++ )

			$s4 = array(
				'_debug' => $s6v['bits'],
				'layer'  => $layer,
			);
			list_add( $quad['keyframe'], $s6k, $s4 );
		}

		$s5 = array();
		if ( $s6v['s5'][1] > 0 )
		{
			$layer = array();
			for ( $i=0; $i < $s6v['s5'][1]; $i++ )
			{
				$s5k = $s6v['s5'][0] + $i;
				$s5v = $gp_json['s5'][$s5k];

				$s3k = $s5v['s3'];
				$s3v = $gp_json['s3'][$s3k];

				$data = array(
					'_debug'  => $s5v['bits'],
					'hitquad' => $s3v['rect'],
				);
				$gp_s5_flag = hexdec( $s5v['bits'] );

				//if ( s5_flags('is_damage') )  $data['type'] = 'damage';
				//if ( s5_flags('is_attack') )  $data['type'] = 'attack';

				$layer[$i] = $data;
			} // for ( $i=0; $i < $s6v['s5'][1]; $i++ )

			$s5 = array(
				'layer'  => $layer,
			);
			list_add( $quad['hitbox'], $s6k, $s5 );
		}

		if ( ! empty($s4) && ! empty($s5) )
		{
			$set = array(
				array('type' => 'keyframe' , 'id' => $s6k),
				array('type' => 'hitbox'   , 'id' => $s6k),
			);
			list_add( $quad['set'], $s6k, $set );
		}
	} // foreach ( $gp_json['s6'] as $s6k => $s6v )
	return;
}
//////////////////////////////
function s7_matrix( $s7, $flipx, $flipy )
{
	// in scale - rotate z-y-x - move - flip order
	$m = matrix_scale(4, $s7['scale'][0], $s7['scale'][1]);

	$t = matrix_rotate_z(4, $s7['rotate'][2]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$t = matrix_rotate_y(4, $s7['rotate'][1]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$t = matrix_rotate_x(4, $s7['rotate'][0]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$t = matrix(4);
	$t[0+3] = $s7['move'][0];
	$t[4+3] = $s7['move'][1];
	$t[8+3] = $s7['move'][2];
	$m = matrix_multi44($m, $t);

	$bx = ( $flipx ) ? -1 : 1;
	$by = ( $flipy ) ? -1 : 1;
	$t = matrix_scale(4, $bx, $by);
	$m = matrix_multi44($m, $t);
	return $m;
}
//////////////////////////////

/*
function add_attach_set( &$quad, $s6_id, $is_s4s5, $sfx )
{
	return;
}

function s5_remap( &$s5 )
{
	$uniq  = array();
	$remap = array();
	foreach ( $s5 as $k => $v )
	{
		$id = array_search($v, $uniq);
		if ( $id === false )
			$uniq[] = $v;
		else
			$remap[$k] = $id;
	}
	return array($uniq,$remap);
}
//////////////////////////////
function s8_timeline( &$s8, &$quad )
{
	global $gp_s8_flag;
	$time = array();
	$anim = array();
	$s8k = 0;
	$fps = 0;
	while (1)
	{
		if ( ! isset($s8[$s8k]) )
			break;

		$s8v = $s8[$s8k];
		$gp_s8_flag = hexdec( $s8v['bits'] );

		$time[$s8k] = $fps;
		if ( s8_flags('is_skip') )
		{
			$s8k++;
			continue;
		}

		$flip = array(1,1);
		if ( s8_flags('is_flipx') )  $flip[0] = -1;
		if ( s8_flags('is_flipy') )  $flip[1] = -1;


		$is_s4s5 = s8_flags('is_s4s5');
		$sfx = ( ! s8_flags('is_sfx') ) ? -1 : $s8v['sfx'];
		$set = add_attach_set($quad, $s8v['s6'], $is_s4s5, $sfx);

		$s7 = $gp_json['s7'][ $s8v['s7'] ];
		$mat4 = 0;
		$fog  = $s7['fog'];

		$dot = array(
			'frame'    => $fps ,
			'duration' => $s8v['anim'][0] ,
			'matrix'   => $mat4 ,
			'fog'      => $fog ,
			'flip'     => $flip ,
			'attach'   => $set ,
			'_debug_'  => $s8v['bits'] ,
		);
		$fps += $s8v['anim'][0];

		if ( s8_flags('is_loop') )
		{
			$loop = $s8v['anim'][1];
			// is not loop when it jump forward
			if ( $s8k < $loop )
			{
				$s8k = $loop;
				continue;
			}
			else // anim end by looping
			{
				$dot['loop'] = $time[$loop];
				$anim[] = $dot;
				goto done;
			}
		}

		// anim end by clearing
		if ( s8_flags('is_end') )
		{
			$dot['loop'] = -1;
			$anim[] = $dot;
			goto done;
		}

		$anim[] = $dot;
		$s8k++;
	} // while (1)

done:
	return $anim;
}
//////////////////////////////
function fmbs_loop( &$quad )
{
	$s5_remap = s5_remap( $gp_json['s5'] );

	$quad['animation'] = array();
	foreach ( $gp_json['s9'] as $s9k => $s9v )
	{
		if ( ! isset( $s9v['name'] ) || empty( $s9v['name'] ) )
			continue;

		$sa = array();
		for ( $i=0; $i < $s9v['sa'][1]; $i++ )
			$sa[$i] = $gp_json['sa'][ $s9v['sa'][0] + $i ];

		$s8data = array();
		foreach ( $sa as $sak => $sav )
		{
			$s8 = array();
			for ( $i=0; $i < $sav['sa'][1]; $i++ )
				$s8[$i] = $gp_json['sa'][ $sav['sa'][0] + $i ];

			for ( $i=0; $i < $sav['sa'][3]; $i++ )
				array_shift($s8);

			$s8data[$sak] = $s8;
		} // foreach ( $sa as $sak => $sav )
		s8_tidy( $s8data );

		$track = array();

		$sfx = s8_get_sfx($s8data);
		if ( ! empty($sfx) )
			$track['sfx'] = $sfx;

		$box = s8_get_hitbox($s8data);
		if ( ! empty($box) )
			$track['hitbox'] = $box;

		$s9data = array(
			'name'  => $s9v['name'],
			'track' => $track,
		);
		list_add($quad['animation'], $s9k, $s9data);
	} // foreach ( $gp_json['s9'] as $s9k => $s9v )
	return;
}
*/
//////////////////////////////
function vanilla( $line, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_json;
	$gp_json = json_decode($file, true);
	if ( empty($gp_json) )  return;

	$dir = str_replace('.', '_', $fname);

	$quad = load_idtagfile( $gp_json['id3'] );
	if ( $line )
		s6s4_lines($dir);

	s6_loop($quad);
	//s9_loop($quad);

	$quad = json_pretty($quad, '');
	save_file("$fname.quad", $quad);
	return;
}

$line = false;
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		vanilla( $line, $argv[$i] );
	else
		$line = $argv[$i];
}
