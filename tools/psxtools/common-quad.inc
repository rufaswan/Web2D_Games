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
// 3D texture quad-rendering + transformation related
// product = factor * factor
//             | x 0 0 |
// | x y z | = | 0 y 0 |
//             | 0 0 z |
// cross = 0 parallel      , 1 perpendicular
// dot   = 0 perpendicular , 1 parallel
//   perpendicular = 90 degree
//   parallel      = 0 or 180 degree (sin = 0)

function dot_product( $base, $V1, $V2 )
{
	// The direct, scalar, or dot product of two vectors
	// dot product is normalized from $base as 0,0
	$V1[0] -= $base[0];
	$V1[1] -= $base[1];
	$V1[2] -= $base[2];
	$V2[0] -= $base[0];
	$V2[1] -= $base[1];
	$V2[2] -= $base[2];

	// dot(a,a) = 1
	// s = axbx + ayby
	// s = axbx + ayby + azbz
	$dot = $V1[0]*$V2[0] + $V1[1]*$V2[1] + $V1[2]*$V2[2];
	return $dot;
}

function cross_product( $vb, $vc )
{
	// The skew, vector, or cross product of two vectors

	// crx(a,a) = 0
	// | ax bx cx |
	// | ay by cy |
	// | az bz cz |
	//   ax = bycz - cybz
	//   ay = bzcx - czbx
	//   az = bxcy - cxby
	$ax = ($vb[1] * $vc[2]) - ($vc[1] * $vb[2]);
	$ay = ($vb[2] * $vc[0]) - ($vc[2] * $vb[0]);
	$az = ($vb[0] * $vc[1]) - ($vc[0] * $vb[1]);
	return array($ax, $ay, $az);
}

function triad_area( $V1, $V2, $V3 )
{
	// https://www.mathopenref.com/coordtrianglearea.html
	// 2r = ax(by-cy) + bx(cy-ay) + cx(ay-by)
	$r1 = $V1[0] * ($V2[1] - $V3[1]);
	$r2 = $V2[0] * ($V3[1] - $V1[1]);
	$r3 = $V3[0] * ($V1[1] - $V2[1]);
	$r = 0.5 * ($r1 + $r2 + $r3);
	return abs($r);
}
//////////////////////////////
function vector_dump( $V, $name )
{
	if ( defined("NO_TRACE") )
		return;
	trace("$name ");
	trace(" xyz (%4d , %4d , %4.2f)", $V[0], $V[1], $V[2]);

	// ERROR : $V[2] divide by zero
	if ( $V[2] == 0.0 )
	{
		$V[2] = 1 / $V[2];
		trace("  xy (%4d , %4d)", $V[0]*$V[2], $V[1]*$V[2]);
	}
	trace("\n");
	return;
}

function vector_box( $vector )
{
	$x1 = PHP_INT_MAX;
	$y1 = PHP_INT_MAX;
	$x2 = -PHP_INT_MAX;
	$y2 = -PHP_INT_MAX;
	for ( $i=0; $i < 4; $i++ ) // A B C D
	{
		if ( $x1 > $vector[$i][0] )  $x1 = (int)floor($vector[$i][0]);
		if ( $y1 > $vector[$i][1] )  $y1 = (int)floor($vector[$i][1]);
		if ( $x2 < $vector[$i][0] )  $x2 = (int)ceil ($vector[$i][0]);
		if ( $y2 < $vector[$i][1] )  $y2 = (int)ceil ($vector[$i][1]);
	} // for ( $i=0; $i < 4; $i++ )
	return array($x1, $y1, $x2, $y2);
}
//////////////////////////////
function quad_rebase( $V4 )
{
	// rebase $V4 to 0,0
	$base = array((int)$V4[0][0] , (int)$V4[0][1]);
	for ( $i=0; $i < 4; $i++ ) // A B C D
	{
		$V4[$i][0] -= $base[0];
		$V4[$i][1] -= $base[1];
	}

	// get box size
	$x1 = PHP_INT_MAX;
	$y1 = PHP_INT_MAX;
	$x2 = -PHP_INT_MAX;
	$y2 = -PHP_INT_MAX;
	for ( $i=0; $i < 4; $i++ ) // A B C D
	{
		if ( $x1 > $V4[$i][0] )  $x1 = (int)floor($V4[$i][0]);
		if ( $y1 > $V4[$i][1] )  $y1 = (int)floor($V4[$i][1]);
		if ( $x2 < $V4[$i][0] )  $x2 = (int)ceil ($V4[$i][0]);
		if ( $y2 < $V4[$i][1] )  $y2 = (int)ceil ($V4[$i][1]);
	}

	$quad = array(
		'quad' => $V4,
		'base' => $base,
		'box'  => array($x1, $y1, $x2, $y2),
	);
	trace("== quad_rebase() = %.2f , %.2f\n", $base[0], $base[1]);
	trace("   box x = %.2f - %.2f  y = %.2f - %.2f\n", $x1, $x2, $y1, $y2);
	return $quad;
}

