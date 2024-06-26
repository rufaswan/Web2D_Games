<?php
//////////////////////////////
// int32
$int32 = 1 << 0x20;
$inv32 = 1.0 / $int32;
$const = array(
	0xeeeeeeef, // 0.933333 = 14 / 15
	0xddddddde, // 0.866666 = 13 / 15
	0xcccccccd, // 0.8      = 12 / 15 , 4 / 5
	0xbbbbbbbc, // 0.733333 = 11 / 15
	0xaaaaaaab, // 0.666666 = 10 / 15 , 4 / 6 , 2 / 3
	0x9999999a, // 0.6      =  9 / 15 , 3 / 5
	0x88888889, // 0.533333 =  8 / 15
	0x77777778, // 0.466666 =  7 / 15
	0x66666667, // 0.4      =  6 / 15 , 2 / 5
	0x55555556, // 0.333333 =  5 / 15 , 2 / 6 , 1 / 3
	0x44444445, // 0.266666 =  4 / 15
	0x33333334, // 0.2      =  3 / 15 , 1 / 5
	0x22222223, // 0.133333 =  2 / 15
	0x11111112, // 0.066666 =  1 / 15

	0xd5555555, // 0.833333 =  5 / 6
	0x2aaaaaab, // 0.166666 =  1 / 6

	0xb60b60b7, // 0.711111 = 32 / 45
	0x92492493, // 0.571428 =  4 / 7
	0x51eb851f, // 0.32     =  8 / 25
	0x1b4e81b5, // 0.106666 =  8 / 75
);
foreach ( $const as $v )
{
	// mult  v0, $v  // multiply
	// mfhi  $f      // move from hi
	$f = $v * $inv32;
	$f = (int)($f * 1000000) * 0.000001;
	printf("%8x / int32 = %f [%8x]\n", $v, $f, $int32 * $f);
}

//////////////////////////////
// int16
$int16 = 1 << 0x10;
$inv16 = 1.0 / $int16;
foreach ( $const as $v )
{
	$v = ($v + 0x8000) >> 0x10;
	$f = $v * $inv16;
	$f = (int)($f * 1000000) * 0.000001;
	printf("%4x / int16 = %f [%4x]\n", $v, $f, $int16 * $f);
}
//////////////////////////////
$const = array(
	 10 ,  25 ,  50 ,  75 ,
	100 , 125 , 150 , 175 ,
);
foreach ( $const as $v )
{
	$div = 1.0 / $v;
	printf("1.0 / %4d.0 = %8x / int32 = %f\n", $v, $div*$int32, $div);
}
//////////////////////////////
