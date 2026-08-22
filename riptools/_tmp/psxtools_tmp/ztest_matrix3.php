<?php
require 'common.inc';
require 'common-quad.inc';

function quad2vec3( &$quad )
{
	$q = json_decode($quad, true);
	$quad = [
		[$q[0],$q[1],1],
		[$q[2],$q[3],1],
		[$q[4],$q[5],1],
	];
	return;
}

function mat3( $name, $src, $dst )
{
	echo "== $name ==\n";
	quad2vec3($src);
	quad2vec3($dst);
	printf("%6d %6d    %6d %6d\n", $src[0][0], $src[0][1], $dst[0][0], $dst[0][1]);
	printf("%6d %6d    %6d %6d\n", $src[1][0], $src[1][1], $dst[1][0], $dst[1][1]);
	printf("%6d %6d    %6d %6d\n", $src[2][0], $src[2][1], $dst[2][0], $dst[2][1]);

	//   | H1x H2x H3x |   | h1x h2x h3x |
	// M | H1y H2y H3y | = | h1y h2y h3y |
	//   | H1z H2z H3z |   | h1z h2z h3z |
	//                MH = h
	//                M  = hH^-1
	$H = [
		$src[0][0] , $src[1][0] , $src[2][0] ,
		$src[0][1] , $src[1][1] , $src[2][1] ,
		1 , 1 , 1 ,
	];

	$h = [
		$dst[0][0] , $dst[1][0] , $dst[2][0] ,
		$dst[0][1] , $dst[1][1] , $dst[2][1] ,
		1 , 1 , 1 ,
	];

	$Hinv = matrix_inv3($H);
	$M    = matrix_multi33($h, $Hinv);
	matrix_dump($M, 'M');

	echo "new DST\n";
	$d0 = matrix_multi31($M, $src[0]);
	$d1 = matrix_multi31($M, $src[1]);
	$d2 = matrix_multi31($M, $src[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $d0[0], $d0[1], $d0[2], $d0[0]/$d0[2], $d0[1]/$d0[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $d1[0], $d1[1], $d1[2], $d1[0]/$d1[2], $d1[1]/$d1[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $d2[0], $d2[1], $d2[2], $d2[0]/$d2[2], $d2[1]/$d2[2]);

	$Minv = matrix_inv3($M);
	echo "new SRC\n";
	$s0 = matrix_multi31($Minv, $dst[0]);
	$s1 = matrix_multi31($Minv, $dst[1]);
	$s2 = matrix_multi31($Minv, $dst[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $s0[0], $s0[1], $s0[2], $s0[0]/$s0[2], $s0[1]/$s0[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $s1[0], $s1[1], $s1[2], $s1[0]/$s1[2], $s1[1]/$s1[2]);
	printf("  | %6d %6d %6d |  | %6d %6d |\n", $s2[0], $s2[1], $s2[2], $s2[0]/$s2[2], $s2[1]/$s2[2]);
	return;
}

//  A
// B C
$src = '[15,10 , 10,20 , 20,20]';

mat3('+40', $src, '[55,50 , 50,60 , 60,60]');
mat3('x4 ', $src, '[60,40 , 40,80 , 80,80]');
mat3('123', $src, '[15,10 , 20,40 , 60,60]');

// | scale.x  shear.x  move.x |   | 1 0 0 |
// | shear.y  scale.y  move.y | = | 0 1 0 |
// | 0        0        1      |   | 0 0 1 |
// move.xy  (in + 0.0 pixel)
// scale.xy (in * 1.0 float)
// shear.xy (in radian)
//
// scale.x * -1 = flip x
// scale.y * -1 = flip y
// rotate   = scale.xy , shear.xy
