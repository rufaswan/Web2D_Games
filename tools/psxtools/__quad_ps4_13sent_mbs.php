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
require 'common-quad.inc';
require 'quad.inc';

define('METAFILE', true);

function sectspr( &$json, &$mbs, $pfx )
{
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbs, $pfx )
{
	return;
}
//////////////////////////////
function ps4_dragon( &$json, &$mbs, $pfx )
{
	return;
}
function ps4_odin( &$json, &$mbs, $pfx )
{
// ERROR Odin , dragon = s4[k]
	return;
}

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

function ps4_13sent( &$json, &$mbs, $pfx )
{
	// 0 - 1 - | 1-0 2-1
	// 2 - 3 - | 3-2 6-3
	// 4 - 5 - | 5-4 9-5
	// 6 - 7 - | 7-6 8-7
	// 8 - 9 - | 4-8 a-9
	// a - b - | b-a s-b
	// cutin_EnemyDamage.mbs
	//   120 - 138 - | 1*18 1*30
	//   168 -   - - | 1*30
	//   1f8 -   - - | 1*14
	//   198 - 1b4 - | 1*1c 1*24
	//   1d8 - 20c - | 1*20 1*30
	//   23c -   - - | 1*18
	// Fuyusaka00.mbs
	//      120 -   1e18 - |   135*18  168*30
	//     6198 - 134448 - |  64b9*30    3*50
	//   165f34 - 32acfc - | 16a4a*14    3*8
	//   134538 - 146d30 - |   a92*1c   a9*24
	//   1484f4 - 32ad14 - |   ed2*20  147*30
	//   32ea64 - 33108c - |   197*18   11*14
	// s9[+28] =   196+1  => sa
	// sa[+ 0] =   ecc+6  => s8
	// s8[+ 0] =   a91    => s6 , [+ 4] =  a8 => s7
	// s6[+10] = 16a2c+1e => s4
	// s4[+ c] =   167    => s1 , [+ e] = 134 => s0 , [+10] = 64b8 => s2
	// s7
	$sect = array(
		array('p' => 0xb0  , 'k' => 0x18), // 0
		array('p' => 0xb8  , 'k' => 0x30), // 1
		array('p' => 0xc0  , 'k' => 0x30), // 2
		array('p' => 0xc8  , 'k' => 0x50), // 3 , cutin=0
		array('p' => 0xd0  , 'k' => 0x14), // 4
		array('p' => 0xd8  , 'k' => 0x8 ), // 5 , cutin=0
		array('p' => 0xe0  , 'k' => 0x1c), // 6
		array('p' => 0xe8  , 'k' => 0x24), // 7
		array('p' => 0xf0  , 'k' => 0x20), // 8
		array('p' => 0xf8  , 'k' => 0x30), // 9
		array('p' => 0x100 , 'k' => 0x18), // a
		array('p' => 0x108 , 'k' => 0x14), // b , cutin=0
		array('o' => strrpos($mbs, "FEOC")),
	);
	sect_addoff($mbs, $sect);
	load_sect($mbs, $sect);
	save_sect($mbs, "$pfx/meta");
	return;

	sectanim($json, $mbs, $pfx);
	sectspr ($json, $mbs, $pfx);

	save_quadfile($pfx, $json);
	return;
}
//////////////////////////////
function aegis( $fname, $idtag )
{
	$mbs = file_get_contents($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs, 0, 4) !== 'FMBS' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	if ( $idtag == '' )
		return;
	$json = load_idtagfile($idtag);

	printf("== %s( %s )\n", $idtag, $pfx);
	$idtag($json, $mbs, $pfx);
	return;
}

printf("%s  -dragon/-odin/-13sent  MBS_FILE...\n", $argv[0]);
$idtag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-dragon':  $idtag = 'ps4_dragon'; break;
		case '-odin'  :  $idtag = 'ps4_odin';   break;
		case '-13sent':  $idtag = 'ps4_13sent'; break;
		default:
			aegis( $argv[$i], $idtag );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )
