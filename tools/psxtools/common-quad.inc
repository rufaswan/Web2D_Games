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
//
// TESTING : ztest_quad.php
//
// product = factor * factor
//             | x 0 0 |
// | x y z | = | 0 y 0 |
//             | 0 0 z |
// cross = 0 parallel      , 1 perpendicular
// dot   = 0 perpendicular , 1 parallel
//   perpendicular = 90 degree
//   parallel      = 0 or 180 degree (sin = 0)
//
//   dot(x,y) = |x||y| cos Q
// cross(x,y) = |x||y| sin Q

function cross_product()
{
	$args = func_get_args();
	switch ( count($args) )
	{
		case 4: // x1,y1 , x2,y2
			$bx = $args[0];  $by = $args[1];  $bz = 1;
			$cx = $args[2];  $cy = $args[3];  $cz = 1;
			break;
		case 2: // [x1,y1,1] , [x2,y2,1]
			list($bx,$by,$bz) = $args[0];
			list($cx,$cy,$cz) = $args[1];
			break;
		default:
			return -1;
	} // switch ( count($args) )

	// | ax bx cx |
	// | ay by cy |
	// | az bz cz |
	$ax = ($by * $cz) - ($cy * $bz);
	$ay = ($bz * $cx) - ($cz * $bx);
	$az = ($bx * $cy) - ($cx * $by);
	return array($ax, $ay, $az);
}

function cross_product_2d()
{
	$args = func_get_args();
	switch ( count($args) )
	{
		case 4: // x1,y1 , x2,y2
			$crx = cross_product($args[0],$args[1],$args[2],$args[3]);
			break;
		case 2: // [x1,y1,1] , [x2,y2,1]
			$crx = cross_product($args[0],$args[1]);
			break;
		default:
			return -1;
	} // switch ( count($args) )

	list($x,$y,$z) = $crx;
	if ( $z == 0 )
		return -1;

	// convert x,y,z (3d) to x,y (2d)
	$x /= $z;
	$y /= $z;
	//$z /= $z; // $z == 1
	return array($x, $y);
}

function triad_area()
{
	$args = func_get_args();
	switch ( count($args) )
	{
		case 6: // x1,y1 , x2,y2 , x3,y3
			$ax = $args[0];  $ay = $args[1];
			$bx = $args[2];  $by = $args[3];
			$cx = $args[4];  $cy = $args[5];
			break;
		case 3: // [x1,y1] , [x2,y2] , [x3,y3]
			list($ax,$ay) = $args[0];
			list($bx,$by) = $args[1];
			list($cx,$cy) = $args[2];
			break;
		default:
			return -1;
	} // switch ( count($args) )

	// https://www.mathopenref.com/coordtrianglearea.html
	// | ax bx cx |
	// | ay by cy |
	// 2r = ax(by-cy) + bx(cy-ay) + cx(ay-by)
	$r1 = $ax * ($by - $cy);
	$r2 = $bx * ($cy - $ay);
	$r3 = $cx * ($ay - $by);
	$r  = 0.5 * ($r1 + $r2 + $r3);
	return abs($r);
}
//////////////////////////////
function matrix_dump( $m , $name )
{
	if ( defined("NO_TRACE") )
		return;
	$c = (int)sqrt( count($m) );
	trace("matrix %dx%d ( %s )\n", $c, $c, $name);

	$p = 0;
	for ( $y=0; $y < $c; $y++ )
	{
		trace("  | ");
		for ( $x=0; $x < $c; $x++ )
		{
			trace("%10.2f ", $m[$p]);
				$p++;
		} // for ( $x=0; $x < $c; $x++ )
		trace("|\n");
	} // for ( $y=0; $y < $c; $y++ )
	return;
}

// multiply series
function matrix_multi12( $v, $m )
{
	//          | a b |
	// | 0 1 |  | c d | = | 0a+1c  0b+1d |
	$vm = array(
		$v[0]*$m[0] + $v[1]*$m[2] ,
		$v[0]*$m[1] + $v[1]*$m[3] ,
	);
	return $vm;
}

