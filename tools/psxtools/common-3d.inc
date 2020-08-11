<?php
// 3D texture quad-rendering + distortion related

function vertex_type( $x1, $y1, $x2, $y2, $x3, $y3, $x3, $y3 )
{
	// quad vertex
	//  -| 12 43  |- 21 34  || 14 41  -- 23 32
	//  -| 43 12  |- 34 21  -- 23 32  || 14 41
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

	$rot = (int)$rot & BIT8;
	// 20  0  e0
	//    \|/
	// 40 -*- c0
	//    /|\
	// 60 80  a0
	return $rot;
}
