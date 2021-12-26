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

function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "KDSB" )
		return;

	$op_cnt = str2int($file,  8, 4);
	$jp_cnt = str2int($file, 12, 4);

	$op_off = 0x10;
	$jp_off = 0x10 + ($op_cnt * 4);

	echo "== $fname : opcode ==\n";
	for ( $i=0; $i < $op_cnt; $i++ )
	{
		$op = str2int($file, $op_off, 4);
			$op_off += 4;
		printf("%3d : %8x\n", $i, $op);
	} // for ( $i=0; $i < $op_cnt; $i++ )

	echo "== $fname : sjis ==\n";
	for ( $i=0; $i < $jp_cnt; $i++ )
	{
		$jp = substr0($file, $jp_off);
			$jp_off += strlen($jp) + 1;
		printf("%3d : %s\n", $i, $jp);
	} // for ( $i=0; $i < $jp_cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
iconv -f cp932 -t utf8

drama momo 24 == eve_sumo_b24.kds
== eve_sumo_b24.kds : opcode ==
  0 :    90611
  1 :   800a11
  2 :   180711
  3 :      911
  4 :    60711
  5 :      911
  6 :   150711
  7 :  1691200
  8 :      911
  9 : 414e0100
== eve_sumo_b24.kds : sjis ==
  0 : く～まくまくま
  1 : え？　この歌？
くまたんが
歌ってたの
  2 : かわいいよね～

RAM 2359790  KDSB eve_sumo_b24.kds
RAM 21ced80  FMBS momo01.mbs
RAM 22e28b0  FMBS momo01.mbs

[START]
	2026428  ldrb  r0[  11], 0(r4[ 23597a0])
	2026434  ldrb  r0[   6], 1(r4[ 23597a0])
	2026c3c  ldrh  r2[ 611], 0(r5[ 23597a0])
	2026c40  ldrh  r1[   9], 2(r5[ 23597a0])
	2026750  ldrb  r0[   6], 1(r4[ 23597a0])
		if ( r0 == 1b )

	[MOMO LOADED]

	2026428  ldrb  r0[  11], 0(r4[ 23597a4])
	2026434  ldrb  r0[   a], 1(r4[ 23597a4])
	2026c3c  ldrh  r2[ a11], 0(r5[ 23597a4])
	2026c40  ldrh  r1[  80], 2(r5[ 23597a4])
	2026750  ldrb  r0[   a], 1(r4[ 23597a4])
		if ( r0 == 1b )

	2026428  ldrb  r0[  11], 0(r4[ 23597a8])
	2026434  ldrb  r0[   7], 1(r4[ 23597a8])
	2026c3c  ldrh  r2[ 711], 0(r5[ 23597a8])
	2026c40  ldrh  r1[  18], 2(r5[ 23597a8])
	2026750  ldrb  r0[   7], 1(r4[ 23597a8])
		if ( r0 == 1b )

	2026428  ldrb  r0[  11], 0(r4[ 23597ac])
	2026434  ldrb  r0[   9], 1(r4[ 23597ac])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597ac])
	2026c40  ldrh  r1[   0], 2(r5[ 23597ac])
	20264d8  ldrb  r0[  11], 0(r4[ 23597ac])
	2026750  ldrb  r0[   9], 1(r4[ 23597ac])
		if ( r0 == 1b )

	2027bac  ldrsbne  r0[82], 0(r7[ 23597c8])
	20110b0  ldrb     r3[82], 0(r1[ 23597c8])
	201120c  ldrb     r3[82], 0(r0[ 23597c8])
	202819c  ldrsbne  r0[82], 0(r5[ 23597c8])
	2010fb8  ldr      r5[6081ad82], 0(r1[ 23597c8])

	ANIM=19
	JP=0
