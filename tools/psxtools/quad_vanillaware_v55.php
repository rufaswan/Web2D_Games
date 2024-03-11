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
require 'quad.inc';
require 'class-pixlines.inc';

php_req_extension('json_decode', 'json');

$gp_json = '';
$gp_s4_flag = 0;
$gp_s5_flag = 0;
$gp_s6_flag = 0;
$gp_s8_flag = 0;

function s4_flags( $is )
{
	//           skip  tex
	// ps2_grim     2   ~4  5,1
	// ps4_grim
	// swi_grim
	// ps2_odin     2   ~4  ASM
	// vit_odin     2   ~4  OR 9,1  29,1  2d,1  29,2  RESD  5,1  11,1  21,1  25,1  2d,1  21,2  29,2  2d,2
	// ps3_odin     2   ~4  5,1  11,1  21,1  25,1  2d,1  21,2  29,2  2d,2
	// ps4_odin     2   ~4  5,1  11,1  21,1  25,1  2d,1  21,2  29,2  2d,2
	// wii_mura         ~4  2d,1
	// vit_mura         ~4  2d,1
	//      dlc     2
	// vit_drag     2
	// ps3_drag     2
	// ps4_drag     2
	// ps4_sent     2       9,1  29,1
	// swi_sent

	// nds_kuma     2
	// psp_gran
	//
	//   skip = s6.rect === [0,0,0,0] && s6.s4_set_no > 0
	global $gp_json, $gp_s4_flag;
	switch ( $is )
	{
		case 'skip':
			return ($gp_s4_flag & 0x02);
		case 'tex':
			return ($gp_s4_flag & 0x04) === 0;
	} // switch ( $is )
	return 0;
}

function s5_flags( $is )
{
	return 0;
}

function s6_flags( $is )
{
	return 0;
}

function s8_flags( $is )
{
	// -          last  jump  s6zr   fx  fy
	// ps2_grim    800     4   ~40
	// ps4_grim
	// swi_grim
	// ps2_odin      8     4  ~400    1   2  ASM
	// vit_odin    800     4  ~400    1   2
	// ps3_odin    800     4  ~400    1   2
	// ps4_odin    800     4  ~400    1   2
	// wii_mura    800     4  ~400    1   2
	// vit_mura    800     4  ~400    1   2
	//     dlc     800     4     -    1   2
	// vit_drag    800     4     -
	// ps3_drag    800     4     -
	// ps4_drag    800     4     -
	// ps4_sent    800     4     -    1   2
	// swi_sent

	// nds_kuma    800     4  ~400    1   2
	// psp_gran    800     4  ~400
	//
	//   last = sa->s8 , last entry
	//   jump = s8[10] !== 0 , sa.s8_time_sum
	//   fx / fy = s9.name === 'turn'
	//
	//   depreciated
	//     s6zr = s6.rect === [0,0,0,0]
	global $gp_json, $gp_s8_flag;
	switch ( $is )
	{
		case 'last':
			if ( $gp_json['tag'] === 'ps2_odin' )
				return ($gp_s8_flag & 0x08);
			else
				return ($gp_s8_flag & 0x800);

		case 'jump':
			return ($gp_s8_flag & 0x04);

		case 'flipx':
			return ($gp_s8_flag & 0x01);
		case 'flipy':
			return ($gp_s8_flag & 0x02);
	} // switch ( $is )
	return 0;
}
//////////////////////////////
function s6s4_lines( $dir )
{
	global $gp_json;
	ob_start();

	$grid = new pixel_lines;
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
	$dummy = 0;

	$quad['keyframe'] = array();
	$quad['hitbox']   = array();
	$quad['slot']     = array();
	foreach ( $gp_json['s6'] as $s6k => $s6v )
	{
		if ( empty($s6v) )
			continue;

		$gp_s6_flag = hexdec( $s6v['bits'] );

		// section keyframe
		$s4 = array();
		if ( $s6v['s4'][1] > 0 )
		{
			$layer = array();
			list_add( $layer, $s6v['s4'][1]-1, $dummy );

			for ( $i=0; $i < $s6v['s4'][1]; $i++ )
			{
				$s4k = $s6v['s4'][0] + $i;
				$s4v = $gp_json['s4'][$s4k];

				$gp_s4_flag = hexdec( $s4v['bits'] );

				if ( s4_flags('skip') )
					continue;

				$s2k = $s4v['s0s1s2'][2];
				$s2v = $gp_json['s2'][$s2k];

				$data = array(
					'_debug'   => sprintf('%s,%s', $s4v['bits'], $s4v['blend']),
					'dstquad'  => $s2v,
					'blend_id' => $s4v['blend'],
				);

				$s0k = $s4v['s0s1s2'][0];
				$s0v = $gp_json['s0'][$s0k];
				if ( $s0v !== '#ffffffff' )
					$data['fogquad'] = $s0v;

				if ( s4_flags('tex') )
				{
					$data['tex_id'] = $s4v['tex'];

					$s1k = $s4v['s0s1s2'][1];
					$s1v = $gp_json['s1'][$s1k];
					$data['srcquad'] = $s1v;
				}

				quad_convexfix($data);
				$layer[$i] = $data;
			} // for ( $i=0; $i < $s6v['s4'][1]; $i++ )

			$s4 = array(
				'_debug' => $s6v['bits'],
				'name'   => sprintf('keyframe %d', $s6k),
				'layer'  => $layer,
			);
			list_add( $quad['keyframe'], $s6k, $s4 );
		}

		// section hitbox
		$s5 = array();
		if ( $s6v['s5'][1] > 0 )
		{
			$layer = array();
			list_add( $layer, $s6v['s5'][1]-1, $dummy );

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

				//if ( s5_flags('damage') )  $data['type'] = 'damage';
				//if ( s5_flags('attack') )  $data['type'] = 'attack';

				$layer[$i] = $data;
			} // for ( $i=0; $i < $s6v['s5'][1]; $i++ )

			$s5 = array(
				'name'   => sprintf('hitbox %d', $s6k),
				'layer'  => $layer,
			);
			list_add( $quad['hitbox'], $s6k, $s5 );
		}

		// section slot
		if ( ! empty($s4) && ! empty($s5) )
		{
			$slot = array(
				array('type' => 'keyframe' , 'id' => $s6k),
				array('type' => 'hitbox'   , 'id' => $s6k),
			);
			list_add( $quad['slot'], $s6k, $slot );
		}
	} // foreach ( $gp_json['s6'] as $s6k => $s6v )
	return;
}
//////////////////////////////
function sas8_loop()
{
	$salist = array();

	global $gp_json, $gp_s8_flag;
	foreach ( $gp_json['sa'] as $sak => $sav )
	{
		if ( empty($sav) )
			continue;

		// ERROR gwendlyn.mbp , sa 7b , s8 3ce-3de (+1)
		//   0  3cf
		//   1  3d0
		//   2  3d1  sfx
		//   3  3d2
		//   4  3d3
		//   5  3d4  jump b <- forward , not loop
		//   6  3d5
		//   7  3d6
		//   8  3d7
		//   9  3d8
		//   a  3d9
		//   b  3da
		//   c  3db  jump 7 <- backward , but entry not added
		//   d  3dc         <- unused
		//   e  3dd  end    <- unused
		// anim = 0 1 2 3 4 5 - [b c 7 8 9 a] - [b c 7 8 9 a] ...
		$i = 0;
		$time = array();
		$loop = -1;

		$line = array();
		while (1)
		{
			$s8k = $sav['s8'][0] + $i;
			if ( ! isset($gp_json['s8'][$s8k]) )
				goto anim_end;
			$s8v = $gp_json['s8'][$s8k];
			$gp_s8_flag = hexdec( $s8v['bits'] );

			if ( ! isset($line[$s8k]) ) // new entry
			{
				$line[$s8k] = count($line);
				$time[] = $s8v;

				// when last+jump together
				// ignore last , goto jump
				if ( s8_flags('jump') )
					$i = $s8v['loop'];
				else
				{
					if ( s8_flags('last') )
						goto anim_end;
					else
						$i++;
				}
			}
			else // looped back
			{
				$loop = $line[$s8k];
				goto anim_end;
			}
		} // while (1)

anim_end:
		$anim = array(
			'time' => $time,
			'loop' => $loop,
		);
		list_add( $salist, $sak, $anim );
	} // foreach ( $gp_json['sa'] as $sak => $sav )
	return $salist;
}
//////////////////////////////
function s7_matrix( $s7, $flipx, $flipy )
{
	$bx = ( $flipx ) ? -1 : 1;
	$by = ( $flipy ) ? -1 : 1;

	// in scale - rotate z-y-x - move - flip order
	$m = matrix_scale(4, $s7['scale'][0]*$bx, $s7['scale'][1]*$by);

	$t = matrix_rotate_z(4, $s7['rotate'][2]);
	$m = matrix_multi44($m, $t);

	if ( $s7['rotate'][1] !== 0 )
	{
		$t = matrix_rotate_y(4, $s7['rotate'][1]);
		$m = matrix_multi44($m, $t);
	}

	if ( $s7['rotate'][0] !== 0 )
	{
		$t = matrix_rotate_x(4, $s7['rotate'][0]);
		$m = matrix_multi44($m, $t);
	}

	$m[0+3] += ($s7['move'][0] * $bx);
	$m[4+3] += ($s7['move'][1] * $by);
	$m[8+3] +=  $s7['move'][2];

	$s = implode(',', $m);
	if ( $s === '1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1' )
		return 0;
	return $m;
}

