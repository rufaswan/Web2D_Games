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

//////////////////////////////

// Akumajou Dracula X JP
function bislpm_86023( &$file, $off )
{
	// items amount
	for ( $i = 0x25f; $i < 0x361; $i++ )
		int_update($file, $off + $i, 0x55, 1);

	// item sub id
	for ( $i=0; $i < 0xa8; $i++ )
		int_update($file, $off + 0x362 + $i, $i+1, 1);
	for ( $i=0; $i < 0x59; $i++ )
		int_update($file, $off + 0x40b + $i, $i+1, 1);

	// 474/478  HP
	// 47c/480  Heart
	// 484/488  MP
	// 48c + 49c = 4ac  STR
	// 490 + 4a0 = 4b0  CON
	// 494 + 4a4 = 4b4  INT
	// 498 + 4a8 = 4b8  LCK
	int_update($file, $off + 0x4bc, 1, 1); // Level
	// 4c0  EXP
	int_update($file, $off + 0x4c4, 500000, 4); // Gold
	// 4c8  Beat/Kill

	// 504:508:50c.510  Time (hh:mm:ss.ff)

	// Familiar
	for ( $i=0; $i < 7; $i++ )
	{
		// +00  Familiar Level
		// +04  Familiar EXP
		// +08
		$p = $off + 0x518 + ($i * 0xc);
		int_update($file, $p + 4, 9899, 2);
	}

	// Time Attack , 0x27fc = 10236 = 1:02:36
	// 5e8  Richter defeat Dracula
	// 5ec  Defeat Olrox
	// 5f0  Defeat Doppelganger LV 10
	// 5f4  Defeat Legion/Granfaloon
	// 5f8  Defeat Minotaur + Werewolf
	// 5fc  Defeat Scylla
	// 600  Defeat Berigan + Gaibon
	// 604  Defeat Hippogryph
	// 608  Defeat Beezelbub
	// 60c  Defeat Succubus
	// 610  Defeat Malphas/Karasuman
	// 614  Defeat Fake Ralph/Trevor + Fake Grant + Fake Sypha
	// 618  Defeat Death
	// 61c  Defeat Cerberos
	// 620  Defeat Richter Belmont
	// 624  Defeat Medusa
	// 628  Defeat Franken/The Creature
	// 62c  Defeat Lesser Demon
	// 630  Defeat Doppelganger LV 40
	// 634  Defeat Mummy/Akmodan II
	// 638  Defeat Darkwing Bat
	// 63c  Defeat Galamoth
	// 640  Last Save Point
	// 644  Meet Death (loses Alucard equipments)
	// 648  Meet Maria (get Holy Glass)
	// 64c  Meet Librarian
	// 650  Meet Maria

	// re-fight Bosses
	for ( $i = 0x5ec; $i < 0x640; $i += 4 )
	{
		if ( $i === 0x620 )
			continue;
		int_update($file, $off + $i, 0, 4);
	}

	// no checksum
	return;
}

// Castlevania SOTN US
function baslus_00067( &$file, $off )  { return bislpm_86023($file, $off); }

//////////////////////////////

// Xenogears JP
function bislps_01160( &$file, $off )
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
		int_update($file, $off + 0x163c + $i, 0x55, 1);
		int_update($file, $off + 0x16a0 + $i, $i+1, 1);
	}

	// char armors
	for ( $i=0; $i < 0x8f; $i++ )
	{
		int_update($file, $off + 0x1704 + $i, 0x55, 1);
		int_update($file, $off + 0x17cc + $i, $i+1, 1);
	}

	// char items
	for ( $i=0; $i < 0x6c; $i++ )
	{
		int_update($file, $off + 0x1894 + $i, 0x55, 1);
		int_update($file, $off + 0x192a + $i, $i+1, 1);
	}

	// gear weapons
	for ( $i=0; $i < 0x48; $i++ )
	{
		int_update($file, $off + 0x19c0 + $i, 0x55, 1);
		int_update($file, $off + 0x1a24 + $i, $i+1, 1);
	}

	// gear armors
	for ( $i=0; $i < 0x68; $i++ )
	{
		int_update($file, $off + 0x1a88 + $i, 0x55, 1);
		int_update($file, $off + 0x1b1e + $i, $i+1, 1);
	}

	// update checksum
	$sum = lbu_sum_add($file, $off + 0x100, $off + 0x1fff);
	int_update($file, $off + 0x1fff, $sum, 1);
	return;
}

// Xenogears US
function baslus_00664( &$file, $off )  { return bislps_01160($file, $off); }

//////////////////////////////
