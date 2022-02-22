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

			nds_quad30p($mbs[1], $s1, $sqd);
			nds_quad18c($mbs[0], $s0, $cqd);
			nds_quad30p($mbs[2], $s2, $dqd);

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
			//quad_convexfix($data[$i4]);

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

		$data = array();
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

		$json['Animation'][$anim] = $data;
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
	save_sect($mbs, $pfx);

	sectanim($json, $mbs);
	sectspr ($json, $mbs);

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
		0c  2032884  ldrh  r0, c(r8)  // loop s8 ID
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
WORK RAM s8 [BLOCK 0x74]
	// 4x4 transformation matrix?
	00  200113c  ldr  r5,  0(r0)
		200116c  str  r6,  0(ra)
	04  2001134  ldr  r4,  4(r0)
		2001198  str  r6,  4(ra)
	08  2001148  ldr  r3,  8(r0)
		20011f4  str  r2,  8(ra)
	0c  2001154  ldr  r2,  c(r0)
		20011c4  str  r6,  c(ra)
	10  2001214  ldr  r7, 10(r0)
		2001340  str  r4, 10(ra)
	14  2001210  ldr  r6, 14(r0)
		20012e8  str  r3, 14(ra)
	18  2001224  ldr  r5, 18(r0)
		20012bc  str  r3, 18(ra)
	1c  2001230  ldr  r4, 1c(r0)
		2001314  str  r3, 1c(ra)
	20  2001364  ldr  r8, 20(r0)
		2001414  str  r3, 20(ra)
	24  2001358  ldr  r7, 24(r0)
		2001458  str  r2, 24(ra)
	28  2001368  ldr  r6, 28(r0)
		20014e0  str  r4, 28(ra)
	2c  200136c  ldr  r5, 2c(r0)
		200149c  str  r2, 2c(ra)
	30  20014e8  ldr  r3, 30(r0)
		200169c  str  r6, 30(ra)
	34  20014e4  ldr  r4, 34(r0)
		20015e8  str  r6, 34(ra)
	38  2001504  ldr  r2, 38(r0)
		2001520  str  r0, 38(ra)
	3c  2001508  ldr  rc, 3c(r0)
		2001740  str  r1, 3c(ra)
	40  2032088  str  r3, 40(ra)  // from s6 0 x1
	44  2032094  str  r3, 44(ra)  // from s6 4 y1
	48  203209c  str  r3, 48(ra)  // from s6 8 x2
	4c  20320a4  str  r3, 44(ra)  // from s6 c y2
	50
	54
	58
	5c
	60  *pointer*
		2031f40  ldr  rb, 60(r8)
		2031ff4  ldr  r4, 60(ra)
		203265c  ldr  r1, 60(r0)
		2032698  ldr  r1, 60(r5)
		2032734  ldr  r4, 60(ra)
	64  // from s8 6 frames
		203283c  ldr   r0, 64(ra)  r0 -= r9
		203284c  str   r0, 64(ra)
		20328b8  ldrh  r0,  6(r0) // r0 = s8
		20328c0  str   r0, 64(ra)
		2032920  ldrh  r0,  6(r0) // r0 = s8
		2032928  str   r0, 64(ra)
	68  // from s8 6 frames
		20328b8  ldrh  r0,  6(r0) // r0 = s8
		20328bc  strh  r0, 68(ra)
		2032920  ldrh  r0,  6(r0) // r0 = s8
		2032924  strh  r0, 68(ra)
	6a  // sa ID
		2032660  ldrh  r0, 6a(r0)

		ldr  r1, 60(r0)     // ^pointer
		ldr  r1, bc(r1)
		ldr  r1, 98(r1)
		ldr  r1, 7c(r1)     // r1 = FMBS
		r0 = r1 + (r0 << 4) // r0 = sa
	6c  // from s8 c loop s8 ID
		20326b0  ldrsh  r2, 6c(r5)
		2032798  ldrsh  r1, 6c(ra)
		2032884  ldrh   r0,  c(r8) // r8 = s8
		2032888  strh   r0, 6c(ra)

		203288c  ldrsh  r2, 6c(ra)
		2032904  ldrsh  r0, 6c(ra)  r0 += 1
		203290c  strh   r0, 6c(ra)

		2032910  ldrsh  r1, 6c(ra)
	6e  // loop s8 ID adjust
		20326b4  ldrsb  r1, 6e(r5)
		203279c  ldrsb  r0, 6e(ra)
		2032890  ldrsb  r1, 6e(ra)
		2032914  ldrsb  r0, 6e(ra)
	6f
	70  // flags
		20320ac  ldrb  r1, 70(ra)  r0 |= 20  // always
		20320d0  strb  r1, 70(ra)

		2032684  ldrb  r2, 70(r5)  tst  r2, 08  // skip draw
		2032744  ldrb  r0, 70(ra)
			tst  r0, 04
			tst  r0, 02
			tst  r0, 01
		2032764  ldrbne  r0, 70(ra)  r0 &= ~1
		203276c  strbne  r0, 70(ra)

		2032780  ldrb    r2, 70(ra)
			tst  r0, 02
			tst  r0, 10
		20327b0  ldrb  r0, 70(ra)  r0 &= ~10
		20327b8  strb  r0, 70(ra)
		20328a4  ldrb  r0, 70(ra)  r0 |= 3  // skip draw + frames
		20328ac  strb  r0, 70(ra)
		20328c4  ldrb  r0, 70(ra)  r0 |= 1  // load loop s8 ID
		20328cc  strb  r0, 70(ra)
		20328dc  ldrb  r0, 70(ra)  r0 |= 3  // skip draw + frames
		20328e4  strb  r0, 70(ra)
		20328f4  ldrb  r0, 70(ra)  r0 |= 5  // skip frames
		20328fc  strb  r0, 70(ra)
		203292c  ldrb  r0, 70(ra)  r0 |= 10  // load s8 frames
		2032934  strb  r0, 70(ra)

		2033230  ldrb  r1, 70(r0)  tst  r1, 02
	71
	72
	73
//////////////////////////////
^ 70
	00  has draw , has countdown
	01  NEW
	02  no  draw , no  countdown
	04  has draw , no  countdown
	08  no  draw , has countdown
	10  NEW
	20  *always*
//////////////////////////////
WORK RAM s9 [BLOCK 0x]
	00  *pointer*
	04
	08
	0c
	10
	14
	18
	1c
	20
	24
	28
	2c
	30
	34
	38
	3c
	40
	44
	48
	4c
	50
	54
	58
	5c
	60
	64
	68
	6c
	70
	74
	78
	7c
	80
	84
	88
	8c
	90
	94
	98
	9c
	a0
	a4
	a8
	ac
	b0
		2032018  ldr  r1, b0(r4)
		2033024  ldr  r1, b0(r5)  r1 |= 1
		203302c  str  r1, b0(r5)
		2033038  ldr  r1, b0(r5)  r1 &= ~6000
		2033044  str  r1, b0(r5)
		2033274  ldr  r1, b0(r6)  r1 &= ~1
		2033280  str  r1, b0(r6)
		2033290  ldr  r0, b0(r6)  r0 &= ~2000
		2033298  str  r0, b0(r6)

		2033744  ldr  r2, b0(ra)  tst r2, 1000
	b4
	b8
	bc  *pointer*
		2032018  ldr  r2, bc(r4)
		203337c  ldr  r1, bc(r0)
		20333e0  ldr  r1, bc(r0)
	c0  *pointer*
	c4  // s9 ID
		2033020  strh     r1, c4(r5)
		20331f4  ldrsh    r3, c4(r8)
		2033284  ldrsh    r1, c4(r6)
		2033368  ldrsh    rc, c4(r0)
		20333cc  ldrsh    rc, c4(r0)
		20335dc  ldrsh    r1, c4(r6)
		203374c  ldrshne  r1, c4(ra)
	c6
		2032004  ldrsh  r2, c6(r4)
	c8
		2033030  str  r0, c8(r5)
		20332e8  ldr  r0, c8(r6)  r0 += r5
		20332f0  str  r0, c8(r6)

		2033310  strne  0, c8(r6)
	cc
		2033034  str  r0, cc(r5)
		20332f4  ldr  r0, cc(r6)  r0 += r5
		20332fc  str  r0, cc(r6)
	d0
		20332a4  ldr  r5, d0(r6)
	d4  *pointer*
		2033090  str   0, d4(r5)
		2033094  ldr  r0, d4(r5)
		20332b4  ldr  r0, d4(r6)
		2033338  ldr  r3, d4(r0)
		2033408  ldr  r3, d4(r0)
	d8
		2033048  ldrh  r2, d8(r5)  r1 = 2a(r7)
		2033050  strh  r1, d8(r5)
		2033054  ldrh  r1, d8(r5)
		20330a0  ldrh  r6, d8(r5)
		20332a0  ldrh  r0, d8(r6)
		20332c4  ldrh  r0, d8(r6)
		203334c  ldrh  r1, d8(r0)
	da
	db
	dc
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

	eve_sumo_b24.kds
	ANIM 19 , 7d-5 , 21935e0
		3f8-12  21dbf4c  219e7e0
		40a-15  21dc18c  219e854
		41f-12  21dc42c  219e8c8
		431-12  21dc66c  219e93c
		443-12  21dc8ac  219e9b0
	ANIM 7 , 20-4
		 f4-12  21d5ecc
		106-15  21d610c
		11b-12  21d63ac
		12d-12  21d65ec
	ANIM 16 , 6d-6
		364-16  21daccc
		37a-16  21daf8c
		390-16  21db24c
		3a6-16  21db50c
		3bc-16  21db7cc
		3d2-16  21dba8c

	2032f74  ldrh  r0[  2f], 4e(r8[ 21ced80])
	2032f78  cmp   r0[  2f], r1[  19]

	20335e0  mov   r4[19], r0[19]
	20335e4  cmp   r4[19], r1[ 6]

	20335d8  blx  r3
	2022d00  mov  r0, r1
	2022d04  bx   re

	202136c  ldr  r1[18], 1b0(r4[ 2193580])
	r2 = 1ac(r4)
	r0 = 0
	if ( r2 & 80 )
		if ( r2 & 04 )
		else
			r0 = 1
	else
		if ( r2 & 04 )
		else
			if ( r2 & 08 )
			else
				r0 = 1

	if ( r0 == 0 )
		if ( r2 & 08 )
		else
			r1 += 1
	else
		r1 += 2

	20213f4  ldr  r0, 1ac(r4)  r0 |= 08
	20213fc  str  r0, 1ac(r4)

	2026ef0  str  r4[18], 1b0(r5[ 2193580])

	ldr  r1[ 21935e0], 60(r0[ 219e7e0])
	ldr  r1[ 2193840], bc(r1[ 21935e0])
	ldr  r1[ 21ced80], 98(r1[ 2193840])  // CMNR
	ldr  r1[ 21e5d0c], 7c(r1[ 21ced80])  // FMBS

	2193730

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
float32 -> int32
	(float) 1.0 == (int) 0x1000

	0.333 = 3eaaaaab -> 0x0555
	3eaaaaab = sign 0  exp 7d/-2  man 2aaaab

	123.456 = 42f6e979 -> 0x07b74b
	42f6e979 = sign 0  exp 85/+6  man 76e979
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
