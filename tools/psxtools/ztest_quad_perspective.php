<?php
require 'common.inc';
require 'common-quad.inc';

///// MATRIX TEST /////
	$m = array(-3,1 , 5,-2);
		matrix_dump($m, 'M2');
	$minv = matrix_inv2($m);
		matrix_dump($minv, 'Minv2');
	$i = matrix_multi22($m, $minv);
		matrix_dump($i, 'I2');

	$m = array(3,0,2 , 2,0,-2 , 0,1,1);
		matrix_dump($m, 'M3');
	$minv = matrix_inv3($m);
		matrix_dump($minv, 'Minv3');
	$i = matrix_multi33($m, $minv);
		matrix_dump($i, 'I3');

	$m = array(5,0,0,0 , 0,0,3,0 , 0,1,3,0 , 1,0,0,1);
		matrix_dump($m, 'M4');
	$minv = matrix_inv4($m);
		matrix_dump($minv, 'Minv4');
	$i = matrix_multi44($m, $minv);
		matrix_dump($i, 'I4');

///// PERSPECTIVE TEST /////
function perspective_quad( &$srcquad, &$dstquad )
{
	echo "== perspective_quad() ==\n";

	// https://mrl.nyu.edu/~dzorin/ug-graphics/lectures/lecture7/sld024.html
	// M * SRC = DST
	//       M = DST * SRC_INV
	//     SRC = M_INV * DST
	$src = array(
		array($srcquad[0] , $srcquad[1] , 1),
		array($srcquad[2] , $srcquad[3] , 1),
		array($srcquad[4] , $srcquad[5] , 1),
		array($srcquad[6] , $srcquad[7] , 1),
	);
	$dst = array(
		array($dstquad[0] , $dstquad[1] , 1),
		array($dstquad[2] , $dstquad[3] , 1),
		array($dstquad[4] , $dstquad[5] , 1),
		array($dstquad[6] , $dstquad[7] , 1),
	);

	$crx = 'cross_product';
	// 0 - 1  =  hor 0x1 x 3x2
	//     |  =  ver 0x3 x 1x2
	// 3 - 2  =  cor 0x2 x 1x3
	$src0132 = $crx( $crx($src[0],$src[1]) , $crx($src[3],$src[2]) );
	$src0312 = $crx( $crx($src[0],$src[3]) , $crx($src[1],$src[2]) );
	$src0213 = $crx( $crx($src[0],$src[2]) , $crx($src[1],$src[3]) );

	$dst0132 = $crx( $crx($dst[0],$dst[1]) , $crx($dst[3],$dst[2]) );
	$dst0312 = $crx( $crx($dst[0],$dst[3]) , $crx($dst[1],$dst[2]) );
	$dst0213 = $crx( $crx($dst[0],$dst[2]) , $crx($dst[1],$dst[3]) );

	$src33 = array(
		$src0132[0] , $src0312[0] , $src0213[0] ,
		$src0132[1] , $src0312[1] , $src0213[1] ,
		$src0132[2] , $src0312[2] , $src0213[2] ,
	);
	$dst33 = array(
		$dst0132[0] , $dst0312[0] , $dst0213[0] ,
		$dst0132[1] , $dst0312[1] , $dst0213[1] ,
		$dst0132[2] , $dst0312[2] , $dst0213[2] ,
	);
	matrix_dump($src33, 'src33');
	matrix_dump($dst33, 'dst33');

	$src33_inv = matrix_inv3($src33);
	$mat3      = matrix_multi33($dst33, $src33_inv);
	$mat3_inv  = matrix_inv3($mat3);
	matrix_dump($mat3    , 'mat3');
	matrix_dump($mat3_inv, 'mat3_inv');

	$src4 = array(0,0,0,0);
	$dst4 = array(0,0,0,0);

	echo "-----\n";
	echo "M * SRC = DST\n";
	foreach ( $src as $k => $v )
	{
		$xyz = matrix_multi31($mat3, $v);
		$xy  = array($xyz[0]/$xyz[2] , $xyz[1]/$xyz[2]);
		$dst4[$k] = $xyz;

		printf("M * %10.2f,%10.2f\n", $v[0], $v[1]);
		printf("= xyz  %10.2f,%10.2f,%10.2f\n", $xyz[0], $xyz[1], $xyz[2]);
		printf("= xy   %10.2f,%10.2f\n", $xy[0], $xy[1]);
		printf("= dst  %10.2f,%10.2f\n", $dst[$k][0], $dst[$k][1]);
	}

	echo "-----\n";
	echo "SRC = M_INV * DST\n";
	foreach ( $dst as $k => $v )
	{
		$uvw = matrix_multi31($mat3_inv, $v);
		$uv  = array($uvw[0]/$uvw[2] , $uvw[1]/$uvw[2]);
		$src4[$k] = $uvw;

		printf("M_INV * %10.2f,%10.2f\n", $v[0], $v[1]);
		printf("= uvw  %10.2f,%10.2f,%10.2f\n", $uvw[0], $uvw[1], $uvw[2]);
		printf("= uv   %10.2f,%10.2f\n", $uv[0], $uv[1]);
		printf("= src  %10.2f,%10.2f\n", $src[$k][0], $src[$k][1]);
	}

	echo "-----\n";
	for ( $k=0; $k < 4; $k++ )
	{
		$srcz = 1.0 / $src4[$k][2];
		$dstz = 1.0 / $dst4[$k][2];

		printf("== corner %d ==\n", $k);
		printf("src %10.2f,%10.2f,[%10.2f] = dst %10.2f,%10.2f, %10.2f \n",
			$src4[$k][0]*$srcz, $src4[$k][1]*$srcz, $src4[$k][2]*$srcz,
			 $dst[$k][0]*$srcz,  $dst[$k][1]*$srcz,              $srcz
		);
		printf("src %10.2f,%10.2f, %10.2f  = dst %10.2f,%10.2f,[%10.2f]\n",
			 $src[$k][0]*$dstz,  $src[$k][1]*$dstz,              $dstz,
			$dst4[$k][0]*$dstz, $dst4[$k][1]*$dstz, $dst4[$k][2]*$dstz
		);
	}
	return;
}

$src = array(10,100 , 100,100 , 100,10 , 10,10);
$dst = array(-100,-100 , 50,-50 , 100,100 , -50,50);
perspective_quad($src, $dst);
