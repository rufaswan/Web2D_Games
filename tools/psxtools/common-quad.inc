<?php
// 3D texture quad-rendering + transformation related
// product = factor * factor

function dot_product( $vb, $vc )
{
	// The direct, scalar, or dot product of two vectors
	// s = axbx + ayby
	// s = axbx + ayby + azbz
	$dot = $vb[0]*$vc[0] + $vb[1]*$vc[1] + $vb[2]*$vc[2];
	return $dot;
}

function cross_product( $vb, $vc )
{
	// The skew, vector, or cross product of two vectors
	//             | x 0 0 |
	// | x y z | = | 0 y 0 |
	//             | 0 0 z |
	// cross = 0 parallel      , 1 perpendicular
	// dot   = 0 perpendicular , 1 parallel
	//   perpendicular = 90 degree
	//   parallel      = 0 or 180 degree (sin = 0)
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
//////////////////////////////
function vector_dump( $V, $name )
{
	echo "$name ";
	printf(" xyz (%4d , %4d , %4.2f)", $V[0], $V[1], $V[2]);
	if ( (int)($V[2]*100) !== 0 )
		printf("  xy (%4d , %4d)", $V[0]/$V[2], $V[1]/$V[2]);
	echo "\n";
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
		if ( $x1 > $vector[$i][0] )  $x1 = $vector[$i][0];
		if ( $x2 < $vector[$i][0] )  $x2 = $vector[$i][0];
		if ( $y1 > $vector[$i][1] )  $y1 = $vector[$i][1];
		if ( $y2 < $vector[$i][1] )  $y2 = $vector[$i][1];
	} // for ( $i=0; $i < 4; $i++ )
	return array($x1, $y1, $x2, $y2);
}
//////////////////////////////
function quad_dump( $V4, $name )
{
	// 1 - 2
	//     |  order
	// 4 - 3
	printf("quad( %s )\n", $name);
	printf("  %10.2f,%10.2f  %10.2f,%10.2f\n", $V4[0][0], $V4[0][1], $V4[1][0], $V4[1][1]);
	printf("  %10.2f,%10.2f  %10.2f,%10.2f\n", $V4[3][0], $V4[3][1], $V4[2][0], $V4[2][1]);
	printf("  w %.2f  h %.2f\n", $V4[2][0]-$V4[0][0], $V4[2][1]-$V4[0][1]);
	return;
}

function matrix_dump( $M , $name )
{
	printf("matrix( %s )\n", $name);
	printf("  | %10.2f %10.2f %10.2f |\n", $M[0], $M[1], $M[2]);
	printf("  | %10.2f %10.2f %10.2f |\n", $M[3], $M[4], $M[5]);
	printf("  | %10.2f %10.2f %10.2f |\n", $M[6], $M[7], $M[8]);
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
	$s1 = array(
		$M[4]*$M[8]-$M[5]*$M[7] , $M[3]*$M[8]-$M[5]*$M[6] , $M[3]*$M[7]-$M[4]*$M[6] ,
		$M[1]*$M[8]-$M[2]*$M[7] , $M[0]*$M[8]-$M[2]*$M[6] , $M[0]*$M[7]-$M[1]*$M[6] ,
		$M[1]*$M[5]-$M[2]*$M[4] , $M[0]*$M[5]-$M[2]*$M[3] , $M[0]*$M[4]-$M[1]*$M[3] ,
	);
	//matrix_dump($s1, "inv s1");

	// s2 matrix of cofactors | + - + |
	//                        | - + - |
	//                        | + - + |
	$s2 = array(
		+$s1[0] , -$s1[1] , +$s1[2] ,
		-$s1[3] , +$s1[4] , -$s1[5] ,
		+$s1[6] , -$s1[7] , +$s1[8] ,
	);
	//matrix_dump($s2, "inv s2");

	// 3. adjugate
	//    | 0 1 2 |    | 0 3 6 |
	//    | 3 4 5 | => | 1 4 7 |
	//    | 6 7 8 |    | 2 5 8 |
	$s3 = array(
		$s2[0] , $s2[3] , $s2[6] ,
		$s2[1] , $s2[4] , $s2[7] ,
		$s2[2] , $s2[5] , $s2[8] ,
	);
	//matrix_dump($s3, "inv s3");

	// 4. multiply by 1/det
	// https://www.mathsisfun.com/algebra/matrix-determinant.html
	$det = $M[0]*$s2[0] + $M[1]*$s2[1] + $M[2]*$s2[2];
	if ( $det == 0 )
		return array(0,0,0,0,0,0,0,0,0);

	for ( $i=0; $i < 9; $i++ )
		$s3[$i] = $s3[$i] / $det;
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
			$err .= "copyquad() vector $i x not set\n";
		if ( ! isset( $pix['vector'][$i][1] ) )
			$err .= "copyquad() vector $i y not set\n";

		if ( $pix['vector'][$i][0] < 0 )
			$err .= sprintf("copyquad() vector $i x = %d\n", $pix['vector'][$i][0]);
		if ( $pix['vector'][$i][1] < 0 )
			$err .= sprintf("copyquad() vector $i y = %d\n", $pix['vector'][$i][1]);

		if ( $pix['vector'][$i][0] > $pix['rgba']['w'] )
			$err .= sprintf("copyquad() vector $i x = %d [%d]\n", $pix['vector'][$i][0], $pix['rgba']['w']);
		if ( $pix['vector'][$i][1] > $pix['rgba']['h'] )
			$err .= sprintf("copyquad() vector $i y = %d [%d]\n", $pix['vector'][$i][1], $pix['rgba']['h']);

	} // for ( $i=0; $i < 4; $i++ )

	if ( ! empty($err) )
	{
		trigger_error($err, E_USER_ERROR);
		return true;
	}
	return false;
}

