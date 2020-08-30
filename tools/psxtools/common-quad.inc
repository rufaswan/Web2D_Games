<?php
// 3D texture quad-rendering + distortion related

function vertex_type( $x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4 )
{
	// quad vertex
	//  -| 12 43  |- 21 34  || 14 41  -- 23 32
	//  -| 43 12  |- 34 21  -- 23 32  || 14 41
	// normal
	//  0 = 12  40 = 41  80 = 34  c0 = 23
	//      43       32       21       14
	// reversed
	//  0 = 21  40 = 32  80 = 43  c0 = 14
	//      34       41       12       23

/*
	$fact_x = $w / $pix['src']['w'];
	$fact_y = $h / $pix['src']['h'];
	$rot = hex_rotate( $x4, $y4, $x1, $y1 );
	$dx = ($x3 > $x1) ? $x1 : $x3;
	$dy = ($y3 > $y1) ? $y1 : $y3;
	if ( $rot < 0x80 )
	{
		if ( $x2 < $x1 || $y2 < $y1 )
			$flip = true;
	}
	else
	{
		if ( $x1 < $x2 || $y1 < $y2 )
			$flip = true;
	}
	$iu = 1 / $w;
	$iv = 1 / $h;
*/
	$ret = array();
	return $ret;
}

function copyquad( &$pix, $byte=1 )
{
	printf("copyquad() : %4d,%4d  %4d,%4d  %4d,%4d  %4d,%4d\n",
		$pix['vertex'][0][0], $pix['vertex'][0][1],
		$pix['vertex'][1][0], $pix['vertex'][1][1],
		$pix['vertex'][2][0], $pix['vertex'][2][1],
		$pix['vertex'][3][0], $pix['vertex'][3][1]
	);

	return;
}

function hex_rotate( $x1, $y1, $x2, $y2 )
{
	$dx = $x1 - $x2;
	$dy = $y1 - $y2;
	$rot = atan( $dx/$dy ) * 0x80 / pi();
	if ( $dy < 0 )  $rot += 0x80;
	else
	if ( $dx < 0 )  $rot += 0x100;

	// 20  0  e0
	//    \|/
	// 40 -*- c0
	//    /|\
	// 60 80  a0
	// $rot from atan() is reverse-clockwise
	$rot = (int)(0x100 - $rot) & BIT8;
	return $rot;
}
