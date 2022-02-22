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
		$op = str2int($file, $op_off+0, 2);
		$ar = str2int($file, $op_off+2, 2);
			$op_off += 4;
		printf("%3d : %4x,%4x\n", $i, $op, $ar);

		global $gp_op;
		$gp_op[$op] = 1;
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

$gp_op = array();
for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

ksort($gp_op);
echo "== op_list ==\n";
foreach ( $gp_op as $k=>$v )
	printf("  %4x\n", $k);

/*
iconv -f cp932 -t utf8

drama momo 24 == eve_sumo_b24.kds
== eve_sumo_b24.kds : opcode ==
  0 :    90611  // char  set   9
  1 :   800a11  // pos   set  80,0
  2 :   180711  // anim  set  18
  3 :      911
  4 :    60711  // anim  set   6
  5 :      911
  6 :   150711  // anim  set   6
  7 :  1691200  // voice set 169
  8 :      911
  9 : 414e0100
== eve_sumo_b24.kds : sjis ==
  0 : く～まくまくま
  1 : え？　この歌？
くまたんが
歌ってたの
  2 : かわいいよね～

RAM 2359790  KDSB eve_sumo_b24.kds [jp 23597c8]
RAM 21ced80  FMBS momo01.mbs
RAM 22e28b0  FMBS momo01.mbs

[START]
	2026428  ldrb  r0[  11], 0(r4[ 23597a0])
	2026434  ldrb  r0[   6], 1(r4[ 23597a0])
	2026c3c  ldrh  r2[ 611], 0(r5[ 23597a0])
	2026c40  ldrh  r1[   9], 2(r5[ 23597a0])
	2026750  ldrb  r0[   6], 1(r4[ 23597a0])

	[MOMO LOADED]

	2026428  ldrb  r0[  11], 0(r4[ 23597a4])
	2026434  ldrb  r0[   a], 1(r4[ 23597a4])
	2026c3c  ldrh  r2[ a11], 0(r5[ 23597a4])
	2026c40  ldrh  r1[  80], 2(r5[ 23597a4])
	2026750  ldrb  r0[   a], 1(r4[ 23597a4])

	2026428  ldrb  r0[  11], 0(r4[ 23597a8])
	2026434  ldrb  r0[   7], 1(r4[ 23597a8])
	2026c3c  ldrh  r2[ 711], 0(r5[ 23597a8])
	2026c40  ldrh  r1[  18], 2(r5[ 23597a8])
	2026750  ldrb  r0[   7], 1(r4[ 23597a8])

	2026428  ldrb  r0[  11], 0(r4[ 23597ac])
	2026434  ldrb  r0[   9], 1(r4[ 23597ac])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597ac])
	2026c40  ldrh  r1[   0], 2(r5[ 23597ac])
	20264d8  ldrb  r0[  11], 0(r4[ 23597ac])
	2026750  ldrb  r0[   9], 1(r4[ 23597ac])

	2027bac  ldrsbne  r0[82], 0(r7[ 23597c8])
	20110b0  ldrb     r3[82], 0(r1[ 23597c8])
	201120c  ldrb     r3[82], 0(r0[ 23597c8])
	202819c  ldrsbne  r0[82], 0(r5[ 23597c8])
	2010fb8  ldr      r5[6081ad82], 0(r1[ 23597c8])

	ANIM=18,19
	JP=0
[INPUT WAIT]
	2026428  ldrb  r0[  11], 0(r4[ 23597b0])
		(r0 << 1c ) >> 1c // N=0 Z=0 C=0 -V
	2026434  ldrb  r0[   7], 1(r4[ 23597b0])
		r8  =  Z // (r0 & f) != 0
		// r0 -=  9
		// cmp r0, 0x11
		// -2 = N=1 Z=0 C=1 V=0
		// ls = (c==0 | Z==1)
		case r0 (slt 11)
			def:
			a-e 12-14: // command
				# 6498
				bl  2026b84(r9, r4, 0, 94(r9))
				break
			9 f-10:
				# 64b0
				bl  2026b84(r9, r4, a0(r7)+70(r9)*4, 94(r9))
				break
			15:
				# 64fc
				break
			16:
				# 651c
				break
			17:
				# 6560
				break
			18:
				# 65e0
				break
			19:
				# 6640
				break
			1a:
				# 6704
				break
			11:
				# 6748
				ldrsh  r0,  2(r4)
				str    r0, 94(r9)
				break
		esac
	2026c3c  ldrh  r2[ 711], 0(r5[ 23597b0])
	2026c40  ldrh  r2[   6], 2(r5[ 23597b0])
	2026750  ldrb  r0[   7], 1(r4[ 23597b0])
		case r0 (slt 1b)
			7-8 d 11-12 15-16 18-1b:
				# 67d0
				r8 = 0
			def:
			0-6 9-c e-10 13-14 17:
				break
		esac
		# 67d4
		if ( r8 == 0 )

	2026428  ldrb  r0[  11], 0(r4[ 23597b4])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597b4])
	2026c40  ldrh  r1[   0], 2(r5[ 23597b4])
	20264d8  ldrb  r0[  11], 0(r4[ 23597b4])
	2026750  ldrb  r0[   9], 1(r4[ 23597b4])

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

	2026428  ldrb  r0[   0], 0(r4[ 23597bc])
	2026434  ldrb  r0[  12], 1(r4[ 23597bc])
	2026c3c  ldrh  r2[1200], 0(r5[ 23597bc])
	2026c40  ldrh  r1[ 169], 2(r5[ 23597bc])
	2026750  ldrb  r0[  12], 1(r4[ 23597bc])

	2026428  ldrb  r0[  11], 0(r4[ 23597c0])
	2026434  ldrb  r0[   9], 1(r4[ 23597c0])
	2026c3c  ldrh  r2[ 911], 0(r5[ 23597c0])
	2026c40  ldrh  r1[   0], 2(r5[ 23597c0])
	20264d8  ldrb  r0[  11], 0(r4[ 23597c0])
	2026750  ldrb  r0[   9], 1(r4[ 23597c0])

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
[END]

21cedf8 => 21e543c
	20333f4  mov  r1, 30
	20333f8  ldr  r3[ 21e543c], 78(r2[ 21ced80])
	2033400  smlabb  r1[ 21e58ec], r12[19], r1[30], r3[ 21e543c]
		r1 = 19 * 30 + 21e543c
	20333cc  ldrsh  r12[19], c4(r0[ 21935f0])
		=> 21936b4

RAM 21936a4  ANIM ID
	2033020  strh     r1[   6], c4(r5[ 21935e0])
	20331f4  ldrsh    r3[   6], c4(r8[ 21935e0])
	2033284  ldrsh    r1[   6], c4(r6[ 21935e0])
	20333cc  ldrsh    rc[   6], c4(r0[ 21935e0])
	2033368  ldrsh    rc[   6], c4(r0[ 21935e0])
	20335dc  ldrsh    r1[   6], c4(r6[ 21935e0])
	203374c  ldrshne  r1[   6], c4(ra[ 21935e0])
	2033020  strh     r1[   8], c4(r5[ 21935e0])



op list
	1   00 10        // end        [arg 414e]
	-
	3   01           // loading slide out [arg ??? 1e/3c/5a]
	4   01           // loading slide in  [arg 5a]
	5   00           // screen  shake     [arg 414e]
	6   10 11 21     // char  set  [arg ID]
	7   10 11 20 21  // talk  anim [arg ID]
	8   10 11 20 21  // walk  anim [arg ID]
	9   11 21        // talk  text [arg -]
	a   10 11 20 21  // pos   set  [arg pixel]
	b   10 11 21     // walk  pos  [arg pixel]
	-
	d   10 11 20 21  // face  set  [arg 1=right -1=left]
	e   01           // bg    set  [arg ID]
	f   11 21        // think text [arg -]
	10  11 21        // navi  text [arg -]
	-
	12  00           // voice set  [arg ID]
	13  00           // music set  [arg ID]
	14  00 20        // title bg   [arg ID]
	15  10           // title text [arg -]
	16  10 20        // bath  text [arg -] *bath*
	17  11 21        // sound set  [arg ID] *bath*
	18  10 20        // var value  [arg value] *bath*
	19  00           // var ID     [arg ID] *bath*
	1a  00           // bg scroll  [arg 0=L2R 1=R2L] *prol_a01 epil_a02*
	1b  00           // [arg 38] *bath*
		xy
			x  0=bg     1=obj_left  2=obj_right
			y  0=cache  1=exec
		pixel  0=left edge  100=right edge
		char ID
			0  kuma-tan
			1  rabbi-tan
			2  neko-kun
			3  tora-neesan
			4  ushi-neesan
			5  saru-jii
			6  maguro
			7  twin monkey
			8  owner
			9  sumomo
		var ID
			1  popularity         [75-72-6f-6c-69-47-44-41-3e-3b]
			2  will               [75-72-6f-6c-69]
			3  talent music       [7e-7b-78-75]
			4  talent strenght    [81-7e-7b-75]
			5  talent skill       [7e-7b-78-75]
			6  talent show art    [81-7e-7b-78-75]
			7  kuma-player like   [a5-a2-9f-9c-99-5b-58-55-52]
			8  show performance   [5b-58-55-52]
			9  hunger             [47-44-41-3e-3b]
			a  sumomo-player like [56-50]



drama saru 38 == eve_saru_l06.kds
== eve_saru_l06.kds : opcode ==
  0 :    d1300  // bgm
  1 :    71400
  2 :     1510
  3 :   5a0401
  4 : ffff0e01
  5 :    50610  // char 1
  6 :      621  // char 2
  7 :   440a10  // pos  1
  8 :   bc0a21  // pos  2
  9 :   190710  // anim 1
 10 :   df0721  // anim 2
 11 :    10d10  // face 1
 12 : ffff0d21  // face 2
 13 :   1e0301  // title
 14 :    a0710  // anim 1
 15 :  11b0721  // anim 2
 16 : 414e0500  // screen shake
 17 :  1131200  // voice
 18 :      911
 19 :   190710  // anim 1
 20 :   eb0721  // anim 2
 21 :  1231200  // voice
 22 :      911
 23 :      911
 24 :   220710  // anim 1
 25 :  1090721  // anim 2
 26 :   271200  // voice
 27 :      921
 28 :   2b0711  // anim 1
 29 :  10d1200  // voice
 30 :      911
 31 :   5a0401
 32 : 414e0100
== eve_saru_l06.kds : sjis ==
  0 : 持ちネタなんですね
  1 : ゴフッ！
  2 : 持病のコプルニコス症候群が
発症したようじゃ…
  3 : 体内の活力が失われて次第に…
  4 : それは　まえにやったでしょ
  5 : …そうじゃったかの？

RAM 2384380  KDSB eve_saru_l06.kds [jp 2384414]
RAM 2  FMBS .mbs
RAM 2  FMBS .mbs
 */
