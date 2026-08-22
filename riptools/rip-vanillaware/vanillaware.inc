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
declare( strict_types=1 );

tool::class('ieee754');

$gp_data = [];

	//////////////////////////////
	$gp_data['ver55'] = [
		// ps2_grim  ps2_odin
		// 3c   0 1  2 4 3 5 7 8 6 9  a
		['p'=>0x54 , 'k'=>0x20 , 'c'=>[0x3c,2]], // s0
		['p'=>0x58 , 'k'=>0x20 , 'c'=>[0x3e,2]], // s1
		['p'=>0x5c , 'k'=>0x20 , 'c'=>[0x40,2]], // s2
		['p'=>0x60 , 'k'=>0x50 , 'c'=>[0x44,2]], // s3 bg=0
		['p'=>0x64 , 'k'=>0x18 , 'c'=>[0x42,2]], // s4
		['p'=>0x68 , 'k'=>0x8  , 'c'=>[0x46,2]], // s5 bg=0
		['p'=>0x6c , 'k'=>0x18 , 'c'=>[0x4c,2]], // s6
		['p'=>0x70 , 'k'=>0x30 , 'c'=>[0x48,2]], // s7
		['p'=>0x74 , 'k'=>0x20 , 'c'=>[0x4a,2]], // s8
		['p'=>0x78 , 'k'=>0x30 , 'c'=>[0x4e,2]], // s9
		['p'=>0x7c , 'k'=>0x8  , 'c'=>[0x50,2]], // sa
	]; // ver55

	$gp_data['ps2_grim'] = [
		'idtag'  => 'ps2 grim grimoire',
		'bigend' => false,
		'sect'  => &$gp_data['ver55'],
	]; // ps2_grim
	$gp_data['ps2_odin'] = [
		'idtag'  => 'ps2 odin sphere',
		'bigend' => false,
		'sect'  => &$gp_data['ver55'],
	]; // ps2_odin
	//////////////////////////////
	$gp_data['ver66'] = [
		// nds_kuma  wii_mura
		// 3c   0 1  2 4 3 5 7 8 6 9  a
		['p'=>0x54 , 'k'=>0x18 , 'c'=>[0x3c,2]], // s0
		['p'=>0x58 , 'k'=>0x30 , 'c'=>[0x3e,2]], // s1
		['p'=>0x5c , 'k'=>0x30 , 'c'=>[0x40,2]], // s2
		['p'=>0x60 , 'k'=>0x50 , 'c'=>[0x44,2]], // s3 bg=0
		['p'=>0x64 , 'k'=>0xc  , 'c'=>[0x42,2]], // s4
		['p'=>0x68 , 'k'=>0x8  , 'c'=>[0x46,2]], // s5 bg=0
		['p'=>0x6c , 'k'=>0x18 , 'c'=>[0x4c,2]], // s6
		['p'=>0x70 , 'k'=>0x24 , 'c'=>[0x48,2]], // s7
		['p'=>0x74 , 'k'=>0x20 , 'c'=>[0x4a,2]], // s8
		['p'=>0x78 , 'k'=>0x30 , 'c'=>[0x4e,2]], // s9
		['p'=>0x7c , 'k'=>0x10 , 'c'=>[0x50,2]], // sa
	]; // ver66

	$gp_data['nds_kuma'] = [
		'idtag'  => 'nds kumatanchi',
		'bigend' => false,
		'sect'  => &$gp_data['ver66'],
	]; // nds_kuma
	$gp_data['wii_mura'] = [
		'idtag'  => 'wii muramasa',
		'bigend' => true,
		'sect'   => &$gp_data['ver66'],
	]; // wii_mura
	//////////////////////////////
	// ver6e
	$gp_data['ps3_drag'] = [
		'idtag'  => 'ps3 dragon crown',
		'bigend' => true,
		'sect'   => [
			// 50  4+  0 1 2 3 4 5 7  8 - 6 9 a
			['p'=>0x74 , 'k'=>0x18 , 'c'=>[0x54,2]], // s0
			['p'=>0x78 , 'k'=>0x30 , 'c'=>[0x56,2]], // s1
			['p'=>0x7c , 'k'=>0x30 , 'c'=>[0x58,2]], // s2
			['p'=>0x80 , 'k'=>0x50 , 'c'=>[0x5a,2]], // s3 bg=0
			['p'=>0x84 , 'k'=>0xc  , 'c'=>[0x50,4]], // s4
			['p'=>0x88 , 'k'=>0x8  , 'c'=>[0x5c,2]], // s5 bg=0
			['p'=>0x8c , 'k'=>0x1c , 'c'=>[0x64,2]], // s6
			['p'=>0x90 , 'k'=>0x24 , 'c'=>[0x5e,2]], // s7
			['p'=>0x94 , 'k'=>0x20 , 'c'=>[0x60,2]], // s8
			['p'=>0x98 , 'k'=>0x30 , 'c'=>[0x66,2]], // s9
			['p'=>0x9c , 'k'=>0x14 , 'c'=>[0x68,2]], // sa
		],
	]; // ps3_drag
	//////////////////////////////
	$gp_data['ver72'] = [
		// ps4_drag  ps3_odin  ps4_odin
		// 50   4+ 0 1 2 3 5 7  8 6 9 a - b
		['p'=>0xb0  , 'k'=>0x18 , 'c'=>[0x54,2]], // s0
		['p'=>0xb8  , 'k'=>0x30 , 'c'=>[0x56,2]], // s1
		['p'=>0xc0  , 'k'=>0x30 , 'c'=>[0x58,2]], // s2
		['p'=>0xc8  , 'k'=>0x50 , 'c'=>[0x5a,2]], // s3 bg=0
		['p'=>0xd0  , 'k'=>0x10 , 'c'=>[0x50,4]], // s4
		['p'=>0xd8  , 'k'=>0x8  , 'c'=>[0x5c,2]], // s5 bg=0
		['p'=>0xe0  , 'k'=>0x1c , 'c'=>[0x62,2]], // s6
		['p'=>0xe8  , 'k'=>0x24 , 'c'=>[0x5e,2]], // s7
		['p'=>0xf0  , 'k'=>0x20 , 'c'=>[0x60,2]], // s8
		['p'=>0xf8  , 'k'=>0x30 , 'c'=>[0x64,2]], // s9
		['p'=>0x100 , 'k'=>0x18 , 'c'=>[0x66,2]], // sa
		['p'=>0x108 , 'k'=>0x14 , 'c'=>[0x6a,2]], // sb bg=0
	]; // ver72

	$gp_data['ps3_odin'] = [
		'idtag'  => 'ps3 odin sphere',
		'bigend' => true,
		'sect'   => &$gp_data['ver72'],
	]; // ps3_odin
	$gp_data['ps4_odin'] = [
		'idtag'  => 'ps4 odin sphere',
		'bigend' => false,
		'sect'   => &$gp_data['ver72'],
	]; // ps4_odin
	$gp_data['ps4_drag'] = [
		'idtag'  => 'ps4 dragon crown',
		'bigend' => false,
		'sect'   => &$gp_data['ver72'],
	]; // ps4_drag
	//////////////////////////////
	// ver76
	$gp_data['ps4_sent'] = [
		'idtag'  => 'ps4 13 sentinels',
		'bigend' => false,
		'sect'   => [
			// 50  4+ 0 1 2 3 5 7  8 6 9 a - b
			['p'=>0xb0  , 'k'=>0x18 , 'c'=>[0x54,2]], // s0
			['p'=>0xb8  , 'k'=>0x30 , 'c'=>[0x56,2]], // s1
			['p'=>0xc0  , 'k'=>0x30 , 'c'=>[0x58,2]], // s2
			['p'=>0xc8  , 'k'=>0x50 , 'c'=>[0x5a,2]], // s3 bg=0
			['p'=>0xd0  , 'k'=>0x14 , 'c'=>[0x50,4]], // s4
			['p'=>0xd8  , 'k'=>0x8  , 'c'=>[0x5c,2]], // s5 bg=0
			['p'=>0xe0  , 'k'=>0x1c , 'c'=>[0x62,2]], // s6
			['p'=>0xe8  , 'k'=>0x24 , 'c'=>[0x5e,2]], // s7
			['p'=>0xf0  , 'k'=>0x20 , 'c'=>[0x60,2]], // s8
			['p'=>0xf8  , 'k'=>0x30 , 'c'=>[0x64,2]], // s9
			['p'=>0x100 , 'k'=>0x18 , 'c'=>[0x66,2]], // sa
			['p'=>0x108 , 'k'=>0x14 , 'c'=>[0x6a,2]], // sb bg=0
		],
	]; // ps4_sent
	//////////////////////////////
	$gp_data['ver77'] = [
		// swi_sent  swi_grim  swi_unic
		// 50   4+ 0+ 1+ 2+  3+ 5 7 8 6 9 a  - b
		['p'=>0xb0  , 'k'=>0x18 , 'c'=>[0x54,4]], // s0
		['p'=>0xb8  , 'k'=>0x30 , 'c'=>[0x58,4]], // s1
		['p'=>0xc0  , 'k'=>0x30 , 'c'=>[0x5c,4]], // s2
		['p'=>0xc8  , 'k'=>0x50 , 'c'=>[0x60,4]], // s3 bg=0
		['p'=>0xd0  , 'k'=>0x14 , 'c'=>[0x50,4]], // s4
		['p'=>0xd8  , 'k'=>0x8  , 'c'=>[0x64,2]], // s5 bg=0
		['p'=>0xe0  , 'k'=>0x1c , 'c'=>[0x6a,2]], // s6
		['p'=>0xe8  , 'k'=>0x24 , 'c'=>[0x66,2]], // s7
		['p'=>0xf0  , 'k'=>0x20 , 'c'=>[0x68,2]], // s8
		['p'=>0xf8  , 'k'=>0x30 , 'c'=>[0x6c,2]], // s9
		['p'=>0x100 , 'k'=>0x18 , 'c'=>[0x6e,2]], // sa
		['p'=>0x108 , 'k'=>0x14 , 'c'=>[0x72,2]], // sb bg=0
	]; // ver77

	$gp_data['swi_sent'] = [
		'idtag'  => 'swit 13 sentinels',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // swi_sent
	$gp_data['swi_grim'] = [
		'idtag'  => 'swit grim grimoire',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // swi_grim
	$gp_data['ps4_grim'] = [
		'idtag'  => 'ps4 grim grimoire',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // ps4_grim
	$gp_data['ps5_grim'] = [
		'idtag'  => 'ps5 grim grimoire',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // ps5_grim
	$gp_data['swi_unic'] = [
		'idtag'  => 'swit unicorn overlord',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // swi_unic
	$gp_data['ps4_unic'] = [
		'idtag'  => 'ps4 unicorn overlord',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // ps4_unic
	$gp_data['ps5_unic'] = [
		'idtag'  => 'ps5 unicorn overlord',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // ps5_unic
	$gp_data['xbx_unic'] = [
		'idtag'  => 'xbsx unicorn overlord',
		'bigend' => false,
		'sect'   => &$gp_data['ver77'],
	]; // xbx_unic
	//////////////////////////////
	// ver55 -> ver66
	//   s0 *20 -> *18
	//   s1s2 *20 -> *30
	//   s4 *18 -> *c
	//   s7 *30 -> *24
	//   sa *8  -> *10
	// ver66 -> ver6e
	//   head_off 3c -> 50
	//   head 4 -> 4+
	//   s6 *18 -> *1c
	//   sa *10 -> *14
	// ver6e -> ver72
	//   new sb
	//   s4 *c  -> *10
	//   sa *14 -> *18
	// ver72 -> ver76
	//   s1 pre-divided
	//   s4 *10 -> *14
	// ver76 -> ver77
	//   head 0 1 2 3 -> 0+ 1+ 2+ 3+
	//////////////////////////////
	// ver6b-m
	$gp_data['psp_gran'] = [
		'idtag'  => 'psp grand knights history',
		'bigend' => false,
		'sect'   => [
			// 56  1 0 2 4 5  3 6 7 - - 8
			['p'=>0x84 , 'k'=>0x50 , 'c'=>[0x58,2]], // s0 bg=0
			['p'=>0x88 , 'k'=>0xc  , 'c'=>[0x56,2]], // s1
			['p'=>0x8c , 'k'=>0x8  , 'c'=>[0x5a,2]], // s2 bg=0
			['p'=>0x90 , 'k'=>0x18 , 'c'=>[0x60,2]], // s3
			['p'=>0x94 , 'k'=>0x24 , 'c'=>[0x5c,2]], // s4
			['p'=>0x98 , 'k'=>0x20 , 'c'=>[0x5e,2]], // s5
			['p'=>0x9c , 'k'=>0x30 , 'c'=>[0x62,2]], // s6
			['p'=>0xa0 , 'k'=>0x14 , 'c'=>[0x64,2]], // s7
			['p'=>0xa4 , 'k'=>0x60 , 'c'=>[0x68,2]], // s8
		],
	]; // psp_gran
	//////////////////////////////
	// ver6d-m
	$gp_data['vit_mura'] = [
		'idtag'  => 'vita muramasa',
		'bigend' => false,
		'sect'   => [
			// 56  1 0 2 4 5  3 6 7 - - 8
			['p'=>0x7c , 'k'=>0x50 , 'c'=>[0x58,2]], // s0 bg=0
			['p'=>0x80 , 'k'=>0xc  , 'c'=>[0x56,2]], // s1
			['p'=>0x84 , 'k'=>0x8  , 'c'=>[0x5a,2]], // s2 bg=0
			['p'=>0x88 , 'k'=>0x18 , 'c'=>[0x60,2]], // s3
			['p'=>0x8c , 'k'=>0x24 , 'c'=>[0x5c,2]], // s4
			['p'=>0x90 , 'k'=>0x20 , 'c'=>[0x5e,2]], // s5
			['p'=>0x94 , 'k'=>0x30 , 'c'=>[0x62,2]], // s6
			['p'=>0x98 , 'k'=>0x14 , 'c'=>[0x64,2]], // s7
			['p'=>0xa0 , 'k'=>0x78 , 'c'=>[0x6a,2]], // s8
		],
	]; // vit_mura
	//////////////////////////////
	// ver6e-m
	$gp_data['vit_drag'] = [
		'idtag'  => 'vita dragon crown',
		'bigend' => false,
		'sect'   => [
			// 50  1+ - - - 0 2 4  5 - 3 6 7 - 8
			['p'=>0x80 , 'k'=>0x50 , 'c'=>[0x5a,2]], // s0 bg=0
			['p'=>0x84 , 'k'=>0xc  , 'c'=>[0x50,4]], // s1
			['p'=>0x88 , 'k'=>0x8  , 'c'=>[0x5c,2]], // s2 bg=0
			['p'=>0x8c , 'k'=>0x1c , 'c'=>[0x64,2]], // s3
			['p'=>0x90 , 'k'=>0x24 , 'c'=>[0x5e,2]], // s4
			['p'=>0x94 , 'k'=>0x20 , 'c'=>[0x60,2]], // s5
			['p'=>0x98 , 'k'=>0x30 , 'c'=>[0x66,2]], // s6
			['p'=>0x9c , 'k'=>0x14 , 'c'=>[0x68,2]], // s7
			['p'=>0xa4 , 'k'=>0x78 , 'c'=>[0x6e,2]], // s8
		],
	]; // vit_drag
	//////////////////////////////
	// ver72-m
	$gp_data['vit_odin'] = [
		'idtag'  => 'vita odin sphere',
		'bigend' => false,
		'sect'   => [
			// 50  1+ - - - 0 2 4  5 3 6 7 - 8 - 9
			['p'=>0xc8  , 'k'=>0x50 , 'c'=>[0x5a,2]], // s0 bg=0
			['p'=>0xd0  , 'k'=>0x10 , 'c'=>[0x50,4]], // s1
			['p'=>0xd8  , 'k'=>0x8  , 'c'=>[0x5c,2]], // s2 bg=0
			['p'=>0xe0  , 'k'=>0x1c , 'c'=>[0x62,2]], // s3
			['p'=>0xe8  , 'k'=>0x24 , 'c'=>[0x5e,2]], // s4
			['p'=>0xf0  , 'k'=>0x20 , 'c'=>[0x60,2]], // s5
			['p'=>0xf8  , 'k'=>0x30 , 'c'=>[0x64,2]], // s6
			['p'=>0x100 , 'k'=>0x18 , 'c'=>[0x66,2]], // s7
			['p'=>0x108 , 'k'=>0x14 , 'c'=>[0x6a,2]], // s8 bg=0
			['p'=>0x118 , 'k'=>0x78 , 'c'=>[0x6e,2]], // s9
		],
	]; // vit_odin
//////////////////////////////
$gp_big = false;
function van_float32( string &$file, int $pos ) : float
{
	global $gp_big;
	$int = ( $gp_big ) ? ieee754::ordstr($file,$pos,4) : tool::ordstr($file,$pos,4);
	return ieee754::to($int, 4);
}
function van_int( string &$file, int $pos, int $byte, bool $sign=false ) : int
{
	global $gp_big;
	$int = ( $gp_big ) ? ieee754::ordstr($file,$pos,$byte) : tool::ordstr($file,$pos,$byte);
	if ( $sign )
	{
		$sra = (PHP_INT_SIZE - $byte) * 8; // shift right arithmetic
		return ( ($int << $sra) >> $sra );
	}
	return $int;
}
//////////////////////////////
// return string[]
// if count(string[]) == 1, return string[0]
function fog_quad( string &$s, int $p1, int $p2, int $p3, int $p4 )
{
	$quad = [
		'#' . bin2hex( substr($s,$p1,4) ),
		'#' . bin2hex( substr($s,$p2,4) ),
		'#' . bin2hex( substr($s,$p3,4) ),
		'#' . bin2hex( substr($s,$p4,4) ),
	];
	if ( $quad[0] !== $quad[1] )  return $quad;
	if ( $quad[0] !== $quad[2] )  return $quad;
	if ( $quad[0] !== $quad[3] )  return $quad;
	return $quad[0];
}

function ps2_quad20p( string &$mbp ) : array
{
	$float = [];
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		$b = van_int($mbp, $i, 2, true);
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
	$quad = [
		$float[ 4] , $float[ 5] ,
		$float[ 6] , $float[ 7] ,
		$float[ 8] , $float[ 9] ,
		$float[10] , $float[11] ,
	];
	return $quad;
}

// return string[]
// if count(string[]) == 1, return string[0]
function ps2_quad20c( string &$mbp )
{
	for ( $i=0; $i < 0x20; $i++ )
	{
		$b = ord($mbp[$i]);
		$b = tool::int_clamp($b << 1, 0, BIT8);
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

// +wii,ps3,ps4
function nds_quad30p( string &$mbs ) : array
{
	$float = [];
	for ( $i=0; $i < 0x30; $i += 4 )
		$float[] = van_float32($mbs, $i);

	//  0  1  center
	//  2  3  c0
	//  4  5  c1
	//  6  7  c2
	//  8  9  c3
	// 10 11  c0
	$quad = [
		$float[2] , $float[3] ,
		$float[4] , $float[5] ,
		$float[6] , $float[7] ,
		$float[8] , $float[9] ,
	];
	return $quad;
}

// return string[]
// if count(string[]) == 1, return string[0]
// +wii,ps3,ps4
function nds_quad18c( string &$mbs )
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

function psp_quad60pc( string &$mbs ) : array
{
	$float = [];
	for ( $i=0; $i < 0x60; $i += 2 )
	{
		$b = van_int($mbs, $i, 2, true);
		$float[] = $b / 0x10;
	}

	// src           dst
	//  0  1   2  3   4  5   6  7  center
	//  8  9  10 11  12 13  14 15  c0
	// 16 17  18 19  20 21  22 23  c1
	// 24 25  26 27  28 29  30 31  c2
	// 32 33  34 35  36 37  38 39  c3
	// 40 41  42 43  44 45  46 47  c0
	$src = [
		$float[ 8] , $float[ 9] ,
		$float[16] , $float[17] ,
		$float[24] , $float[25] ,
		$float[32] , $float[33] ,
	];
	$dst = [
		$float[12] , $float[13] ,
		$float[20] , $float[21] ,
		$float[28] , $float[29] ,
		$float[36] , $float[37] ,
	];

	//        clr
	//  0  2   4   8  a   c  e  center
	// 10 12  14  18 1a  1c 1e  c0
	// 20 22  24  28 2a  2c 2e  c1
	// 30 32  34  38 3a  3c 3e  c2
	// 40 42  44  48 4a  4c 4e  c3
	// 50 52  54  58 5a  5c 5e  c0
	$clr = fog_quad($mbs, 0x14, 0x24, 0x34, 0x44);
	return [$clr, $src, $dst];
}

function vit_quad78pc( string &$mbs ) : array
{
	$float = [];
	for ( $i=0; $i < 0x78; $i += 4 )
		$float[] = van_float32($mbs, $i);

	// dst        src
	//  0  1   2   3  4  center
	//  5  6   7   8  9  c0
	// 10 11  12  13 14  c1
	// 15 16  17  18 19  c2
	// 20 21  22  23 24  c3
	// 25 26  27  28 29  c0
	$dst = [
		$float[ 5] , $float[ 6] ,
		$float[10] , $float[11] ,
		$float[15] , $float[16] ,
		$float[20] , $float[21] ,
	];
	$src = [
		$float[ 8] , $float[ 9] ,
		$float[13] , $float[14] ,
		$float[18] , $float[19] ,
		$float[23] , $float[24] ,
	];

	//        clr
	//  0  4   8   c 10  center
	// 14 18  1c  20 24  c0
	// 28 2c  30  34 38  c1
	// 3c 40  44  48 4c  c2
	// 50 54  58  5c 60  c3
	// 64 68  6c  70 74  c0
	$clr = fog_quad($mbs, 0x1c, 0x30, 0x44, 0x58);
	return [$clr, $src, $dst];
}
//////////////////////////////
function sect_head( int $id ) : array
{
	global $gp_share;

	$file = &$gp_share['file'];
	$sect = &$gp_share['data']['sect'][$id];

	$sp = van_int($file, $sect['p'] , 4);
	$sc = van_int($file, $sect['c'][0], $sect['c'][1]);
	$sk = $sect['k'];

	$idx = $sc;
	$gp_share['idx'] = 0;
	while ( $idx > 0 )
	{
		$gp_share['idx']++;
		$idx >>= 4;
	}
	return [$sp,$sc,$sk];
}

function sect_data_txt( int $i, string $data ) : string
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
