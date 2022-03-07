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
require 'common.inc';
require 'common-guest.inc';
require 'quad.inc';
require 'quad_vanillaware.inc';

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

	if ( substr($mbs,0,4) !== 'FMBS' )
		return;

	if ( str2int($mbs, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	global $gp_data;
	load_mbsfile($mbs, $gp_data['nds_kuma']['sect'], false);
	$json = load_idtagfile( $gp_data['nds_kuma']['idtag'] );

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
s4 0
	LOOP 2030ad0-2030bcc
	r5 = s6
	r6 = s6
	r9 = s4
	rb = s0
	rd = GPU
	sub_2030640 =
	sub_2034834 =

	tst  r0, 2 // ( r0 == 0 ) ? ZF=1 : ZF=0
	beq  xxx   // if ( ZF == 1 ) b xxx , run if r0=not &2
	bne  xxx   // if ( ZF == 0 ) b xxx , run if r0=&2

	cmp  r0, 2 // ( r0 == 0 ) ? ZF=0 : ZF=1
	beq  xxx   // if ( ZF == 1 )  b xxx , run if r0=2
	bne  xxx   // if ( ZF == 0 )  b xxx , run if r0=not 2

2030ad0
	ldrh  r0, 0(r9) // s4 flags
	if ( r0 & 2 )
		goto END
	else
		// 4=ZF 0  ~4=ZF 1
		if ( (r0 & 4) == 0 )
			//goto 2030b20
			//2030b20
			r0 = 040004a8
			r1 = 0
			ldr  r4, 14(rd)
			str  r1,  0(r0)
		else
			ldrb  r1, 3(r9) // tex id
			if ( r4 == r1 )
			else
				ldr  r0,  8(rd)
				ldr  r3, a0(r0)
				r0 = r1 * 28 + r3
				r4 = r1

				r2 = ra + r1
				ldrb  r1, af(r2)
				sub_2034834

		//2030b30
		if ( r7 == 1 )
			//goto 2030b44
			//2030b44
			ldrb  r0, 14(r6) // s4 cnt
			if ( r0 < r8 )
				r7 = 0
			//goto 2030b78
			goto END

		if ( r7 == 2 )
			//goto 2030b54
			//2030b54
			ldr   r0, 10(rd) //
			ldrb  r0, 14(r0)
			if ( r0 > r8 )
				ldrb  r0, 14(r6) // s4 cnt
				if ( r0 > r8 )
					ldr   r0,  c(rd) //
					ldrb  r0, 14(r0)
			if ( r0 < r8 )
				r7 = 0
			//goto 2030b78
			goto END

		//2030b78
		//goto 2030b8c
		//2030b8c
		ldrh  r1, 8(r9) // s0 id
		ldrh  r2, 4(r9) // s1 id
		r0 = r1 * 18 + rb

		ldr  r1, 4(rd)
		r1 = r2 * 30 + r1

		ldrh  r3, a(r9) // s2 id
		ldr   r2, 0(rd) // fmbs s2
		r2 = r3 * 30 + r2
		r3 = ra + 58
		sub_2030640

		goto END

END/2030bbc
	r9 += c
	r8 += 1
	ldrb  r0, 14(r5) // s4 cnt
	if ( r8 < r0 )
		goto 2030ad0

rd
	00 s2 // + s2 id*30
	04 s1 // + s1 id*30
	08 // + tex id*28
	0c s6 // r0 > r8
	10 s6 // r0 > r8
	14 -1 // r4 == r1/tex id
//////////////////////////////
WORK RAM s8 70
	2032684  ldrb  r2, 70(r5)
	if ( r2 & 8 )
		return

2032744
	2032744  ldrb  r0, 70(ra)
	if ( r0 & 4 )
		return
	if ( r0 & 2 )
		return
	if ( r0 & 1 )
		ldrb  r0, 70(ra)
		r0 &= ~1
		strb  r0, 70(ra)

	2032780  ldrb  r2, 70(ra)
	if ( r0 & 2 )
		goto END

	if ( r0 & 10 )
	else
		goto 203283c

	20327b0  ldrb  r0, 70(ra)
	r0 &= ~10
	strb  r0, 70(ra)

	ldr  r0, 8(r8)
	if ( r0 & 80 )
	else
		goto 203283c

	ldr  r0, b0(r4)
	if ( r0 & 200 )
		goto 203283c
	if ( r0 & 20000 )
		goto 203283c

	ldr  r0, f4(r4)
	if ( r0 == 0 )
		//goto 2032814
		//2032814
		ldr  r0, 60(ra)
		ldr  rc, f0(r0)
		if ( rc == 0 )
			goto 203283c

	goto 203283c

203283c
	ldr  r0, 8(r8)
	if ( r0 & 100 )
		r0 = r4
		sub_203347c
		goto 203292c
	else
		//goto 203287c
		//203287c
		if ( r0 & 4 )
		else
			//goto 20328d4
			//20328d4
			if ( r0 & 8 )
			else
				//goto 20328ec
				//20328ec
				if ( r0 & 1800 )
				else
					goto 2032904

				20328f4  ldrb  r0, 70(ra)
				r0 |= 5
				strb  r0, 70(ra)
				goto 203292c

			20328dc  ldrb  r0, 70(ra)
			r0 |= 3
			strb  r0, 70(ra)
			goto 203292c

		20328a4  ldrb  r0, 70(ra)
		r0 |= 3
		strb  r0, 70(ra)
		goto 203292c

		20328c4  ldrb  r0, 70(ra)
		r0 |= 1
		strb  r0, 70(ra)
		goto 203292c

2032904
203292c
	203292c  ldrb  r0, 70(ra)
	r0 |= 10
	strb  r0, 70(ra)

	ldr  r0, b0(r4)
	r0 |= 1
	str  r0, b0(r4)

	ldr  r0, 8(r8)
	if ( r0 & 2000 )
		ldr  r0, b0(r4)
		r0 |= 6000
		str  r0, b0(r4)

END/2032958
	if ( r9 > 0 )
		goto 2032744



2033230  ldrb  r1, 70(r0)  tst  r1, 02
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
DRAMA momo 24
	momo01.mbs = RAM 21ced80
		s0  54  21cee20 + *18
		s1  58  21cee68 + *30
		s2  5c  21d08a8 + *30
		s3  60  -
		s4  64  21e372c + *c
		s5  68  -
		s6  6c  21d2cd8 + *18
		s7  70  21d3b60 + *24
		s8  74  21d404c + *20
		s9  78  21e543c + *30
		sa  7c  21e5d0c + *10

	21ced80 + 78 -> [21cedf8]? -> rc=19 , r0=21935e0
	[21e64dc]? -> r3=3f8 , r5=219e7e0

	eve_sumo_b24.kds
	ANIM 19 , 7d-5 , 21e64dc
		3f8-12  21dbf4c  219e7e0
		40a-15  21dc18c  219e854
		41f-12  21dc42c  219e8c8
		431-12  21dc66c  219e93c
		443-12  21dc8ac  219e9b0
	ANIM 7 , 20-4 , 21e5f0c
		 f4-12  21d5ecc
		106-15  21d610c
		11b-12  21d63ac
		12d-12  21d65ec
	ANIM 16 , 6d-6 , 21e63dc
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
DRAMA saru 38
	kuma01.mbs = RAM 2206f90  22e28b0
		s0  54  2207030 + *18
		s1  58  22071f8 + *30
		s2  5c  220cd78 + *30
		s3  60  2220518 + *50
		s4  64  2260b1c + *c
		s5  68  226d350 + *8
		s6  6c  2220568 + *18
		s7  70  2224948 + *24
		s8  74  2229e5c + *20
		s9  78  226d368 + *30
		sa  7c  2271808 + *10

	2206f90 + 78 -> [2207008]? -> rc=11c , r0=21941b0
	[2275248]? -> r3=14f4 , r5=219eef0

	eve_saru_l06.kds
	ANIM 11c , 3a4-5 , 2275248
		14f4-8  2253cdc  219eef0
		14fc-e  2253ddc  219ef64
		150a-b  2253f9c  219efd8
		1515-e  22540fc  219f04c
		1523-1  22542bc  219f0c0
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
http://box-sentence.net/data/bikou/ds_note/ds_st/kumatan_st.html
 */
