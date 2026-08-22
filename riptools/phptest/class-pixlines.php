<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');
tool::require('class-pixlines');
tool::require('func-matrix');

//////////////////////////////
function clamp4( int $int ) : int
{
	while ( $int < 0 )
		$int += 4;
	$int &= 3; // 0-3
	return $int;
}

function vec4_multi( array $a, array $b ) : float
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
function splice_bezier( array &$quad, string $pfx, array &$mat4 ) : void
{
	printf("== inter_catmull( %s )\n", $pfx);
	$grid = new pixlines;

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

		$allx = [$q0[0] , $q1[0] , $q2[0] , $q3[0]];
		$ally = [$q0[1] , $q1[1] , $q2[1] , $q3[1]];
		for ( $t=0; $t < 0x100; $t++ )
		{
			$rate = $t / 0x100;
			$power = [
				$rate ** 0 ,
				$rate ** 1 ,
				$rate ** 2 ,
				$rate ** 3 ,
			];

			$tmp = matrix::multi14($power, $mat4);
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
	clutfile::save($pfx, $img);
}

function inter_catmull( array &$quad, string $pfx ) : void
{
	printf("== inter_catmull( %s )\n", $pfx);
	$mod  = 1.0 / 2;
	$mat4 = [
		 0 ,  2 ,  0 ,  0 ,
		-1 ,  0 ,  1 ,  0 ,
		 2 , -5 ,  4 , -1 ,
		-1 ,  3 , -3 ,  1 ,
	];
	for ( $i=0; $i < 16; $i++ )
		$mat4[$i] *= $mod;
	splice_bezier($quad, "$pfx-catmull", $mat4);
}

function inter_bezier( array &$quad, string $pfx ) : void
{
	printf("== inter_bezier( %s )\n", $pfx);
	$mat4 = [
		 1 ,  0 ,  0 , 0 ,
		-3 ,  3 ,  0 , 0 ,
		 3 , -6 ,  3 , 0 ,
		-1 ,  3 , -3 , 1 ,
	];
	splice_bezier($quad, "$pfx-bezier", $mat4);
}

function inter_bspline( array &$quad, string $pfx ) : void
{
	printf("== inter_bspline( %s )\n", $pfx);
	$mod  = 1.0 / 6;
	$mat4 = [
		 1 ,  4 ,  1 , 0 ,
		-3 ,  0 ,  3 , 0 ,
		 3 , -6 ,  3 , 0 ,
		-1 ,  3 , -3 , 1 ,
	];
	for ( $i=0; $i < 16; $i++ )
		$mat4[$i] *= $mod;
	splice_bezier($quad, "$pfx-bspline", $mat4);
}

function inter_hermite( array &$quad, string $pfx ) : void
{
	printf("== inter_hermite( %s )\n", $pfx);
	$mat4 = [
		 1 ,  0 ,  0 ,  0 ,
		 0 ,  1 ,  0 ,  0 ,
		-3 , -2 ,  3 , -1 ,
		 2 ,  1 , -2 ,  1 ,
	];
	splice_bezier($quad, "$pfx-hermite", $mat4);
}
//////////////////////////////
function inter_linear( array &$quad, string $pfx ) : void
{
	printf("== inter_linear( %s )\n", $pfx);
	$grid = new pixlines;
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
	clutfile::save($pfx.'-linear', $img);
}

function pix_inter( array &$quad, string $pfx ) : void
{
	inter_linear ($quad, $pfx);
	inter_catmull($quad, $pfx);
	inter_bezier ($quad, $pfx);
	inter_bspline($quad, $pfx);
	inter_hermite($quad, $pfx);
}
//////////////////////////////
$quad = [
	[  0,-80],
	[ 80,  0],
	[  0, 80],
	[-80,  0],
];
pix_inter($quad, 'pix_inter/rotate0');

$quad = [
	[ 80, 80],
	[ 80,-80],
	[-80,-80],
	[-80, 80],
];
pix_inter($quad, 'pix_inter/rotate45');

$quad = [
	[ 80, 80],
	[-80,-80],
	[-80, 80],
	[ 80,-80],
];
pix_inter($quad, 'pix_inter/twist45');