function matrix_multi21( $m, $v )
{
	// | 0 1 |  | a |   | 0a+1b |
	// | 2 3 |  | b | = | 2a+3b |
	$mv = array(
		$m[0]*$v[0] + $m[1]*$v[1] ,
		$m[2]*$v[0] + $m[3]*$v[1] ,
	);
	return $mv;
}

function matrix_multi22( $m1, $m2 )
{
	//            | 1 0 |
	// M * Minv = | 0 1 | or identify matrix
	$m = array(
		$m1[0]*$m2[0] + $m1[1]*$m2[2],
		$m1[0]*$m2[1] + $m1[1]*$m2[3],

		$m1[2]*$m2[0] + $m1[3]*$m2[2],
		$m1[2]*$m2[1] + $m1[3]*$m2[3],
	);
	return $m;
}

function matrix_multi13( $v, $m )
{
	//            | a b c |
	// | 0 1 2 |  | d e f | = | 0a+1d+2g  0b+1e+2h  0c+1f+2i |
	//            | g h i |
	$vm = array(
		$v[0]*$m[0] + $v[1]*$m[3] + $v[2]*$m[6] ,
		$v[0]*$m[1] + $v[1]*$m[4] + $v[2]*$m[7] ,
		$v[0]*$m[2] + $v[1]*$m[5] + $v[2]*$m[8] ,
	);
	return $vm;
}

function matrix_multi31( $m, $v )
{
	// | 0 1 2 |  | a |   | 0a+1b+2c |
	// | 3 4 5 |  | b | = | 3a+4b+5c |
	// | 6 7 8 |  | c |   | 6a+7b+8c |
	$mv = array(
		$m[0]*$v[0] + $m[1]*$v[1] + $m[2]*$v[2] ,
		$m[3]*$v[0] + $m[4]*$v[1] + $m[5]*$v[2] ,
		$m[6]*$v[0] + $m[7]*$v[1] + $m[8]*$v[2] ,
	);
	return $mv;
}

function matrix_multi33( $m1, $m2 )
{
	//            | 1 0 0 |
	// M * Minv = | 0 1 0 | or identify matrix
	//            | 0 0 1 |
	$m = array(
		$m1[0]*$m2[0] + $m1[1]*$m2[3] + $m1[2]*$m2[6],
		$m1[0]*$m2[1] + $m1[1]*$m2[4] + $m1[2]*$m2[7],
		$m1[0]*$m2[2] + $m1[1]*$m2[5] + $m1[2]*$m2[8],

		$m1[3]*$m2[0] + $m1[4]*$m2[3] + $m1[5]*$m2[6],
		$m1[3]*$m2[1] + $m1[4]*$m2[4] + $m1[5]*$m2[7],
		$m1[3]*$m2[2] + $m1[4]*$m2[5] + $m1[5]*$m2[8],

		$m1[6]*$m2[0] + $m1[7]*$m2[3] + $m1[8]*$m2[6],
		$m1[6]*$m2[1] + $m1[7]*$m2[4] + $m1[8]*$m2[7],
		$m1[6]*$m2[2] + $m1[7]*$m2[5] + $m1[8]*$m2[8],
	);
	return $m;
}

function matrix_multi14( $v, $m )
{
	//              | a b c d |
	// | 0 1 2 3 |  | e f g h | = | 0a+1e+2i+3m  0b+1f+2j+3n  0c+1g+2k+3o  0d+1h+2l+3p |
	//              | i j k l |
	//              | m n o p |
	$vm = array(
		$v[0]*$m[0] + $v[1]*$m[4] + $v[2]*$m[ 8] + $v[3]*$m[12] ,
		$v[0]*$m[1] + $v[1]*$m[5] + $v[2]*$m[ 9] + $v[3]*$m[13] ,
		$v[0]*$m[2] + $v[1]*$m[6] + $v[2]*$m[10] + $v[3]*$m[14] ,
		$v[0]*$m[3] + $v[1]*$m[7] + $v[2]*$m[11] + $v[3]*$m[15] ,
	);
	return $vm;
}

