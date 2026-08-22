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
if ( ! class_exists('json_pretty') )
	exit("no json_pretty() [from common-json.inc]\n");

//////////////////////////////


function empty_quad_section( &$quad, $section )
{
	if ( ! isset($quad[$section]) )
		return true;
	if ( ! is_array($quad[$section]) )
		return empty($quad[$section]);

	foreach ( $quad[$section] as $sk => $sv )
	{
		if ( ! empty($sv) )
			return false;
	}
	return true;
}
//////////////////////////////
// .quad file
function load_idtagfile( $pfx )
{
	$list = [];
	lsfile_r(__DIR__ . '/idtag/', $list);

	$fn = '';
	foreach ( $list as $v )
	{
		if ( stripos($v, $pfx) !== false )
			$fn = $v;
	}
	if ( empty($fn) )
		return [];

	$tag  = [];
	$head = '';
	foreach ( file($fn) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$line = htmlspecialchars($line, ENT_QUOTES|ENT_HTML5, 'UTF-8');
		if ( $line[0] == '[' )
			$head = trim($line, '[]');
		else
		{
			if ( ! isset( $tag[$head] ) )
				$tag[$head] = [];
			$tag[$head][] = $line;
		}
	} // foreach ( file($fn) as $line )

	foreach ( $tag as $head => $line )
	{
		if ( count($line) == 1 )
			$tag[$head] = $line[0];
	}

	$data = [
		'tag' => $tag
	];
	return $data;
}

function anim_allkeys( &$quad )
{
	if ( empty($quad) )
		return;
	if ( ! empty_quad_section($quad, 'skeleton') )
		return;
	if ( ! isset( $quad['keyframe'] ) )
		return;
	if ( ! isset( $quad['animation'] ) )
		$quad['animation'] = [];

	// to keep export the same numbering
	$time = [];
	$rep  = 0;
	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( ! isset($kv['layer']) )
		{
			$rep++;
			continue;
		}
		if ( count($kv['layer']) === 1 && empty($kv['layer'][0]) )
		{
			$rep++;
			continue;
		}

		if ( $rep > 0 )
		{
			$time[] = ['time' => $rep];
			$rep = 0;
		}

		$en = [
			'time'   => 1,
			'attach' => quad_attach('keyframe',$kk),
		];
		$time[] = $en;
	} // foreach ( $quad['keyframe'] as $kk => $kv )

	$anim = [
		'name'     => 'ALL KEYFRAMES',
		'timeline' => $time,
	];
	$quad['animation'][] = $anim;
	return;
}

//////////////////////////////
function qmat3_dxdy( $radian, $dx, $dy )
{
	$sin = sin($radian);
	$cos = cos($radian);

	$m3 = [
		$cos , -$sin , $dx ,
		$sin ,  $cos , $dy ,
		   0 ,     0 ,   1 ,
	];
	return $m3;
}

function qdxdy_dist( $radian, $dist )
{
	$dx = $dist * cos($radian);
	$dy = $dist * sin($radian);
	return [$dx, $dy];
}

function qmat3_mult( &$mat3, &$quad )
{
	for ( $i=0; $i < 8; $i += 2 )
	{
		$x = $quad[$i+0];
		$y = $quad[$i+1];
		$quad[$i+0] = $mat3[0]*$x + $mat3[1]*$y + $mat3[2];
		$quad[$i+1] = $mat3[3]*$x + $mat3[4]*$y + $mat3[5];
	}
	return;
}

