<?php
require 'common.inc';
require 'common-quad.inc';

$A = [1,2,3,4,5,6,7,8,9];
$B = [11,12,13,14,15,16,17,18,19];
$C = [21,22,23,24,25,26,27,28,29];
$D = [31,32,33,34,35,36,37,38,39];
$E = [41,42,43,44,45,46,47,48,49];

$t = matrix_multi33($A,$B);
$t = matrix_multi33($t,$C);
$t = matrix_multi33($t,$D);
$t = matrix_multi33($t,$E);
matrix_dump($t, 'ABCDE');

$t = matrix_multi33($D,$E);
$t = matrix_multi33($C,$t);
$t = matrix_multi33($B,$t);
$t = matrix_multi33($A,$t);
matrix_dump($t, 'DECBA');

// A*B !== B*A
// (A*B)*C === A*(B*C)
//
// (((A*B)*C)*D)*E === (A*(B*(C*(D*E))))