function matrix_multi41( $m, $v )
{
	// | 0 1 2 3 |  | a |   | 0a+1b+2c+3d |
	// | 4 5 6 7 |  | b | = | 4a+5b+6c+7d |
	// | 8 9 A B |  | c |   | 8a+9b+Ac+Bd |
	// | C D E F |  | d |   | Ca+Db+Ec+Fd |
	$mv = array(
		$m[ 0]*$v[0] + $m[ 1]*$v[1] + $m[ 2]*$v[2] + $m[ 3]*$v[3] ,
		$m[ 4]*$v[0] + $m[ 5]*$v[1] + $m[ 6]*$v[2] + $m[ 7]*$v[3] ,
		$m[ 8]*$v[0] + $m[ 9]*$v[1] + $m[10]*$v[2] + $m[11]*$v[3] ,
		$m[12]*$v[0] + $m[13]*$v[1] + $m[14]*$v[2] + $m[15]*$v[3] ,
	);
	return $mv;
}

function matrix_multi44( $m1, $m2 )
{
	//            | 1 0 0 0 |
	// M * Minv = | 0 1 0 0 | or identify matrix
	//            | 0 0 1 0 |
	//            | 0 0 0 1 |
	$m = array(
		$m1[ 0]*$m2[0] + $m1[ 1]*$m2[4] + $m1[ 2]*$m2[ 8] + $m1[ 3]*$m2[12] ,
		$m1[ 0]*$m2[1] + $m1[ 1]*$m2[5] + $m1[ 2]*$m2[ 9] + $m1[ 3]*$m2[13] ,
		$m1[ 0]*$m2[2] + $m1[ 1]*$m2[6] + $m1[ 2]*$m2[10] + $m1[ 3]*$m2[14] ,
		$m1[ 0]*$m2[3] + $m1[ 1]*$m2[7] + $m1[ 2]*$m2[11] + $m1[ 3]*$m2[15] ,

		$m1[ 4]*$m2[0] + $m1[ 5]*$m2[4] + $m1[ 6]*$m2[ 8] + $m1[ 7]*$m2[12] ,
		$m1[ 4]*$m2[1] + $m1[ 5]*$m2[5] + $m1[ 6]*$m2[ 9] + $m1[ 7]*$m2[13] ,
		$m1[ 4]*$m2[2] + $m1[ 5]*$m2[6] + $m1[ 6]*$m2[10] + $m1[ 7]*$m2[14] ,
		$m1[ 4]*$m2[3] + $m1[ 5]*$m2[7] + $m1[ 6]*$m2[11] + $m1[ 7]*$m2[15] ,

		$m1[ 8]*$m2[0] + $m1[ 9]*$m2[4] + $m1[10]*$m2[ 8] + $m1[11]*$m2[12] ,
		$m1[ 8]*$m2[1] + $m1[ 9]*$m2[5] + $m1[10]*$m2[ 9] + $m1[11]*$m2[13] ,
		$m1[ 8]*$m2[2] + $m1[ 9]*$m2[6] + $m1[10]*$m2[10] + $m1[11]*$m2[14] ,
		$m1[ 8]*$m2[3] + $m1[ 9]*$m2[7] + $m1[10]*$m2[11] + $m1[11]*$m2[15] ,

		$m1[12]*$m2[0] + $m1[13]*$m2[4] + $m1[14]*$m2[ 8] + $m1[15]*$m2[12] ,
		$m1[12]*$m2[1] + $m1[13]*$m2[5] + $m1[14]*$m2[ 9] + $m1[15]*$m2[13] ,
		$m1[12]*$m2[2] + $m1[13]*$m2[6] + $m1[14]*$m2[10] + $m1[15]*$m2[14] ,
		$m1[12]*$m2[3] + $m1[13]*$m2[7] + $m1[14]*$m2[11] + $m1[15]*$m2[15] ,
	);
	return $m;
}

// determinant series
function matrix_det2( $m )
{
	// | 0 1 |
	// | 2 3 |
	$d = $m[0]*$m[3] - $m[1]*$m[2];
	return $d;
}

