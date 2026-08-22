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

// 2000004a  0
// 2000004a  +85,42800 (mini photo)
// 2000004a  +ad,56800 (photo)
// 2000004a  +4e3,271800 (npc front)
// 2000004a  +6bf,35f800
// 20000002  +1123,891800 (char bg)
// 20000042  +1123,891800 (^)
// 20000042  +114d,8a6800 (char upper body + weapon)
// 2000000a  +118e,8c7000 (char lower body)
// 20000042  +1fab,fd5800
// 2000000a  +1fbe,fdf000/+1fd6,feb000/+1fee,ff7000 (char side/back/front)
// 2000004a  +1fee,ff7000 (^)
// 2000000a  +2311,1188800/+231b,118d800/+2325,1192800 (cloth side/back/front)
// 20000042  +2311,1188800 (^)
// 2000004a  +2331,1198800 (cloth full + upper)
// 2000004a  +40cf,2067800 (cloth lower)
// 2000004a  +42f5,217a800/+42fe,217f000 (shoe/shield)

// merge.mrg
// loc 54db0         loc 594cc         loc 59804        loc 59c68        loc 59d14        loc 59e7c         loc 59f60        loc 5a4e0        loc 63b78
// sll  v0, 3 =   8  sll  t0, 1 =   2  sll  t0, 2 =  4  sll  s4, 1 =  2  sll  s1, 1 =  2  sll  s0, 5 =  32  sll  s3, 2 =  4  sll  t0, 4 = 16  sll  s2, 1 =  2
// addu v0    =   9  addu t0    =   3  addu t0    =  5  addu s4    =  3  addu s1    =  3  subu s0    =  31  addu s3    =  5  addu t0    = 17  addu s2    =  3
// sll  2     =  36  sll  2     =  12  sll  4     = 80  sll  3     = 24  sll  2     = 12  sll  2     = 124  sll  4     = 80  sll  1     = 34  sll  4     = 48
// subu v0    =  35  subu t0    =  11                   addu s4    = 25  addu s1    = 13                    addu s3    = 81                   addu s2    = 49
// sll  2     = 140  sll  3     =  88                                    sll  1     = 26
//                   subu t0    =  87
//                   sll  1     = 174
// = 6bf + i*8c      = 1fee + i*ae     = 2325 + i*50    = 40cf + i*19    = 42f5 + i*1a    = 1123 + i*7c     = 2331 + i*51    = 4e3 + i*22     = ad + i*31

//////////////////////////////
function loopsect( &$mrg, $st, $ed, $bk, $callback )
{
	printf("== loopsect( %x , %x , %x )\n", $st, $ed, $bk);
	if ( $bk == 0 || ! function_exists($callback) )
		return;

	// 0x800 === 1 << 11
	$id = 0;
	while ( $st < $ed )
	{
		$meta = substr($mrg, $st<<11, $bk<<11);
		$callback($meta);

		$fn = sprintf("mrg/%s/%04d.meta", $callback, $id);
		save_file($fn, $meta);

		$st += $bk;
		$id++;
	}
	return;
}

