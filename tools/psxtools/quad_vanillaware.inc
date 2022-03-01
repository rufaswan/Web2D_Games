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

$gp_data = array(
	'ps2_odin' => array(
		'idtag' => 'ps2 odin sphere',
		'sect' => array(
			// oswald.mbp
			//    -    4b8*20  fc*20 9bd0*20
			// 103*50 3108*18 252*8   4bb*18
			// 3ca*30 1010*20  c4*30  22c*8
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
		),
	), // 'ps2_odin'

	'vit_odin' => array(
		'idtag' => 'vita odin sphere resd',
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
	), // 'vit_odin'

	'ps3_odin' => array(
		'idtag' => 'ps3 odin sphere rehd',
		'sect' => array(
			// REHD_Oswald.mbs
			//   984*18 -  157*30 -
			//  a6f3*30 -   88*50 -
			// 100d1*10 -  20f*8 -
			//   c6e*1c - 1d51*24 -
			//  f899*20 -  219*30 -
			//  1176*18 -  160*14 -
			array('p'=>0xb0  , 'k'=>0x18 , 'c'=>array(0x54,2)), // s0
			array('p'=>0xb8  , 'k'=>0x30 , 'c'=>array(0x56,2)), // s1
			array('p'=>0xc0  , 'k'=>0x30 , 'c'=>array(0x58,2)), // s2
			array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s3 bg=0
			array('p'=>0xd0  , 'k'=>0x10 , 'c'=>array(0x50,4)), // s4
			array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s5 bg=0
			array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x62,2)), // s6
			array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s7
			array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x60,2)), // s8
			array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x64,2)), // s9
			array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x66,2)), // sa
			array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x6a,2)), // sb bg=0
		),
	), // 'ps3_odin'

	'ps4_odin' => array(
		'idtag' => 'ps4 odin sphere rehd',
		'sect' => array(
			// REHD_Oswald.mbs
			//   984*18 -  157*30 -
			//  a6f3*30 -   88*50 -
			// 100d1*10 -  20f*8 -
			//   c6e*1c - 1d51*24 -
			//  f899*20 -  219*30 -
			//  1176*18 -  160*14 -
			array('p'=>0xb0  , 'k'=>0x18 , 'c'=>array(0x54,2)), // s0
			array('p'=>0xb8  , 'k'=>0x30 , 'c'=>array(0x56,2)), // s1
			array('p'=>0xc0  , 'k'=>0x30 , 'c'=>array(0x58,2)), // s2
			array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s3 bg=0
			array('p'=>0xd0  , 'k'=>0x10 , 'c'=>array(0x50,4)), // s4
			array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s5 bg=0
			array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x62,2)), // s6
			array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s7
			array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x60,2)), // s8
			array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x64,2)), // s9
			array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x66,2)), // sa
			array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x6a,2)), // sb bg=0
		),
	), // 'ps4_odin'
	//////////////////////////////
	'wii_mura' => array(
		'idtag' => 'wii muramasa',
		'sect' => array(
			// torahime00.mbs
			//   -     63*18  f6*30 413a*30
			// 65*50 9080*c  30a*8   493*18
			// cd*24  7bb*20  6b*30   7a*10
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
		),
	), // 'wii_mura'

	'vit_mura' => array(
		'idtag' => 'vita muramasa rebirth',
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
	), // 'vit_mura'
	//////////////////////////////
	'vit_drag' => array(
		'idtag' => 'vita dragon crown',
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
	), // 'vit_drag'

	'ps3_drag' => array(
		'idtag' => 'ps3 dragon crown',
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
	), // 'ps3_drag'

	'ps4_drag' => array(
		'idtag' => 'ps4 dragon crown pro',
		'sect' => array(
			// Fighter00.mbs
			//  181c*18 - 200*30 -
			//  8361*30 -  49*50 -
			// 11472*10 - 5d0*8 -
			//   b03*1c - 25f*24 -
			//  2188*20 - 328*30 -
			//   44e*18 - 151*14 -
			array('p'=>0xb0  , 'k'=>0x18 , 'c'=>array(0x54,2)), // s0
			array('p'=>0xb8  , 'k'=>0x30 , 'c'=>array(0x56,2)), // s1
			array('p'=>0xc0  , 'k'=>0x30 , 'c'=>array(0x58,2)), // s2
			array('p'=>0xc8  , 'k'=>0x50 , 'c'=>array(0x5a,2)), // s3 bg=0
			array('p'=>0xd0  , 'k'=>0x10 , 'c'=>array(0x50,4)), // s4
			array('p'=>0xd8  , 'k'=>0x8  , 'c'=>array(0x5c,2)), // s5 bg=0
			array('p'=>0xe0  , 'k'=>0x1c , 'c'=>array(0x62,2)), // s6
			array('p'=>0xe8  , 'k'=>0x24 , 'c'=>array(0x5e,2)), // s7
			array('p'=>0xf0  , 'k'=>0x20 , 'c'=>array(0x60,2)), // s8
			array('p'=>0xf8  , 'k'=>0x30 , 'c'=>array(0x64,2)), // s9
			array('p'=>0x100 , 'k'=>0x18 , 'c'=>array(0x66,2)), // sa
			array('p'=>0x108 , 'k'=>0x14 , 'c'=>array(0x6a,2)), // sb bg=0
		),
	), // 'ps4_drag'
	//////////////////////////////
	'ps2_grim' => array(
		'idtag' => 'ps2 grim grimoire',
		'sect' => array(
			// opalnera00.mbp
			//    -     c4*20 11b*20 3ce8*20
			//   1*50 1c0a*18   1*8   1cf*18
			// 295*30  bd8*20 44*30   197*8
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
		),
	), // 'ps2_grim'

	'nds_kuma' => array(
		'idtag' => 'nds kumatanchi',
		'sect' => array(
			// k_item00.mbs
			//    -     1d*18 1ee*30 857*30
			//   1*50 11a2*c    4*8  30b*18
			// 20b*24 2091*20 1ca*30 310*10
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
		),
	), // 'nds_kuma'

	'psp_gran' => array(
		'idtag' => 'psp grand knights history',
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
	), // 'psp_gran'

	'ps4_sent' => array(
		'idtag' => 'ps4 13 sentinels',
		'sect' => array(
			// Miura00.mbs
			//   46a*18 - 197*30 -
			//  8ff7*30 -   2*50 -
			// 11345*14 -   2*8  -
			//   83c*1c -  3f*24 -
			//   b67*20 -  df*30 -
			//    ee*18 -   1*14 -
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
		),
	), // 'ps4_sent'
);
//////////////////////////////
function colorhex32( $c, $order='0123', $sll=false )
{
	$r = ord( $c[ $order[0] ] );
	$g = ord( $c[ $order[1] ] );
	$b = ord( $c[ $order[2] ] );
	$a = ord( $c[ $order[3] ] );
	if ( $sll )
	{
		$r = int_clamp($r<<1, 0, BIT8);
		$g = int_clamp($g<<1, 0, BIT8);
		$b = int_clamp($b<<1, 0, BIT8);
		$a = int_clamp($a<<1, 0, BIT8);
	}

	// shortcut
	if ( $r === $g && $g === $b && $b === $a )
		return $r;
	else
		return sprintf("#%02x%02x%02x%02x", $r, $g, $b, $a);
}