function matrix_det3( $m )
{
	// | 0 1 2 |
	// | 3 4 5 |
	// | 6 7 8 |
	$d0 =  $m[0] * matrix_det2( array($m[4],$m[5] , $m[7],$m[8]) );
	$d1 = -$m[1] * matrix_det2( array($m[3],$m[5] , $m[6],$m[8]) );
	$d2 =  $m[2] * matrix_det2( array($m[3],$m[4] , $m[6],$m[7]) );
	$d = $d0 + $d1 + $d2;
	return $d;
}

function matrix_det4( $m )
{
	// | 0 1 2 3 |
	// | 4 5 6 7 |
	// | 8 9 a b |
	// | c d e f |
	$d0 =  $m[0] * matrix_det3( array($m[5],$m[6],$m[7] , $m[9],$m[10],$m[11] , $m[13],$m[14],$m[15]) );
	$d1 = -$m[1] * matrix_det3( array($m[4],$m[6],$m[7] , $m[8],$m[10],$m[11] , $m[12],$m[14],$m[15]) );
	$d2 =  $m[2] * matrix_det3( array($m[4],$m[5],$m[7] , $m[8],$m[ 9],$m[11] , $m[12],$m[13],$m[15]) );
	$d3 = -$m[3] * matrix_det3( array($m[4],$m[5],$m[6] , $m[8],$m[ 9],$m[10] , $m[12],$m[13],$m[14]) );
	$d = $d0 + $d1 + $d2 + $d3;
	return $d;
}

// inverse series
// https://en.wikipedia.org/wiki/Invertible_matrix#Analytic_solution
function matrix_inv2( $m )
{
	//            |  3 -1 |
	// 1/det(m) * | -2  0 |
	$d = matrix_det2($m);
	if ( $d == 0 )
		return 0;
	$dinv = 1 / $d;
	$mco = array(
		 $m[3] , -$m[1] ,
		-$m[2] ,  $m[0] ,
	);
	for ( $i=0; $i < 4; $i++ )
		$mco[$i] *= $dinv;
	return $mco;
}

function matrix_inv3( $m )
{
	// https://www.mathsisfun.com/algebra/matrix-inverse-minors-cofactors-adjugate.html
	// matrix of minors
	// | 0 1 2 |
	// | 3 4 5 |
	// | 6 7 8 |
	$mdet = array(
		matrix_det2( array($m[4],$m[5] , $m[7],$m[8]) ) ,
		matrix_det2( array($m[3],$m[5] , $m[6],$m[8]) ) ,
		matrix_det2( array($m[3],$m[4] , $m[6],$m[7]) ) ,

		matrix_det2( array($m[1],$m[2] , $m[7],$m[8]) ) ,
		matrix_det2( array($m[0],$m[2] , $m[6],$m[8]) ) ,
		matrix_det2( array($m[0],$m[1] , $m[6],$m[7]) ) ,

		matrix_det2( array($m[1],$m[2] , $m[4],$m[5]) ) ,
		matrix_det2( array($m[0],$m[2] , $m[3],$m[5]) ) ,
		matrix_det2( array($m[0],$m[1] , $m[3],$m[4]) ) ,
	);

	// matrix of cofactors + adjugate
	$mco = array(
		 $mdet[0] , -$mdet[3] ,  $mdet[6] ,
		-$mdet[1] ,  $mdet[4] , -$mdet[7] ,
		 $mdet[2] , -$mdet[5] ,  $mdet[8] ,
	);

	//            |  c0 -c3  c6 |
	// 1/det(M) * | -c1  c4 -c7 |
	//            |  c2 -c5  c8 |
	$d = $m[0]*$mco[0] + $m[1]*$mco[3] + $m[2]*$mco[6];
	if ( $d == 0 )
		return 0;
	$dinv = 1 / $d;

	for ( $i=0; $i < 9; $i++ )
		$mco[$i] *= $dinv;
	return $mco;
}

