<?php
require 'common.inc';
require 'common-quad.inc';

function json2vec3( $str )
{
	$q = json_decode($str, true);
	$quad = [
		[$q[0],$q[1],1],
		[$q[2],$q[3],1],
		[$q[4],$q[5],1],
		[$q[6],$q[7],1],
	];
	return $quad;
}

function trans_mat3( $src, $dst )
{
	$src = json2vec3($src);
	$dst = json2vec3($dst);
	printf("%6d %6d    %6d %6d\n", $src[0][0], $src[0][1], $dst[0][0], $dst[0][1]);
	printf("%6d %6d    %6d %6d\n", $src[1][0], $src[1][1], $dst[1][0], $dst[1][1]);
	printf("%6d %6d    %6d %6d\n", $src[2][0], $src[2][1], $dst[2][0], $dst[2][1]);
	printf("%6d %6d    %6d %6d\n", $src[3][0], $src[3][1], $dst[3][0], $dst[3][1]);

	$crx = 'cross_product';
	$H0 = $crx( $crx($src[0],$src[2]) , $crx($src[1],$src[3]) ); // corners
	$H1 = $crx( $crx($src[0],$src[1]) , $crx($src[3],$src[2]) ); // top
	$H2 = $crx( $crx($src[0],$src[3]) , $crx($src[1],$src[2]) ); // left

	$h0 = $crx( $crx($dst[0],$dst[2]) , $crx($dst[1],$dst[3]) ); // corners
	$h1 = $crx( $crx($dst[0],$dst[1]) , $crx($dst[3],$dst[2]) ); // top
	$h2 = $crx( $crx($dst[0],$dst[3]) , $crx($dst[1],$dst[2]) ); // left

	$H = [
		$H0[0] , $H1[0] , $H2[0] ,
		$H0[1] , $H1[1] , $H2[1] ,
		$H0[2] , $H1[2] , $H2[2] ,
	];

	$h = [
		$h0[0] , $h1[0] , $h2[0] ,
		$h0[1] , $h1[1] , $h2[1] ,
		$h0[2] , $h1[2] , $h2[2] ,
	];

	$Hinv = matrix_inv3($H);
	$M    = matrix_multi33($h, $Hinv);
	return $M;
}

// +20,20  hflip
$src = '[10,10 , 20,10 , 20,20 , 10,20]';
$dst = '[40,30 , 30,30 , 30,40 , 40,40]';

$M = trans_mat3($src, $dst);
matrix_dump($M, 'M1');

$src = '[30,30 , 60,30 , 60,60 , 30,60]';
$dst = '[80,50 , 50,50 , 50,80 , 80,80]';

$M = trans_mat3($src, $dst);
matrix_dump($M, 'M2');
