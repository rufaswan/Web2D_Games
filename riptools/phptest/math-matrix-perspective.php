<?php
declare(strict_types=1);

require 'tool.inc';
tool::require('class-jsonpretty');
tool::require('class-quadfile');
tool::require('func-matrix');

function quad2_dump( array &$q, string $name ) : void
{
	printf("quad2( %s )\n", $name);
	for ( $i=0; $i < 4; $i++ )
		printf(" | %7.2f %7.2f |\n", $q[$i*2+0], $q[$i*2+1]);
}
function quad3_dump( array &$q, string $name ) : void
{
	printf("quad3( %s )\n", $name);
	for ( $i=0; $i < 4; $i++ )
		printf(" | %7.2f %7.2f %7.2f |\n", $q[$i][0], $q[$i][1], $q[$i][2]);
}

function quad2mat3( array &$q ) : array
{
	$crx = ['quadfile', 'cross'];

	// a --- b
	// |     |
	// d --- c
	$a = [$q[0] , $q[1] , 1];
	$b = [$q[2] , $q[3] , 1];
	$c = [$q[4] , $q[5] , 1];
	$d = [$q[6] , $q[7] , 1];

	$inn = $crx( $crx($a,$c) , $crx($b,$d) );
	$hor = $crx( $crx($a,$b) , $crx($d,$c) );
	$ver = $crx( $crx($a,$d) , $crx($b,$c) );

	$r = [
		$inn[0] , $hor[0] , $ver[0] ,
		$inn[1] , $hor[1] , $ver[1] ,
		$inn[2] , $hor[2] , $ver[2] ,
	];
	return $r;
}
function multi_m3v2( array &$mat3, float $x, float $y ) : array
{
	$v3 = [$x, $y, 1];
	return matrix::multi31($mat3, $v3);
}

function stu_dst( array $stu, float $dx, float $dy ) : void
{
	$inv = 1.0 / $stu[2];
	printf("%7.2f %7.2f %7.2f = %7.2f %7.2f %7.2f\n",
		$stu[0]*$inv, $stu[1]*$inv, $stu[2]*$inv,
		$dx*$inv    , $dy*$inv    , $inv
	);
}
function src_xyz( float $sx, float $sy, array $xyz ) : void
{
	$inv = 1.0 / $xyz[2];
	printf("%7.2f %7.2f %7.2f = %7.2f %7.2f %7.2f\n",
		$sx*$inv    , $sy*$inv    , $inv,
		$xyz[0]*$inv, $xyz[1]*$inv, $xyz[2]*$inv
	);
}
//////////////////////////////
$src = [ 10,10  , 20,10  , 20,20 ,  10,20];
$dst = [-10,-10 , 10,-10 , 50,0  , -50,0 ];
quad2_dump($src, 'src');
quad2_dump($dst, 'dst');

$src_mat3 = quad2mat3($src);
$dst_mat3 = quad2mat3($dst);
matrix::dump($src_mat3, 'src_mat3');
matrix::dump($dst_mat3, 'dst_mat3');

$src_inv3 = matrix::inv3($src_mat3);
$dst_inv3 = matrix::inv3($dst_mat3);
matrix::dump($src_inv3, 'src_inv3');
matrix::dump($dst_inv3, 'dst_inv3');

// M * src = dst
//       M = dst * src_inv
//     src = M_inv * dst
$mat3 = matrix::multi33($dst_mat3, $src_inv3);
$inv3 = matrix::inv3($mat3);
matrix::dump($mat3, 'mat3');
matrix::dump($inv3, 'inv3');

$stu = [
	multi_m3v2($inv3, $dst[0], $dst[1]),
	multi_m3v2($inv3, $dst[2], $dst[3]),
	multi_m3v2($inv3, $dst[4], $dst[5]),
	multi_m3v2($inv3, $dst[6], $dst[7]),
];
$xyz = [
	multi_m3v2($mat3, $src[0], $src[1]),
	multi_m3v2($mat3, $src[2], $src[3]),
	multi_m3v2($mat3, $src[4], $src[5]),
	multi_m3v2($mat3, $src[6], $src[7]),
];
quad3_dump($stu, 'stu');
quad3_dump($xyz, 'xyz');

echo "src/z = xyz/z\n";
src_xyz($src[0], $src[1], $xyz[0]);
src_xyz($src[2], $src[3], $xyz[1]);
src_xyz($src[4], $src[5], $xyz[2]);
src_xyz($src[6], $src[7], $xyz[3]);

echo "stu/u = xy/u\n";
stu_dst($stu[0], $dst[0], $dst[1]);
stu_dst($stu[1], $dst[2], $dst[3]);
stu_dst($stu[2], $dst[4], $dst[5]);
stu_dst($stu[3], $dst[6], $dst[7]);