function matrix_inv4( $m )
{
	// matrix of minors
	// | 0 1 2 3 |
	// | 4 5 6 7 |
	// | 8 9 a b |
	// | c d e f |
	$mdet = array(
		matrix_det3( array($m[ 5],$m[ 6],$m[ 7] , $m[ 9],$m[10],$m[11] , $m[13],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 4],$m[ 6],$m[ 7] , $m[ 8],$m[10],$m[11] , $m[12],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 4],$m[ 5],$m[ 7] , $m[ 8],$m[ 9],$m[11] , $m[12],$m[13],$m[15]) ) ,
		matrix_det3( array($m[ 4],$m[ 5],$m[ 6] , $m[ 8],$m[ 9],$m[10] , $m[12],$m[13],$m[14]) ) ,

		matrix_det3( array($m[ 1],$m[ 2],$m[ 3] , $m[ 9],$m[10],$m[11] , $m[13],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 2],$m[ 3] , $m[ 8],$m[10],$m[11] , $m[12],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 3] , $m[ 8],$m[ 9],$m[11] , $m[12],$m[13],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 2] , $m[ 8],$m[ 9],$m[10] , $m[12],$m[13],$m[14]) ) ,

		matrix_det3( array($m[ 1],$m[ 2],$m[ 3] , $m[ 5],$m[ 6],$m[ 7] , $m[13],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 2],$m[ 3] , $m[ 4],$m[ 6],$m[ 7] , $m[12],$m[14],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 3] , $m[ 4],$m[ 5],$m[ 7] , $m[12],$m[13],$m[15]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 2] , $m[ 4],$m[ 5],$m[ 6] , $m[12],$m[13],$m[14]) ) ,

		matrix_det3( array($m[ 1],$m[ 2],$m[ 3] , $m[ 5],$m[ 6],$m[ 7] , $m[ 9],$m[10],$m[11]) ) ,
		matrix_det3( array($m[ 0],$m[ 2],$m[ 3] , $m[ 4],$m[ 6],$m[ 7] , $m[ 8],$m[10],$m[11]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 3] , $m[ 4],$m[ 5],$m[ 7] , $m[ 8],$m[ 9],$m[11]) ) ,
		matrix_det3( array($m[ 0],$m[ 1],$m[ 2] , $m[ 4],$m[ 5],$m[ 6] , $m[ 8],$m[ 9],$m[10]) ) ,
	);

	// matrix of cofactors + adjugate
	$mco = array(
		 $mdet[0] , -$mdet[4] ,  $mdet[ 8] , -$mdet[12] ,
		-$mdet[1] ,  $mdet[5] , -$mdet[ 9] ,  $mdet[13] ,
		 $mdet[2] , -$mdet[6] ,  $mdet[10] , -$mdet[14] ,
		-$mdet[3] ,  $mdet[7] , -$mdet[11] ,  $mdet[15] ,
	);

	//            |  c0 -c4  c8 -cc |
	// 1/det(M) * | -c1  c5 -c9  cd |
	//            |  c2 -c6  ca -ce |
	//            | -c3  c7 -cb  cf |
	$d = $m[0]*$mco[0] + $m[1]*$mco[4] + $m[2]*$mco[8] + $m[3]*$mco[12];
	if ( $d == 0 )
		return 0;
	$dinv = 1 / $d;

	for ( $i=0; $i < 16; $i++ )
		$mco[$i] *= $dinv;
	return $mco;
}

//////////////////////////////
function matrix( $c )
{
	$c = (int)$c;
	$m = array();
	for ( $y=0; $y < $c; $y++ )
	{
		for ( $x=0; $x < $c; $x++ )
			$m[] = ( $x === $y ) ? 1 : 0;
	}
	return $m;
}

function matrix_rotate_z( $c, $radian )
{
	// | cos  -sin |
	// | sin  cos  |
	$c = (int)$c;
	if ( $c < 2 ) // mat2+
		return -1;
	$sin = sin($radian);
	$cos = cos($radian);

	$m = matrix($c);
	$m[0*$c+0] =  $cos;
	$m[0*$c+1] = -$sin;
	$m[1*$c+0] =  $sin;
	$m[1*$c+1] =  $cos;
	return $m;
}

