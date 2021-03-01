<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";

php_req_extension("json_encode", "json");

$gp_json = array();

function colorquad( &$cqd, &$mbs, $pos )
{
	$cqd[] = ord( $mbs[$pos+0] ) / BIT8; // r
	$cqd[] = ord( $mbs[$pos+1] ) / BIT8; // g
	$cqd[] = ord( $mbs[$pos+2] ) / BIT8; // b
	$cqd[] = ord( $mbs[$pos+3] ) / BIT8; // a
	return;
}

function sectquad_int( &$mbs, $pos, &$sqd, &$dqd, &$cqd )
{
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 2 )
	{
		$p = ($pos * $mbs['k']) + $i;
		$b = str2int($mbs['d'], $p, 2, true);
		$float[] = $b / 0x10;
	}

	// 1 4    1-2    8-20-18-10
	// | | =>   |  , c-24-1c-14
	// 2-3    4-3
	$sqd = array(
		$float[0x08] , $float[0x09] ,
		$float[0x20] , $float[0x21] ,
		$float[0x18] , $float[0x19] ,
		$float[0x10] , $float[0x11] ,
	);
	$dqd = array(
		$float[0x0c] , $float[0x0d] ,
		$float[0x24] , $float[0x25] ,
		$float[0x1c] , $float[0x1d] ,
		$float[0x14] , $float[0x15] ,
	);

	return;
}

function sectquad_float( &$mbs, $pos, &$sqd, &$dqd, &$cqd )
{
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$p = ($pos * $mbs['k']) + $i;
		$b = substr($mbs['d'], $p, 4);
		$float[] = float32($b);
	}

	//  0  1  2  3  4
	//  5  6  7  8  9
	// 10 11 12 13 14
	// 15 16 17 18 19
	// 20 21 22 23 24
	//   1 4    1-2    8-23-18-13
	//   | | =>   |  , 5-20-15-10
	//   2-3    4-3
	$sqd = array(
		$float[ 8] , $float[ 9] ,
		$float[23] , $float[24] ,
		$float[18] , $float[19] ,
		$float[13] , $float[14] ,
	);
	$dqd = array(
		$float[ 5] , $float[ 6] ,
		$float[20] , $float[21] ,
		$float[15] , $float[16] ,
		$float[10] , $float[11] ,
	);

	//  0  4  8  c 10
	// 14 18 1c 20 24
	// 28 2c 30 34 38
	// 3c 40 44 48 4c
	// 50 54 58 5c 60
	$p = $pos * $mbs['k'];
	$cqd = array();
	colorquad($cqd, $mbs['d'], $p+0x1c);
	colorquad($cqd, $mbs['d'], $p+0x58);
	colorquad($cqd, $mbs['d'], $p+0x44);
	colorquad($cqd, $mbs['d'], $p+0x30);
	if ( (int)array_sum($cqd) == 16 )
		$cqd = '';
	return;
}
//////////////////////////////
function sectpart( &$mbs, $pfx, $k3, $id3, $no3, $game )
{
	global $gp_json;

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
				sectquad_float($mbs[8], $s8, $sqd, $dqd, $cqd);
				break;
			case "gran":
				// 0 1 2 3  4 5 6 7  8 9  a b
				// sub      - - - -  - -  s8
				$sub = substr ($mbs[1]['d'], $p1+0 , 4);
				$s8  = str2int($mbs[1]['d'], $p1+10, 2); // quads
				sectquad_int($mbs[8], $s8, $sqd, $dqd, $cqd);
				break;
			case "odin":
				// 0 1 2 3  4 5 6 7  8 9 a b  c d e f
				// - - - -  sub      - - - -  - - s8
				//$sub = substr ($mbs[1]['d'], $p1+5 , 4);
				$sub = $mbs[1]['d'][$p1+5] . $mbs[1]['d'][$p1+4] . $mbs[1]['d'][$p1+6] . $mbs[1]['d'][$p1+7];
				$s8  = str2int($mbs[1]['d'], $p1+14, 2); // quads
				sectquad_float($mbs[9], $s8, $sqd, $dqd, $cqd);
				break;
		}

		$s1 = str2int($sub, 0, 2); // ??
		$s3 = ord( $sub[2] ); // mask
		$s4 = ord( $sub[3] ); // tid

		$data[$i1] = array();
		if ( $s1 & (4|2) )
			continue;

		$data[$i1]['DstQuad'] = $dqd;
		// if ( $cqd !== '' )
			//$data[$i1]['ClrQuad']  = $cqd;

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

		if ( $s3 != 0 )
		{
			$data[$i1]['Blend'] = array('ADD', 'ONE', 'ONE');
		}

	} // for ( $i4=0; $i4 < $no6; $i4++ )

	$gp_json['Frame'][$k3] = $data;
	return;
}