function colorquad( $c0, $c1, $c2, $c3, $order='0123', $sll=false )
{
	$quad = array(
		colorhex32($c0, $order, $sll), // 0
		colorhex32($c1, $order, $sll), // 1
		colorhex32($c2, $order, $sll), // 2
		colorhex32($c3, $order, $sll), // 3
	);

	// attempt to fold all same colors into one
	if ( $quad[0] !== $quad[1] )  return $quad;
	if ( $quad[1] !== $quad[2] )  return $quad;
	if ( $quad[2] !== $quad[3] )  return $quad;

	// skip default white pixels
	$c = ( $quad[0] === BIT8 ) ? '' : $quad[0];
	return $c;
}

function load_mbsfile( &$file, $sect, $big=false )
{
	$ord = ( $big ) ? 'str2big' : 'str2int';
	foreach ( $sect as $sk => $sv )
	{
		$c = $ord($file, $sv['c'][0], $sv['c'][1]);
		$o = $ord($file, $sv['p'], 4);
		if ( $c === 0 || $o === 0 )
			continue;

		$sect[$sk]['o'] = $o;
		$sect[$sk]['d'] = substr($file, $o, $c*$sv['k']);
	} // foreach ( $sect as $sk => $sv )

	$file = $sect;
	return;
}
//////////////////////////////
function ps2_quad20p( &$mbp, $id, &$quad )
{
	if ( $mbp['k'] !== 0x20 )
		return;
	$pos   = $id * 0x20;
	$float = array();
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		$b = str2int($mbp['d'], $pos+$i, 2, true);
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
	return;
}