function matrix_rotate_y( $c, $radian )
{
	// | cos   0  sin  |
	// | 0     1  0    |
	// | -sin  0  cos  |
	$c = (int)$c;
	if ( $c < 3 ) // mat3+
		return -1;
	$sin = sin($radian);
	$cos = cos($radian);

	$m = matrix($c);
	$m[0*$c+0] =  $cos;
	$m[0*$c+2] =  $sin;
	$m[2*$c+0] = -$sin;
	$m[2*$c+2] =  $cos;
	return $m;
}

function matrix_rotate_x( $c, $radian )
{
	// | 1  0    0    |
	// | 0  cos  -sin |
	// | 0  sin  cos  |
	$c = (int)$c;
	if ( $c < 3 ) // mat3+
		return -1;
	$sin = sin($radian);
	$cos = cos($radian);

	$m = matrix($c);
	$m[1*$c+1] =  $cos;
	$m[1*$c+2] = -$sin;
	$m[2*$c+1] =  $sin;
	$m[2*$c+2] =  $cos;
	return $m;
}

function matrix_scale( $c, $sx=1, $sy=1, $sz=1 )
{
	// | sx  0   0  |
	// | 0   sy  0  |
	// | 0   0   sz |
	$c = (int)$c;
	if ( $c < 2 ) // mat2+
		return -1;

	$m = matrix($c);
	$m[0*$c+0] = $sx;
	$m[1*$c+1] = $sy;
	if ( $c >= 3 )  $m[2*$c+2] = $sz;
	return $m;
}
//////////////////////////////
/*
	sum of all interior angles
		side  degree  radian
		3     180     pi      // tri
		4     360     2 pi    // quad
		5     540     3 pi    // penta
		6     720     4 pi    // hex
		...

	https://en.m.wikipedia.org/wiki/Trigonometric_functions
	-       90
	-     Q2 | Q1    Qref = Q1         sin Q rel Y value
	- 180 ------- 0  Qref = 180 - Q2   cos Q rel X value
	-     Q3 | Q4    Qref = Q3  - 180  tan Q is Y/X value
	-       270      Qref = 360 - Q4

	SOH  sine    = opposite / hypotenuse , cosecant  = hypotenuse / opposite
	CAH  cosine  = adjacent / hypotenuse , secant    = hypotenuse / adjacent
	TOA  tangent = opposite / adjacent   , cotangent = adjacent   / opposite
//////////////////////////////
	Move
		| 1  0  mx |  | 1  0  0  mx |
		| 0  1  my |  | 0  1  0  my |
		| 0  0  1  |  | 0  0  1  mz |
		-             | 0  0  0  1  |
//////////////////////////////
	2D Rotation
		| cos  -sin  0 |
		| sin   cos  0 |
		| 0     0    1 |
//////////////////////////////
	3D Rotation
	X axis (horizontal , x flip effect)
		| 1  0    0     0 |
		| 0  cos  -sin  0 |
		| 0  sin  cos   0 |
		| 0  0    0     1 |
	Y axis (vertical , y flip effect)
		| cos   0  sin  0 |
		| 0     1  0    0 |
		| -sin  0  cos  0 |
		| 0     0  0    1 |
	Z axis (depth , screen rotate)
		| cos  -sin  0  0 |
		| sin  cos   0  0 |
		| 0    0     1  0 |
		| 0    0     0  1 |
//////////////////////////////
	| x1  y1  z1  w1 |   | cos   sin   -sin  0 |
	| x2  y2  z2  w2 | * | -sin  cos   sin   0 |
	| x3  y3  z3  w3 |   | sin   -sin  cos   0 |
	| x4  y4  z4  w4 |   | mx    my    mz    1 |
//////////////////////////////
	| cos   -sin  sin   mx |   | x1  x2  x3  x4 |
	| sin   cos   -sin  my | * | y1  y2  y3  y4 |
	| -sin  sin   cos   mz |   | z1  z2  z3  z4 |
	| 0     0     0     1  |   | w1  w2  w3  w4 |
 */
