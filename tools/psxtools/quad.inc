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
function quad_attach( $type, $id )
{
	$attach = array(
		'type' => $type,
		'id'   => $id,
	);
	return $attach;
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
	if ( ! empty_quad_section($quad, 'skeleton') )
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
			'time'   => 1,
			'attach' => quad_attach('keyframe',$kk),
		);
		$time[] = $en;
	} // foreach ( $quad['keyframe'] as $kk => $kv )

	$anim = array(
		'name'     => 'ALL KEYFRAMES',
		'timeline' => $time,
	);
	$quad['animation'][] = $anim;
	return;
}

function is_srcquad_prediv( &$quad )
{
	if ( empty($quad) )
		return false;
	if ( ! isset($quad['keyframe']) )
		return false;

	$px = array();
	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		if ( ! isset($kv['layer']) )
			continue;
		foreach ( $kv['layer'] as $lk => $lv )
		{
			if ( empty($lv) )
				continue;
			if ( ! isset($lv['srcquad']) )
				continue;

			// check srcquad is in 0.0 to 1.0
			// OR in pixels
			for ( $i=0 ; $i < 8; $i++ )
			{
				$id = (int)$lv['srcquad'][$i];
				if ( ! isset($px[$id]) )
					$px[$id] = 0;
				$px[$id]++;
			}
		} // foreach ( $kv['layer'] as $lk => $lv )
	} // foreach ( $quad['keyframe'] as $kk => $kv )

	if ( count($px) > 2 )
		return false;
	return true;
}

