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

// Xenogears JP
function BISLPS_01160( &$file, $off )
{
	// char stats
	for ( $i=0; $i < 11; $i++ )
	{
		$p = $off + 0x390 + ($i * 0xa4);
		int_update($file, $p + 0x4c, 990, 2); // HP
		int_update($file, $p + 0x4e, 999, 2); // max HP
		int_update($file, $p + 0x50,  90, 2); // EP
		int_update($file, $p + 0x52,  99, 2); // max EP

		int_update($file, $p + 0x55,  99, 1); // deathblow learning

		int_update($file, $p + 0x58, 200, 1); // atk
		int_update($file, $p + 0x59, 200, 1); // def
		int_update($file, $p + 0x5a,  16, 1); // agi
		int_update($file, $p + 0x5b, 200, 1); // eth
		int_update($file, $p + 0x5c, 200, 1); // eth def
		//int_update($file, $p + 0x5d,   0, 1); // base def
		int_update($file, $p + 0x5e,  99, 1); // hit
		int_update($file, $p + 0x5f,  99, 1); // evd

		//int_update($file, $p + 0x62,  99, 1); // Level

		int_update($file, $p + 0x74, 0x7c, 1); // speed shoes
		int_update($file, $p + 0x75, 0x6e, 1); // holy pendant
		int_update($file, $p + 0x76, 0x7e, 1); // ether doubler
	} // for ( $i=0; $i < 11; $i++ )

	// gear stats
	for ( $i=0; $i < 19; $i++ )
	{
		$p = $off + 0xa9c + ($i * 0x3c);
		int_update($file, $p +  2,  0x11, 1); // xeno 30400
		int_update($file, $p +  3,  0x36, 1); // omega 100

		int_update($file, $p +  8,  0x38, 1); // z alloy 80/30
		int_update($file, $p +  9,  0x43, 1); // magnetic coat
		int_update($file, $p + 10,  0x43, 1); // magnetic coat
		int_update($file, $p + 11,  0x43, 1); // magnetic coat
	} // for ( $i=0; $i < 17; $i++ )

	// char weapons
	for ( $i=0; $i < 0x48; $i++ )
	{
		int_update($file, $off + 0x163c + $i,   50, 1);
		int_update($file, $off + 0x16a0 + $i, $i+1, 1);
	}

	// char armors
	for ( $i=0; $i < 0x8f; $i++ )
	{
		int_update($file, $off + 0x1704 + $i,   50, 1);
		int_update($file, $off + 0x17cc + $i, $i+1, 1);
	}

	// char items
	for ( $i=0; $i < 0x6c; $i++ )
	{
		int_update($file, $off + 0x1894 + $i,   50, 1);
		int_update($file, $off + 0x192a + $i, $i+1, 1);
	}

	// gear weapons
	for ( $i=0; $i < 0x48; $i++ )
	{
		int_update($file, $off + 0x19c0 + $i,   50, 1);
		int_update($file, $off + 0x1a24 + $i, $i+1, 1);
	}

	// gear armors
	for ( $i=0; $i < 0x68; $i++ )
	{
		int_update($file, $off + 0x1a88 + $i,   50, 1);
		int_update($file, $off + 0x1b1e + $i, $i+1, 1);
	}

	// update checksum
	$sum = lbu_sum_add($file, $off + 0x100, $off + 0x1fff);
	int_update($file, $off + 0x1fff, $sum, 1);
	return;
}

// Xenogears US
function BASLUS_00664( &$file, $off )  { return BISLPS_01160($file, $off); }
