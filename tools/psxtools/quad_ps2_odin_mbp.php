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

function colorquad( &$mbp, $pos )
{
	$color = array();
	for ( $i=0; $i < $mbp['k']; $i += 4 )
	{
		$s = substr($mbp['d'], $pos+$i, 4);

		$r = int_clamp( ord($s[0]) << 1, 0, BIT8);
		$g = int_clamp( ord($s[1]) << 1, 0, BIT8);
		$b = int_clamp( ord($s[2]) << 1, 0, BIT8);
		$a = int_clamp( ord($s[3]) << 1, 0, BIT8);
		$rgba = sprintf("#%02x%02x%02x%02x", $r, $g, $b, $a);

		if ( $rgba == '#ffffffff' )
			$color[] = '1';
		else
		if ( $rgba == '#00000000' )
			$color[] = '0';
		else
			$color[] = $rgba;
	} // for ( $i=0; $i < $mbp['k']; $i += 4 )

	$cqd = array($color[2] , $color[3] , $color[4] , $color[5]);
	if ( implode('',$cqd) == '1111' )
		$cqd = '';
	return $cqd;
}

function sectquad( &$mbp, $pos )
{
	$float = array();
	for ( $i=0; $i < $mbp['k']; $i += 2 )
		$float[] = str2int($mbp['d'], $pos+$i, 2, true) / 0x10;

	cmp_quadxy($float, 4, 12);
	cmp_quadxy($float, 5, 13);

	//  0  1
	//  2  3  center
	//  4  5  c1
	//  6  7  c2
	//  8  9  c3
	// 10 11  c4
	// 12 13  c1
	// 14 15  padding
	$cdef = array(
		$float[ 4] , $float[ 5] ,
		$float[ 6] , $float[ 7] ,
		$float[ 8] , $float[ 9] ,
		$float[10] , $float[11] ,
	);
	return $cdef;
}
//////////////////////////////
function sectspr( &$json, &$mbp, $pfx )
{
	// s6-s4-s0/s1/s2 [18-18-20/20/20]
	$len6 = strlen( $mbp[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbp[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2int($mbp[6]['d'], $i6+0x10, 2);
		$no6 = str2int($mbp[6]['d'], $i6+0x14, 1);
		// DO NOT skip numbering
		// JSON will become {object} instead [array]

		$k6 = $i6 / $mbp[6]['k'];
		$data = array();
		for ( $i4=0; $i4 < $no6; $i4++ )
		{
			$p4 = ($id6 + $i4) * $mbp[4]['k'];

			// 0 1 2 3  4   6  8    a    c    e     10   12   14   16
			// sub      s1  -  s0-0 s0-6 s0-c s0-2  s2-0 s2-6 s2-c s2-2
			$sub = substr($mbp[4]['d'], $p4+ 0, 4);

			$s1 = str2int($mbp[4]['d'], $p4+ 4, 2); // sx,sy
			$s0 = str2int($mbp[4]['d'], $p4+ 8, 2);
			$s2 = str2int($mbp[4]['d'], $p4+16, 2); // dx,dy

			$sqd = sectquad ($mbp[1], $s1*$mbp[1]['k']);
			$cqd = colorquad($mbp[0], $s0*$mbp[0]['k']);
			$dqd = sectquad ($mbp[2], $s2*$mbp[2]['k']);

			$s1 = str2int($sub, 0, 2); // ??
			$s3 = ord( $sub[2] ); // mask
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

	/*
			switch ( $s3 )
			{
				case 1:
					$data[$i4]['Blend'] = array('SUB', 1);
					break;
				case 2:
					$data[$i4]['Blend'] = array('ADD', 1);
					break;
				default: // 0
					//$data[$i4]['Blend'] = array('NORMAL', 1);
					break;
			} // switch ( $s3 )
	*/

		} // for ( $i4=0; $i4 < $no6; $i4++ )

		$json['Frame'][$k6] = $data;
	} // for ( $i6=0; $i6 < $len6; $i6 += $mbp[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbp, $pfx )
{
	// s9-sa-s8 [30-8-20]
	$len9 = strlen( $mbp[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbp[9]['k'] )
	{
		// 0 4 8 c  10    28 29  2a  2b 2c 2d 2e 2f
		// - - - -  name  id     no  -  -  -  -  -
		$name = substr0($mbp[9]['d'], $i9+0x10);
		$id9  = str2int($mbp[9]['d'], $i9+0x28, 2);
		$no9  = str2int($mbp[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbp[10]['k'];

			// 0 1  2 3  4 5 6 7
			// id   no   - - - -
			$ida = str2int($mbp[10]['d'], $pa+0, 2);
			$noa = str2int($mbp[10]['d'], $pa+2, 2);

			$ent = array(
				'FID' => array(),
				'POS' => array(),
				'FPS' => array(),
			);
			$is_mov = false;
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbp[8]['k'];

				// 0   2  4    6   8 c 10 14 18 1c
				// id  -  pos  no  - - -  -  -  -
				$id8 = str2int($mbp[8]['d'], $p8+0, 2);
				$id7 = str2int($mbp[8]['d'], $p8+4, 2);
				$no8 = str2int($mbp[8]['d'], $p8+6, 2);

				$p7 = $id7 * $mbp[7]['k'];
				$x7 = float32( substr($mbp[7]['d'], $p7+0, 4) );
				$y7 = float32( substr($mbp[7]['d'], $p7+4, 4) );

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

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbp[9]['k'] )

	return;
}
//////////////////////////////
function sect_addoff( &$file, &$sect )
{
	foreach ( $sect as $k => $v )
	{
		$off = str2int($file, $v['p'], 4);
		if ( $off !== 0 )
			$sect[$k]['o'] = $off;
	}
	return;
}

function odin( $fname, $idtag )
{
	$mbp = load_file($fname);
	if ( empty($mbp) )  return;

	if ( substr($mbp,0,4) != "FMBP" )
		return;

	if ( str2int($mbp, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	// $siz = str2int($mbp, 4, 3);
	// $hdz = str2int($mbp, 8, 3);
	// $len = 0x10 + $hdz + $siz;
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// staff_dummy.mbp
	//        a0 1a0 1e0 |      8*20 2*20 8*20
	//     - 550   - 2e0 |    - 2*18    - 2*18
	//   310 370 580 850 | 2*30 f*20 f*30 d*8
	// gwendlyn.mbp
	//            a0   6fa0   8260 |         378*20  96*20 9078*20
	// 129160 157080 19a490 12dee0 |  f8*50 2cd6*18 209*8   376*18
	// 1331f0 13b440 19b4d8 19da58 | 2b7*30  de2*20  c8*30  193*8
	// s9[+28] =  190+3  => sa
	// sa[+ 0] =  ddf+3  => s8
	// s8[+ 0] =  375    => s6 , [+ 4] = 2b6   => s7
	// s6[+10] = 2cb9+1d => s4 , [+12] = 208+1 => s5
	// s5[+ 0] =   f7    => s3
	// s7
	// s4
	// s3
	$sect = array(
		array('p' => 0x54 , 'k' => 0x20), // 0
		array('p' => 0x58 , 'k' => 0x20), // 1
		array('p' => 0x5c , 'k' => 0x20), // 2
		array('p' => 0x60 , 'k' => 0x50), // 3 area=0
		array('p' => 0x64 , 'k' => 0x18), // 4
		array('p' => 0x68 , 'k' => 0x08), // 5 area=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x30), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x08), // 10
		array('o' => strrpos($mbp, "FEOC")),
	);
	sect_addoff($mbp, $sect);
	load_sect($mbp, $sect);
	save_sect($mbp, "$pfx/meta");

	if ( $idtag == '' )
		return php_error('NO TAG %s', $fname);
	$json = load_idtagfile($idtag);

	sectanim($json, $mbp, $pfx);
	sectspr ($json, $mbp, $pfx);

	save_quadfile($pfx, $json);
	return;
}

printf("%s  -grim/-odin  MBP_FILE...\n", $argv[0]);
$idtag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-grim':  $idtag = 'ps2_grim'; break;
		case '-odin':  $idtag = 'ps2_odin'; break;
		default:
			odin( $argv[$i], $idtag );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )

/*
mbp 4-01 valids
	grim 1 3 5 7 9 d 11 29
	odin 0 1 2 3 4 5 6 7 9 b d f 10 11 13 15 19 21 28 29 2d 2f 31 35 39
mbp 4-2 valids
	0 1 2
 */