// for usort , list = { p : parent , i : layer , o : order }
// a < b == 3,2,1  DESC , a > b == 1,2,3  ASC
// a - b == 1,2,3  ASC  , b - a == 3,2,1  DESC
function sort_by_order( $a, $b )
{
	$r = (int)($a['o'] - $b['o']);
	if ( $r === 0 )
		$r = (int)($a['i'] - $b['i']);
	return $d;
}
function sort_by_parent( $a, $b )
{
	$r = (int)($a['p'] - $b['p']);
	if ( $r === 0 )
		$r = (int)($a['i'] - $b['i']);
	return $d;
}
//////////////////////////////
function quad_type( &$quad )
{
	//  1   2
	//    m
	//  4   3
	$midx = ($quad[0] + $quad[2] + $quad[4] + $quad[6]) / 4;
	$midy = ($quad[1] + $quad[3] + $quad[5] + $quad[7]) / 4;

	$type = 'q';
	for ( $i=0; $i < 8; $i += 2 )
	{
		$x = $quad[$i+0];
		$y = $quad[$i+1];
		if ( $x < $midx && $y < $midy )  $type .= '1';
		else
		if ( $x > $midx && $y < $midy )  $type .= '2';
		else
		if ( $x > $midx && $y > $midy )  $type .= '3';
		else
		if ( $x < $midx && $y > $midy )  $type .= '4';
		else
			$type .= '0';
	}
	return $type;
}

// debug + fix
function quad_ext1px( &$quad )
{
	// To fix Lunar 2 EB 1px gap between quads
	//    17, b   0, b   0, 0   17, 0  =>   18, c  0, c  0,0   18,0
	//    17,-c   0,-c   0,-1   17,-1  =>   18,-c  0,-c  0,0   18,0
	//   -18, b  -1, b  -1, 0  -18, 0  =>  -18, c  0, c  0,0  -18,0
	//   -18,-c  -1,-c  -1,-1  -18,-1  =>  -18,-c  0,-c  0,0  -18,0
	//
	// -18,0  - 0,c  - 18,c
	//          |
	// -18,0  - 0,0  - 18,0
	//          |
	// -18,-c - 0,-c - 18,-c
	$cx = ($quad[0] + $quad[2] + $quad[4] + $quad[6]) / 4;
	$cy = ($quad[1] + $quad[3] + $quad[5] + $quad[7]) / 4;

	// extend bottom right
	for ( $i=0; $i < 8; $i += 2 )
	{
		if ( $quad[$i+0] > $cx )  $quad[$i+0]++;
		if ( $quad[$i+1] > $cy )  $quad[$i+1]++;
	}
	return;
}
//////////////////////////////
function is_point_on_line( $pnt, $v2a, $v2b )
{
	$x1 = min($v2a[0], $v2b[0]);
	$y1 = min($v2a[1], $v2b[1]);
	$x2 = max($v2a[0], $v2b[0]);
	$y2 = max($v2a[1], $v2b[1]);
	if ( $pnt[0] < $x1 || $pnt[0] > $x2 )  return false;
	if ( $pnt[1] < $y1 || $pnt[1] > $y2 )  return false;
	return true;
}

function is_line_on_line( $v1a, $v1b, $v2a, $v2b )
{
	$crx = 'cross_product';
	$pnt = $crx( $crx($v1a,$v1b) , $crx($v2a,$v2b));
	$z = (int)($pnt[2] * 1000000);
	if ( $z === 0 )
		return -1;

	$z = 1.0 / $pnt[2];
	$pnt[0] *= $z;
	$pnt[1] *= $z;
	$pnt[2] *= $z;
	if ( is_point_on_line($pnt,$v1a,$v1b) && is_point_on_line($pnt,$v2a,$v2b) )
		return $pnt;
	return -1;
}

