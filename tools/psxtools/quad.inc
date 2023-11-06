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
if ( ! function_exists('json_pretty') )
	exit("no json_pretty() [from common-json.inc]\n");

// .quad file
function load_idtagfile( $pfx )
{
	$list = array();
	lsfile_r(__DIR__ . '/idtag/', $list);

	$fn = '';
	foreach ( $list as $v )
	{
		if ( stripos($v, $pfx) !== false )
			$fn = $v;
	}
	if ( empty($fn) )
		return array();

	$tag  = array();
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
				$tag[$head] = array();
			$tag[$head][] = $line;
		}
	} // foreach ( file($fn) as $line )

	foreach ( $tag as $head => $line )
	{
		if ( count($line) == 1 )
			$tag[$head] = $line[0];
	}

	$data = array(
		'tag' => $tag
	);
	return $data;
}

function anim_allkeys( &$quad )
{
	if ( empty($quad) )
		return;
	if ( ! isset( $quad['keyframe'] ) )
		return;
	if ( ! isset( $quad['animation'] ) )
		$quad['animation'] = array();

	$time = array();
	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		$en = array(
			'time' => 1,
			'attach' => array(
				'type' => 'keyframe',
				'id'   => $kk
			),
		);
		$time[] = $en;
	}

	$anim = array(
		'name' => 'ALL KEYFRAMES',
		'timeline' => $time,
	);
	$quad['animation'][] = $anim;
	return;
}

function list_add( &$list, $k, &$v )
{
	$k = (int)$k;
	for ( $i=0; $i <= $k; $i++ )
	{
		if ( ! isset($list[$i]) )
			$list[$i] = 0;
		if ( $i === $k )
			$list[$i] = $v;
	}

	// unorder keys is std::map, not std::vector
	ksort($list);
	return;
}

function save_quadfile( $pfx, &$quad )
{
	if ( empty($quad) )
		return;
	anim_allkeys($quad);

	$quad = json_pretty($quad, '');
	save_file("$pfx.quad", $quad);
	return;
}
//////////////////////////////
function xywh_quad( $w, $h, $flipx=false, $flipy=false )
{
	$quad = array(
		0  , 0  ,
		$w , 0  ,
		$w , $h ,
		0  , $h ,
	);

	//  normal     flipx      flipy
	//  0,1  2,3 | 2,3  0,1 | 6,7  4,5
	//  6,7  4,5 | 4,5  6,7 | 0,1  2,3
	if ( $flipx )
	{
		$n = array(
			$quad[2] , $quad[3] ,
			$quad[0] , $quad[1] ,
			$quad[6] , $quad[7] ,
			$quad[4] , $quad[5] ,
		);
		$quad = $n;
	}
	if ( $flipy )
	{
		$n = array(
			$quad[6] , $quad[7] ,
			$quad[4] , $quad[5] ,
			$quad[2] , $quad[3] ,
			$quad[0] , $quad[1] ,
		);
		$quad = $n;
	}
	return $quad;
}

function xywh_rotate( &$quad, $radian )
{
	if ( $radian == 0 )
		return;
	// rotate is based on top-left as 0,0
	$sin = sin($radian);
	$cos = cos($radian);

	for ( $i=0; $i < 8; $i += 2 )
	{
		$x = $quad[$i+0];
		$y = $quad[$i+1];
		$quad[$i+0] = $x * $cos - $y * $sin;
		$quad[$i+1] = $x * $sin + $y * $cos;
	}
	return;
}