function q3D_sas8s9_loop( &$salist, &$quad )
{
	global $gp_json, $gp_s8_flag;

	$quad['animation'] = array();
	$quad['skeleton' ] = array();
	foreach ( $gp_json['s9'] as $s9k => $s9v )
	{
		if ( empty($s9v) )
			continue;

		if ( $s9v['sa'][1] < 1 )
			continue;

		$bone = array();
		for ( $i=0; $i < $s9v['sa'][1]; $i++ )
		{
			$sak = $s9v['sa'][0] + $i;

			$time = array();
			foreach ( $salist[$sak]['time'] as $s8k => $s8v )
			{
				$gp_s8_flag = hexdec( $s8v['bits'] );

				$s6k = $s8v['s6'];
				$attach = array();
				if ( ! empty( $quad['slot'][$s6k] ) )
					$attach = array('type' => 'slot' , 'id' => $s6k);
				else
				if ( ! empty( $quad['keyframe'][$s6k] ) )
					$attach = array('type' => 'keyframe' , 'id' => $s6k);
				else
				if ( ! empty( $quad['hitbox'][$s6k] ) )
					$attach = array('type' => 'hitbox' , 'id' => $s6k);

				$flipx = s8_flags('flipx');
				$flipy = s8_flags('flipy');

				$s7k = $s8v['s7'];
				$matrix = s7_matrix( $gp_json['s7'][$s7k], $flipx, $flipy );

				$ent = array(
					'_debug'       => $s8v['bits'],
					'time'         => $s8v['time'],
					'matrix_mix'   => $s8v['in_s7'],
					'color_mix'    => $s8v['in_s7'],
					'keyframe_mix' => $s8v['in_s6'],
					'hitbox_mix'   => $s8v['in_s5s3'],
				);

				if ( ! empty($attach) )
					$ent['attach'] = $attach;
				if ( $gp_json['s7'][$s7k]['fog'] !== '#ffffffff' )
					$ent['color'] = $gp_json['s7'][$s7k]['fog'];
				if ( $matrix !== 0 )
					$ent['matrix'] = $matrix;

				$time[] = $ent;
			} // foreach ( $salist[$sak] as $s8k => $s8v )

			$anim = array(
				'name'     => "animation $sak",
				'timeline' => $time,
				'loop_id'  => $salist[$sak]['loop'],
			);
			list_add( $quad['animation'], $sak, $anim );

			$bone[] = array(
				//name
				'attach' => array('type' => 'animation' , 'id' => $sak),
				//child
			);
		} // for ( $i=0; $i < $s9v['sa'][1]; $i++ )

		$skel = array(
			//'name' => "skeleton $s9k",
			'name' => $s9v['name'],
			'bone' => $bone,
		);
		list_add( $quad['skeleton'], $s9k, $skel );
	} // foreach ( $gp_json['s9'] as $s9k => $s9v )
	return;
}
//////////////////////////////
function vanilla_blendmode( $tag )
{
	$blend = array();
	switch ( $tag )
	{
		case 'ps2_grim': // 0 1 2
		case 'ps2_odin': // 0 1 2
			// (A.rgb - B.rgb) * C.a + D.rgb
			// ABCD are 2 bits
			//   0=FG  1=BG  2=0  3=reserved
			$blend[0] = array(
				'name' => '44 = 0101',
				'mode' => array('FUNC_ADD', 'SRC_ALPHA', 'ONE_MINUS_SRC_ALPHA'),
				'_debug' => '((FG.rgb - BG.rgb) * FG.a) + BG.rgb',
			);
			$blend[1] = array(
				'name' => '48 = 0201',
				'mode' => array('FUNC_ADD', 'SRC_ALPHA', 'ONE'),
				'_debug' => '((FG.rgb - 0) * FG.a) + BG.rgb',
			);
			$blend[2] = array(
				'name' => '42 = 2001',
				'mode' => array('FUNC_REVERSE_SUBTRACT', 'SRC_ALPHA', 'ONE'),
				'_debug' => '((0 - FG.rgb) * FG.a) + BG.rgb',
			);
			$blend[3] = array(
				'name' => '54 = 0111',
				'mode' => array('FUNC_ADD', 'DST_ALPHA', 'ONE_MINUS_DST_ALPHA'),
				'_debug' => '((FG.rgb - BG.rgb) * BG.a) + BG.rgb',
			);
			$blend[4] = array(
				'name' => '58 = 0211',
				'mode' => array('FUNC_ADD', 'DST_ALPHA', 'ONE'),
				'_debug' => '((FG.rgb - 0) * BG.a) + BG.rgb',
			);
			$blend[5] = array(
				'name' => '52 = 2011',
				'mode' => array('FUNC_REVERSE_SUBTRACT', 'DST_ALPHA', 'ONE'),
				'_debug' => '((0 - FG.rgb) * BG.a) + BG.rgb',
			);
			return $blend;

		case 'wii_mura': // 0 1 2
			// D +/- ( ((1 - C) * A) + (C * B) )
			//   D + A = Addition
			//   D - A = Subtraction
			//   C * B = Multiplication
			//   D + C * B = Addition + Multiplication
			//   ((1 - C) * A) + (C * B) = Decal
			//   D + ((1 - C) * A) = Proportional
			//return $blend;

		case 'psp_gran': // 0 1 2
			//return $blend;

		case 'ps3_drag': // 0 1 2 6
		case 'ps3_odin': // 0 1 2 6
		case 'ps4_odin': // 0 1 2 6
		case 'ps4_drag': // 0 1 2 5 6
		case 'ps4_sent': // 0 1 2 3
		case 'vit_mura': // 0 1 2 6
		case 'vit_drag': // 0 1 2 6
		case 'vit_odin': // 0 1 2 6
		//case 'swi': //

		case 'nds_kuma': // 0
		default:
			$blend[0] = array(
				'name' => 'default',
				'mode' => array('FUNC_ADD', 'SRC_ALPHA', 'ONE_MINUS_SRC_ALPHA'),
			);
			return $blend;
	} // switch ( $cons )

	return $blend;
}

function vanilla( $line, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_json;
	$gp_json = json_decode($file, true);
	if ( empty($gp_json) )  return;

	$dir = str_replace('.', '_', $fname);
	echo "JSON $fname\n";

	$quad = load_idtagfile( $gp_json['id3'] );
	$quad['blend'] = vanilla_blendmode( $gp_json['tag'] );
	if ( $line )
		s6s4_lines($dir);

	s6_loop($quad);
	$sa = sas8_loop();

	q3D_sas8s9_loop($sa, $quad);

	save_quadfile($fname, $quad);
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
