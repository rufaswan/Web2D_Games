<?php
// 3D texture quad-rendering + distortion related

function int_unique()
{
	$int = array();
	foreach ( func_get_args() as $i )
	{
		if ( array_search($i, $int) === false )
			$int[] = $i;
	}
	sort($int);
	return $int;
}

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
	$vx = int_unique($x1, $x2, $x3, $x4);
	$vy = int_unique($y1, $y2, $y3, $y4);
	$cvx = count($vx);
	$cvy = count($vy);
	$w = abs($vx[$cvx-1] - $vx[0]);
	$h = abs($vy[$cvy-1] - $vy[0]);

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
	if ( ($x1 == $x4) && ($x2 == $x3) && ($y1 == $y2) && ($y3 == $y4) )
	{
		// 1243  4312  2134  3421
		if ( $x1 < $x2 )
		{
			// 1243  4312
			if ( $y2 < $y3 )
				$ret = array("1243", $x1, $y1, $x3, $y3);
			else
				$ret = array("4312", $x4, $y4, $x2, $y2);
		}
		else
		{
			// 2134  3421
			if ( $y2 < $y3 )
				$ret = array("2134", $x2, $y2, $x4, $y4);
			else
				$ret = array("3421", $x3, $y3, $x1, $y1);
		}
	}
	if ( ($x1 == $x2) && ($x3 == $x4) && ($y1 == $y4) && ($y2 == $y3) )
	{
		// 1423  4132  2314  3241
		if ( $y1 < $y2 )
		{
			// 1423  4132
			if ( $x2 < $x3 )
				$ret = array("1423", $x1, $y1, $x3, $y3);
			else
				$ret = array("4132", $x4, $y4, $x2, $y2);
		}
		else
		{
			// 2314  3241
			if ( $x2 < $x3 )
				$ret = array("2314", $x2, $y2, $x4, $y4);
			else
				$ret = array("3241", $x3, $y3, $x1, $y1);
		}
	}
	if ( empty($ret) )
		trigger_error("vertex_type() corners not parallel\n", E_USER_ERROR);
	return $ret;
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
