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
php_req_extension('json_encode', 'json');

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

	$data = array('TAG' => array());
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
			if ( ! isset( $data['TAG'][$head] ) )
				$data['TAG'][$head] = array();
			$data['TAG'][$head][] = $line;
		}
	} // foreach ( file($fn) as $line )

	foreach ( $data['TAG'] as $head => $line )
	{
		if ( count($line) == 1 )
			$data['TAG'][$head] = $line[0];
	}
	return $data;
}

function save_quadfile( $pfx, &$json, $pretty=false )
{
	if ( empty($json) )
		return;

	// JSON_PRETTY_PRINT
	// JSON_FORCE_OBJECT
	if ( $pretty )
		$json = json_encode($json, JSON_PRETTY_PRINT);
	else
		$json = json_encode($json);

	save_file("$pfx.quad.txt", $json);
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

	// x1 y1  x2 y2  x3 y3  x4 y4
	// 0  1   2  3   4  5   6  7
	( $quad[0] > $quad[4] ) ? $quad[0]++ : $quad[4]++;
	( $quad[1] > $quad[5] ) ? $quad[1]++ : $quad[5]++;
	( $quad[2] > $quad[6] ) ? $quad[2]++ : $quad[6]++;
	( $quad[3] > $quad[7] ) ? $quad[3]++ : $quad[7]++;
	return;
}

function quad_move( &$quad, $opt )
{
	foreach ( $opt as $k => $v )
	{
		switch ( $k )
		{
			case 'hflip':
				for ( $i=0; $i < 8; $i += 2 )
					$quad[$i+0] = -$quad[$i+0];
				break;
			case 'vflip':
				for ( $i=0; $i < 8; $i += 2 )
					$quad[$i+1] = -$quad[$i+1];
				break;
			case 'move':
				list($x,$y) = $v;
				for ( $i=0; $i < 8; $i += 2 )
				{
					$quad[$i+0] += $x;
					$quad[$i+1] += $y;
				} // for ( $i=0; $i < 8; $i += 2 )
				break;
			case 'rotate':
				list($degree,$rx,$ry) = $v;

				// 360 degree = 2 * pi() radian
				// 180 degree = pi() radian
				$radian = $degree / 180 * pi();
				$sin = sin($radian);
				$cos = cos($radian);
				for ( $i=0; $i < 8; $i += 2 )
				{
					$quad[$i+0] += $rx;
					$quad[$i+1] += $ry;
					$x = $quad[$i+0] * $cos - $quad[$i+1] * $sin;
					$y = $quad[$i+0] * $sin + $quad[$i+1] * $cos;
					$quad[$i+0] = $x;
					$quad[$i+1] = $y;
				} // for ( $i=0; $i < 8; $i += 2 )
				break;
		} // switch ( $k )
	} // foreach ( $opt as $k => $v )
	return;
}
//////////////////////////////
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
