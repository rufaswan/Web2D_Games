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

define("METAFILE", true);

function colorquad( &$mbs, $pos )
{
	return '';
}

function sectquad( &$mbs, $pos )
{
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$b = substr($mbs['d'], $pos+$i, 4);
		$float[] = float32($b);
	}

	cmp_quadxy($float, 2, 10);
	cmp_quadxy($float, 3, 11);

	//  0  1  center
	//  2  3  c1
	//  4  5  c2
	//  6  7  c3
	//  8  9  c4
	// 10 11  c1
	$bcde = array(
		$float[2] , $float[3] ,
		$float[4] , $float[5] ,
		$float[6] , $float[7] ,
		$float[8] , $float[9] ,
	);
	return $bcde;
}
//////////////////////////////
function sectspr( &$json, &$mbs, $pfx )
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

		} // for ( $i4=0; $i4 < $no6; $i4++ )

		$json['Frame'][$k6] = $data;
	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbs, $pfx )
{
	// s9-sa-s8 [30-8-20]
	$len9 = strlen( $mbs[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )
	{
		// 0 4 8 c  10    28 29  2a  2b   2c 2d 2e 2f
		// - - - -  name  id     no  tr   -  -  -  -
		//   sa[tr] == longest fps sum
		$name = substr0($mbs[9]['d'], $i9+0x10);
		$id9  = str2int($mbs[9]['d'], $i9+0x28, 2);
		$no9  = str2int($mbs[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbs[10]['k'];

			// 01  23  4567  89ab cdef
			// s8  no  fps   -    -
			$ida = str2int($mbs[10]['d'], $pa+0, 2);
			$noa = str2int($mbs[10]['d'], $pa+2, 2);

			$ent = array(
				'FID' => array(),
				'POS' => array(),
				'FPS' => array(),
			);
			$is_mov = false;
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbs[8]['k'];

				// 01  23  45  67  89ab  cd  ef 0123 4567 89ab cdef
				// s6  -   s7  no  bt    nx  -  -    -    -    -
				$id8 = str2int($mbs[8]['d'], $p8+0, 2);
				$id7 = str2int($mbs[8]['d'], $p8+4, 2);
				$no8 = str2int($mbs[8]['d'], $p8+6, 2);

				$p7 = $id7 * $mbs[7]['k'];
				$x7 = float32( substr($mbs[7]['d'], $p7+0, 4) );
				$y7 = float32( substr($mbs[7]['d'], $p7+4, 4) );

				$ent['FID'][] = $id8;
				$ent['FPS'][] = $no8;
				$ent['POS'][] = array($x7,$y7);

				if ( $x7 != 0 || $y7 != 0 )
					$is_mov = true;
			} // for ( $i8=0; $i8 < $noa; $i8++ )

			// skip all zero Pos
			if ( ! $is_mov )
				unset( $ent['POS'] );
			$json['Animation'][$name][$ia] = $ent;
		} // for ( $ia=0; $ia < $no9; $ia++ )

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )

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

	// $siz = str2int($mbs, 4, 3);
	// $hdz = str2int($mbs, 8, 3);
	// $len = 0x10 + $hdz + $siz;
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// reform01b.mbs
	//        a0  d0 100 |  -   2*18 1*30 2*30
	//     - 1bc   - 160 |  -   2*c   -   1*18
	//   178 19c 1d4 204 | 1*24 1*20 1*30 1*10
	// kuma01.mbs
	//            a0   268  5de8 |    -     13*18 1e8*30 67e*30
	//   19588 59b8c 663c0 195d8 |   4*14 10af*c    3*8  2d4*18
	//   1d9b8 22ecc 663d8 6a878 | 25d*24 1b66*20 16e*30 4e8*10
	// s9[+28] =  4e5+3 => sa
	// sa[+ 0] = 1b64+2 => s8
	// s8[+ 0] =  2d3   => s6 , [+ 4] = 25c   => s7
	// s6[+10] = 10a6+9 => s4 , [+12] =   3+0 => s3
	// s4[+ 4] =  1e7   => s1 , [+ 8] =  12   => s0 , [+ a] = 67d => s2
	// s2
	// s0
	// s1
	// s3
	// s7
	$sect = array(
		array('p' => 0x54 , 'k' => 0x18), // 0
		array('p' => 0x58 , 'k' => 0x30), // 1
		array('p' => 0x5c , 'k' => 0x30), // 2
		array('p' => 0x60 , 'k' => 0x14), // 3 reform=0
		array('p' => 0x64 , 'k' => 0xc ), // 4
		array('p' => 0x68 , 'k' => 0x8 ), // 5 reform=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x24), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x10), // a
	);
	file2sect($mbs, $sect, $pfx, array('str2int', 4), strrpos($mbs, "FEOC"), METAFILE);
	if ( METAFILE )
	{
		sect_sum($mbs[4], 'mbs[4][0]', 0); //
		sect_sum($mbs[4], 'mbs[4][1]', 1); // = 0
		sect_sum($mbs[4], 'mbs[4][2]', 2); //
	}

	$json = load_idtagfile('nds_kuma');

	sectanim($json, $mbs, $pfx);
	sectspr ($json, $mbs, $pfx);

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

SHOPPING
	momo01.mbs = RAM 21ced80
		74  s8 =  52cc/21d404c + n * 20
		78  s9 = 166bc/21e543c + n * 30
		7c  sa = 16f8c/21e5d0c + n * 10

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

			9a8  d   e   f     10     11             0 1 2 3 4   5     6     7     8             9 a b c d   e   f     10     11               0
			a1c  1 2   3   4 5    6 7    8 9 a b c d           1   2 3   4 5   6 7   8 9 a b c d           1   2   3 4    5 6    7 8 9 a b c d

		s8 21d404c + cbe0 = 21e0c2c
			2032028  ldrh  r1[  90], 0(r9[ 21e0c4c])
			203203c  ldrh  rc[  90], 0(r5[ 21e0c4c])
			2032058  ldrh  rb[  16], 4(r9[ 21e0c4c])
			20328b8  ldrh  r0[   4], 6(r0[ 21e0c4c])

			2032128  ldr   r0[      21], 8(r9[ 21e0c4c])
					tst  r0, 0x01
			203215c  ldr   r0[      21], 8(r9[ 21e0c4c])
					tst  r0, 0x02
			203222c  ldr   r0[      21], 8(r9[ 21e0c4c])
					tst  r0, 0x400
			20324b0  ldr   r0[      21], 8(r9[ 21e0c4c])
					tst  r0, 0x20
			20326c4  ldr   r1[      21], 8(r3[ 21e0c4c])
					tst  r1, 0x40
			20327bc  ldr   r0[      21], 8(r8[ 21e0c4c])
					tst  r0, 0x80
			2032864  ldr   r0[      21], 8(r8[ 21e0c4c])
					tst  r0, 0x100
			2032944  ldr   r0[      21], 8(r8[ 21e0c4c])
					tst  r0, 0x2000

			& 0x0001 = flip x
			& 0x0002 = flip y
			& 0x0040 = clear
			& 0x0400 = clear
			& 0x0800 = loop

RAM 218a2a0 = YEN
	206bb04  str  r0[YEN], 1c(r2[ 218a284])

 */