//       frames        parts     quad
// mura  s3,18[10,14]  s1,c [a]  s8,78
// drag  s3,1c[10,16]  s1,c [a]  s8,78
// gran  s3,18[10,14]  s1,c [a]  s8,60
// odin  s3,1c[10,16]  s1,10[e]  s9,78
function sectspr( &$mbs, $pfx, $game )
{
	$len3 = strlen( $mbs[3]['d'] );
	for ( $i3=0; $i3 < $len3; $i3 += $mbs[3]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		// 0 4 8 c  10 11  12 13 14 15  16  17 18 19 1a 1b
		// - - - -  id     -  -  -  -   no  -  -  -  -  -
		if ( $game == 'mura' || $game == 'gran' )
			$k3 = 0x14;
		if ( $game == 'drag' || $game == 'odin' )
			$k3 = 0x16;

		$id3 = str2int($mbs[3]['d'], $i3+0x10, 2);
		$no3 = str2int($mbs[3]['d'], $i3+$k3 , 1);
		// DO NOT skip numbering
		// JSON will become {object} instead [array]

		$k3 = $i3 / $mbs[3]['k'];
		sectpart($mbs, $pfx, $k3, $id3, $no3, $game);

	} // for ( $i3=0; $i3 < $len3; $i3 += $mbs[3]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$mbs, $pfx )
{
	global $gp_json;

	// s6-s7-s5 [30-14-20]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10
		// - - - -  name
		// 28 29  2a  2b 2c 2d 2e 2f
		// id     no  -  -  -  -  -
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

			$ent = array();
			for ( $i5=0; $i5 < $no7; $i5++ )
			{
				$p5 = ($id7 + $i5) * $mbs[5]['k'];

				// 0   2 4  6   8 c 10 14 18 1c
				// id  - -  no  - - -  -  -  -
				$id5 = str2int($mbs[5]['d'], $p5+0, 2);
				$no5 = str2int($mbs[5]['d'], $p5+6, 2);

				$ent[] = array($id5,$no5);

			} // for ( $i5=0; $i5 < $no7; $i5++ )

			$gp_json['Animation'][$name][$i7] = $ent;
		} // for ( $i7=0; $i7 < $no6; $i7++ )

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )

	return;
}
//////////////////////////////
function loadmbs( &$mbs, $sect, $pfx )
{
	$offs = array();
	$offs[] = strrpos($mbs, "FEOC");
	foreach ( $sect as $k => $v )
	{
		$b1 = str2int($mbs, $v['p'], 4);
		if ( $b1 == 0 )
			continue;
		$offs[] = $b1;
		$sect[$k]['o'] = $b1;
	}
	sort($offs);

	foreach ( $sect as $k => $v )
	{
		if ( ! isset( $v['o'] ) )
			continue;
		$id = array_search($v['o'], $offs);
		$sz = int_floor($offs[$id+1] - $v['o'], $v['k']);
		$dat = substr($mbs, $v['o'], $sz);

		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$mbs = $sect;
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
	//
	// Momohime_Battle_drm.mbs
	//       -     -     -   e0 |                     18*50
	//    c52c 17b24   860 2408 | f2a*c  f2*8  127*18 11*24
	//    266c 182b4 18ee4      | 4f6*20 41*30  87*14
	//   19970                  | 7df*78
	// s6[+28] =  85+2 => s7
	// s7[+ 0] = 4ee+8 => s5
	// s5[+ 0] = 126   => s3
	// s3[+10] = f29+1 => s1 , [+12] = f2+0 => s2
	// s1[+ a] = 7de   => s8
	// s8
	// s2
	//
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
	loadmbs($mbs, $sect, $pfx);

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx, "mura");
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
	// s6[+28] = 0+1 =>
	//
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
	//
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
	loadmbs($mbs, $sect, $pfx);

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx, "drag");
	return;
}

function head_e8( &$mbs, $pfx )
{
	printf("DETECT e8 = Grand Kinghts History [%s]\n", $pfx);
	// - 0 1 2 |     3-0 2-1 6-2
	// 3 4 5 6 | 4-3 5-4 1-5 7-6
	// 7 8 - - | 8-7 s-8
	// Cut_In00.mbs
	//     -   - 314   - |           5*c
	//    e8 148 1b4 350 | 4*18 3*24 b*20 2*30
	//   3b0 400   -   - | 4*14 5*60
	// s6[+28] = 2+2 =>
	//
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
	//
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
	);
	loadmbs($mbs, $sect, $pfx);

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx, "gran");
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
	// s6[+28] = 2c+3 => s7
	// s7[+ 0] = a3+2 => s5
	// s5[+ 0] = 27   => s3
	// s3[+10] = 64+1 => s1
	// s1[+ e] = 44   => s9
	// s9
	//
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
	//
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
	loadmbs($mbs, $sect, $pfx);

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx, "odin");
	return;
}
//////////////////////////////
function mura( $fname )
{
	$mbs = file_get_contents($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs, 0, 4) != "FMBS" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$typ = str2int($mbs, 8, 4);

	global $gp_json;
	$gp_json = array();

	$func = sprintf("head_%x", $typ);
	if ( ! function_exists($func) )
		return php_error("NO FUNC %s() for %s", $func, $fname);

	$func($mbs, $pfx);

	// JSON_PRETTY_PRINT
	// JSON_FORCE_OBJECT
	if ( ! empty($gp_json) )
		file_put_contents("$fname.quad", json_encode($gp_json));
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );

