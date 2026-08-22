<?php
require 'common.inc';
require 'common-quad.inc';

$crx = 'cross_product';
function vec3p( $v3 )
{
	printf("%.2f %.2f %.2f\n", $v3[0], $v3[1], $v3[2]);
}

$t1 = $crx( [10,10,1] , [20,20,1] );
$t2 = $crx( [20,10,1] , [10,20,1] );
$a  = $crx( $t1 , $t2 );
vec3p($t1);
vec3p($t2);
vec3p($a);

$t1 = $crx( [10,10,1] , [20,10,1] );
$t2 = $crx( [10,20,1] , [20,20,1] );
$b  = $crx( $t1 , $t2 );
vec3p($t1);
vec3p($t2);
vec3p($b);

$t1 = $crx( [10,10,1] , [10,20,1] );
$t2 = $crx( [20,10,1] , [20,20,1] );
$c  = $crx( $t1 , $t2 );
vec3p($t1);
vec3p($t2);
vec3p($c);

$H = [
	$a[0] , $b[0] , $c[0] ,
	$a[1] , $b[1] , $c[1] ,
	$a[2] , $b[2] , $c[2] ,
];
$Hinv = matrix_inv3($H);
print_r($H);
print_r($Hinv);

$I = matrix_multi33($H, $Hinv);
print_r($I);

$I = matrix_multi33($Hinv, $H);
print_r($I);
