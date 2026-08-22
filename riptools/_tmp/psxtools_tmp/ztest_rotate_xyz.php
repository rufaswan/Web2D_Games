<?php
require 'common.inc';
require 'common-quad.inc';

$rot_x = [
	1,0,0 ,
	0,2,2 ,
	0,2,2
];
$rot_y = [
	3,0,3 ,
	0,1,0 ,
	3,0,3
];
$rot_z = [
	4,4,0 ,
	4,4,0 ,
	0,0,1
];

$xy = matrix_multi33($rot_x, $rot_y);
matrix_dump($xy, 'xy');

$xz = matrix_multi33($rot_x, $rot_z);
matrix_dump($xz, 'xz');

$yz = matrix_multi33($rot_y, $rot_z);
matrix_dump($yz, 'yz');

$xyz = matrix_multi33($xy, $rot_z);
matrix_dump($xyz, 'xyz');

/*
	| 12 12 3 |   |   3*4        3*4        3 |
	| 32 32 6 | = | 2*3*4+2*4  2*3*4+2*4  2*3 |
	| 32 32 6 |   | 2*3*4+2*4  2*3*4+2*4  2*3 |
 */