function mkr( $fname )
{
	$mrg = load_file("merge.mrg");
	if ( empty($mrg) )  return;

	$len = strlen($mrg);
	$ed = $len >> 11;
	loopsect($mrg, 0     , 0x85  , 0x85, "merge_0");
	loopsect($mrg, 0x85  , 0xad  , 0x0 , "merge_85");
	loopsect($mrg, 0xad  , 0x4e3 , 0x31, "merge_ad");
	loopsect($mrg, 0x4e3 , 0x6bf , 0x22, "merge_4e3");
	loopsect($mrg, 0x6bf , 0x1123, 0x8c, "merge_6bf");
	loopsect($mrg, 0x1123, 0x1fab, 0x7c, "merge_1123");
	loopsect($mrg, 0x114d, 0x1fab, 0x0 , "merge_114d");
	loopsect($mrg, 0x118e, 0x1fab, 0x0 , "merge_118e");
	loopsect($mrg, 0x1fab, 0x1fbe, 0x0 , "merge_1fab");
	loopsect($mrg, 0x1fbe, 0x2311, 0xae, "merge_1fbe");
	loopsect($mrg, 0x1fd6, 0x2311, 0xae, "merge_1fd6");
	loopsect($mrg, 0x1fee, 0x2311, 0xae, "merge_1fee");
	loopsect($mrg, 0x2311, 0x40cf, 0x50, "merge_2311");
	loopsect($mrg, 0x231b, 0x40cf, 0x50, "merge_231b");
	loopsect($mrg, 0x2325, 0x40cf, 0x50, "merge_2325");
	loopsect($mrg, 0x2331, 0x40cf, 0x51, "merge_2331");
	loopsect($mrg, 0x40cf, 0x42f5, 0x19, "merge_40cf");
	loopsect($mrg, 0x42f5, $ed   , 0x1a, "merge_42f5");
	loopsect($mrg, 0x42fe, $ed   , 0x1a, "merge_42fe");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkr( $argv[$i] );

/*
sub 8003099c
	(s2 >> 16) &  400 = mapdat.mrg
	(s2 >> 16) & 4000 = wa_mrg.mrg
	(s2 >> 16) & 2000 = merge.mrg
	(s2 >> 16) & 1000 = batdat.mrg
	(s2 >> 16) &  800 = picevt.mrg
	s2 & 8
	s2 |= 8000

	s2 &= not 2
	if ( s2 & ( 400 << 16) )  copy(800d8460, 800d83f0, 18)
	if ( s2 & (4000 << 16) )  copy(800d8478, 800d83f0, 18)
	if ( s2 & (2000 << 16) )  copy(800d8490, 800d83f0, 18)
	if ( s2 & (1000 << 16) )  copy(800d84a8, 800d83f0, 18)
	if ( s2 & ( 800 << 16) )  copy(800d84c0, 800d83f0, 18)
	if ( s2 & 8 )  has_pal

sub_8003099c("merge.mrg", 8016e000, sector+id*block)

VRAM
	linda body  128,384  48x120 (80,180  30x78)
	=> 80045820

8016208e
	80045710  lhu  s1[ c5f], 0(s6[8016208e])
	80045820  lhu  s1[2109], 2(s6[8016208e])
		v0 = ((s1 & e000) >> 9) + 10 // 70+10
			= 10+10 = 20
		v0 = ((s1 & 1c00) >> 6) + 10 // 70+10
			=  0+10 = 10
		v0 = (s1 & f0)
		v0 = (s1 & 0f)
	8004588c  lhu  s1[3086], 4(s6[8016208e])
		v0 = (s1 & 0f)
		v0 = (s1 & f0)
		v0 = (s1 & 8000) >> 8
		v0 = (s1 & 4000)
		v0 = (s1 & 2000)

change cloth
	ra/80059bd0  2311 + 19*51 = 2afa , 18* = 2aa9 (cloth)
	ra/80059d1c  42f5 +  0*1a = 42f5  (shoe)

	18/19 => RAM 800d83f0
		[16] RAM 800d8490 ->
		[16] RAM 8007b1ec  lbu v1, 0(a0)
		jal ra -> RAM 800310cc
		[18] RAM 8007b1ec  lbu v1, 0(a0)
		800795e4  lwl  v0[  672618], 3(s2)
		800795e8  lwr  v0[  672618], 0(s2)

	sub_8007b1ec (min:sec:frame => uint)
		16 01 21 => v0[119a0-96=1190a]

	merge
		1564000  256 color pal
		158c800  256 color pal

	exe
		801bd000

800d83f0
	+00  c[]   min:sec:fra
	+04  uint  fsize
	+08  c[]   mrg name
	+18
	+1c
	+20
	+24  uint  RAM pal
	+28  uint  RAM pal (+800)
	+2c
	+30
	+34  uint  mrg lba
	+38
	+3c
	+40
	+44  uint  RAM callback func
	+48
	+4c  uint  (type*8 + 8009d5b4)[4,6]

48 full
08 upper/shoe/shield
09 lower
01 weapon


mapdat  27be  80019a68
merge   140b  80058b88  weapon      1cc3
merge   2517  80058e80  upper       2aa9 3813
merge   40cf  800590e4  lower
merge   42f5  80059194  shoe/shield 52cd
	callback_exec  80030e70
	BREAK tex changed 80071e2c

800ba89c  hero exp
800ba8dc  p1 exp
800ba91c  p2 exp

RAM 800ba94c
	80061000  lh  s7[  c1], 6e(v0[800ba8de])
800d91f0

RAM 8009d5b4
	-> sp
	-> 80082474 -> 1f801810

start
	wa  54  8001bea4
	wa  2c  8001bea4
	wa  a6  8001bea4

byte cmp 800a8a28
	+00 lw   0(800a83f4) | 500<<16
	+04 lhu  c(gpu)
	+08 lw   0(gpu)
	+0c lhu  4(gpu)
	+10 lbu  e(gpu)
	+14 lhu  8(gpu)
1f800000
	=> 8005f690

800bc328 + 50
801dc000
	19 << 1 = 32
	0014 + 32 = 801dc046
		02f7 = 801dc2f7
			02f9 = 801dc2f9
				3c32[00] = 801dfc32
					09
						58 28 -- -- 40 9
						58 -- a0 -- 40 5
						58 18  c -- 60 2
						78 18 4c -- 60 2
						98 18 8c -- 40 2
						-- -- -- -- 40 f
						40 40 8c -- 40 2
						20 40 4c -- 60 2
						-- 40  c -- 60 2
=> 891800 = 1123
=> 8cf800
=> 90d800
=> 94b800
=> 989800
=> 9c7800
=>
=>
=>
=>
=>
=>
=>
=> f97800 = 112f

78 18 4c -- 60 2

	// 0 2 3
	//   fedc ba98 7654 3210
	//   44-f ffff fffe eeee
	a0 = [004c] & 1fff = 4c
		if ( a0 == 0 )
			a0 = 1f
	gpu[e] = (a0 & 1f) * 8 = c * 8 = 60
	gpu[f] = (a0 >> 5) * 8 = 2 * 8 = 10

	v0 = ((a0 >> e) << 8) | [78] = 78
	if ( v0 & 200 )
		v0 -= 400
	gpu[4] = v0

	// 1 4 5
	//   fedc ba98 7654 3210
	//   66-a aaa8 888- ----
	v1 = 0260
	gpu[8] = (((v1 >> 5) & f) + 1) * 8 = 3 * 8 = 18
	gpu[a] = (((v1 >> 9) & f) + 1) * 8 = 1 * 8 =  8

	v0 = ((v1 >> e) << 8) | [18] = 18
	if ( v0 & 200 )
		v0 -= 400
	gpu[6] = v0

1f801e00


800bc378
	8005f464

80162e0
	case v1 in
		0) ram=801d0000;;
		1) ram=801dc000;;
		2) ram=801d4800;;
		3) ram=801db800;;
		4) ram=801d8000;;
		5) ram=801dbc00;;
		6) ram=801df800;;
		7) ram=80164000;;
	esac

80018e44 - [full] mitsumete armor
	lbu  v0[ 0], 1(v1[801dc002])
	lbu  v1[50], 0(v1[801dc002])
	v0 = ((v0 << 8) | v1) + 801dc000
	lbu  v1[41], 56(a3[800bc9a8])
	v0 = v0 + (v1 << 1)

	lbu  v1[ 5], 1(v0[801dc0d6])
	lbu  v0[97], 0(v0[801dc0d6])
	v1 = ((v1 << 8) | v0) + 801dc000
	lbu  v0[ 0], 57(a3[800bc9a8])
	v1 = v1 + (v0 << 1)

	lbu  a0[ 5], 1(v1[801dc597])
	lbu  v1[a1], 0(v1[801dc597])
	a1 = ((a0 << 8) | v1) + 801dc000
	sw   a1[801dc5a1], 14(a3[800bc9a8])

164000 =
1d0000 =
1d4800 = 1107800/220f
	DATA
	1fbe + 3*ae + 47
1d8000 = 10b0800/2161
	DATA
	1fbe + 2*ae + 47
1db800 = 1471000/28e2
	PALETTE
	2311 + 12*51 + 1f (12 = 3*6)
1dbc00 = 137e000/26fc
	PALETTE
	2311 +  c*51 + 1f ( c = 2*6)
1dc000 = 3a2800/745  3e8800/7d1  42e800/85d ... 88e800/111d
	WALK BG
	n * 8c , n = 13
	6bf + n*8c + 86
1df800 = 895000/112a  8d3000/11a6  911000/1222  ... f9b000/1f36
	LEFTOVER 1dc000
	n * 7c , n = 1e
	1123 + n*7c + 7  ( n = i*6+j)

164000 =
1d0000 =
1d4800 = same
1d8000 = same
1db800 = same
1dbc00 = same
1dc000 = mapdat 1db3298 , 1dd87c8 , 1dfdcf8
1df800 = 2f9000/5f2
	PALETTE
	4e3 + 7*22 + 21
*/