function save_quadfile( $pfx, &$quad )
{
	if ( empty($quad) )
		return;
	anim_allkeys($quad);

	$json = json_pretty::encode($quad);
	if ( is_srcquad_prediv($quad) )
	{
		php_warning('%s srcquad is in 0.0 to 1.0 / pre-divided', $pfx);
		save_file("$pfx.prediv.quad", $json);
	}
	else
		save_file("$pfx.quad", $json);
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

function xywh_scale( &$quad, $sx, $sy )
{
	for ( $i=0; $i < 8; $i += 2 )
	{
		$quad[$i+0] *= $sx;
		$quad[$i+1] *= $sy;
	}
	return;
}
//////////////////////////////
function qmat3_dxdy( $radian, $dx, $dy )
{
	$sin = sin($radian);
	$cos = cos($radian);

	$m3 = array(
		$cos , -$sin , $dx ,
		$sin ,  $cos , $dy ,
		   0 ,     0 ,   1 ,
	);
	return $m3;
}

function qdxdy_dist( $radian, $dist )
{
	$dx = $dist * cos($radian);
	$dy = $dist * sin($radian);
	return array($dx, $dy);
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
	$d = $a['o'] - $b['o'];
	if ( $d !== 0 )
		return $d;
	return $a['i'] - $b['i'];
}
function sort_by_parent( $a, $b )
{
	$d = $a['p'] - $b['p'];
	if ( $d !== 0 )
		return $d;
	return $a['i'] - $b['i'];
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
function psx_blend_mode( $f, $b )
{
	// (SRC * f) + (DST * b)
	$blend = array(
		'name'  => sprintf('PSX = SRC * %.2f + DST * %.2f', $f, $b),
		'mode_rgb'   => 0,
		'mode_alpha' => array('FUNC_ADD' , 'ONE' , 'ONE'),
	);
	$mode = sprintf('%d,%d', $f*100, $b*100);
	switch ( $mode )
	{
		case '50,50': // 0.5 , 0.5 / average
			$blend['mode_rgb'] = array('FUNC_ADD' , 'CONSTANT_ALPHA' , 'CONSTANT_ALPHA');
			$blend['color']    = '#ffffff80';
			return $blend;
		case '100,100': // 1.0 , 1.0 / addition
			$blend['mode_rgb'] = array('FUNC_ADD' , 'ONE' , 'ONE');
			return $blend;
		case '25,100': // 0.25 , 1.0 / partial addition
			$blend['mode_rgb'] = array('FUNC_ADD' , 'CONSTANT_ALPHA' , 'ONE');
			$blend['color']    = '#ffffff40';
			return $blend;
		case '-100,100': // -1.0 , 1.0 / subtraction
			$blend['mode_rgb'] = array('FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE');
			return $blend;
	} // switch ( $mode )
	return php_error('unknown f = %.2f , b = %.2f', $f, $b);
}

function ps2_blend_mode( $a, $b, $c, $d )
{
	// (A - B) * C + D
	// ABCD are 2 bits
	//   0=SRC  1=DST  2=0  3=reserved
	// ABD are color , C is alpha
	$mode = "$a,$b,$c,$d";
	$blend = array(
		'name' => sprintf('PS2 = (%s.rgb - %s.rgb) * %s.a + %s.rgb', $a, $b, $c, $d),
		'mode_rgb'   => 0,
		'mode_alpha' => array('FUNC_ADD' , 'ONE' , 'ONE'),
	);
	switch ( $mode )
	{
		case 'SRC,DST,SRC,DST':
			// ((SRC.rgb - DST.rgb) * SRC.a) + DST.rgb
			// = SRC.rgb * SRC.a + (-DST.rgb * SRC.a + DST.rgb)
			// = SRC.rgb * SRC.a + DST.rgb * (-1 * SRC.a + 1)
			// = SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)
			$blend['mode_rgb'] = array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA');
			return $blend;
		case 'SRC,0,SRC,DST':
			// ((SRC.rgb - 0) * SRC.a) + DST.rgb
			$blend['mode_rgb'] = array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE');
			return $blend;
		case '0,SRC,SRC,DST':
			// ((0 - SRC.rgb) * SRC.a) + DST.rgb
			$blend['mode_rgb'] = array('FUNC_REVERSE_SUBTRACT' , 'SRC_ALPHA' , 'ONE');
			return $blend;
		case 'SRC,DST,DST,DST':
			// ((SRC.rgb - DST.rgb) * DST.a) + DST.rgb
			// = SRC.rgb * DST.a + (-DST.rgb * DST.a + DST.rgb)
			// = SRC.rgb * DST.a + DST.rgb * (-1 * DST.a + 1)
			// = SRC.rgb * DST.a + DST.rgb * (1 - DST.a)
			$blend['mode_rgb'] = array('FUNC_ADD' , 'DST_ALPHA' , 'ONE_MINUS_DST_ALPHA');
			return $blend;
		case 'SRC,0,DST,DST':
			// ((SRC.rgb - 0) * DST.a) + DST.rgb
			$blend['mode_rgb'] = array('FUNC_ADD' , 'DST_ALPHA' , 'ONE');
			return $blend;
		case '0,SRC,DST,DST':
			// ((0 - SRC.rgb) * DST.a) + DST.rgb
			$blend['mode_rgb'] = array('FUNC_REVERSE_SUBTRACT' , 'DST_ALPHA' , 'ONE');
			return $blend;
	} // switch ( $mode )
	return php_error('unknown %s', $mode);
}

function wii_blend_mode( $a, $b, $c, $d )
{
	// D +/- ( (1 - C) * A + (C * B) )
	$mode = "$a,$b,$c,$d";
	$blend = array(
		'name' => sprintf('WII = %s + ( (1 - %s) * %s + %s * %s )', $d, $c, $a, $c, $b),
		'mode_rgb'   => 0,
		'mode_alpha' => array('FUNC_ADD' , 'ZERO' , 'ONE'),
	);
	switch ( $mode )
	{
		case 'SRC.rgb,0,0,DST.rgb': // addition = D + A
			// rgb = SRC.rgb + DST.rgb
			// a   = DST.a
			$blend['mode_rgb'] = array('FUNC_ADD' , 'ONE' , 'ONE');
			return $blend;
		case '-SRC.rgb,0,0,DST.rgb': // subtraction = D - A
			// rgb = SRC.rgb * -1 + DST.rgb
			// a   = DST.a
			$blend['mode_rgb'] = array('FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE');
			return $blend;
		case '0,SRC.rgb,DST.rgb,0': // multiplication = C * B
			// rgb = SRC.rgb * DST.rgb
			// a   = DST.a
			$blend['mode_rgb'] = array('FUNC_ADD' , 'DST_COLOR' , 'ZERO');
			return $blend;
		case '0,SRC.rgb,SRC.a,DST.rgb': // addition and multiplication = D + C * B
			// rgb = SRC.rgb * SRC.a + DST.rgb
			// a   = DST.a
			$blend['mode_rgb'] = array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE');
			return $blend;
		case 'DST.rgb,SRC.rgb,SRC.a,0': // decal = (1 - C) * A + (C * B)
			// rgb = SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)
			// a   = DST.a
			$blend['mode_rgb'] = array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA');
			return $blend;
		case 'SRC,0,0,DST': // proportional
			// rgba = SRC * (1 - k) + DST * k
			$blend['mode_rgb']   = array('FUNC_ADD' , 'ONE_MINUS_CONSTANT_COLOR' , 'CONSTANT_COLOR');
			$blend['mode_alpha'] = array('FUNC_ADD' , 'ONE_MINUS_CONSTANT_ALPHA' , 'CONSTANT_ALPHA');
			$blend['color']      = '#ffffffff';
			return $blend;
	} // switch ( $mode )
	return php_error('unknown %s', $mode);
}

function blend_modes( $mode )
{
	switch ( $mode )
	{
		// rgba series
		case 'normal':
		case 'over':
			$blend = array(
				'name' => 'normal',
				'mode_rgb'   => array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'),
				'mode_alpha' => array('FUNC_ADD' , 'ONE'       , 'ONE_MINUS_SRC_ALPHA'),
				'debug' => array(
					'SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)',
					'SRC.a           + DST.a   * (1 - SRC.a)',
				),
			);
			return $blend;

		case 'mult':
		case 'multiply':
			$blend = array(
				'name' => 'multiply',
				'mode_rgb'   => array('FUNC_ADD' , 'DST_COLOR' , 'ZERO'),
				'mode_alpha' => array('FUNC_ADD' , 'DST_ALPHA' , 'ONE'),
				'debug' => array(
					'SRC.rgb * DST.rgb' ,
					'SRC.a   * DST.a',
				),
			);
			return $blend;

		case 'fade in':
			// K 0..1 => SRC * 0..1 + DST * 1..0
			$blend = array(
				'name' => 'fade in',
				'mode_rgb'   => array('FUNC_ADD' , 'CONSTANT_COLOR' , 'ONE_MINUS_CONSTANT_COLOR'),
				'mode_alpha' => array('FUNC_ADD' , 'CONSTANT_ALPHA' , 'ONE_MINUS_CONSTANT_ALPHA'),
				'color' => '#ffffffff', // to update
				'debug' => 'SRC * K + DST * (1 - K)',
			);
			return $blend;

		case 'fade out':
			// K 0..1 => SRC * 1..0 + DST * 0..1
			$blend = array(
				'name' => 'fade out',
				'mode_rgb'   => array('FUNC_ADD' , 'ONE_MINUS_CONSTANT_COLOR' , 'CONSTANT_COLOR'),
				'mode_alpha' => array('FUNC_ADD' , 'ONE_MINUS_CONSTANT_ALPHA' , 'CONSTANT_ALPHA'),
				'color' => '#ffffffff', // to update
				'debug' => 'SRC * (1 - K) + DST * K',
			);
			return $blend;

		// rgba + rgb series
		case 'average':
			$blend = array(
				'name' => 'average',
				'mode_rgb' => array('FUNC_ADD' , 'CONSTANT_ALPHA' , 'CONSTANT_ALPHA'),
				'color' => '#ffffff80',
				'debug' => 'SRC * 0.5 + DST * 0.5',
			);
			return $blend;

		case 'add':
			$blend = array(
				'name' => 'add',
				'mode_rgb' => array('FUNC_ADD' , 'ONE' , 'ONE'),
				'debug' => 'SRC + DST',
			);
			return $blend;

		// rgb solid.a series
		case 'linear rgb':
			$blend = array(
				'name' => 'rgb normal',
				'mode_rgb'   => array('FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'),
				'mode_alpha' => array('FUNC_ADD' , 'ONE' , 'ONE'),
				'debug' => 'SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a) , solid.a)',
			);
			return $blend;

		case 'sub':
		case 'subtract':
			$blend = array(
				'name' => 'subtract',
				'mode_rgb'   => array('FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE'),
				'mode_alpha' => array('FUNC_ADD' , 'ONE' , 'ONE'),
				'debug' => '-SRC.rgb + DST.rgb  , solid.a',
			);
			return $blend;
	} // switch ( $mode )
	return 0;
}

function bitflag_attr( $int, $pfx )
{
	$int = (int)$int;
	if ( $int < 1 ) // 0 and negative
		return 0;

	$attrib = array();
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
	return array($cx,$cy);
}

function quad_rebase( $v4 )
{
	// rebase $v4 to 0,0
	$base = array(
		(int)$v4[0] ,
		(int)$v4[1] ,
	);
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

	$quad = array(
		'quad' => array(
			array( $v4[0] , $v4[1] , 1 ),
			array( $v4[2] , $v4[3] , 1 ),
			array( $v4[4] , $v4[5] , 1 ),
			array( $v4[6] , $v4[7] , 1 ),
		),
		'base' => $base,
		'box'  => array($x1, $y1, $x2, $y2),
	);
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

	$xywh = array(
		'xy' => array($x1, $y1),
		'wh' => array($x2-$x1, $y2-$y1),
		'quad' => array(
			$v4[0]-$x1, $v4[1]-$y1,
			$v4[2]-$x1, $v4[3]-$y1,
			$v4[4]-$x1, $v4[5]-$y1,
			$v4[6]-$x1, $v4[7]-$y1,
		),
	);
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