function copyquad( &$pix, $byte=1 )
{
	if ( copyquad_errs($pix) )
		return;
	if ( defined("DRY_RUN") )
		return;

	// https://mrl.nyu.edu/~dzorin/ug-graphics/lectures/lecture7/sld024.html
	// vector ordering
	//                       A      B      C      D
	//   src 20x25 image =  0,0   19,0   19,24   0,24
	//   transformed to  = 20,40  40,20  30,10  10,30
	//
	//   Lunar 2
	//   P.Crown  Muramasa
	//   1 - 2    1   4
	//       |    |   |
	//   4 - 3    2 - 3
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
	$H1 = $crx( $crx( $pix['src']['vector'][1],$pix['src']['vector'][0] ) , $crx( $pix['src']['vector'][2],$pix['src']['vector'][3] ) );
	$H2 = $crx( $crx( $pix['src']['vector'][0],$pix['src']['vector'][3] ) , $crx( $pix['src']['vector'][1],$pix['src']['vector'][2] ) );
	$H3 = $crx( $crx( $pix['src']['vector'][0],$pix['src']['vector'][2] ) , $crx( $pix['src']['vector'][1],$pix['src']['vector'][3] ) );

	$h1 = $crx( $crx( $pix['vector'][1],$pix['vector'][0] ) , $crx( $pix['vector'][2],$pix['vector'][3] ) );
	$h2 = $crx( $crx( $pix['vector'][0],$pix['vector'][3] ) , $crx( $pix['vector'][1],$pix['vector'][2] ) );
	$h3 = $crx( $crx( $pix['vector'][0],$pix['vector'][2] ) , $crx( $pix['vector'][1],$pix['vector'][3] ) );

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
	$M    = matrix_multi33($h, $Hinv);
	$Minv = matrix_inverse($M);
		matrix_dump($Hinv, "Hinv");
		matrix_dump($M   , "M");
		matrix_dump($Minv, "Minv");

	list($x1,$y1,$x2,$y2) = vector_box( $pix['vector'] );
	for ( $y=$y1; $y <= $y2; $y++ )
	{
		for ( $x=$x1; $x <= $x2; $x++ )
		{
			// t,r,s = M^-1 * (x,y,1)
			// sx,sy = t/s,r/s
			$t = matrix_multi31($Minv, array($x,$y,1));
			//vector_dump($t, "$x,$y");

			if ( (int)($t[2]*100) === 0 )
				continue;
			$sx = (int)round($t[0] / $t[2]);
			$sy = (int)round($t[1] / $t[2]);

			updatepix($pix, $byte, $sx, $sy, $x, $y);
		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )

	return;
}
//////////////////////////////
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
