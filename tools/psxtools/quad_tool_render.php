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
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";
require "quad.inc";

$gp_pix = array();

function copyquad_errs( &$pix )
{
	$err = "";
	for ( $i=0; $i < 8; $i += 2 ) // A B C D
	{
		// x y
		if ( ! isset( $pix['quad'][$i+0] ) )
			$err .= "copyquad() vector x$i not set\n";
		if ( ! isset( $pix['quad'][$i+1] ) )
			$err .= "copyquad() vector y$i not set\n";

		if ( $pix['quad'][$i+0] < 0 )
			$err .= sprintf("copyquad() vector x$i = %d\n", $pix['quad'][$i+0]);
		if ( $pix['quad'][$i+1] < 0 )
			$err .= sprintf("copyquad() vector y$i = %d\n", $pix['quad'][$i+1]);

		if ( $pix['quad'][$i+0] > $pix['rgba']['w'] )
			$err .= sprintf("copyquad() vector x$i = %d [%d]\n", $pix['quad'][$i+0], $pix['rgba']['w']);
		if ( $pix['quad'][$i+1] > $pix['rgba']['h'] )
			$err .= sprintf("copyquad() vector y$i = %d [%d]\n", $pix['quad'][$i+1], $pix['rgba']['h']);

	} // for ( $i=0; $i < 8; $i += 2 )

	if ( ! empty($err) )
	{
		php_error($err);
		return true;
	}
	return false;
}

function copyquad( &$pix, $byte=1 )
{
	if ( $byte != 1 && $byte != 4 ) // 1=CLUT  4=RGBA  *=invalid
		return;
	if ( empty( $pix['src']['pix'] ) )
		return;
	if ( copyquad_errs($pix) )
		return;
	if ( defined("DRY_RUN") )
		return;

	$qsrc = quad_rebase( $pix['src']['quad'] );
	$qdst = quad_rebase( $pix['quad'] );
	// https://mrl.nyu.edu/~dzorin/ug-graphics/lectures/lecture7/sld024.html
	//
	// 2D transformation
	//   |  0 19 19  0 |    | 20 40 30 10 |
	//   |  0  0 24 24 | => | 40 20 10 30 |
	//   |  1  1  1  1 |    |  1  1  1  1 |
	//
	// vector = magnitude(size) and direction
	//   B = vector from 0,0 to point B(x,y)
	//   cross(B,A) = pseudo-vector from point B(x,y) to point A(x,y)
	//   ...
	//
	// h1 = cross( cross(B,A) , cross(C,D) ) // cross( top    , bottom )
	// h2 = cross( cross(A,D) , cross(B,C) ) // cross( left   , right )
	// h3 = cross( cross(A,C) , cross(B,D) ) // cross( center , center )
	$crx = "cross_product";
	$H1 = $crx( $crx( $qsrc['quad'][1],$qsrc['quad'][0] ) , $crx( $qsrc['quad'][2],$qsrc['quad'][3] ) );
	$H2 = $crx( $crx( $qsrc['quad'][0],$qsrc['quad'][3] ) , $crx( $qsrc['quad'][1],$qsrc['quad'][2] ) );
	$H3 = $crx( $crx( $qsrc['quad'][0],$qsrc['quad'][2] ) , $crx( $qsrc['quad'][1],$qsrc['quad'][3] ) );

	$h1 = $crx( $crx( $qdst['quad'][1],$qdst['quad'][0] ) , $crx( $qdst['quad'][2],$qdst['quad'][3] ) );
	$h2 = $crx( $crx( $qdst['quad'][0],$qdst['quad'][3] ) , $crx( $qdst['quad'][1],$qdst['quad'][2] ) );
	$h3 = $crx( $crx( $qdst['quad'][0],$qdst['quad'][2] ) , $crx( $qdst['quad'][1],$qdst['quad'][3] ) );

	//   | H1x H2x H3x |   | h1x h2x h3x |
	// M | H1y H2y H3y | = | h1y h2y h3y |
	//   | H1z H2z H3z |   | h1z h2z h3z |
	//                MH = h
	//                M  = hH^-1
	$H = array(
		$H1[0],$H2[0],$H3[0],
		$H1[1],$H2[1],$H3[1],
		$H1[2],$H2[2],$H3[2],
	);
	$h = array(
		$h1[0],$h2[0],$h3[0],
		$h1[1],$h2[1],$h3[1],
		$h1[2],$h2[2],$h3[2],
	);
		matrix_dump($H, "H");
		matrix_dump($h, "h");

	$Hinv = matrix_inv3($H);
		if ( $Hinv === 0 )  return;
		matrix_dump($Hinv, "Hinv");

	$M    = matrix_multi33($h, $Hinv);
	$Minv = matrix_inv3($M);
		if ( $Minv === 0 )  return;
		matrix_dump($M   , "M");
		matrix_dump($Minv, "Minv");

	// sum of quad area
	// if $xy is outside quad , sum of $q will be bigger than $qdstsz
	$q1 = triad_area($qdst['quad'][0], $qdst['quad'][1], $qdst['quad'][2]); // ABC
	$q2 = triad_area($qdst['quad'][0], $qdst['quad'][3], $qdst['quad'][2]); // ADC
	$qdstsz = $q1 + $q2;

	//$q1 = triad_area($qsrc['quad'][0], $qsrc['quad'][1], $qsrc['quad'][2]); // ABC
	//$q2 = triad_area($qsrc['quad'][0], $qsrc['quad'][3], $qsrc['quad'][2]); // ADC
	//$qsrcsz = $q1 + $q2;

	for ( $y = $qdst['box'][1]; $y <= $qdst['box'][3]; $y++ )
	{
		for ( $x = $qdst['box'][0]; $x <= $qdst['box'][2]; $x++ )
		{
			$xy = array($x,$y,1);
			$q1 = triad_area($xy, $qdst['quad'][0], $qdst['quad'][1]); // pAB
			$q2 = triad_area($xy, $qdst['quad'][1], $qdst['quad'][2]); // pBC
			$q3 = triad_area($xy, $qdst['quad'][2], $qdst['quad'][3]); // pCD
			$q4 = triad_area($xy, $qdst['quad'][3], $qdst['quad'][0]); // pDA
			if ( ($q1+$q2+$q3+$q4) > $qdstsz )
				continue;

			// (t,r,s) = M^-1 * (x,y,1)
			//  sx,sy  = t/s,r/s
			list($mx,$my,$mz) = matrix_multi31($Minv, array($x,$y,1));
			if ( $mz == 0.0 )
				continue;
			$mx /= $mz;
			$my /= $mz; // $mz = 1

			// Rasterization (consistant rule)
			//   for subpixel from 0.0 to 1.0, but not including 1.0
			//   are considered part of pixel 0
			$sx = (int)$mx;
			$sy = (int)$my;

			// is this check required ???
			//$xy = array($sx,$sy,1);
			//$q1 = triad_area($xy, $qsrc['quad'][0], $qsrc['quad'][1]); // pAB
			//$q2 = triad_area($xy, $qsrc['quad'][1], $qsrc['quad'][2]); // pBC
			//$q3 = triad_area($xy, $qsrc['quad'][2], $qsrc['quad'][3]); // pCD
			//$q4 = triad_area($xy, $qsrc['quad'][3], $qsrc['quad'][0]); // pDA
			//if ( ($q1+$q2+$q3+$q4) > $qsrcsz )
				//continue;

			updatepix($pix, $byte, $qsrc['base'][0]+$sx, $qsrc['base'][1]+$sy, $qdst['base'][0]+$x, $qdst['base'][1]+$y);
		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )

	return;
}
//////////////////////////////
function qtexture( &$pix, $pfx, $tid )
{
	global $gp_pix;
	if ( ! isset($gp_pix[$tid]) )
	{
		$fn = sprintf("%s.%d.", $pfx, $tid);
		$img = load_clutfile($fn);
		if ( $img === 0 )
			return php_error("NOT FOUND %s", $fn);

		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');
		if ( isset( $img['cc'] ) )
		{
			$gp_pix[$tid]['w'] = $img['w'];
			$gp_pix[$tid]['h'] = $img['h'];
			$gp_pix[$tid]['d'] = clut2rgba($img['pal'], $img['pix'], false);
		}
		else
		{
			$gp_pix[$tid]['w'] = $img['w'];
			$gp_pix[$tid]['h'] = $img['h'];
			$gp_pix[$tid]['d'] = $img['pix'];
		}
	} // if ( ! isset($gp_pix[$tid]) )

	printf("== qtexture( %s, %d ) = %x x %x\n", $pfx, $tid, $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	$pix['src']['w'] = $gp_pix[$tid]['w'];
	$pix['src']['h'] = $gp_pix[$tid]['h'];
	$pix['src']['pix'] = &$gp_pix[$tid]['d'];
	$pix['src']['pal'] = "";
	return;
}

function qrender( &$frame, $pfx, $id )
{
	printf("== qrender( %s , %x )\n", $pfx, $id);
	//print_r($frame);

	// ERROR : computer run out of memory
	// required CANV_S is too large for backgrounds
	//   auto canvas size detection
	//   auto move origin 0,0 from middle-center to top-left
	//   auto trim is DISABLED
	$CANV_S = 0;
	$is_mid = false;
	foreach ( $frame as $pk => $pv )
	{
		if ( ! isset($pv['DstQuad']) )
			continue;

		// detect origin and canvas size
		foreach ( $pv['DstQuad'] as $dst )
		{
			$b = abs($dst);
			if ( $b > $CANV_S )  $CANV_S = $b;
			if ( $dst < 0 )
				$is_mid = true;
		}
	} // foreach ( $fv as $pk => $pv )

	$ceil   = ( $is_mid ) ? int_ceil($CANV_S*2, 16) : int_ceil($CANV_S, 16);
	$origin = ( $is_mid ) ? $ceil / 2 : 0;

	$pix = COPYPIX_DEF($ceil,$ceil);
	$pix['alpha'] = "alpha_normal";

	foreach ( $frame as $pk => $pv )
	{
		if ( ! isset($pv['DstQuad']) )
			continue;

		// skip non-texture data + custom blending parts
		if ( ! isset($pv['TexID']) || $pv['TexID'] < 0 )
			continue;
		if ( isset($pv['Blend']) )
			continue;

		$pix['quad'] = array(
			$pv['DstQuad'][0]+$origin , $pv['DstQuad'][1]+$origin ,
			$pv['DstQuad'][2]+$origin , $pv['DstQuad'][3]+$origin ,
			$pv['DstQuad'][4]+$origin , $pv['DstQuad'][5]+$origin ,
			$pv['DstQuad'][6]+$origin , $pv['DstQuad'][7]+$origin ,
		);

		$pix['src']['quad'] = array(
			$pv['SrcQuad'][0] , $pv['SrcQuad'][1] ,
			$pv['SrcQuad'][2] , $pv['SrcQuad'][3] ,
			$pv['SrcQuad'][4] , $pv['SrcQuad'][5] ,
			$pv['SrcQuad'][6] , $pv['SrcQuad'][7] ,
		);

		quad_dump($pix['src']['quad'], 'src quad');
		quad_dump($pix['quad']       , 'dst quad');

		qtexture($pix, $pfx, $pv['TexID']);
		copyquad($pix, 4);
	} // foreach ( $fv as $pk => $pv )

	$fn = sprintf("%s/%04d", $pfx, $id);
	savepix($fn, $pix, false);
	return;
}

function quad( $fname )
{
	// for *.quad only
	if ( stripos($fname, '.quad') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$file = json_decode($file, true);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	global $gp_pix;
	$gp_pix = array();

	foreach ( $file['Frame'] as $fk => $fv )
	{
		if ( empty($fv) )
			continue;
		qrender($fv, $pfx, $fk);
	} // foreach ( $file['Frame'] as $fk => $fv )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	quad( $argv[$i] );