function quad_convexfix( &$quad )
{
	//          0          0--\
	// 0 - 1   / \   0--3   \  \
	// |   |  3   1   \/     3  1
	// 3 - 2   \ /    /\    /  /
	//          2    2--1  2--/
	//
	if ( ! isset( $quad['dstquad'] ) )
		return;
	$dstquad = $quad['dstquad'];

	$dst = [
		[ $dstquad[0] , $dstquad[1] , 1],
		[ $dstquad[2] , $dstquad[3] , 1],
		[ $dstquad[4] , $dstquad[5] , 1],
		[ $dstquad[6] , $dstquad[7] , 1],
	];

	// simple = OK
	$pnt = is_line_on_line($dst[0],$dst[2] , $dst[1],$dst[3]);
	if ( $pnt !== -1 ) // is simple
		return;

	// twisted = NOT SUPPORTED
	$pnt = is_line_on_line($dst[0],$dst[1] , $dst[3],$dst[2]);
	if ( $pnt !== -1 ) // is top-bottom twisted
	{
		$quad = ['_error' => 'is top-bottom twisted'];
		return;
	}
	$pnt = is_line_on_line($dst[0],$dst[3] , $dst[1],$dst[2]);
	if ( $pnt !== -1 ) // is left-right twisted
	{
		$quad = ['_error' => 'is left-right twisted'];
		return;
	}

	// bended
	$b1a = triad_area($dst[0] , $dst[1] , $dst[2]);
	$b1b = triad_area($dst[0] , $dst[2] , $dst[3]);
		$b1 = $b1a + $b1b;

	$b2a = triad_area($dst[1] , $dst[2] , $dst[3]);
	$b2b = triad_area($dst[1] , $dst[3] , $dst[0]);
		$b2 = $b2a + $b2b;

	if ( $b1 < $b2 ) // doesnt included concave area
		return;

	$quad['_error'] = 'is bended';
	$b1 = array_shift($quad['dstquad']);
	$b2 = array_shift($quad['dstquad']);
		$quad['dstquad'][] = $b1;
		$quad['dstquad'][] = $b2;

	if ( isset( $quad['srcquad'] ) )
	{
		$b1 = array_shift($quad['srcquad']);
		$b2 = array_shift($quad['srcquad']);
			$quad['srcquad'][] = $b1;
			$quad['srcquad'][] = $b2;
	}

	if ( isset( $quad['fogquad'] ) && is_array( $quad['fogquad'] ) )
	{
		$b1 = array_shift($quad['fogquad']);
		$b2 = array_shift($quad['fogquad']);
			$quad['fogquad'][] = $b1;
			$quad['fogquad'][] = $b2;
	}
	return;
}
//////////////////////////////
/*
	public static function blend_rgb( string $mode ) : array
	{
		switch ( $mode )
		{
			case 'fade in':
				// K 0..1 => SRC * 0..1 + DST * 1..0
				$blend = [
					'name' => 'fade in',
					'mode_rgb'   => ['FUNC_ADD' , 'CONSTANT_COLOR' , 'ONE_MINUS_CONSTANT_COLOR'],
					'mode_alpha' => ['FUNC_ADD' , 'CONSTANT_ALPHA' , 'ONE_MINUS_CONSTANT_ALPHA'],
					'color' => '#ffffffff', // to update
					'debug' => 'SRC * K + DST * (1 - K)',
				];
				return $blend;

			case 'fade out':
				// K 0..1 => SRC * 1..0 + DST * 0..1
				$blend = [
					'name' => 'fade out',
					'mode_rgb'   => ['FUNC_ADD' , 'ONE_MINUS_CONSTANT_COLOR' , 'CONSTANT_COLOR'],
					'mode_alpha' => ['FUNC_ADD' , 'ONE_MINUS_CONSTANT_ALPHA' , 'CONSTANT_ALPHA'],
					'color' => '#ffffffff', // to update
					'debug' => 'SRC * (1 - K) + DST * K',
				];
				return $blend;

			// rgba + rgb series
			// rgb solid.a series
			case 'linear rgb':
				$blend = [
					'name' => 'rgb normal',
					'mode_rgb'   => ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'],
					'mode_alpha' => ['FUNC_ADD' , 'ONE' , 'ONE'],
					'debug' => 'SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a) , solid.a)',
				];
				return $blend;

		} // switch ( $mode )
		return [];
	}
*/
function bitflag_attr( $int, $pfx )
{
	$int = (int)$int;
	if ( $int < 1 ) // 0 and negative
		return 0;

	$attrib = [];
	$sll   = 0;
	while ( $int > 0 )
	{
		$bit = $int & 1;
		if ( $bit )
			$attrib[] = sprintf('%s_%x', $pfx, 1 << $sll);

		$int >>= 1;
		$sll++;
	}

	if ( count($attrib) === 1 )
		return $attrib[0];
	return $attrib;
}
//////////////////////////////
/*
function quad_center( $v4 )
{
	$cx = ($v4[0] + $v4[2] + $v4[4] + $v4[6]) / 4;
	$cy = ($v4[1] + $v4[3] + $v4[5] + $v4[7]) / 4;
	trace("== center : %.2f , %.2f\n", $cx, $cy);
	return [$cx,$cy];
}

function quad_rebase( $v4 )
{
	// rebase $v4 to 0,0
	$base = [
		(int)$v4[0] ,
		(int)$v4[1] ,
	];
	for ( $i=0; $i < 8; $i += 2 ) // A B C D
	{
		$v4[$i+0] -= $base[0];
		$v4[$i+1] -= $base[1];
	}

	// get box size
	$x1 = var_min($v4[0], $v4[2], $v4[4], $v4[6]);
	$y1 = var_min($v4[1], $v4[3], $v4[5], $v4[7]);
	$x2 = var_max($v4[0], $v4[2], $v4[4], $v4[6]);
	$y2 = var_max($v4[1], $v4[3], $v4[5], $v4[7]);
		$x1 = (int)floor($x1);
		$y1 = (int)floor($y1);
		$x2 = (int)ceil ($x2);
		$y2 = (int)ceil ($y2);

	$quad = [
		'quad' => [
			[ $v4[0] , $v4[1] , 1 ],
			[ $v4[2] , $v4[3] , 1 ],
			[ $v4[4] , $v4[5] , 1 ],
			[ $v4[6] , $v4[7] , 1 ],
		],
		'base' => $base,
		'box'  => [$x1, $y1, $x2, $y2],
	];
	trace("== quad_rebase() = %.2f , %.2f\n", $base[0], $base[1]);
	trace("   box x = %.2f - %.2f  y = %.2f - %.2f\n", $x1, $x2, $y1, $y2);
	quad_dump($v4, 'rebase');
	return $quad;
}

function quad2xywh( $v4 )
{
	$x1 = var_min($v4[0], $v4[2], $v4[4], $v4[6]);
	$y1 = var_min($v4[1], $v4[3], $v4[5], $v4[7]);
	$x2 = var_max($v4[0], $v4[2], $v4[4], $v4[6]);
	$y2 = var_max($v4[1], $v4[3], $v4[5], $v4[7]);
		$x1 = (int)floor($x1);
		$y1 = (int)floor($y1);
		$x2 = (int)ceil($x2);
		$y2 = (int)ceil($y2);

	$xywh = [
		'xy' => [$x1, $y1],
		'wh' => [$x2-$x1, $y2-$y1],
		'quad' => [
			$v4[0]-$x1, $v4[1]-$y1,
			$v4[2]-$x1, $v4[3]-$y1,
			$v4[4]-$x1, $v4[5]-$y1,
			$v4[6]-$x1, $v4[7]-$y1,
		],
	];
	quad_dump($xywh['quad'], 'xywh quad');
	return $xywh;
}

function quad_dump( $v4, $name )
{
	if ( defined("NO_TRACE") )
		return;

	$minx = var_min($v4[0], $v4[2], $v4[4], $v4[6]);
	$miny = var_min($v4[1], $v4[3], $v4[5], $v4[7]);
	$maxx = var_max($v4[0], $v4[2], $v4[4], $v4[6]);
	$maxy = var_max($v4[1], $v4[3], $v4[5], $v4[7]);

	trace("quad( %s )\n", $name);
	trace("  %10.2f,%10.2f\n", $v4[0], $v4[1]);
	trace("  %10.2f,%10.2f\n", $v4[2], $v4[3]);
	trace("  %10.2f,%10.2f\n", $v4[4], $v4[5]);
	trace("  %10.2f,%10.2f\n", $v4[6], $v4[7]);
	trace("  w %10.2f - %10.2f [%10.2f]\n", $minx, $maxx, $maxx-$minx);
	trace("  h %10.2f - %10.2f [%10.2f]\n", $miny, $maxy, $maxy-$miny);
	return;
}
*/
//////////////////////////////
