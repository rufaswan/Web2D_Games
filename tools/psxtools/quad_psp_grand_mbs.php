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

function colorquad( &$cqd, &$mbs, $pos )
{
	$s = substr($mbs, $pos, 4);
	$rgba = '#' . bin2hex($s);

	if ( $rgba == '#ffffffff' )
		$cqd[] = '1';
	else
	if ( $rgba == '#00000000' )
		$cqd[] = '0';
	else
		$cqd[] = $rgba;
	return;
}

function sectquad( &$mbs, $pos, &$sqd, &$dqd, &$cqd )
{
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 2 )
	{
		$p = ($pos * $mbs['k']) + $i;
		$b = str2int($mbs['d'], $p, 2, true);
		$float[] = $b / 0x10;
	}

	// sqd           dqd
	//  0  1   2  3   4  5   6  7  center
	//  8  9  10 11  12 13  14 15  c1
	// 16 17  18 19  20 21  22 23  c2
	// 24 25  26 27  28 29  30 31  c3
	// 32 33  34 35  36 37  38 39  c4
	// 40 41  42 43  44 45  46 47  c1
	$sqd = array(
		$float[ 8] , $float[ 9] ,
		$float[16] , $float[17] ,
		$float[24] , $float[25] ,
		$float[32] , $float[33] ,
	);
	$dqd = array(
		$float[12] , $float[13] ,
		$float[20] , $float[21] ,
		$float[28] , $float[29] ,
		$float[36] , $float[37] ,
	);

	//        cqd
	//  0  2   4   8  a   c  e  center
	// 10 12  14  18 1a  1c 1e  c1
	// 20 22  24  28 2a  2c 2e  c2
	// 30 32  34  38 3a  3c 3e  c3
	// 40 42  44  48 4a  4c 4e  c4
	// 50 52  54  58 5a  5c 5e  c1
	$p = $pos * $mbs['k'];
	$cqd = array();
	colorquad($cqd, $mbs['d'], $p+0x14);
	colorquad($cqd, $mbs['d'], $p+0x24);
	colorquad($cqd, $mbs['d'], $p+0x34);
	colorquad($cqd, $mbs['d'], $p+0x44);
	if ( implode('',$cqd) == '1111' )
		$cqd = '';
	return;
}
//////////////////////////////
//       frames        parts     quad
// gran  s3,18[10,14]  s1,c [a]  s8,60
function sectspr( &$json, &$mbs, $pfx )
{
	// s3-s1-s8 [18-c-60]
	$len3 = strlen( $mbs[3]['d'] );
	for ( $i3=0; $i3 < $len3; $i3 += $mbs[3]['k'] )
	{
		// 0 4 8 c   10 11  12 13  14  15 16 17
		// sizequad  id     -  -   no  -  -  -
		$id3 = str2int($mbs[3]['d'], $i3+0x10, 2);
		$no3 = str2int($mbs[3]['d'], $i3+0x14, 1);
		// DO NOT skip numbering
		// JSON will become {object} instead [array]

		$k3 = $i3 / $mbs[3]['k'];
		$data = array();
		for ( $i1=0; $i1 < $no3; $i1++ )
		{
			$p1 = ($id3 + $i1) * $mbs[1]['k'];
			$sqd = array();
			$dqd = array();
			$cqd = array();

			// 0 1 2 3  4 5 6 7  8 9  a b
			// sub      - - - -  - -  s8
			$sub = substr ($mbs[1]['d'], $p1+0 , 4);
			$s8  = str2int($mbs[1]['d'], $p1+10, 2); // quads
			sectquad($mbs[8], $s8, $sqd, $dqd, $cqd);

			$s1 = str2int($sub, 0, 2); // ??
			$s3 = ord( $sub[2] ); // mask
			$s4 = ord( $sub[3] ); // tid

			$data[$i1] = array();
			if ( $s1 & 2 )
				continue;

			$data[$i1]['DstQuad'] = $dqd;
			if ( ! empty($cqd) )
				$data[$i1]['ClrQuad']  = $cqd;

			//  1 layer normal
			//  2 layer top
			//  4 gradientFill
			//  8 attack box
			// 10
			// 20
			if ( ($s1 & 4) == 0 )
			{
				$data[$i1]['TexID']   = $s4;
				$data[$i1]['SrcQuad'] = $sqd;
			}
			quad_convexfix($data[$i1]);

	/*
			switch ( $s3 )
			{
				case 1:
					$data[$i1]['Blend'] = array('SUB', 1);
					break;
				case 2:
					$data[$i1]['Blend'] = array('ADD', 1);
					break;
				default: // 0 6
					//$data[$i1]['Blend'] = array('NORMAL', 1);
					break;
			} // switch ( $s3 )
	*/

		} // for ( $i4=0; $i4 < $no6; $i4++ )

		$json['Frame'][$k3] = $data;
	} // for ( $i3=0; $i3 < $len3; $i3 += $mbs[3]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbs, $pfx )
{
	// s6-s7-s5 [30-14-20]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10    28 29  2a  2b 2c 2d 2e 2f
		// - - - -  name  id     no  -  -  -  -  -
		$name = substr0($mbs[6]['d'], $i6+0x10);
		$id6  = str2int($mbs[6]['d'], $i6+0x28, 2);
		$no6  = str2int($mbs[6]['d'], $i6+0x2a, 1);

		for ( $i7=0; $i7 < $no6; $i7++ )
		{
			$p7 = ($id6 + $i7) * $mbs[7]['k'];

			// 0 1  2 3  4 5 6 7
			// id   no   - - - -
			$id7 = str2int($mbs[7]['d'], $p7+0, 2);
			$no7 = str2int($mbs[7]['d'], $p7+2, 2);

			$ent = array(
				'FID' => array(),
				'POS' => array(),
				'FPS' => array(),
			);
			$is_mov = false;
			for ( $i5=0; $i5 < $no7; $i5++ )
			{
				$p5 = ($id7 + $i5) * $mbs[5]['k'];

				// 0   2  4    6   8 c 10 14 18 1c
				// id  -  pos  no  - - -  -  -  -
				$id5 = str2int($mbs[5]['d'], $p5+0, 2);
				$id4 = str2int($mbs[5]['d'], $p5+4, 2);
				$no5 = str2int($mbs[5]['d'], $p5+6, 2);

				$p4 = $id4 * $mbs[4]['k'];
				$x4 = float32( substr($mbs[4]['d'], $p4+0, 4) );
				$y4 = float32( substr($mbs[4]['d'], $p4+4, 4) );

				$ent['FID'][] = $id5;
				$ent['FPS'][] = $no5;
				$ent['POS'][] = array($x4,$y4);

				if ( $x4 != 0 || $y4 != 0 )
					$is_mov = true;
			} // for ( $i5=0; $i5 < $no7; $i5++ )

			// skip all zero Pos
			if ( ! $is_mov )
				unset( $ent['POS'] );
			$json['Animation'][$name][$i7] = $ent;
		} // for ( $i7=0; $i7 < $no6; $i7++ )

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )

	return;
}
//////////////////////////////
function sect_addoff( &$file, &$sect )
{
	foreach ( $sect as $k => $v )
	{
		if ( ! isset($v['p']) )
			continue;
		$off = str2int($file, $v['p'], 4);
		if ( $off !== 0 )
			$sect[$k]['o'] = $off;
	}
	return;
}