function quad_dump( $V4, $name )
{
	if ( defined("NO_TRACE") )
		return;
	// winding order (x-hand-coordinate system)
	//   1243 = 1 2 =  left-hand / clockwise (CW)
	//          4 3
	//   1423 = 1 4 = right-hand / counter clockwise (CCW)
	//          2 3
	// NOTE : it doesn't matter
	$minx = PHP_INT_MAX;
	$miny = PHP_INT_MAX;
	$maxx = -PHP_INT_MAX;
	$maxy = -PHP_INT_MAX;
	for ( $i=0; $i < 4; $i++ )
	{
		if ( $V4[$i][0] < $minx )  $minx = $V4[$i][0];
		if ( $V4[$i][0] > $maxx )  $maxx = $V4[$i][0];
		if ( $V4[$i][1] < $miny )  $miny = $V4[$i][1];
		if ( $V4[$i][1] > $maxy )  $maxy = $V4[$i][1];
	}

	trace("quad( %s )\n", $name);
	trace("  %10.2f,%10.2f\n", $V4[0][0], $V4[0][1]);
	trace("  %10.2f,%10.2f\n", $V4[1][0], $V4[1][1]);
	trace("  %10.2f,%10.2f\n", $V4[2][0], $V4[2][1]);
	trace("  %10.2f,%10.2f\n", $V4[3][0], $V4[3][1]);
	trace("  w %10.2f - %10.2f [%10.2f]\n", $minx, $maxx, $maxx-$minx+1);
	trace("  h %10.2f - %10.2f [%10.2f]\n", $miny, $maxy, $maxy-$miny+1);
	return;
}

function matrix_dump( $M , $name )
{
	if ( defined("NO_TRACE") )
		return;
	trace("matrix( %s )\n", $name);
	trace("  | %10.2f %10.2f %10.2f |\n", $M[0], $M[1], $M[2]);
	trace("  | %10.2f %10.2f %10.2f |\n", $M[3], $M[4], $M[5]);
	trace("  | %10.2f %10.2f %10.2f |\n", $M[6], $M[7], $M[8]);
	return;
}

function matrix_multi13( $V, $M )
{
	//            | a b c |
	// | 0 1 2 |  | d e f | = | 0a+1d+2g  0b+1e+2h  0c+1f+2i |
	//            | g h i |
	$VM = array(
		$V[0]*$M[0] + $V[1]*$M[3] + $V[2]*$M[6] ,
		$V[0]*$M[1] + $V[1]*$M[4] + $V[2]*$M[7] ,
		$V[0]*$M[2] + $V[1]*$M[5] + $V[2]*$M[8] ,
	);
	return $VM;
}

function matrix_multi31( $M, $V )
{
	// | 0 1 2 |  | a |   | 0a+1b+2c |
	// | 3 4 5 |  | b | = | 3a+4b+5c |
	// | 6 7 8 |  | c |   | 6a+7b+8c |
	$MV = array(
		$M[0]*$V[0] + $M[1]*$V[1] + $M[2]*$V[2] ,
		$M[3]*$V[0] + $M[4]*$V[1] + $M[5]*$V[2] ,
		$M[6]*$V[0] + $M[7]*$V[1] + $M[8]*$V[2] ,
	);
	return $MV;
}

function matrix_multi33( $M1, $M2 )
{
	//            | 1 0 0 |
	// M * Minv = | 0 1 0 | or identify matrix
	//            | 0 0 1 |
	$M = array(
		$M1[0]*$M2[0] + $M1[1]*$M2[3] + $M1[2]*$M2[6],
		$M1[0]*$M2[1] + $M1[1]*$M2[4] + $M1[2]*$M2[7],
		$M1[0]*$M2[2] + $M1[1]*$M2[5] + $M1[2]*$M2[8],

		$M1[3]*$M2[0] + $M1[4]*$M2[3] + $M1[5]*$M2[6],
		$M1[3]*$M2[1] + $M1[4]*$M2[4] + $M1[5]*$M2[7],
		$M1[3]*$M2[2] + $M1[4]*$M2[5] + $M1[5]*$M2[8],

		$M1[6]*$M2[0] + $M1[7]*$M2[3] + $M1[8]*$M2[6],
		$M1[6]*$M2[1] + $M1[7]*$M2[4] + $M1[8]*$M2[7],
		$M1[6]*$M2[2] + $M1[7]*$M2[5] + $M1[8]*$M2[8],
	);
	return $M;
}