function xywh_move( &$quad, $mx, $my )
{
	for ( $i=0; $i < 8; $i += 2 )
	{
		$quad[$i+0] += $mx;
		$quad[$i+1] += $my;
	}
	return;
}
//////////////////////////////
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

	$pnt[0] /= $pnt[2];
	$pnt[1] /= $pnt[2];
	$pnt[2] /= $pnt[2];
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

	$dst = array(
		array( $dstquad[0] , $dstquad[1] , 1),
		array( $dstquad[2] , $dstquad[3] , 1),
		array( $dstquad[4] , $dstquad[5] , 1),
		array( $dstquad[6] , $dstquad[7] , 1),
	);

	// simple = OK
	$pnt = is_line_on_line($dst[0],$dst[2] , $dst[1],$dst[3]);
	if ( $pnt !== -1 ) // is simple
		return;

	// twisted = NOT SUPPORTED
	$pnt = is_line_on_line($dst[0],$dst[1] , $dst[3],$dst[2]);
	if ( $pnt !== -1 ) // is top-bottom twisted
	{
		$quad = array('_error' => 'is top-bottom twisted');
		return;
	}
	$pnt = is_line_on_line($dst[0],$dst[3] , $dst[1],$dst[2]);
	if ( $pnt !== -1 ) // is left-right twisted
	{
		$quad = array('_error' => 'is left-right twisted');
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
function blend_modes( $mode )
{
	switch ( $mode )
	{
		case 'normal':
		case 'over':
			$blend = array(
				'name' => 'normal',
				'mode' => array('FUNC_ADD', 'SRC_ALPHA', 'ONE_MINUS_SRC_ALPHA'),
				'_debug' => '(FG.rgb * FG.a) + ((BG.rgb * BG.a) * (1 - FG.a))',
			);
			return $blend;

		case 'add':
		case 'addition':
			$blend = array(
				'name' => 'add',
				'mode' => array('FUNC_ADD', 'ONE', 'ONE'),
				'_debug' => 'FG.rgba + BG.rgba',
			);
			return $blend;

		case 'sub':
		case 'subtract':
		case 'subtraction':
			$blend = array(
				'name' => 'subtract',
				'mode' => array('FUNC_REVERSE_SUBTRACT', 'ONE', 'ONE'),
				'_debug' => 'BG.rgba - FG.rgba',
			);
			return $blend;
	} // switch ( $mode )
	return 0;
}
//////////////////////////////
function quad_center( $V4 )
{
	$cx = ($V4[0] + $V4[2] + $V4[4] + $V4[6]) / 4;
	$cy = ($V4[1] + $V4[3] + $V4[5] + $V4[7]) / 4;
	trace("== center : %.2f , %.2f\n", $cx, $cy);
	return array($cx,$cy);
}

function quad_rebase( $V4 )
{
	// rebase $V4 to 0,0
	$base = array(
		(int)$V4[0] ,
		(int)$V4[1] ,
	);
	for ( $i=0; $i < 8; $i += 2 ) // A B C D
	{
		$V4[$i+0] -= $base[0];
		$V4[$i+1] -= $base[1];
	}

	// get box size
	$x1 = var_min($V4[0], $V4[2], $V4[4], $V4[6]);
	$y1 = var_min($V4[1], $V4[3], $V4[5], $V4[7]);
	$x2 = var_max($V4[0], $V4[2], $V4[4], $V4[6]);
	$y2 = var_max($V4[1], $V4[3], $V4[5], $V4[7]);
		$x1 = (int)floor($x1);
		$y1 = (int)floor($y1);
		$x2 = (int)ceil ($x2);
		$y2 = (int)ceil ($y2);

	$quad = array(
		'quad' => array(
			array( $V4[0] , $V4[1] , 1 ),
			array( $V4[2] , $V4[3] , 1 ),
			array( $V4[4] , $V4[5] , 1 ),
			array( $V4[6] , $V4[7] , 1 ),
		),
		'base' => $base,
		'box'  => array($x1, $y1, $x2, $y2),
	);
	trace("== quad_rebase() = %.2f , %.2f\n", $base[0], $base[1]);
	trace("   box x = %.2f - %.2f  y = %.2f - %.2f\n", $x1, $x2, $y1, $y2);
	quad_dump($V4, 'rebase');
	return $quad;
}

function quad2xywh( $V4 )
{
	$x1 = var_min($V4[0], $V4[2], $V4[4], $V4[6]);
	$y1 = var_min($V4[1], $V4[3], $V4[5], $V4[7]);
	$x2 = var_max($V4[0], $V4[2], $V4[4], $V4[6]);
	$y2 = var_max($V4[1], $V4[3], $V4[5], $V4[7]);
		$x1 = (int)floor($x1);
		$y1 = (int)floor($y1);
		$x2 = (int)ceil($x2);
		$y2 = (int)ceil($y2);

	$xywh = array(
		'xy' => array($x1, $y1),
		'wh' => array($x2-$x1, $y2-$y1),
		'quad' => array(
			$V4[0]-$x1, $V4[1]-$y1,
			$V4[2]-$x1, $V4[3]-$y1,
			$V4[4]-$x1, $V4[5]-$y1,
			$V4[6]-$x1, $V4[7]-$y1,
		),
	);
	quad_dump($xywh['quad'], 'xywh quad');
	return $xywh;
}

function quad_dump( $V4, $name )
{
	if ( defined("NO_TRACE") )
		return;

	$minx = var_min($V4[0], $V4[2], $V4[4], $V4[6]);
	$miny = var_min($V4[1], $V4[3], $V4[5], $V4[7]);
	$maxx = var_max($V4[0], $V4[2], $V4[4], $V4[6]);
	$maxy = var_max($V4[1], $V4[3], $V4[5], $V4[7]);

	trace("quad( %s )\n", $name);
	trace("  %10.2f,%10.2f\n", $V4[0], $V4[1]);
	trace("  %10.2f,%10.2f\n", $V4[2], $V4[3]);
	trace("  %10.2f,%10.2f\n", $V4[4], $V4[5]);
	trace("  %10.2f,%10.2f\n", $V4[6], $V4[7]);
	trace("  w %10.2f - %10.2f [%10.2f]\n", $minx, $maxx, $maxx-$minx);
	trace("  h %10.2f - %10.2f [%10.2f]\n", $miny, $maxy, $maxy-$miny);
	return;
}
//////////////////////////////
