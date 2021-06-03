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
require "common-quad.inc";
require "quad.inc";

define("METAFILE", true);

$gp_json = array();

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
	//   1 4    1-2
	//   | | =>   |  , 2-8-6-4
	//   2-3    4-3
	$bcde = array(
		$float[2] , $float[3] ,
		$float[8] , $float[9] ,
		$float[6] , $float[7] ,
		$float[4] , $float[5] ,
	);
	return $bcde;
}
//////////////////////////////
function sectpart( &$mbs, $pfx, $k6, $id6, $no6 )
{
	global $gp_json;

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

	$gp_json['Frame'][$k6] = $data;
	return;
}

function sectspr( &$mbs, $pfx )
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
		sectpart($mbs, $pfx, $k6, $id6, $no6);

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$mbs, $pfx )
{
	global $gp_json;

	// s9-sa-s8 [30-8-20]
	$len9 = strlen( $mbs[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )
	{
		// 0 4 8 c  10    28 29  2a  2b 2c 2d 2e 2f
		// - - - -  name  id     no  -  -  -  -  -
		$name = substr0($mbs[9]['d'], $i9+0x10);
		$id9  = str2int($mbs[9]['d'], $i9+0x28, 2);
		$no9  = str2int($mbs[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbs[10]['k'];

			// 0 1  2 3  4 5 6 7
			// id   no   - - - -
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

				// 0   2  4    6   8 c 10 14 18 1c
				// id  -  pos  no  - - -  -  -  -
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
			$gp_json['Animation'][$name][$ia] = $ent;
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

	global $gp_pix;
	$gp_pix = array();

	//   0 1 2 |
	// 3 4 5 6 |
	// 7 8 9 a |
	// reform01b.mbs
	//        a0  d0 100 |  -   2*18 1*30 2*30
	//     - 1bc   - 160 |  -   2*c   -   1*18
	//   178 19c 1d4 204 | 1*24 1*20 1*30 1*10
	//
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

	global $gp_json;
	$gp_json = load_idtagfile('nds_kuma');

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx);

	save_quadfile($pfx, $gp_json);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
mbs 4-01 valids
	0 2 4 6
mbs 4-2 valids
	0
 */
