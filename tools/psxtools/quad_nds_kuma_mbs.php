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
require "common-guest.inc";
require "quad.inc";
require "quad_vanillaware.inc";

function sectspr( &$json, &$mbs )
{
	// s6-s4-s0/s1/s2 [18-c-18/30/30]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2int($mbs[6]['d'], $i6+0x10, 2);
		$no6 = str2int($mbs[6]['d'], $i6+0x14, 1);
		// DO NOT skip numbering
		// JSON will become {object} instead [array]

		$k6 = $i6 / $mbs[6]['k'];
		$data = array();
		for ( $i4=0; $i4 < $no6; $i4++ )
		{
			$p4 = ($id6 + $i4) * $mbs[4]['k'];

			// 0 1 2 3  4 5  6 7  8 9  a b
			// sub      s1   - -  s0   s2
			$sub = substr ($mbs[4]['d'], $p4+ 0, 4);

			$s1  = str2int($mbs[4]['d'], $p4+ 4, 2); // sx,sy
			$s0  = str2int($mbs[4]['d'], $p4+ 8, 2);
			$s2  = str2int($mbs[4]['d'], $p4+10, 2); // dx,dy

			$sqd = sectquad ($mbs[1], $s1*$mbs[1]['k']);
			$cqd = colorquad($mbs[0], $s0*$mbs[0]['k']);
			$dqd = sectquad ($mbs[2], $s2*$mbs[2]['k']);

			$s1 = str2int($sub, 0, 2); // ???
			$s3 = ord( $sub[2] ); // mask = 0
			$s4 = ord( $sub[3] ); // tid

			$data[$i4] = array();
			if ( $s1 & 2 )
				continue;

			$data[$i4]['DstQuad'] = $dqd;
			if ( ! empty($cqd) )
				$data[$i4]['ClrQuad']  = $cqd;

			//  1 layer normal
			//  2 layer top
			//  4 gradientFill
			//  8 attack box
			// 10
			// 20
			if ( ($s1 & 4) == 0 )
			{
				$data[$i4]['TexID']   = $s4;
				$data[$i4]['SrcQuad'] = $sqd;
			}
			quad_convexfix($data[$i4]);

		} // for ( $i4=0; $i4 < $no6; $i4++ )

		$json['Frame'][$k6] = $data;
	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbs )
{
	$no9 = strlen($mbs[9]['d']) / $mbs[9]['k'];
	for ( $i9=0; $i9 < $no9; $i9++ )
	{
		$pos9 = $i9 * $mbs[9]['k'];
		printf("%x : ", $i9);

		// 0  4  8  c   10    28   2a   2b    2c   2d 2e 2f [30]
		// x1 y1 x2 y2  anim  said sano sabg  dum  -  -  -
		for ( $i=0; $i < 16; $i += 4 )
		{
			$b = substr($mbs[9]['d'], $pos9+$i, 4);
			$b = float32($b);
			printf("%.2f  ", $b);
		}
		$anim = substr0($mbs[9]['d'], $pos9+16);
		$ida = str2int($mbs[9]['d'], $pos9+0x28, 2);
		$noa = str2int($mbs[9]['d'], $pos9+0x2a, 1);
		$bga = str2int($mbs[9]['d'], $pos9+0x2b, 1);
		$dum = str2int($mbs[9]['d'], $pos9+0x2c, 1);
		printf("%s  %x %x %x  %x\n", $anim, $ida, $noa, $bga, $dum);

		for ( $ia=0; $ia < $noa; $ia++ )
		{
			$posa = ($ida + $ia) * $mbs[10]['k'];
			printf("  %x : ", $ida+$ia);

			// 0    2    4     6 8 c [10]
			// s8id s8no s8bg  - - -
			$id8 = str2int($mbs[10]['d'], $posa+0, 2);
			$no8 = str2int($mbs[10]['d'], $posa+2, 2);
			$bg8 = str2int($mbs[10]['d'], $posa+4, 2);
			printf("%x  %x  %x\n", $id8, $no8, $bg8);

			for ( $i8=0; $i8 < $no8; $i8++ )
			{
				$pos8 = ($id8 + $i8) * $mbs[8]['k'];
				printf("    %x : ", $id8+$i8);

				// 0     2 3  4     6    8     c d e f 10 11 12 13 14 18 1a 1c [20]
				// s6id  - -  s7id  fps  flag  ? - ? - -  ?  ?  ?  ?  ?  ?  ?
				$id6 = str2int($mbs[8]['d'], $pos8+0, 2);
				$id7 = str2int($mbs[8]['d'], $pos8+4, 2);
				$fps = str2int($mbs[8]['d'], $pos8+6, 2);
				$flg = str2int($mbs[8]['d'], $pos8+8, 4);
				printf("%x  %x  %x  %x\n", $id6, $id7, $fps, $flg);


			} // for ( $i8=0; $i8 < $no8; $i8++ )
		} // for ( $ia=0; $ia < $noa; $ia++ )
	} // for ( $id9=0; $id9 < $cnt9; $id9++ )

	return;
}
//////////////////////////////
function kuma( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs,0,4) != "FMBS" )
		return;

	if ( str2int($mbs, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	global $gp_data;
	load_mbsfile($mbs, $gp_data['nds kuma']['sect'], false);
	$json = load_idtagfile( $gp_data['nds kuma']['idtag'] );

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	//save_sect($mbs, $pfx);

	sectanim($json, $mbs);
	//sectspr ($json, $mbs);

	save_quadfile($pfx, $json);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
mbs 4-01 valids
	0 2 4 6
mbs 4-2 valids
	0
//////////////////////////////
MBS file
	s0*18 clr (18 done)
		00  // center RGB
		04  2030834  ldr   r1, 0(r1 + r3 << 2) // RGB 1
			20308d4  ldrb  r1, 0(r0)
		05  20308dc  ldrb  r1, 1(r0)
		06  20308e4  ldrb  r0, 2(r0)
		08  2030834  ldr   r1, 0(r1 + r3 << 2) // RGB 2
			20308d4  ldrb  r1, 0(r0)
		09  20308dc  ldrb  r1, 1(r0)
		0a  20308e4  ldrb  r0, 2(r0)
		0c  2030834  ldr   r1, 0(r1 + r3 << 2) // RGB 3
			20308d4  ldrb  r1, 0(r0)
		0d  20308dc  ldrb  r1, 1(r0)
		0e  20308e4  ldrb  r0, 2(r0)
		10  2030834  ldr   r1, 0(r1 + r3 << 2) // RGB 4
			20308d4  ldrb  r1, 0(r0)
		11  20308dc  ldrb  r1, 1(r0)
		12  20308e4  ldrb  r0, 2(r0)
		14  // RGB 5 == RGB 1
	s1*30 src (30 done)
		00  // center x
		04  // center y
		08  2030578  ldr  r2,  0(r0)  // x1
			20306b8  ldr  r7   8(r5)
		0c  2030574  ldr  r1,  4(r0)  // y1
			20306d0  ldr  r7   c(r5)
		10  2030578  ldr  r2,  0(r0)  // x2
		14  2030574  ldr  r1,  4(r0)  // y2
		18  2030578  ldr  r2,  0(r0)  // x3
			20306bc  ldr  r3, 18(r5)
		1c  2030574  ldr  r1,  4(r0)  // y3
			20306d4  ldr  r3, 1c(r5)
		20  2030578  ldr  r2,  0(r0)  // x4
		24  2030574  ldr  r1,  4(r0)  // y4
		28  // x5 == x1
		2c  // y5 == y1
	s2*30 dst (30 done)
		00  // center x
		04  // center y
		08  2030538  ldrh   r3,  0(r0) // x1
			2030688  ldrsh  r1,  8(rb)
		0a  2030690  ldrsh  r0,  a(rb)
		0c  // y1
		10  2030538  ldrh   r3,  0(r0) // x2
		14  // y2
		18  2030538  ldrh   r3,  0(r0) // x3
			203068c  ldrsh  r2, 18(rb)
		1a  2030694  ldrsh  r3, 1a(rb)
		1c  // y3
		20  2030538  ldrh   r3,  0(r0) // x4
		24  // y4
		28  // x5 == x1
		2c  // y5 == y1
	s3 bg skip
	s4*c  part (9 done)
		00  2030ad0  ldrh  r0, 0(r9) //
		02  //
		03  2030af0  ldrb  r1, 3(r9) // [kuma02] tex ID
		04  2030b94  ldrh  r2, 4(r9) // s1 ID
		06  //
		07
		08  2030b8c  ldrh  r1, 8(r9) // s0 ID
		0a  2030ba8  ldrh  r3, a(r9) // s2 ID
	s5 bg skip
	s6*18 key (14 done)
		00  2032080  ldr     r3, r0(r1)  // x1
			20320a8  ldr     r0, r0(r1)
		04  203208c  ldr     r3,  4(r5)  // y1
			20320b4  ldr     r0,  4(r5)
		08  2032098  ldr     r3,  8(r5)  // x2
			20320c0  ldr     r0,  8(r5)
		0c  20320a0  ldr     r3,  c(r5)  // y2
			20320c8  ldr     r0,  c(r5)
		10  2030a48  ldrh    r2, 10(r5)  // s4 ID
		12  // [kuma02] s5 ID
		13
		14  2030ab8  ldrb    r0, 14(r5)  // s4 count
			2030bc4  ldrb    r0, 14(r5)
			203221c  ldrbne  r0, 14(r5)
		15  2031f38  ldrb    r3, 15(r5) // [kuma02] s5 count
		16  //
		17
	s7*24 meta (14 done)
		00  // + x
		04  // + y
		08
		0c  203211c  ldr  r3,  c(r6)
		10  2032104  ldr  r1, 10(r6)
		14  2032110  ldr  r1, 14(r6)
		18  2032100  ldr  r2, 18(r6) // * scale x
		1c  // * scale y
		20  2032394  ldrb  r6, 20(r6) // red
		21  2032398  ldrb  r0, 21(r6) // green
		22  2032364  ldrb  r7, 22(r6) // blue
		23  2032360  ldrb  rc, 23(r6) // alpha
	s8*20 frame (1a done)
		00  2032028  ldrh  r1, 0(r9)  // s6 ID
			203203c  ldrh  rc, 0(r5)
		02
		03
		04  2032058  ldrh  rb, 4(r9)  // s7 ID
		06  20328b8  ldrh  r0, 6(r0)  // frames
		08  2032128  ldr   r0, 8(r9)  tst  r0,   01  // flip x
			203215c  ldr   r0, 8(r9)  tst  r0,   02  // flip y
			203222c  ldr   r0, 8(r9)  tst  r0, 0400  // skip
			20324b0  ldr   r0, 8(r9)  tst  r0,   20
			20326c4  ldr   r1, 8(r3)  tst  r1,   40  // skip
			20327bc  ldr   r0, 8(r8)  tst  r0,   80
			2032864  ldr   r0, 8(r8)  tst  r0, 0100  // skip
			2032944  ldr   r0, 8(r8)  tst  r0, 2000
		0c  // loop ID
		0d
		0e  20320e0  ldrb  r0, e(r9)
		0f
		10
		11  20325c0  ldrb    r9, 11(r9)
		12  203258c  ldrbeq  r0, 12(r9)
		13  2032008  ldrsb   r1, 13(r9)
		14  2032834  ldr     r1, 14(r8)
		18  203282c  ldrh    r2, 18(r8)
		1a  2032830  ldrh    r3, 1a(r8)
		1c  2032824  ldr     r1, 1c(r8)
	s9*30 anim (2d done)
		00  // x1
		04  // y1
		08  // x2
		0c  // y2
		10  // name[17] + NULL
		28  203317c  ldrh  r0, 28(r7)  // sa ID
		2a  203304c  ldrb  r1, 2a(r7)  // sa count
			2033168  ldrb  r0, 2a(r7)
			203319c  ldrb  r0, 2a(r7)
		2b  // sa ID+n with highest sum of s8 frames
		2c  203300c  ldrb  r0, 2c(r7)  tst r0, 01  // dummied
		2d
		2e
		2f
	sa*10 track (5 done)
		00  20326a8  ldrh  r3, 0(r0) // s8 ID
			2032784  ldrh  r0, 0(r6)
		02  20326d4  ldrh  re, 2(r0) // s8 count
		04  // sum of s8 frames
		06
		07
		08  //
		09
		0a
		0b
		0c  //
		0d
		0e
		0f
//////////////////////////////
SHOPPING
	momo01.mbs = RAM 21ced80
		// 0  54  21cee20 +   0*18 = 21cee20
		// 1  58  21cee68 +  64*30 = 21d0128
		// 2  5c  21d08a8 +  8f*30 = 21d2378
		// 3  60  -
		// 4  64  21e372c + 229*c  = 21e5118
		// 5  68  -
		// 6  6c  21d2cd8 +  8c*18 = 21d39f8
		// 7  70  21d3b60 +   0*24 = 21d3b60
		// 8  74  21d404c + 6be*20 = 21e180c
		// 9  78  21e543c +  28*30 = 21e5bbc
		// a  7c  21e5d0c +  c7*10 = 21e697c


		s9-ptr 21cedf8
			20333cc  ldrsh   rc[  28], c4(r0[ 21933f0])
			20333f8  ldr     r3[ 21e543c], 78(r2[ 21ced80])
			2033400  smlabb  r1[ 21e5bbc], rc[28], r1[30], r3[ 21e543c]
					(sign)r1 = (28 * 30 + 21e543c)
		RAM 21934b4
			2033020  strh   r1[  28], c4(r5[ 21933f0])
			20331f4  ldrsh  r3[  28], c4(r8[ 21933f0])
			2033284  ldrsh  r1[  28], c4(r6[ 21933f0])
			2033368  ldrsh  rc[  28], c4(r0[ 21933f0])
			20333cc  ldrsh  rc[  28], c4(r0[ 21933f0])
			20335dc  ldrsh  r1[  28], c4(r6[ 21933f0])
			203374c  ldrshne  r1[  28], c4(ra[ 21933f0])

		s9 21e543c + 780
			28 = c7,4  MOMO_30B
				6be,12
				6d0,15
				6e5,12
				6f7,12
			RAM 21e5be0

		[change c7 -> b8]
		sa 21e5d0c + b80 = 21e688c
			614,12
			626,15
			63b,12
			64d,12
			65f,e
				20326a8  ldrh  r3[ 65f], 0(r0[ 21e68cc])
					rc = s8
					ldrsh  r2[   c], 6c(r5[ 219e9b0])
					ldrsb  r1[ 0], 6e(r5[ 219e9b0])
					rc = rc + (r3 << 5) // r3 * 20
					r6 = r2 + r1
					r3 = rc + (r6 << 5)
					ldr  r1, 8(r3)
					tst  r1, 0x40
				20326d4  ldrh  re[   e], 2(r0[ 21e68cc])
					r1 = r6 - 1
					movmi  r2, r3 // move if minus
					r0 = r6 + 1
					addpl  r2, rc, (r1 << 5) // add if positive
					cmp    r0, re
					if ( r0 > re )
						r0 = r6
						r1 = r3
					else
						r1 = rc + (r0 << 5)
					r0++
					if ( r0 > re )
						r0 = r1
				2032784  ldrh  r0[ 65f], 0(r6[ 21e68cc])
					r1 = s8
					r7 = r1 + (r0 << 5)
					ldrsh  r1[   1], 6c(ra[ 219ea40])
					ldrsb  r0[ 0], 6e(ra[ 219ea40])
					r0 = r1 + r0
					r8 = r7 + (r0 << 5) // r3 * 20

			21e68cc
			RAM 219ea14 // animation timer (--)
			RAM 219ea1c // animation frame (++)
				2032884  ldrh  r0[   1],  c(r8[ 21e0dcc])
				2032888  strh  r0[   1], 6c(ra[ 219e9b0])

			21e68bc
			RAM 219e9a0
			RAM 219e9a8

			219e9a8  d   e   f     10     11             0 1 2 3 4   5     6     7     8             9 a b c d   e   f     10     11               0
			219ea1c  1 2   3   4 5    6 7    8 9 a b c d           1   2 3   4 5   6 7   8 9 a b c d           1   2   3 4    5 6    7 8 9 a b c d

//////////////////////////////
kuma02.mbs
	s3 [50]
		-22   -31
		-22    22
		 22    22
		 22   -31
		 1   -0   0
		-0   -1   0
		-1   -0   0
		-0    1   0
//////////////////////////////
RAM 218a2a0 = YEN
	206bb04  str   r0[YEN ], 1c(r2[ 218a284])
RAM 218a2ae = LIKE
	203f224  strh  r0[LIKE], 2a(r5[ 218a284])

RAM 218a2c0 = DRAMA [bitflags]
	c0-c5  [ff ff ff ff ff 07]     rabbi-tan   [43]
	c5-ca  [f8 ff ff ff ff 3f]     tora-onesan [43]
	ca-d0  [c0 ff ff ff ff 01]     ushi-onesan [43]
	d0-d5  [fe ff ff ff ff 0f]     neko-kun    [43]
	d5-db  [f0 ff ff ff ff ff 07]  saru-jii    [47]
	db-de  [f8 ff ff 07]           maguro      [24]
	ea-ed  [c0 ff ff 7f]           sumomo      [25]
	ef     [38]                    prologue    [ 3]
	ef-f0  [c0 03]                 epilogue    [ 4]

http://kumatan.half-moon.org/53.html
https://box-sentence.net/data/bikou/ds_note/ds_st/kumatan_st.html
 */