function ps2_quad20c( &$mbp, $id, &$quad )
{
	if ( $mbp['k'] !== 0x20 )
		return;
	$pos  = $id * 0x20;

	//  0
	//  4  center
	//  8  c0
	//  c  c1
	// 10  c2
	// 14  c3
	// 18  c1
	// 1c  padding
	$quad = colorquad(
		substr($mbs['d'], $pos+0x8 , 4),
		substr($mbs['d'], $pos+0xc , 4),
		substr($mbs['d'], $pos+0x10, 4),
		substr($mbs['d'], $pos+0x14, 4),
		'0123', true
	);
	return;
}

function nds_quad30p( &$mbs, $id, &$quad, $big=false ) // +wii,ps3,ps4
{
	if ( $mbs['k'] !== 0x30 )
		return;
	$ord = ( $big ) ? 'str2big' : 'str2int';
	$pos = $id * 0x30;
	$float = array();
	for ( $i=0; $i < 0x30; $i += 4 )
	{
		$b = $ord($mbs['d'], $pos+$i, 4);
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
	return;
}

function nds_quad18c( &$mbs, $id, &$quad, $big=false ) // +wii,ps3,ps4
{
	if ( $mbs['k'] !== 0x18 )
		return;
	$pos = $id * 0x18;

	//  0  center
	//  4  c0
	//  8  c1
	//  c  c2
	// 10  c3
	// 14  c0
	$quad = colorquad(
		substr($mbs['d'], $pos+0x4 , 4),
		substr($mbs['d'], $pos+0x8 , 4),
		substr($mbs['d'], $pos+0xc , 4),
		substr($mbs['d'], $pos+0x10, 4),
		'0123', false
	);
	return;
}

function psp_quad60pc( &$mbs, $id, &$src, &$dst, &$clr )
{
	if ( $mbs['k'] !== 0x60 )
		return;
	$pos   = $id * 0x60;
	$float = array();
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		$b = str2int($mbs['d'], $pos+$i, 2, true);
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
	$clr = colorquad(
		substr($mbs['d'], $pos+0x14, 4),
		substr($mbs['d'], $pos+0x24, 4),
		substr($mbs['d'], $pos+0x34, 4),
		substr($mbs['d'], $pos+0x44, 4),
		'0123', false
	);
	return;
}

function vit_quad78pc( &$mbs, $id, &$src, &$dst, &$clr )
{
	if ( $mbs['k'] !== 0x78 )
		return;
	$pos   = $id * 0x78;
	$float = array();
	for ( $i=0; $i < 0x78; $i += 4 )
	{
		$b = str2int($mbs['d'], $pos+$i, 4);
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
	$clr = colorquad(
		substr($mbs['d'], $pos+0x1c, 4),
		substr($mbs['d'], $pos+0x30, 4),
		substr($mbs['d'], $pos+0x44, 4),
		substr($mbs['d'], $pos+0x58, 4),
		'0123', false
	);
	return;
}
//////////////////////////////
function vanilla_sect( $php )
{
	$php = substr($php, strrpos($php, 'quad_')+5);
	$php = substr($php, 0, strrpos($php, '_FMBS'));

	global $gp_data;
	if ( ! isset( $gp_data[$php] ) )
		return php_error('gp_data[ %s ] not found', $php);
	return $gp_data[$php];
}
//////////////////////////////
