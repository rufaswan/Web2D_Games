<?php
function dot_product( $base, $V1, $V2 )
{
	// The direct, scalar, or dot product of two vectors
	// dot product is normalized from $base as 0,0
	$V1[0] -= $base[0];
	$V1[1] -= $base[1];
	$V1[2] -= $base[2];
	$V2[0] -= $base[0];
	$V2[1] -= $base[1];
	$V2[2] -= $base[2];

	// dot(a,a) = 1
	// s = axbx + ayby
	// s = axbx + ayby + azbz
	$dot = $V1[0]*$V2[0] + $V1[1]*$V2[1] + $V1[2]*$V2[2];
	return $dot;
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

	FAILED :
		copyquad() triangle-base looping
		REASON : WORSE than current

	// opposite corners
	$w02 = $qdst['quad'][2][0] - $qdst['quad'][0][0];
	$h02 = $qdst['quad'][2][1] - $qdst['quad'][0][1];

	$x02 = 0;
	$y02 = 0;
		$w102 = 0.5 / ($w02+1);
		$h102 = 0.5 / ($h02+1);
	while (1)
	{
		if ( ( $x02 > 1.0 || $x02 < -1.0 ) && ( $y02 > 1.0 || $y02 < -1.0 ) )
			break;

		$x02p = $qdst['quad'][0][0] + ($x02 * $w02);
		$y02p = $qdst['quad'][0][1] + ($y02 * $h02);


		// line 0,2 to 1
		$wx1 = $qdst['quad'][1][0] - ($qdst['quad'][0][0] + $x02 * $w02);
		$hx1 = $qdst['quad'][1][1] - ($qdst['quad'][0][1] + $y02 * $h02);

		$xx1 = 0;
		$yx1 = 0;
			$w1x1 = 0.5 / ($wx1+1);
			$h1x1 = 0.5 / ($hx1+1);
		while (1)
		{
			if ( ( $xx1 > 1.0 || $xx1 < -1.0 ) && ( $yx1 > 1.0 || $yx1 < -1.0 ) )
				break;

			$bx = $x02p + ($xx1 * $wx1);
			$by = $y02p + ($yx1 * $hx1);

			// (t,r,s) = M^-1 * (x,y,1)
			//  sx,sy  = t/s,r/s
			$t = matrix_multi31($Minv, [$bx,$by,1]);

			// ERROR : $t[2] divide by zero
			if ( $t[2] !== 0.0 )
			{
				$t[2] = 1 / $t[2];
				$sx = (int)($qsrc['base'][0] + $t[0] * $t[2] - 0);
				$sy = (int)($qsrc['base'][1] + $t[1] * $t[2] - 0);
				$dx = (int)($qdst['base'][0] + $bx);
				$dy = (int)($qdst['base'][1] + $by);
				//trace("xx1  sx %d,%d  dx %d,%d\n", $sx, $sy, $dx, $dy);
				updatepix($pix, $byte, $sx, $sy, $dx, $dy);
			}

			if ( abs($xx1) < abs($yx1) )
				$xx1 += $w1x1;
			else
				$yx1 += $h1x1;
		} // while (1)


		// line 0,2 to 3
		$wx3 = $qdst['quad'][3][0] - ($qdst['quad'][0][0] + $x02 * $w02);
		$hx3 = $qdst['quad'][3][1] - ($qdst['quad'][0][1] + $y02 * $h02);

		$xx3 = 0;
		$yx3 = 0;
			$w1x3 = 0.5 / ($wx3+1);
			$h1x3 = 0.5 / ($hx3+1);
		while (1)
		{
			if ( ( $xx3 > 1.0 || $xx3 < -1.0 ) && ( $yx3 > 1.0 || $yx3 < -1.0 ) )
				break;

			$bx = $x02p + ($xx3 * $wx3);
			$by = $y02p + ($yx3 * $hx3);

			// (t,r,s) = M^-1 * (x,y,1)
			//  sx,sy  = t/s,r/s
			$t = matrix_multi31($Minv, [$bx,$by,1]);

			// ERROR : $t[2] divide by zero
			if ( $t[2] !== 0.0 )
			{
				$t[2] = 1 / $t[2];
				$sx = (int)($qsrc['base'][0] + $t[0] * $t[2] - 0);
				$sy = (int)($qsrc['base'][1] + $t[1] * $t[2] - 0);
				$dx = (int)($qdst['base'][0] + $bx);
				$dy = (int)($qdst['base'][1] + $by);
				//trace("xx3  sx %d,%d  dx %d,%d\n", $sx, $sy, $dx, $dy);
				updatepix($pix, $byte, $sx, $sy, $dx, $dy);
			}

			if ( abs($xx3) < abs($yx3) )
				$xx3 += $w1x3;
			else
				$yx3 += $h1x3;
		} // while (1)


		if ( abs($x02) < abs($y02) )
			$x02 += $w102;
		else
			$y02 += $h102;
	} // while (1)

function vector_dump( $V, $name )
{
	if ( defined("NO_TRACE") )
		return;
	trace("$name ");
	trace(" xyz (%4d , %4d , %4.2f)", $V[0], $V[1], $V[2]);

	// ERROR : $V[2] divide by zero
	if ( $V[2] == 0.0 )
	{
		$V[2] = 1 / $V[2];
		trace("  xy (%4d , %4d)", $V[0]*$V[2], $V[1]*$V[2]);
	}
	trace("\n");
	return;
}

function vector_box( $vector )
{
	$x1 = PHP_INT_MAX;
	$y1 = PHP_INT_MAX;
	$x2 = -PHP_INT_MAX;
	$y2 = -PHP_INT_MAX;
	for ( $i=0; $i < 4; $i++ ) // A B C D
	{
		if ( $x1 > $vector[$i][0] )  $x1 = (int)floor($vector[$i][0]);
		if ( $y1 > $vector[$i][1] )  $y1 = (int)floor($vector[$i][1]);
		if ( $x2 < $vector[$i][0] )  $x2 = (int)ceil ($vector[$i][0]);
		if ( $y2 < $vector[$i][1] )  $y2 = (int)ceil ($vector[$i][1]);
	} // for ( $i=0; $i < 4; $i++ )
	return [$x1, $y1, $x2, $y2];
}

