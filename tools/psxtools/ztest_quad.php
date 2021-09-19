<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
require "common-quad.inc";

///// MATRIX TEST /////
	$M = array(3,0,2, 2,0,-2, 0,1,1);
		matrix_dump($M, "M");
	$Minv = matrix_inverse($M);
		matrix_dump($Minv, "Minv");
	$I = matrix_multi33($M, $Minv);
		matrix_dump($I, "I");

///// QUAD AREA TEST /////
	// Lunar 2 sysspr.pck 0365-0366
	//   0, 0  10,13    0, 0  11,12
	//  13,10  23,23   12,11  23,23
	$quad = array(
		array(23, 0,1),
		array(17,17,1),
		array( 0,23,1),
		array( 7, 7,1),
	);

	$q1 = triad_area($quad[0], $quad[1], $quad[2]); // ABC
	$q2 = triad_area($quad[0], $quad[3], $quad[2]); // ADC
	$qsz = $q1 + $q2;

	for ( $y=0; $y < 26; $y++ )
	{
		for ( $x=0; $x < 26; $x++ )
		{
			$xy = array($x,$y,1);
			$q1 = triad_area($xy, $quad[0], $quad[1]); // pAB
			$q2 = triad_area($xy, $quad[1], $quad[2]); // pBC
			$q3 = triad_area($xy, $quad[2], $quad[3]); // pCD
			$q4 = triad_area($xy, $quad[3], $quad[0]); // pDA

			// if $xy is outside , $q will be bigger than $r
			if ( ($q1+$q2+$q3+$q4) > $qsz )
			{
				if ( ($y%5) == 0 )
					echo ( ($x%5) == 0 ) ? '+' : '-';
				else
					echo ( ($x%5) == 0 ) ? '|' : ' ';
				continue;
			}

			if ( $xy == $quad[0] )
				echo 'A';
			else
			if ( $xy == $quad[1] )
				echo 'B';
			else
			if ( $xy == $quad[2] )
				echo 'C';
			else
			if ( $xy == $quad[3] )
				echo 'D';
			else
				echo '#';

		} // for ( $x=0; $x < 25; $x++ )
		echo "\n";

	} // for ( $y=0; $y < 25; $y++ )

///// x /////
/*
	FAILED :
		0 <= dot(AB,AM) <= dot(AB,AB) && 0 <= dot(BC,BM) <= dot(BC,BC)
		REASON : went crazy when one corner is over 90 degree

	triad_area TEST
		a 10,20
		b 20,30
		c 30,10

		// ax(by-cy) + bx(cy-ay) + cx(ay-by)
		10(30-10) + 20(10-20) + 30(20-30)
		= 10*20 + 20*-10 + 30*-10
		= 200 + -200 + -300
		= -300 / 2
		= 150

		// GIMP
		21*21 - 11*11/2 - 11*21/2 - 21*11/2
		= 441 - 60.5 - 115.5 - 115.5
		= 149.5

 */
