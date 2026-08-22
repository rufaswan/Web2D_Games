<?php
require 'common.inc';
require 'common-quad.inc';

function quad2vec4( &$quad )
{
	$q = json_decode($quad, true);
	$quad = [
		[$q[0],$q[1],1,1],
		[$q[2],$q[3],1,1],
		[$q[4],$q[5],1,1],
		[$q[6],$q[7],1,1],
	];
	return;
}

function mat4( $name, $src, $dst )
{
	echo "== $name ==\n";
	quad2vec4($src);
	quad2vec4($dst);
	printf("%6d %6d    %6d %6d\n", $src[0][0], $src[0][1], $dst[0][0], $dst[0][1]);
	printf("%6d %6d    %6d %6d\n", $src[1][0], $src[1][1], $dst[1][0], $dst[1][1]);
	printf("%6d %6d    %6d %6d\n", $src[2][0], $src[2][1], $dst[2][0], $dst[2][1]);
	printf("%6d %6d    %6d %6d\n", $src[3][0], $src[3][1], $dst[3][0], $dst[3][1]);

	//   | H1x H2x H3x |   | h1x h2x h3x |
	// M | H1y H2y H3y | = | h1y h2y h3y |
	//   | H1z H2z H3z |   | h1z h2z h3z |
	//                MH = h
	//                M  = hH^-1
/*
	$crx = 'cross_product';
	$H0 = $crx($src[0], $src[1]);
	$H1 = $crx($src[1], $src[2]);
	$H2 = $crx($src[2], $src[3]);
	$H3 = $crx($src[3], $src[0]);

	$h0 = $crx($dst[0], $dst[1]);
	$h1 = $crx($dst[1], $dst[2]);
	$h2 = $crx($dst[2], $dst[3]);
	$h3 = $crx($dst[3], $dst[0]);

	$H = [
		$H0[0] , $H1[0] , $H2[0] , $H3[0] ,
		$H0[1] , $H1[1] , $H2[1] , $H3[1] ,
		$H0[2] , $H1[2] , $H2[2] , $H3[2] ,
		0 , 0 , 0 , 1 ,
	];

	$h = [
		$h0[0] , $h1[0] , $h2[0] , $h3[0] ,
		$h0[1] , $h1[1] , $h2[1] , $h3[1] ,
		$h0[2] , $h1[2] , $h2[2] , $h3[2] ,
		0 , 0 , 0 , 1 ,
	];
*/

	$H = [
		$src[0][0] , $src[1][0] , $src[2][0] , $src[3][0] ,
		$src[0][1] , $src[1][1] , $src[2][1] , $src[3][1] ,
		1 , 1 , 1 , 1 ,
		0 , 0 , 0 , 1 ,
	];

	$h = [
		$dst[0][0] , $dst[1][0] , $dst[2][0] , $dst[3][0] ,
		$dst[0][1] , $dst[1][1] , $dst[2][1] , $dst[3][1] ,
		1 , 1 , 1 , 1 ,
		0 , 0 , 0 , 1 ,
	];


	$Hinv = matrix_inv4($H);
	$M    = matrix_multi44($h, $Hinv);
	matrix_dump($M, 'M');

	echo "new DST\n";
	$d0 = matrix_multi41($M, $src[0]);
	$d1 = matrix_multi41($M, $src[1]);
	$d2 = matrix_multi41($M, $src[2]);
	$d3 = matrix_multi41($M, $src[3]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $d0[0], $d0[1], $d0[2], $d0[3], $d0[0]/$d0[3]/$d0[2], $d0[1]/$d0[3]/$d0[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $d1[0], $d1[1], $d1[2], $d1[3], $d1[0]/$d1[3]/$d1[2], $d1[1]/$d1[3]/$d1[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $d2[0], $d2[1], $d2[2], $d2[3], $d2[0]/$d2[3]/$d2[2], $d2[1]/$d2[3]/$d2[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $d3[0], $d3[1], $d3[2], $d3[3], $d3[0]/$d3[3]/$d3[2], $d3[1]/$d3[3]/$d3[2]);

	$Minv = matrix_inv4($M);
	echo "new SRC\n";
	$s0 = matrix_multi41($Minv, $dst[0]);
	$s1 = matrix_multi41($Minv, $dst[1]);
	$s2 = matrix_multi41($Minv, $dst[2]);
	$s3 = matrix_multi41($Minv, $dst[3]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $s0[0], $s0[1], $s0[2], $s0[3], $s0[0]/$s0[3]/$s0[2], $s0[1]/$s0[3]/$s0[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $s1[0], $s1[1], $s1[2], $s1[3], $s1[0]/$s1[3]/$s1[2], $s1[1]/$s1[3]/$s1[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $s2[0], $s2[1], $s2[2], $s2[3], $s2[0]/$s2[3]/$s2[2], $s2[1]/$s2[3]/$s2[2]);
	printf("  | %6d %6d %6d %6d |  | %6d %6d |\n", $s3[0], $s3[1], $s3[2], $s3[3], $s3[0]/$s3[3]/$s3[2], $s3[1]/$s3[3]/$s3[2]);
	return;
}

// A B
//
// D C
$src = '[10,10 , 20,10 , 20,20 , 10,20]';

mat4('+40 '    , $src, '[50,50 , 60,50 , 60,60 , 50,60]');
mat4('x4  '    , $src, '[40,40 , 80,40 , 80,80 , 40,80]');
mat4('1234'    , $src, '[10,10 , 40,20 , 60,60 , 40,80]');
//mat4('twist-tb', $src, '[20,20 , 30,20 , 20,30 , 30,30]');
//mat4('twist-lr', $src, '[20,20 , 30,30 , 30,20 , 20,30]');
//mat4('bend-0'  , $src, '[10,10 , 30,20 , 10,30 , 20,20]');
//mat4('bend-1'  , $src, '[30,20 , 10,30 , 20,20 , 10,10]');
