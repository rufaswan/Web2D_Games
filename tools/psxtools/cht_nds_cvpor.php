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

//////////////////////////////
function cvnds_dos_mon( $fp, $pos )
{
	printf("= cvnds_dos_mon( %x )\n", $pos);
	$siz = 118 * 0x24; // 0x1098 = 116+2 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sr = ZERO; // in-game max = 0x40 , boss = 0
	$dr = BYTE; // in-game max = 0x08
	for ( $i=0; $i < 118; $i++ )
	{
		// $i >= 101 == bosses
		$p = $i * 0x24;

		// 0 1 2 3  4 5 6 7  8 9   a b   c d  e f
		// func     func     drop  drop  - -  hp
		// 10 11  12 13  14      15   16   17    18 19  1a    1b    1c 1d 1e 1f
		// mp     exp    rarity  atk  def  rate  -  -   soul  cost  weak
		// 20 21 22 23
		// half
		//
		// soul rarity (get++) , stars (rare--) , zero = boss/100%
		// drop 1 = (rate * 3) / (1024 - LUCK)
		// drop 2 = 256 / (1024 - LUCK)
		//
		str_update($mon, $p+0x12, "\x10\x27"); // experience points
		str_update($mon, $p+0x14, "\x00"); // soul drop rate
		str_update($mon, $p+0x17, "\xff"); // item 1 drop rate
	}
	fp_update($fp, $pos, $mon);
	return;
}

function cvnds_por_mon( $fp, $pos )
{
	printf("= cvnds_por_mon( %x )\n", $pos);
	$siz = 155 * 0x20; // 0x1360 = 155 mon * 32 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sp = "\x63"; // in-game max = 0x63
	$dr = "\x80"; // in-game max = 0x32
	for ( $i=0; $i < 155; $i++ )
	{
		// $i >= 129 == bosses
		$p = $i * 0x20;

		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  sp  hp
		// 10 11 12  13   14   15   16    17    18 19 1a 1b  1c 1d 1e 1f
		// exp       atk  def  int  rate  rate  weak         half
		//
		// drop rate (get++) , stars (rare--)
		// weak/half (all=FF 07)
		//
		str_update($mon, $p+0x0d, "\x63"); // skill points/jonathan
		str_update($mon, $p+0x10, "\x10\x27"); // experience points
		str_update($mon, $p+0x16, "\xff"); // item 1 drop rate
		str_update($mon, $p+0x17, "\xff"); // item 2 drop rate
	}
	fp_update($fp, $pos, $mon);
	return;
}

function cvnds_ooe_mon( $fp, $pos )
{
	printf("= cvnds_ooe_mon( %x )\n", $pos);
	$siz = 121 * 0x24; // 0x1104 = 121 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$gr = "\x64"; // in-game max = 0x64
	$dr = "\xff"; // in-game max = 0x0f
	for ( $i=0; $i < 121; $i++ )
	{
		// $i >= 108 == bosses
		$p = $i * 0x24;

		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  ap  hp
		// 10 11  12 13  14 15  16      17   18   19   1a    1b    1c 1d 1e 1f
		// exp    -  -   glyph  rarity  atk  def  int  rate  rate  weak
		// 20 21 22 23
		// half
		//
		// glyph rarity (get++) , stars (rare--)
		// drop  rate   (get++) , stars (rare--)
		//
		str_update($mon, $p+0x10, "\x10\x27"); // experience points
		str_update($mon, $p+0x16, "\x64"); // gryph drop rate
		str_update($mon, $p+0x1a, "\xff"); // item 1 drop rate
		str_update($mon, $p+0x1b, "\xff"); // item 2 drop rate
	}
	fp_update($fp, $pos, $mon);
	return;
}
//////////////////////////////
function cvnds( $fname )
{
	$fp = fopen($fname, "rb+");
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0x180);

	// RAM address check
	if ( $head[0x27] != "\x02" )  return;
	if ( $head[0x2b] != "\x02" )  return;
	if ( $head[0x37] != "\x02" )  return;
	if ( $head[0x3b] != "\x02" )  return;

	$code = substr($head, 0x0c, 4);
	$vers = ord( $head[0x1e] );

	$mgc = sprintf("%s_%d", $code, $vers);
	switch ( $mgc )
	{
		case 'ACVJ_1':  return cvnds_dos_mon($fp, 0x7cca8);
		case 'ACVE_0':  return cvnds_dos_mon($fp, 0x7ccac);
		case 'ACVP_0':  return cvnds_dos_mon($fp, 0x7d984);

		case 'ACBJ_0':  return cvnds_por_mon($fp, 0xc25f0);
		case 'ACBJ_1':  return cvnds_por_mon($fp, 0xb67ac);
		case 'ACBE_0':  return cvnds_por_mon($fp, 0xc2568);
		case 'ACBP_0':  return cvnds_por_mon($fp, 0xc32f8);

		case 'YR9J_0':  return cvnds_ooe_mon($fp, 0xba3d4);
		case 'YR9E_0':  return cvnds_ooe_mon($fp, 0xba364);
		case 'YR9P_0':  return cvnds_ooe_mon($fp, 0xdad20);

		default:
			printf("UNKNOWN %s = %s\n", $mgc, $fname);
			return;
	}

	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
function smt_sj_demon( $fp, $pos )
{
	printf("= smt_sj_demon( %x )\n", $pos);
	// BMTE 23b5410-23c4990
	// $siz = 491 * 0x80; // 0xf580 = + demon * 128 bytes
	// if +0x6c == '0' SKIP dummy
	return;
}

function rockzx
	// RAM 214f86c  lives
	//   201c660  ldrb  r0[ 8], 164(r2[ 214f708])
	//   20212dc  ldrb  r0[ 8], 164(r0[ 214f708])
	//   2044602  ldrb  r2[ 8], 164(r0[ 214f708])
	//   2044616  strb  r3[ 8], 164(r0[ 214f708])
	//     d0 1a -> c0 46
	//   20441e0  ldr   r0[ 8], 0(r5[ 216008c])
	//   20441e4  str   r0[ 8], 0(r2[ 214f86c])
	// RAM 214f870  energy crystal (need max=1000)

	// RAM 214f879  sub-tank 1 energy (max=1e)
	// RAM 214f87a  sub-tank 2 energy (max=1e)
	// RAM 214f87b  sub-tank 3 energy (max=1e)
	// RAM 214f87c  sub-tank 4 energy (max=1e)

function rockzxa
	// RAM 2168b58  lives
	// RAM 2168b5c  energy crystal (need max=500)
	// RAM 2168b68  sub-tank 1 energy (max=1e)
 */
