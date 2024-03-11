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

$gp_data = array();

	$gp_data['ver55'] = array(
		// ps2_grim  ps2_odin
		// 3c   0 1  2 4 3 5 7 8 6 9  a
		array('p'=>0x54 , 'k'=>0x20 , 'c'=>array(0x3c,2)), // s0
		array('p'=>0x58 , 'k'=>0x20 , 'c'=>array(0x3e,2)), // s1
		array('p'=>0x5c , 'k'=>0x20 , 'c'=>array(0x40,2)), // s2
		array('p'=>0x60 , 'k'=>0x50 , 'c'=>array(0x44,2)), // s3 bg=0
		array('p'=>0x64 , 'k'=>0x18 , 'c'=>array(0x42,2)), // s4
		array('p'=>0x68 , 'k'=>0x8  , 'c'=>array(0x46,2)), // s5 bg=0
		array('p'=>0x6c , 'k'=>0x18 , 'c'=>array(0x4c,2)), // s6
		array('p'=>0x70 , 'k'=>0x30 , 'c'=>array(0x48,2)), // s7
		array('p'=>0x74 , 'k'=>0x20 , 'c'=>array(0x4a,2)), // s8
		array('p'=>0x78 , 'k'=>0x30 , 'c'=>array(0x4e,2)), // s9
		array('p'=>0x7c , 'k'=>0x8  , 'c'=>array(0x50,2)), // sa
	); // ver55
	$gp_data['ver66'] = array(
		// nds_kuma  wii_mura
		// 3c   0 1  2 4 3 5 7 8 6 9  a
		array('p'=>0x54 , 'k'=>0x18 , 'c'=>array(0x3c,2)), // s0
		array('p'=>0x58 , 'k'=>0x30 , 'c'=>array(0x3e,2)), // s1
		array('p'=>0x5c , 'k'=>0x30 , 'c'=>array(0x40,2)), // s2
		array('p'=>0x60 , 'k'=>0x50 , 'c'=>array(0x44,2)), // s3 bg=0
		array('p'=>0x64 , 'k'=>0xc  , 'c'=>array(0x42,2)), // s4
		array('p'=>0x68 , 'k'=>0x8  , 'c'=>array(0x46,2)), // s5 bg=0
		array('p'=>0x6c , 'k'=>0x18 , 'c'=>array(0x4c,2)), // s6
		array('p'=>0x70 , 'k'=>0x24 , 'c'=>array(0x48,2)), // s7
		array('p'=>0x74 , 'k'=>0x20 , 'c'=>array(0x4a,2)), // s8
		array('p'=>0x78 , 'k'=>0x30 , 'c'=>array(0x4e,2)), // s9
		array('p'=>0x7c , 'k'=>0x10 , 'c'=>array(0x50,2)), // sa
	); // ver66
	$gp_data['ver72'] = array(
		// ps4_drag  ps3_odin  ps4_odin  ps4_sent
		// 50   4+ 0 1 2 3 5 7  8 6 9 a - b
		array('p'=>0xb0  , 'k'=>0x18 , 'c'=>array(0x54,2)), // s0
		array('p'=>0xb8  , 'k'=>0x30 , 'c'=>array(0x56,2)), // s1
		array('p'=>0xc0  , 'k'=>0x30 , 'c'=>array(0x58,2)), // s2
		array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s3 bg=0
		array('p'=>0xd0  , 'k'=>0x14 , 'c'=>array(0x50,4)), // s4
		array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s5 bg=0
		array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x62,2)), // s6
		array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s7
		array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x60,2)), // s8
		array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x64,2)), // s9
		array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x66,2)), // sa
		array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x6a,2)), // sb bg=0
	); // ver72
	$gp_data['ver77'] = array(
		// swi_sent  swi_grim  swi_unic
		// 50   4+ 0+ 1+ 2+  3+ 5 7 8 6 9 a  - b
		array('p'=>0xb0  , 'k'=>0x18 , 'c'=>array(0x54,4)), // s0
		array('p'=>0xb8  , 'k'=>0x30 , 'c'=>array(0x58,4)), // s1
		array('p'=>0xc0  , 'k'=>0x30 , 'c'=>array(0x5c,4)), // s2
		array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x60,4)), // s3 bg=0
		array('p'=>0xd0  , 'k'=>0x14 , 'c'=>array(0x50,4)), // s4
		array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x64,2)), // s5 bg=0
		array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x6a,2)), // s6
		array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x66,2)), // s7
		array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x68,2)), // s8
		array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x6c,2)), // s9
		array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x6e,2)), // sa
		array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x72,2)), // sb bg=0
	); // ver77
	//////////////////////////////
	$gp_data['ps2_grim'] = array(
		'idtag' => 'ps2 grim grimoire',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver55'],
	); // ps2_grim

	$gp_data['swi_grim'] = array(
		'idtag' => 'switch grim grimoire hd',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver77'],
	); // swi_grim
	//////////////////////////////
	$gp_data['ps2_odin'] = array(
		'idtag' => 'ps2 odin sphere',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver55'],
	); // ps2_odin

	$gp_data['vit_odin'] = array(
		'idtag' => 'vita odin sphere resd',
		'ord'  => 'str2int',
		'sect' => array(
			// RESD_Oswald.mbs
			//      -   -   88*50 -
			// 100d1*10 -  20f*8 -
			//   c6e*1c - 1d51*24 -
			//  f899*20 -  219*30 -
			//  1176*18 -  160*14 -
			//      -   - bf6c*78 -
			array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s0 bg=0
			array('p'=>0xd0  , 'k'=>0x10 , 'c'=>array(0x50,4)), // s1
			array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s2 bg=0
			array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x62,2)), // s3
			array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s4
			array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x60,2)), // s5
			array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x64,2)), // s6
			array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x66,2)), // s7
			array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x6a,2)), // s8 bg=0
			array('p'=>0x118 , 'k'=>0x78 , 'c'=>array(0x6e,2)), // s9
		),
	); // vit_odin

	$gp_data['ps3_odin'] = array(
		'idtag' => 'ps3 odin sphere rehd',
		'ord'  => 'str2big',
		'sect' => $gp_data['ver72'],
	); // ps3_odin

	$gp_data['ps4_odin'] = array(
		'idtag' => 'ps4 odin sphere rehd',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver72'],
	); // ps4_odin
	//////////////////////////////
	$gp_data['wii_mura'] = array(
		'idtag' => 'wii muramasa',
		'ord'  => 'str2big',
		'sect' => $gp_data['ver66'],
	); // wii_mura

	$gp_data['vit_mura'] = array(
		'idtag' => 'vita muramasa rebirth',
		'ord'  => 'str2int',
		'sect' => array(
			// torahime00.mbs
			//     -      -     -   65*50
			// 9080*c  30a*8 493*18 cd*24
			//  7bb*20  6b*30 7a*14   -
			// 4271*78    -     -     -
			array('p'=>0x7c , 'k'=>0x50 , 'c'=>array(0x58,2)), // s0 bg=0
			array('p'=>0x80 , 'k'=>0xc  , 'c'=>array(0x56,2)), // s1
			array('p'=>0x84 , 'k'=>0x8  , 'c'=>array(0x5a,2)), // s2 bg=0
			array('p'=>0x88 , 'k'=>0x18 , 'c'=>array(0x60,2)), // s3
			array('p'=>0x8c , 'k'=>0x24 , 'c'=>array(0x5c,2)), // s4
			array('p'=>0x90 , 'k'=>0x20 , 'c'=>array(0x5e,2)), // s5
			array('p'=>0x94 , 'k'=>0x30 , 'c'=>array(0x62,2)), // s6
			array('p'=>0x98 , 'k'=>0x14 , 'c'=>array(0x64,2)), // s7
			array('p'=>0xa0 , 'k'=>0x78 , 'c'=>array(0x6a,2)), // s8
		),
	); // vit_mura
	//////////////////////////////
	$gp_data['vit_drag'] = array(
		'idtag' => 'vita dragon crown',
		'ord'  => 'str2int',
		'sect' => array(
			// Fighter00.mbs
			//  49*50 11479*c  5d0*8  b05*1c
			// 25f*24  2188*20 328*30 44e*14
			//    -    89ca*78    -      -
			array('p'=>0x80 , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s0 bg=0
			array('p'=>0x84 , 'k'=>0xc  , 'c'=>array(0x50,4)), // s1
			array('p'=>0x88 , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s2 bg=0
			array('p'=>0x8c , 'k'=>0x1c , 'c'=>array(0x64,2)), // s3
			array('p'=>0x90 , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s4
			array('p'=>0x94 , 'k'=>0x20 , 'c'=>array(0x60,2)), // s5
			array('p'=>0x98 , 'k'=>0x30 , 'c'=>array(0x66,2)), // s6
			array('p'=>0x9c , 'k'=>0x14 , 'c'=>array(0x68,2)), // s7
			array('p'=>0xa4 , 'k'=>0x78 , 'c'=>array(0x6e,2)), // s8
		),
	); // vit_drag

	$gp_data['ps3_drag'] = array(
		'idtag' => 'ps3 dragon crown',
		'ord'  => 'str2big',
		'sect' => array(
			// Fighter00.mbs
			//    -    1819*18 200*30 8329*30
			//  49*50 11479*c  5d0*8   b05*1c
			// 25f*24  2188*20 328*30  44e*14
			array('p'=>0x74 , 'k'=>0x18 , 'c'=>array(0x54,2)), // s0
			array('p'=>0x78 , 'k'=>0x30 , 'c'=>array(0x56,2)), // s1
			array('p'=>0x7c , 'k'=>0x30 , 'c'=>array(0x58,2)), // s2
			array('p'=>0x80 , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s3 bg=0
			array('p'=>0x84 , 'k'=>0xc  , 'c'=>array(0x50,4)), // s4
			array('p'=>0x88 , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s5 bg=0
			array('p'=>0x8c , 'k'=>0x1c , 'c'=>array(0x64,2)), // s6
			array('p'=>0x90 , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s7
			array('p'=>0x94 , 'k'=>0x20 , 'c'=>array(0x60,2)), // s8
			array('p'=>0x98 , 'k'=>0x30 , 'c'=>array(0x66,2)), // s9
			array('p'=>0x9c , 'k'=>0x14 , 'c'=>array(0x68,2)), // sa
		),
	); // ps3_drag

	$gp_data['ps4_drag'] = array(
		'idtag' => 'ps4 dragon crown pro',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver72'],
	); // ps4_drag
	//////////////////////////////
	$gp_data['ps4_sent'] = array(
		'idtag' => 'ps4 13 sentinels',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver72'],
	); // ps4_sent

	$gp_data['swi_sent'] = array(
		'idtag' => 'switch 13 sentinels',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver77'],
	); // swi_sent
	//////////////////////////////
	$gp_data['swi_unic'] = array(
		'idtag' => 'switch unicorn overlord',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver77'],
	); // swi_unic
	//////////////////////////////
	$gp_data['nds_kuma'] = array(
		'idtag' => 'nds kumatanchi',
		'ord'  => 'str2int',
		'sect' => $gp_data['ver66'],
	); // nds_kuma

	$gp_data['psp_gran'] = array(
		'idtag' => 'psp grand knights history',
		'ord'  => 'str2int',
		'sect' => array(
			// Golem01.mbs
			//    -      6*50 3597*c  31*8
			// 218*18   3f*24  319*20 55*30
			//  41*14 1f81*60     -     -
			array('p'=>0x84 , 'k'=>0x50 , 'c'=>array(0x58,2)), // s0 bg=0
			array('p'=>0x88 , 'k'=>0xc  , 'c'=>array(0x56,2)), // s1
			array('p'=>0x8c , 'k'=>0x8  , 'c'=>array(0x5a,2)), // s2 bg=0
			array('p'=>0x90 , 'k'=>0x18 , 'c'=>array(0x60,2)), // s3
			array('p'=>0x94 , 'k'=>0x24 , 'c'=>array(0x5c,2)), // s4
			array('p'=>0x98 , 'k'=>0x20 , 'c'=>array(0x5e,2)), // s5
			array('p'=>0x9c , 'k'=>0x30 , 'c'=>array(0x62,2)), // s6
			array('p'=>0xa0 , 'k'=>0x14 , 'c'=>array(0x64,2)), // s7
			array('p'=>0xa4 , 'k'=>0x60 , 'c'=>array(0x68,2)), // s8
		),
	); // psp_gran
//////////////////////////////
function fog_quad( &$s, $p1, $p2, $p3, $p4 )
{
	$quad = array(
		'#' . bin2hex( substr($s,$p1,4) ),
		'#' . bin2hex( substr($s,$p2,4) ),
		'#' . bin2hex( substr($s,$p3,4) ),
		'#' . bin2hex( substr($s,$p4,4) ),
	);
	if ( $quad[0] !== $quad[1] )  return $quad;
	if ( $quad[0] !== $quad[2] )  return $quad;
	if ( $quad[0] !== $quad[3] )  return $quad;
	return $quad[0];
}

function ps2_quad20p( &$mbp, $ord )
{
	$float = array();
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		$b = $ord($mbp, $i, 2, true);
		$float[] = $b / 0x10;
	}

	//  0  1
	//  2  3  center
	//  4  5  c0
	//  6  7  c1
	//  8  9  c2
	// 10 11  c3
	// 12 13  c0
	// 14 15  padding
	$quad = array(
		$float[ 4] , $float[ 5] ,
		$float[ 6] , $float[ 7] ,
		$float[ 8] , $float[ 9] ,
		$float[10] , $float[11] ,
	);
	return $quad;
}

function ps2_quad20c( &$mbp )
{
	for ( $i=0; $i < 0x20; $i++ )
	{
		$b = ord($mbp[$i]);
		$b = int_clamp($b << 1, 0, BIT8);
		$mbp[$i] = chr($b);
	}

	//  0
	//  4  center
	//  8  c0
	//  c  c1
	// 10  c2
	// 14  c3
	// 18  c1
	// 1c  padding
	$quad = fog_quad($mbp, 0x08, 0x0c, 0x10, 0x14);
	return $quad;
}

function nds_quad30p( &$mbs, $ord ) // +wii,ps3,ps4
{
	$float = array();
	for ( $i=0; $i < 0x30; $i += 4 )
	{
		$b = $ord($mbs, $i, 4);
		$float[] = float32($b);
	}

	//  0  1  center
	//  2  3  c0
	//  4  5  c1
	//  6  7  c2
	//  8  9  c3
	// 10 11  c0
	$quad = array(
		$float[2] , $float[3] ,
		$float[4] , $float[5] ,
		$float[6] , $float[7] ,
		$float[8] , $float[9] ,
	);
	return $quad;
}

function nds_quad18c( &$mbs ) // +wii,ps3,ps4
{
	//  0  center
	//  4  c0
	//  8  c1
	//  c  c2
	// 10  c3
	// 14  c0
	$quad = fog_quad($mbs, 0x04, 0x08, 0x0c, 0x10);
	return $quad;
}

function psp_quad60pc( &$mbs, $ord )
{
	$float = array();
	for ( $i=0; $i < 0x60; $i += 2 )
	{
		$b = $ord($mbs, $i, 2, true);
		$float[] = $b / 0x10;
	}

	// src           dst
	//  0  1   2  3   4  5   6  7  center
	//  8  9  10 11  12 13  14 15  c0
	// 16 17  18 19  20 21  22 23  c1
	// 24 25  26 27  28 29  30 31  c2
	// 32 33  34 35  36 37  38 39  c3
	// 40 41  42 43  44 45  46 47  c0
	$src = array(
		$float[ 8] , $float[ 9] ,
		$float[16] , $float[17] ,
		$float[24] , $float[25] ,
		$float[32] , $float[33] ,
	);
	$dst = array(
		$float[12] , $float[13] ,
		$float[20] , $float[21] ,
		$float[28] , $float[29] ,
		$float[36] , $float[37] ,
	);

	//        clr
	//  0  2   4   8  a   c  e  center
	// 10 12  14  18 1a  1c 1e  c0
	// 20 22  24  28 2a  2c 2e  c1
	// 30 32  34  38 3a  3c 3e  c2
	// 40 42  44  48 4a  4c 4e  c3
	// 50 52  54  58 5a  5c 5e  c0
	$clr = fog_quad($mbs, 0x14, 0x24, 0x34, 0x44);
	return array($clr, $src, $dst);
}

function vit_quad78pc( &$mbs, $ord )
{
	$float = array();
	for ( $i=0; $i < 0x78; $i += 4 )
	{
		$b = $ord($mbs, $i, 4);
		$float[] = float32($b);
	}

	// dst        src
	//  0  1   2   3  4  center
	//  5  6   7   8  9  c0
	// 10 11  12  13 14  c1
	// 15 16  17  18 19  c2
	// 20 21  22  23 24  c3
	// 25 26  27  28 29  c0
	$dst = array(
		$float[ 5] , $float[ 6] ,
		$float[10] , $float[11] ,
		$float[15] , $float[16] ,
		$float[20] , $float[21] ,
	);
	$src = array(
		$float[ 8] , $float[ 9] ,
		$float[13] , $float[14] ,
		$float[18] , $float[19] ,
		$float[23] , $float[24] ,
	);

	//        clr
	//  0  4   8   c 10  center
	// 14 18  1c  20 24  c0
	// 28 2c  30  34 38  c1
	// 3c 40  44  48 4c  c2
	// 50 54  58  5c 60  c3
	// 64 68  6c  70 74  c0
	$clr = fog_quad($mbs, 0x1c, 0x30, 0x44, 0x58);
	return array($clr, $src, $dst);
}
//////////////////////////////
function sect_head( $id )
{
	global $gp_share;
	$ord = $gp_share['data']['ord'];

	$sp = $ord($gp_share['file'], $gp_share['data']['sect'][$id]['p']   , 4);
	$sc = $ord($gp_share['file'], $gp_share['data']['sect'][$id]['c'][0], $gp_share['data']['sect'][$id]['c'][1]);
	$sk = $gp_share['data']['sect'][$id]['k'];

	$idx = $sc;
	$gp_share['idx'] = 0;
	while ( $idx > 0 )
	{
		$gp_share['idx']++;
		$idx >>= 4;
	}
	return array($sp,$sc,$sk);
}

function sect_data_txt( $i, $data )
{
	global $gp_share;

	$txt = sprintf("%{$gp_share['idx']}x :", $i);
	$len = strlen($data);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( ($i & 3) === 0 )
			$txt .= ' ';

		if ( $data[$i] === ZERO )
			$txt .= '-- ';
		else
			$txt .= sprintf('%2x ', ord($data[$i]));
	}
	$txt .= "\n";
	return $txt;
}
//////////////////////////////
