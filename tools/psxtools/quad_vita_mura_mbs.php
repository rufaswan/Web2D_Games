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
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$p = ($pos * $mbs['k']) + $i;
		$b = substr($mbs['d'], $p, 4);
		$float[] = float32($b);
	}

	cmp_quadxy($float, 5, 25);
	cmp_quadxy($float, 6, 26);
	cmp_quadxy($float, 8, 28);
	cmp_quadxy($float, 9, 29);

	// dqd        sqd
	//  0  1   2   3  4  center
	//  5  6   7   8  9  c1
	// 10 11  12  13 14  c2
	// 15 16  17  18 19  c3
	// 20 21  22  23 24  c4
	// 25 26  27  28 29  c1
	$dqd = array(
		$float[ 5] , $float[ 6] ,
		$float[10] , $float[11] ,
		$float[15] , $float[16] ,
		$float[20] , $float[21] ,
	);
	$sqd = array(
		$float[ 8] , $float[ 9] ,
		$float[13] , $float[14] ,
		$float[18] , $float[19] ,
		$float[23] , $float[24] ,
	);

	//        cqd
	//  0  4   8   c 10  center
	// 14 18  1c  20 24  c1
	// 28 2c  30  34 38  c2
	// 3c 40  44  48 4c  c3
	// 50 54  58  5c 60  c4
	// 64 68  6c  70 74  c1
	$p = $pos * $mbs['k'];
	$cqd = array();
	colorquad($cqd, $mbs['d'], $p+0x1c);
	colorquad($cqd, $mbs['d'], $p+0x30);
	colorquad($cqd, $mbs['d'], $p+0x44);
	colorquad($cqd, $mbs['d'], $p+0x58);
	if ( implode('',$cqd) == '1111' )
		$cqd = '';
	return;
}
//////////////////////////////
//       frames        parts     quad
// mura  s3,18[10,14]  s1,c [a]  s8,78
// drag  s3,1c[10,16]  s1,c [a]  s8,78
// gran  s3,18[10,14]  s1,c [a]  s8,60
// odin  s3,1c[10,16]  s1,10[e]  s9,78
function sectspr( &$json, &$mbs, $pfx, $game )
{
	// s3-s1-s8/s9
	$len3 = strlen( $mbs[3]['d'] );
	for ( $i3=0; $i3 < $len3; $i3 += $mbs[3]['k'] )
	{
		switch ( $game )
		{
			case 'mura':
				// 0 4 8 c   10 11  12 13  14  15 16 17
				// sizequad  id     -  -   no  -  -  -
				$id3 = str2int($mbs[3]['d'], $i3+0x10, 2);
				$no3 = str2int($mbs[3]['d'], $i3+0x14, 1);
				break;
			case 'drag':
			case 'odin':
				// 0 4 8 c   10 11  12 13 14 15  16  17 18 19 1a 1b
				// sizequad  id     -  -  -  -   no  -  -  -  -  -
				$id3 = str2int($mbs[3]['d'], $i3+0x10, 2);
				$no3 = str2int($mbs[3]['d'], $i3+0x16, 1);
				break;
		} // switch ( $game )
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
			switch ( $game )
			{
				case "mura":
				case "drag":
					// 0 1 2 3  4 5 6 7  8 9  a b
					// sub      - - - -  - -  s8
					$sub = substr ($mbs[1]['d'], $p1+0 , 4);
					$s8  = str2int($mbs[1]['d'], $p1+10, 2); // quads
					sectquad($mbs[8], $s8, $sqd, $dqd, $cqd);
					break;
				case "odin":
					// 0 1 2 3  4 5 6 7  8 9 a b  c d e f
					// - - - -  sub      - - - -  - - s8
					//$sub = substr ($mbs[1]['d'], $p1+5 , 4);
					$sub = $mbs[1]['d'][$p1+5] . $mbs[1]['d'][$p1+4] . $mbs[1]['d'][$p1+6] . $mbs[1]['d'][$p1+7];
					$s8  = str2int($mbs[1]['d'], $p1+14, 2); // quads
					sectquad($mbs[9], $s8, $sqd, $dqd, $cqd);
					break;
			}

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
function sectanim( &$json, &$mbs, $pfx, $game )
{
	// s6-s7-s5-s4 [30-14/18-20-24]
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

			switch ( $game )
			{
				case 'mura':
				case 'drag':
					// 0   2   4  6  8 c  10 12
					// id  no  -  -  - -  -  -
					$id7 = str2int($mbs[7]['d'], $p7+0, 2);
					$no7 = str2int($mbs[7]['d'], $p7+2, 2);
					break;
				case 'odin':
					// 0   2   4  6  8 c  10 12 14 16
					// id  no  -  -  - -  -  -  -  -
					$id7 = str2int($mbs[7]['d'], $p7+0, 2);
					$no7 = str2int($mbs[7]['d'], $p7+2, 2);
					break;
			} // switch ( $game )

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
function head_e0( &$mbs, $pfx )
{
	printf("DETECT e0 = Muramasa Rebirth [%s]\n", $pfx);
	// - - - 0 |             3-0
	// 1 2 3 4 | 2-1 6-2 4-3 5-4
	// 5 6 7 - | 1-5 7-6 8-7
	// 8 - - - | s-8
	// P3_bg01_04.mbs
	//     -   -   -  - |
	//   13c   -  e0 f8 | 1*c       1*18 1*24
	//   11c 148 178  - | 1*20 1*30 1*14
	//   18c   -   -  - | 1*78
	// Momohime_Battle_drm.mbs
	//       -     -     -   e0 |                     18*50
	//    c52c 17b24   860 2408 | f2a*c  f2*8  127*18 11*24
	//    266c 182b4 18ee4      | 4f6*20 41*30  87*14
	//   19970                  | 7df*78
	// s6[+28] =  85+2 => s7
	// s7[+ 0] = 4ee+8 => s5
	// s5[+ 0] = 126   => s3 , [+ 4] = => s4
	// s3[+10] = f29+1 => s1 , [+12] = f2+0 => s2
	// s1[+ a] = 7de   => s8
	// s8
	// s2
	$sect = array(
		array('p' => 0x7c , 'k' => 0x50), // 0 bg=0
		array('p' => 0x80 , 'k' => 0xc ), // 1
		array('p' => 0x84 , 'k' => 0x8 ), // 2 bg=0
		array('p' => 0x88 , 'k' => 0x18), // 3
		array('p' => 0x8c , 'k' => 0x24), // 4
		array('p' => 0x90 , 'k' => 0x20), // 5
		array('p' => 0x94 , 'k' => 0x30), // 6
		array('p' => 0x98 , 'k' => 0x14), // 7
		array('p' => 0xa0 , 'k' => 0x78), // 8
	);
	file2sect($mbs, $sect, $pfx, array('str2int', 4), strrpos($mbs, "FEOC"), METAFILE);
	if ( METAFILE )
	{
		sect_sum($mbs[1], 'mbs[1][0]', 0); //
		sect_sum($mbs[1], 'mbs[1][1]', 1); // = 0
		sect_sum($mbs[1], 'mbs[1][2]', 2); //
	}

	$json = load_idtagfile('vita_mura');

	sectanim($json, $mbs, $pfx, "mura");
	sectspr ($json, $mbs, $pfx, "mura");

	save_quadfile($pfx, $json);
	return;
}

function head_e4( &$mbs, $pfx )
{
	printf("DETECT e4 = Dragon Crown [%s]\n", $pfx);
	// 0 1 2 3 | 3-0 2-1 6-2 4-3
	// 4 5 6 7 | 5-4 1-5 7-6 8-7
	// - 8 - - |     s-8
	// bg14a_01.mbs
	//     - 144   -  e4 |      1*c       1*1c
	//   100 124 150 180 | 1*24 1*20 1*30 1*14
	//     - 194   -   - |      1*78
	// Sorceress00.mbs
	//     e4 3bd8c d08e0  c254 | 26b*50 c647*c  30f*8  6ae*1c
	//  17d5c 1f40c d2158 d8098 | 34c*24  e4c*20 1fc*30 232*14
	//      - dac80     -     - |        6486*78
	// s6[+28] =  22f+3  => s7
	// s7[+ 0] =  e45+7  => s5
	// s5[+ 0] =  6ad    => s3 , [+ 4] = 34b   => s4
	// s3[+10] = c629+1e => s1 , [+14] = 30f+0 => s2
	// s1[+ a] = 6485    => s8
	// s8
	// s4
	// s2
	$sect = array(
		array('p' => 0x80 , 'k' => 0x50), // 0 bg=0
		array('p' => 0x84 , 'k' => 0xc ), // 1
		array('p' => 0x88 , 'k' => 0x8 ), // 2 bg=0
		array('p' => 0x8c , 'k' => 0x1c), // 3
		array('p' => 0x90 , 'k' => 0x24), // 4
		array('p' => 0x94 , 'k' => 0x20), // 5
		array('p' => 0x98 , 'k' => 0x30), // 6
		array('p' => 0x9c , 'k' => 0x14), // 7
		array('p' => 0xa4 , 'k' => 0x78), // 8
	);
	file2sect($mbs, $sect, $pfx, array('str2int', 4), strrpos($mbs, "FEOC"), METAFILE);
	if ( METAFILE )
	{
		sect_sum($mbs[1], 'mbs[1][0]', 0); //
		sect_sum($mbs[1], 'mbs[1][1]', 1); // = 0
		sect_sum($mbs[1], 'mbs[1][2]', 2); //
	}

	$json = load_idtagfile('vita_drag');

	sectanim($json, $mbs, $pfx, "drag");
	sectspr ($json, $mbs, $pfx, "drag");

	save_quadfile($pfx, $json);
	return;
}

function head_120( &$mbs, $pfx )
{
	printf("DETECT 120 = Odin Sphere Leifthsar [%s]\n", $pfx);
	// - - 0 - |     3-0
	// 1 - 2 - | 2-1 6-2
	// 3 - 4 - | 4-3 5-4
	// 5 - 6 - | 1-5 7-6
	// 7 - 8 - | 8-7 9-8
	// - - 9 - |     s-9
	// SD_Booter_UI.mbs
	//      - -    - - |
	//   2008 -    - - | 65*10
	//    120 -  580 - | 28*1c  2a*24
	//    b68 - 2658 - | a5*20 3af*30
	//   d728 -    - - | 2f*18
	//      - - db90 - |        45*78
	// Gwendlyn.mbs
	//       - -   120 - |           fb*50
	//   2e8d0 - 5b7b0 - | 2cee*10  20d*8
	//    4f90 -  b094 - |  377*1c  2b7*24
	//   11250 - 5c818 - |  eb4*20   c8*30
	//   5ed98 - 61498 - |  1a0*18   72*14
	//       - - 61d80 - |         259b*78
	// s6[+28] =  19d+3  => s7
	// s7[+ 0] =  eb1+3  => s5
	// s5[+ 0] =  376    => s3 , [+ 4] = 2b6   => s4
	// s3[+10] = 2cd1+1d => s1 , [+14] = 20c+1 => s2
	// s1[+ e] = 259a    => s9
	// s9
	// s4
	// s2
	$sect = array(
		array('p' => 0xc8  , 'k' => 0x50), // 0 sd=0
		array('p' => 0xd0  , 'k' => 0x10), // 1
		array('p' => 0xd8  , 'k' => 0x8 ), // 2 sd=0
		array('p' => 0xe0  , 'k' => 0x1c), // 3
		array('p' => 0xe8  , 'k' => 0x24), // 4
		array('p' => 0xf0  , 'k' => 0x20), // 5
		array('p' => 0xf8  , 'k' => 0x30), // 6
		array('p' => 0x100 , 'k' => 0x18), // 7
		array('p' => 0x108 , 'k' => 0x14), // 8 sd=0
		array('p' => 0x118 , 'k' => 0x78), // 9
	);
	file2sect($mbs, $sect, $pfx, array('str2int', 4), strrpos($mbs, "FEOC"), METAFILE);
	if ( METAFILE )
	{
		sect_sum($mbs[1], 'mbs[1][0]', 0); //
		sect_sum($mbs[1], 'mbs[1][1]', 1); //
		sect_sum($mbs[1], 'mbs[1][2]', 2); // = 0
		sect_sum($mbs[1], 'mbs[1][3]', 3); // = 0
		sect_sum($mbs[1], 'mbs[1][4]', 4); // = 0
		sect_sum($mbs[1], 'mbs[1][5]', 5); //
		sect_sum($mbs[1], 'mbs[1][6]', 6); //
	}

	$json = load_idtagfile('vita_odin');

	sectanim($json, $mbs, $pfx, "odin");
	sectspr ($json, $mbs, $pfx, "odin");

	save_quadfile($pfx, $json);
	return;
}
//////////////////////////////
function mura( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs, 0, 4) != "FMBS" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$typ = str2int($mbs, 8, 4);

	$func = sprintf("head_%x", $typ);
	if ( ! function_exists($func) )
		return php_error("NO FUNC %s() for %s", $func, $fname);

	$func($mbs, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );

/*
mbs 1-01 valids
	mura      0 1 2 3 4 5 7 9 11 13 15 21 29 2d
	mura dlc  0 1 3 4 5 7 9 11 13 15 21 29 2d 2f 39 3d
	drag      0 1 2 3 4 5 7 9 11 21 23 29 31
mbs 1-2 valids
	mura      0 1 2 6
	mura dlc  0 1 2 6
	drag      0 1 2 6

mbs 1-0 valids
	odin
		 0  1  2  3  4  5  6  7  8  9  a  b  c  d  e  f
		10 11 12 13 14 15 16 17 18 19 1a 1b 1c 1d 1e 1f
		20 21 22 23 24 25 26 27 28 29 2a 2b 2c 2d 2e 2f
		30 31 32 33 34 35 36 37 38 39 3a 3b 3c 3d 3e 3f
		40 41 42 43 44 45 46 47 48 49 4a 4b 4c 4d 4e 4f
		50 51 52 53 54 55 56 57 58 59 5a 5b 5c 5d 5e 5f
		60 61 62 63 64 65 66 67 68 69 6a 6b 6c 6d 6e 6f
		70 71 72 73 74 75 76 77 78 79 7a 7b 7c 7d 7e 7f
		80 81 82 83 84 85 86 87 88 89 8a 8b 8c 8d 8e 8f
		90 91 92 93 94 95 96 97 98 99 9a 9b 9c 9d 9e 9f
		a0 a1 a2 a3 a4 a5 a6 a7 a8 a9 aa ab ac ad ae af
		b0 b1 b2 b3 b4 b5 b6 b7 b8 b9 ba bb bc bd be bf
		c0 c1 c2 c3 c4 c5 c6 c7 c8 c9 ca cb cc cd ce cf
		d0 d1 d2 d3 d4 d5 d6 d7 d8 d9 da db dc dd de df
		e0 e1 e2 e3 e4 e5 e6 e7 e8 e9 ea eb ec ee ed ef
		f0 f1 f2 f3 f4 f5 f6 f7 f8 f9 fa fb fc fd fe ff
mbs 1-1 valids
	odin  0 1 2 3 4 5 6 7 8 9 b c d f 10 13 14 17 1b 1f 23 27 29 2a 2b 2e 2f 32 36 4e 4f 75
mbs 1-2 valids
	odin  0
mbs 1-3 valids
	odin  0
mbs 1-4 valids
	odin  0
mbs 1-5 valids
	odin  0 1 2 3 4 5 6 7 9 b d f 10 11 13 15 19 21 23 28 29 2b 2d 2f 31 35 39
mbs 1-6 valids
	odin  0 1 2 6
mbs 1-7 valids
	odin  0 1 2 3 4 5 6 7 8 9 a b c d

odin alice
	ps2      1196 frames
	vita or  1196 frames
	vita re   956 frames

Okoi00 - BACK s6/0+6
	-  s7      s5                             s4
	0   0       -                           -   0.0     0.0
	1   1- 7  2a3 2a9 2aa 2ab 2ac 2ad 2ae   1 -14.66  -84.52
	2   8- e   46  8c  8d  8e  8f  90  91   8   0.0     3.0
	3   f-10   4a   -                       8   0.0     3.0
	4  11-16    4   4   4   4   4   4   4   f -14.0  -179.5
	5  17       6                          15  -8.33 -145.33
 */
