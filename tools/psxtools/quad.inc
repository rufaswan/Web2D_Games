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
	exit("no json_pretty() [from class-json.inc]\n");

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
		'tag' => $tag ,
		'blend' => array(
			'if ( FG.a === 0.0 )  discard;  pixel.rgb = FG.rgb;' ,
		),
	);
	return $data;
}

function list_add( &$list, $k, &$v )
{
	$k = (int)$k;
	while ( ! isset($list[$k]) )
		$list[] = '';
	$list[$k] = $v;
	return;
}

function save_quadfile( $pfx, &$json )
{
	if ( empty($json) )
		return;

	$json = json_pretty($json);
	save_file("$pfx.quad", $json);
	return;
}
//////////////////////////////
// debug + fix
function quad_int_px( &$quad )
{
	$cx = ($quad[0] + $quad[2] + $quad[4] + $quad[6]) / 4;
	$cy = ($quad[1] + $quad[3] + $quad[5] + $quad[7]) / 4;
	for ( $i=0; $i < 8; $i += 2 )
	{
		if ( $quad[$i+0] < $cx )  $quad[$i+0] = (int)floor($quad[$i+0]);
		if ( $quad[$i+1] < $cy )  $quad[$i+1] = (int)floor($quad[$i+1]);

		if ( $quad[$i+0] > $cx )  $quad[$i+0] = (int) ceil($quad[$i+0]);
		if ( $quad[$i+1] > $cy )  $quad[$i+1] = (int) ceil($quad[$i+1]);
	} // for ( $i=0; $i < 8; $i += 2 )
	return;
}

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

function quad_convexfix( &$quad )
{
	//          0          0--\
	// 0 - 1   / \   0--3   \  \
	// |   |  3   1   \/     3  1
	// 3 - 2   \ /    /\    /  /
	//          2    2--1  2--/
	//
	$dst = array(
		array($quad['DstQuad'][0] , $quad['DstQuad'][1] , 1),
		array($quad['DstQuad'][2] , $quad['DstQuad'][3] , 1),
		array($quad['DstQuad'][4] , $quad['DstQuad'][5] , 1),
		array($quad['DstQuad'][6] , $quad['DstQuad'][7] , 1),
	);

	// normal quad
	//   0,1,2 + 0,3,2  ==  1,2,3 + 1,0,3
	$q1 = triad_area($dst[0], $dst[1], $dst[2]);
	$q2 = triad_area($dst[0], $dst[3], $dst[2]);
	$qsz1 = $q1 + $q2;

	$q1 = triad_area($dst[1], $dst[2], $dst[3]);
	$q2 = triad_area($dst[1], $dst[0], $dst[3]);
	$qsz2 = $q1 + $q2;

	if ( $qsz1 == $qsz2 )
		return; // simple

	//   Saturn Princess Crown , e_ex.pak , frame 3
	//     p0 = 116,-109 ,  77,-41 ,  86,-71  , 51,-136
	//     p5 =  70,-49  , 254,-33 , 101,-116 , 95,-57


	return;
}
//////////////////////////////
function glsl_blend_psx( $t )
{
	// FG.rgb * s0 + BG.rgb * s1
	switch ( $t )
	{
		case 'addition':  $s = array( 1.0  , 1.0); break;
		case 'average' :  $s = array( 0.5  , 0.5); break;
		case 'subtract':  $s = array(-1.0  , 1.0); break;
		case 'quarter' :  $s = array( 0.25 , 1.0); break;
		default:
			return php_error('unknown = glsl_blend_psx( $t )');
	}
	$glsl  = sprintf('pixel.rgb = (FG.rgb * %.1f) + (BG.rgb * %.1f); ', $s[0], $s[1]);
	$glsl .= sprintf('pixel.a = 1.0; ');
	return $glsl;
}

function glsl_blend_ps2( $a, $b, $c, $d )
{
	// ((A.rgb - B.rgb) * C.a) + D.rgb
	$glsl  = sprintf('pixel.rgb = ((%s.rgb - %s.rgb) * %s.a) + %s.rgb; ', $a, $b, $c, $d);
	$glsl .= sprintf('pixel.a = 1.0; ');
	return $glsl;
}

function glsl_blend_wii( $a, $b, $c, $d )
{
	// TEV
	// ( D + ( ((1-C) * A) + (C*B) ) + bias ) * scale
	// ( D + ( ( 1    * A)         )        ) * 1 = (BC=0) Addition
	// ( D - ( ( 1    * A)         )        ) * 1 = (BC=0) Subtraction
	// (     (               (C*B) )        ) * 1 = (AD=0) Multiplication
	// ( D + (               (C*B) )        ) * 1 = ( A=0) Addition + Multiplication
	// (     ( ((1-C) * A) + (C*B) )        ) * 1 = ( D=0) Decal
	// ( D + ( ((1-C) * A)         )        ) * 1 = ( B=0) Proportional

	// ( D + ( ( 1    * A)         )        ) * 1 = ( C=0)
	// ( D                                  ) * 1 = (AB=0)
	// (     ( ( 1    * A)         )        ) * 1 = (CD=0)

	$glsl  = sprintf('pixel.rgb = (FG.rgb * %s) + (BG.rgb * %s); ', $a, $b);
	$glsl .= sprintf('pixel.a = 1.0; ');
	return $glsl;
}