function matrix_inverse( $M )
{
	// https://www.mathsisfun.com/algebra/matrix-inverse-minors-cofactors-adjugate.html
	// inverse a matrix
	// s1 matrix of minors | - a - |
	//                     | 1 - 3 | = determinant
	//                     | 4 - 2 |
	//    | 0 1 2 |    | 4*8-5*7  3*8-5*6  3*7-4*6 |
	//    | 3 4 5 | => | 1*8-2*7  0*8-2*6  0*7-1*6 |
	//    | 6 7 8 |    | 1*5-2*4  0*5-2*3  0*4-1*3 |
	// s2 matrix of cofactors
	//    | + - + |
	//    | - + - |
	//    | + - + |
	// s3 adjugate
	//    | 0 1 2 |    | 0 3 6 |
	//    | 3 4 5 | => | 1 4 7 |
	//    | 6 7 8 |    | 2 5 8 |
	//
	// SIMPLIFIED s1 to s3
	//   | 0 1 2 |    | 4*8-5*7  2*7-1*8  1*5-2*4 |
	//   | 3 4 5 | => | 5*6-3*8  0*8-2*6  2*3-0*5 |
	//   | 6 7 8 |    | 3*7-4*6  1*6-0*7  0*4-1*3 |
	$s3 = array(
		$M[4]*$M[8]-$M[5]*$M[7] , $M[2]*$M[7]-$M[1]*$M[8] , $M[1]*$M[5]-$M[2]*$M[4] ,
		$M[5]*$M[6]-$M[3]*$M[8] , $M[0]*$M[8]-$M[2]*$M[6] , $M[2]*$M[3]-$M[0]*$M[5] ,
		$M[3]*$M[7]-$M[4]*$M[6] , $M[1]*$M[6]-$M[0]*$M[7] , $M[0]*$M[4]-$M[1]*$M[3] ,
	);

	// s4 multiply by 1/det
	//    https://www.mathsisfun.com/algebra/matrix-determinant.html
	//    if $det is zero , the matrix cannot be invert
	$det = $M[0]*$s3[0] + $M[1]*$s3[3] + $M[2]*$s3[6];
	if ( $det == 0 )
	{
		php_warning("matrix_inverse() determinant is zero");
		return 0;
	}

	$det = 1 / $det;
	for ( $i=0; $i < 9; $i++ )
		$s3[$i] = $s3[$i] * $det;
	//     |  3  0  2 |         |  0.2  0.2  0   |
	// A = |  2  0 -2 |  A^-1 = | -0.2  0.3  1.0 |
	//     |  0  1  1 |         |  0.2 -0.3  0   |
	return $s3;
}

