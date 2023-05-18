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

function galpani_decode( &$file, $bits )
{
	trace("== begin sub_2436c()\n");
	$pos = 0;
	$end = strlen($file);

	$dec = str_repeat(ZERO, 0x1000);
	$dec .= $file[$pos];
		$pos++;

	while ( $pos < $end )
	{
		$b1 = ord( $file[$pos+0] );

		// 80-ff
		if ( $b1 & 0x80 )
		{
			$b2 = ord( $file[$pos+1] );
				$pos += 2;
			$ref = ($b1 << 8) | $b2; // eax
				$ref &= $bits[0];

			if ( $ref > 3 )
			{
				// fedcba98 76543210
				// -ppppprr rrrrrrrr
				// -pppprrr rrrrrrrr
				// -ppprrrr rrrrrrrr
				$op = $b1 >> $bits[1]; // ecx
					$op &= $bits[2];

				// $op switch cases
				// 0 = 2, 4 = 3, 8 = 4 ... 7c = 33
				$len = ($op / 4) + 2;
				$p = strlen($dec) - 1 - $ref;
				//trace("80>3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$dec .= $dec[$p+$i];
			}
			else
			{
				// 76543210
				// -lllll--
				// -llll---
				// -lll----
				$len = $b1 >> $bits[7]; // ecx
					$len &= $bits[8];
					$len += 2;
				$p = strlen($dec) - 1 - $ref;
				//trace("80-3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$dec .= $dec[$p+$i];
			}
			continue;
		} // if ( $b1 & 0x80 )

		// 40-7f
		if ( $b1 & 0x40 )
		{
			// 76543210 fedcba98 76543210
			// --llllll llllllrr rrrrrrrr
			// --llllll lllllrrr rrrrrrrr
			// --llllll llllrrrr rrrrrrrr
			$b2 = ord( $file[$pos+1] );
			$b3 = ord( $file[$pos+2] );
				$pos += 3;
			$ref = ($b2 << 8) | $b3; // edx
			$len = ($b1 << 8) | $b2; // ecx
				$ref &=  $bits[3];
				$len >>= $bits[4];
				$len &=  $bits[5];
				$len +=  $bits[6];

			$p = strlen($dec) - 1 - $ref;
			//trace("40  REF -%d LEN %d\n", $ref, $len);

			for ( $i=0; $i < $len; $i++ )
				$dec .= $dec[$p+$i];

			continue;
		} // if ( $b1 & 0x40 )

		// 3f
		if ( $b1 == 0x3f )
		{
			$b1 = ord( $file[$pos+1] );

			if ( $b1 == 0 )
			{
				//trace("3f 00  end\n");
				goto done;
			}
			else
			{
				$ed = strlen($dec) - 1;
				$b2 = ord( $dec[$ed] );
				$by = ($b1 + $b2) & BIT8;

				//trace("3f  DIFF %2x + %2x = %2x\n", $b1, $b2, $by);
				$dec .= chr($by);
				$pos += 2;
			}
			continue;
		} // if ( $b1 == 0x3f )

		// 00-3e
		$b1 = $b1 - 0x1f;

		$ed = strlen($dec) - 1;
		$b2 = ord( $dec[$ed] );
		$by = ($b1 + $b2) & BIT8;

		//trace("1f  DIFF %2x + %2x = %2x\n", $b1&BIT8, $b2, $by);
		$dec .= chr($by);
		$pos++;
	} // while ( $pos < $end )
done:
	trace("=== end sub_2436c() ===\n");
	return substr($dec, 0x1000);
}

function galpani_dectype( &$sub, $type )
{
	$bits = array(
		//         0       1   2      3       4   5       6      7   8
		//         80>3               40                         80-3
		1 => array(0x3ff , 0 , 0x7c , 0x3ff , 2 , 0xfff , 0x22 , 2 , 0x1f), // sub_2436c
		2 => array(0x7ff , 1 , 0x3c , 0x7ff , 3 , 0x7ff , 0x12 , 3 , 0x0f), // sub_24910
		3 => array(0xfff , 2 , 0x1c , 0xfff , 4 , 0x3ff , 0x0a , 4 , 0x07), // sub_24b78
	);
	if ( ! isset($bits[$type]) )
		return php_error('UNKNOWN bits type = %x', $type);

	return galpani_decode($sub, $bits[$type]);;
}