function grand( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs, 0, 4) != "FMBS" )
		return;

	if ( str2int($mbs, 8, 4) != 0xe8 )
		return printf("DIFF not 0xe8  %s\n", $fname);

	// $siz = str2int($mbs, 4, 3);
	// $hdz = str2int($mbs, 8, 3);
	// $len = 0x10 + $hdz + $siz;
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	// - 0 1 2 |     3-0 2-1 6-2
	// 3 4 5 6 | 4-3 5-4 1-5 7-6
	// 7 8 - - | 8-7 s-8
	// Cut_In00.mbs
	//     -   - 314   - |           5*c
	//    e8 148 1b4 350 | 4*18 3*24 b*20 2*30
	//   3b0 400   -   - | 4*14 5*60
	// Witch00.mbs
	//      -   e8 5d98 758c |        19*50 1ff*c  36*8
	//    8b8  fc0 1878 773c | 4b*18  3e*24 229*20 7c*30
	//   8e7c 97a0    -    - | 75*14 1bc*60
	// s6[+28] =  68+d  => s7
	// s7[+ 0] = 228+1  => s5
	// s5[+ 0] =  4a    => s3 , [+ 4] = 3d   => s4
	// s3[+10] = 1ef+10 => s1 , [+12] = 36+0 => s2
	// s1[+ a] = 1bb    => s8
	// s8
	// s4
	// s2
	$sect = array(
		array('p' => 0x84 , 'k' => 0x50), // 0 cutin=0
		array('p' => 0x88 , 'k' => 0xc ), // 1
		array('p' => 0x8c , 'k' => 0x8 ), // 2 cutin=0
		array('p' => 0x90 , 'k' => 0x18), // 3
		array('p' => 0x94 , 'k' => 0x24), // 4
		array('p' => 0x98 , 'k' => 0x20), // 5
		array('p' => 0x9c , 'k' => 0x30), // 6
		array('p' => 0xa0 , 'k' => 0x14), // 7
		array('p' => 0xa4 , 'k' => 0x60), // 8
		array('o' => strrpos($mbs, "FEOC")),
	);
	sect_addoff($mbs, $sect);
	load_sect($mbs, $sect);
	save_sect($mbs, "$pfx/meta");

	$json = load_idtagfile('psp_gran');

	sectanim($json, $mbs, $pfx);
	sectspr ($json, $mbs, $pfx);

	save_quadfile($pfx, $json);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	grand( $argv[$i] );

/*
mbs 1-01 valids
	0 1 2 3 4 5 7 d 10 11 21 29 2b 2d
mbs 1-2 valids
	0 1 2
 */
