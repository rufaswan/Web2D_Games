<?php
require 'common.inc';
require 'common-quad.inc';
require 'class-pixlines.inc';

//////////////////////////////
function clamp4( $int )
{
	while ( $int < 0 )
		$int += 4;
	$int &= 3; // 0-3
	return $int;
}

function vec4_multi( $a, $b )
{
	$int = 0;
	for ( $i=0; $i < 4; $i++ )
		$int += ($a[$i] * $b[$i]);
	return $int;
}
//////////////////////////////
// vanillaware interpolation = 2
// p(s) = c0 + c1t + c2tt + c3ttt
//
// res =
//     q0 * (    -0.5t +  tt    + -0.5ttt)
//   + q1 * (1         + -2.5tt +  1.5ttt)
//   + q2 * (     0.5t +  2tt   + -1.5ttt)
//   + q3 * (            -0.5tt +  0.5ttt)
//
// matrix form =
//                | 0     1     0     0   |
//   |1 t tt ttt| |-0.5   0     0.5   0   |
//                | 1    -2.5   2    -0.5 |
//                |-0.5   1.5  -1.5   0.5 |
// same as =
//                    | 0   2   0   0 |
//   |1 t tt ttt| 1/2 |-1   0   1   0 |
//                    | 2  -5   4  -1 |
//                    |-1   3  -3   1 |
// = Catmull-Rom
//
// with tension 0.5
//   |    0           1            0          0      |
//   | -r = -0.5      0       r    =  0.5     0      |
//   | 2r =  1    r-3 = -2.5  3-2r =  2    -r = -0.5 |
//   | -r = -0.5  2-r =  1.5  r-2  = -1.5   r =  0.5 |
//
function splice_bezier( &$quad, $pfx, &$mat4 )
{
	printf("== inter_catmull( %s )\n", $pfx);
	$grid = new pixel_lines;

	for ( $i=0; $i < 4; $i++ )
	{
		$cur = $i;
		$prv = clamp4($cur - 1);
		$nx1 = clamp4($cur + 1);
		$nx2 = clamp4($cur + 1);
			$q0 = $quad[$prv];
			$q1 = $quad[$cur];
			$q2 = $quad[$nx1];
			$q3 = $quad[$nx2];

		$allx = array($q0[0] , $q1[0] , $q2[0] , $q3[0]);
		$ally = array($q0[1] , $q1[1] , $q2[1] , $q3[1]);
		for ( $t=0; $t < 0x100; $t++ )
		{
			$rate = $t / 0x100;
			$power = array(
				$rate ** 0 ,
				$rate ** 1 ,
				$rate ** 2 ,
				$rate ** 3 ,
			);

			$tmp = matrix_multi14($power, $mat4);
			$x = vec4_multi($tmp, $allx);
			$y = vec4_multi($tmp, $ally);
			$grid->addpoint($x, $y, "\x0c");
		} // for ( $t=0; $t < 0x100; $t++ )
	} // for ( $i=0; $i < 4; $i++ )

	$grid->addpoint($quad[0], "\x0e");
	$grid->addpoint($quad[1], "\x0e");
	$grid->addpoint($quad[2], "\x0e");
	$grid->addpoint($quad[3], "\x0e");

	$img = $grid->draw();
	save_clutfile("$pfx.clut", $img);
	return;
}

function inter_catmull( &$quad, $pfx )
{
	printf("== inter_catmull( %s )\n", $pfx);
	$mod  = 1.0 / 2;
	$mat4 = array(
		 0 ,  2 ,  0 ,  0 ,
		-1 ,  0 ,  1 ,  0 ,
		 2 , -5 ,  4 , -1 ,
		-1 ,  3 , -3 ,  1 ,
	);
	for ( $i=0; $i < 16; $i++ )
		$mat4[$i] *= $mod;
	return splice_bezier($quad, "$pfx-catmull", $mat4);
}

function inter_bezier( &$quad, $pfx )
{
	printf("== inter_bezier( %s )\n", $pfx);
	$mat4 = array(
		 1 ,  0 ,  0 , 0 ,
		-3 ,  3 ,  0 , 0 ,
		 3 , -6 ,  3 , 0 ,
		-1 ,  3 , -3 , 1 ,
	);
	return splice_bezier($quad, "$pfx-bezier", $mat4);
}

function inter_bspline( &$quad, $pfx )
{
	printf("== inter_bspline( %s )\n", $pfx);
	$mod  = 1.0 / 6;
	$mat4 = array(
		 1 ,  4 ,  1 , 0 ,
		-3 ,  0 ,  3 , 0 ,
		 3 , -6 ,  3 , 0 ,
		-1 ,  3 , -3 , 1 ,
	);
	for ( $i=0; $i < 16; $i++ )
		$mat4[$i] *= $mod;
	return splice_bezier($quad, "$pfx-bspline", $mat4);
}

function inter_hermite( &$quad, $pfx )
{
	printf("== inter_hermite( %s )\n", $pfx);
	$mat4 = array(
		 1 ,  0 ,  0 ,  0 ,
		 0 ,  1 ,  0 ,  0 ,
		-3 , -2 ,  3 , -1 ,
		 2 ,  1 , -2 ,  1 ,
	);
	return splice_bezier($quad, "$pfx-hermite", $mat4);
}
//////////////////////////////
function inter_linear( &$quad, $pfx )
{
	printf("== inter_linear( %s )\n", $pfx);
	$grid = new pixel_lines;
	for ( $i=0; $i < 4; $i++ )
	{
		$cur = $i;
		$nxt = clamp4($cur + 1);
			$q0 = $quad[$cur];
			$q1 = $quad[$nxt];

		for ( $t=0; $t < 0x100; $t++ )
		{
			$rate = $t / 0x100;
			$invr = 1.0 - $rate;

			$x = ($q0[0] * $invr) + ($q1[0] * $rate);
			$y = ($q0[1] * $invr) + ($q1[1] * $rate);
			$grid->addpoint($x, $y, "\x0c");
		} // for ( $t=0; $t < 0x100; $t++ )
	} // for ( $i=0; $i < 4; $i++ )

	$grid->addpoint($quad[0], "\x0e");
	$grid->addpoint($quad[1], "\x0e");
	$grid->addpoint($quad[2], "\x0e");
	$grid->addpoint($quad[3], "\x0e");

	$img = $grid->draw();
	save_clutfile("$pfx-linear.clut", $img);
	return;
}

function pix_inter( &$quad, $pfx )
{
	inter_linear ($quad, $pfx);
	inter_catmull($quad, $pfx);
	inter_bezier ($quad, $pfx);
	inter_bspline($quad, $pfx);
	inter_hermite($quad, $pfx);
	return;
}
//////////////////////////////
$quad = array(
	array(  0,-80),
	array( 80,  0),
	array(  0, 80),
	array(-80,  0),
);
pix_inter($quad, 'pix_inter/rotate0');

$quad = array(
	array( 80, 80),
	array( 80,-80),
	array(-80,-80),
	array(-80, 80),
);
pix_inter($quad, 'pix_inter/rotate45');

$quad = array(
	array( 80, 80),
	array(-80,-80),
	array(-80, 80),
	array( 80,-80),
);
pix_inter($quad, 'pix_inter/twist45');