function copyquad_errs( &$pix )
{
	$err = "";
	for ( $i=0; $i < 4; $i++ ) // A B C D
	{
		// x y
		if ( ! isset( $pix['vector'][$i][0] ) )
			$err .= "copyquad() vector x$i not set\n";
		if ( ! isset( $pix['vector'][$i][1] ) )
			$err .= "copyquad() vector y$i not set\n";

		if ( $pix['vector'][$i][0] < 0 )
			$err .= sprintf("copyquad() vector x$i = %d\n", $pix['vector'][$i][0]);
		if ( $pix['vector'][$i][1] < 0 )
			$err .= sprintf("copyquad() vector y$i = %d\n", $pix['vector'][$i][1]);

		if ( $pix['vector'][$i][0] > $pix['rgba']['w'] )
			$err .= sprintf("copyquad() vector x$i = %d [%d]\n", $pix['vector'][$i][0], $pix['rgba']['w']);
		if ( $pix['vector'][$i][1] > $pix['rgba']['h'] )
			$err .= sprintf("copyquad() vector y$i = %d [%d]\n", $pix['vector'][$i][1], $pix['rgba']['h']);

	} // for ( $i=0; $i < 4; $i++ )

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

	$qsrc = quad_rebase( $pix['src']['vector'] );
	$qdes = quad_rebase( $pix['vector'] );
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

	$h1 = $crx( $crx( $qdes['quad'][1],$qdes['quad'][0] ) , $crx( $qdes['quad'][2],$qdes['quad'][3] ) );
	$h2 = $crx( $crx( $qdes['quad'][0],$qdes['quad'][3] ) , $crx( $qdes['quad'][1],$qdes['quad'][2] ) );
	$h3 = $crx( $crx( $qdes['quad'][0],$qdes['quad'][2] ) , $crx( $qdes['quad'][1],$qdes['quad'][3] ) );

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

	$Hinv = matrix_inverse($H);
		if ( $Hinv === 0 )  return;
		matrix_dump($Hinv, "Hinv");

	$M    = matrix_multi33($h, $Hinv);
	$Minv = matrix_inverse($M);
		if ( $Minv === 0 )  return;
		matrix_dump($M   , "M");
		matrix_dump($Minv, "Minv");

	// sum of quad area
	// if $xy is outside quad , sum of $q will be bigger than $qsz
	$q1 = triad_area($qdes['quad'][0], $qdes['quad'][1], $qdes['quad'][2]); // ABC
	$q2 = triad_area($qdes['quad'][0], $qdes['quad'][3], $qdes['quad'][2]); // ADC
	$qdessz = $q1 + $q2;

	//$q1 = triad_area($qsrc['quad'][0], $qsrc['quad'][1], $qsrc['quad'][2]); // ABC
	//$q2 = triad_area($qsrc['quad'][0], $qsrc['quad'][3], $qsrc['quad'][2]); // ADC
	//$qsrcsz = $q1 + $q2;

	for ( $y = $qdes['box'][1]; $y <= $qdes['box'][3]; $y++ )
	{
		for ( $x = $qdes['box'][0]; $x <= $qdes['box'][2]; $x++ )
		{
			$xy = array($x,$y,1);
			$q1 = triad_area($xy, $qdes['quad'][0], $qdes['quad'][1]); // pAB
			$q2 = triad_area($xy, $qdes['quad'][1], $qdes['quad'][2]); // pBC
			$q3 = triad_area($xy, $qdes['quad'][2], $qdes['quad'][3]); // pCD
			$q4 = triad_area($xy, $qdes['quad'][3], $qdes['quad'][0]); // pDA
			if ( ($q1+$q2+$q3+$q4) > $qdessz )
				continue;

			// (t,r,s) = M^-1 * (x,y,1)
			//  sx,sy  = t/s,r/s
			$t = matrix_multi31($Minv, array($x,$y,1));

			// ERROR : $t[2] divide by zero
			if ( $t[2] == 0.0 )
				continue;
			// Rasterization (consistant rule)
			//   for subpixel from 0.0 to 1.0, but not including 1.0
			//   are considered part of pixel 0
			$t[2] = 1 / $t[2];
			$sx = (int)($t[0] * $t[2]);
			$sy = (int)($t[1] * $t[2]);

			// is this check required ???
			//$xy = array($sx,$sy,1);
			//$q1 = triad_area($xy, $qsrc['quad'][0], $qsrc['quad'][1]); // pAB
			//$q2 = triad_area($xy, $qsrc['quad'][1], $qsrc['quad'][2]); // pBC
			//$q3 = triad_area($xy, $qsrc['quad'][2], $qsrc['quad'][3]); // pCD
			//$q4 = triad_area($xy, $qsrc['quad'][3], $qsrc['quad'][0]); // pDA
			//if ( ($q1+$q2+$q3+$q4) > $qsrcsz )
				//continue;

			updatepix($pix, $byte, $qsrc['base'][0]+$sx, $qsrc['base'][1]+$sy, $qdes['base'][0]+$x, $qdes['base'][1]+$y);
		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )
	return;
}
//////////////////////////////
function cmp_quadxy( &$data, $id1, $id2 )
{
	if ( $data[$id1] != $data[$id2] )
		php_notice("cmp %d [%7.2f] != %d [%7.2f]", $id1, $data[$id1], $id2, $data[$id2]);
	return;
}

function hex_rotate( $x1, $y1, $x2, $y2 )
{
	$dx = $x1 - $x2;
	$dy = $y1 - $y2;
	$rot = atan( $dx/$dy ) * 0x80 / pi();
	if ( $dy < 0 )  $rot += 0x80;
	else
	if ( $dx < 0 )  $rot += 0x100;

	// 20  0  e0
	//    \|/
	// 40 -*- c0
	//    /|\
	// 60 80  a0
	// $rot from atan() is reverse-clockwise
	$rot = (int)(0x100 - $rot) & BIT8;
	return $rot;
}
//////////////////////////////