[INPUT WAIT]
	2026428  ldrb  r0[  11], 0(r4[ 23597b0])
		(r0 << 1c ) >> 1c // r0 & BIT4; sint4(r0)
	2026434  ldrb  r0[   7], 1(r4[ 23597b0])
		r8 = (ZF == 0) ? 0 : 1;
		r0 -=  9
		r0 == 11 // +NF +CF
	2026c3c  ldrh  r2[ 711], 0(r5[ 23597b0])
	2026c40  ldrh  r2[   6], 2(r5[ 23597b0])
	2026750  ldrb  r0[   7], 1(r4[ 23597b0])
		if ( r0 == 1b )

	2026428  ldrb  r0[  11], 0(r4[ 23597b4])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597b4])
	2026c40  ldrh  r1[   0], 2(r5[ 23597b4])
	20264d8  ldrb  r0[  11], 0(r4[ 23597b4])
	2026750  ldrb  r0[   9], 1(r4[ 23597b4])
		if ( r0 == 1b )

	2027bac  ldrsbne  r0[82], 0(r7[ 23597d7])
	20110b0  ldrb     r3[82], 0(r1[ 23597d7])
	201120c  ldrb     r3[82], 0(r0[ 23597d7])
	202819c  ldrsbne  r0[82], 0(r5[ 23597d7])
	2010ffc  ldrb     r3[82], 0(r1[ 23597d7])

	ANIM=6,8,7
	JP=1
[INPUT WAIT]
	2026428  ldrb  r0[  11], 0(r4[ 23597b8])
	2026434  ldrb  r0[   7], 1(r4[ 23597b8])
	2026c3c  ldrh  r2[ 711], 0(r5[ 23597b8])
	2026c40  ldrh  r1[  15], 2(r5[ 23597b8])
	2026750  ldrb  r0[   7], 1(r4[ 23597b8])
		if ( r0 == 1b )

	2026428  ldrb  r0[   0], 0(r4[ 23597bc])
	2026434  ldrb  r0[  12], 1(r4[ 23597bc])
	2026c3c  ldrh  r2[1200], 0(r5[ 23597bc])
	2026c40  ldrh  r1[ 169], 2(r5[ 23597bc])
	2026750  ldrb  r0[  12], 1(r4[ 23597bc])
		if ( r0 == 1b )

	2026428  ldrb  r0[  11], 0(r4[ 23597c0])
	2026434  ldrb  r0[   9], 1(r4[ 23597c0])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597c0])
	2026c40  ldrh  r1[   0], 2(r5[ 23597c0])
	20264d8  ldrb  r0[  11], 0(r4[ 23597c0])
	2026750  ldrb  r0[   9], 1(r4[ 23597c0])
		if ( r0 == 1b )

	2027bac  ldrsbne  r0[82], 0(r7[ 23597fc])
	20110b0  ldrb     r3[82], 0(r1[ 23597fc])
	201120c  ldrb     r3[82], 0(r0[ 23597fc])
	202819c  ldrsbne  r0[82], 0(r5[ 23597fc])
	2010fb8  ldr      r5[ed82a982], 0(r1[ 23597fc])

	ANIM=15,17,16
	JP=2
[INPUT WAIT]
	2026428  ldrb  r0[   0], 0(r4[ 23597c4])
	2026434  ldrb  r0[   1], 1(r4[ 23597c4])
	2026c3c  ldrh  r2[ 100], 0(r5[ 23597c4])
	2026c40  ldrh  r1[414e], 2(r5[ 23597c4])
	2026750  ldrb  r0[   1], 1(r4[ 23597c4])
		if ( r0 == 1b )
[END]

21cedf8 => 21e543c
	20333f4  mov  r1, 30
	20333f8  ldr  r3[ 21e543c], 78(r2[ 21ced80])
	2033400  smlabb  r1[ 21e58ec], r12[19], r1[30], r3[ 21e543c]
		r1 = 19 * 30 + 21e543c
	20333cc  ldrsh  r12[19], c4(r0[ 21935f0])
		=> 21936b4
 */
