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
define('NOP', "\x00");

$gp_data = array(
	'rockw1' => array('ROCKMAN WORLD   ', "\x28\xeb\xc2"),
	'rockw2' => array(' ROCKMAN WORLD2 ', "\xb6\x0e\x65"),
	'rockw3' => array('ROCKMANWORLD3   ', "\x15\xaf\x45"),
	'rockw4' => array('ROCKMANWORLD4   ', "\x13\x71\x7b"),
	'rockw5' => array('ROCKMANWORLD5   ', "\x7c\xb0\x29"),
	//'' => array('                ', "\x\x\x"),
);

function rockw1( &$file )
{
	// NO E-TANK !!!

	// ram1:dfa5  Weapons
	//   4204  rom1:4204  1a  ld   a, (de)
	//   4205  rom1:4205  90  sub  b
	//   4209  rom1:4209  12  ld   (de), a
	$file[0x4209] = NOP;

	// ram1:dfa8  Carry
	//   4959  rom1:4959  fa a8 df  ld   a, (dfa8)
	//   495f  rom1:495f  d6 08     sub  08
	//   4964  rom1:4964  ea a8 df  ld   (dfa8), a
	nopfile($file, 0x4964, 3);

	// ram1:dfaa  Flash
	//   4c06  rom1:4c06  fa aa df  ld   a, (dfaa)
	//   4c0c  rom1:4c0c  d6 08     sub  08
	//   4c11  rom1:4c11  ea aa df  ld   (dfaa), a
	nopfile($file, 0x4c11, 3);

	// ram1:dfad  Enker
	//   4fbd  rom1:4fbd  fa ad df  ld   a, (dfad)
	//   4fc0  rom1:4fc0  d6 04     sub  04
	//   4fc5  rom1:4fc5  ea ad df  ld   (dfad), a
	nopfile($file, 0x4fc5, 3);
	//   35152  fa ad df
	//   3515a  ea ad df
	return;
}

function rockw2( &$file )
{
	// ram0:cfe9  E-Tank
	//   389c  rom0:389c  fa e9 cf  ld   a, (cfe9)
	//   389f  rom0:389f  3d        dec  a
	//   38a0  rom0:38a0  ea e9 cf  ld   (cfe9), a
	nopfile($file, 0x38a0, 3);

	// ram0:cfd7  Weapons
	//   39f4  rom0:39f4  21 d0 cf  ld   hl, cfd0
	//   39fa  rom0:39fa  09        add  hl, bc
	//   39fb  rom0:39fb  fa e0 cf  ld   a, (cfe0)
	//   39fe  rom0:39fe  77        ld   (hl), a
	//$file[0x39fe] = NOP;

	// ram0:cfe0  Weapons/Active
	//   3a11  rom0:3a11  fa e0 cf  ld   a, (cfe0)
	//   3a14  rom0:3a14  90        sub  b
	//   3a17  rom0:3a17  ea e0 cf  ld   (cfe0), a
	nopfile($file, 0x3a17, 3);
	return;
}

function rockw3( &$file )
{
	// ram1:df05  E-Tank
	//   67d4  rom1:67d4  21 05 df  ld   hl, df05
	//   67da  rom1:67da  35        dec  (hl)
	$file[0x67da] = NOP;

	// ram1:dea1  Weapons
	//   4230  rom1:4230  1a  ld   a, (de)
	//   4231  rom1:4231  90  sub  b
	//   4235  rom1:4235  12  ld   (de), a
	$file[0x4235] = NOP;

	// ram1:de9d  Rush Coil
	//   547f  rom1:547f  fa 9d de  ld   a, (de9d)
	//   5483  rom1:5483  d6 10     sub  10
	//   5488  rom1:5488  ea 9d de  ld   (de9d), a
	nopfile($file, 0x5488, 3);

	// ram1:dea3  Rush Jet
	//   58a1  rom1:58a1  fa a3 de  ld   a, (dea3)
	//   58a4  rom1:58a4  d6 08     sub  08
	//   58a9  rom1:58a9  ea a3 de  ld   (dea3), a
	nopfile($file, 0x58a9, 3);
	return;
}

function rockw4( &$file )
{
	php_notice('ERROR = stuck at Nintendo logo');

	// ram1:df3d  PChip/Shop
	//   5adac  rom16:6dac  7e  ld   a, (hl)
	//   5adad  rom16:6dad  93  sub  e
	//   5adae  rom16:6dae  22  ldi  (hl), a
	$file[0x5adae] = NOP;

	// ram1:df38  Small E-Tank
	// ram1:df39  E-Tank (4x small E-Tank)
	//   107f  rom0:107f  21 38 df  ld  hl, df38
	//   1082  rom0:1082  77        ld  (hl), a
	//   1087  rom0:1087  36 10     ld  (hl), 10
	nopfile($file, 0x1085, 2);

	// ram1:deb0  Weapons
	//   18621  rom6:4621  1a  ld   a, (de)
	//   18622  rom6:4622  90  sub  b
	//   18626  rom6:4626  12  ld   (de), a
	$file[0x18626] = NOP;

	// ram1:deaf  Rush Coil
	//   1a259  rom6:6259  fa af de  ld   a, (deaf)
	//   1a25d  rom6:625d  b7        sub  10
	//   1a262  rom6:6262  ea af de  ld   (deaf), a
	nopfile($file, 0x1a262, 3);

	// ram1:deb1  Bright
	//   18aa0  rom6:4aa0  fa b1 de  ld   a, (deb1)
	//   18aab  rom6:4aab  d6 18     sub  18
	//   18ab0  rom6:4ab0  ea b1 de  ld   (deb1), a
	nopfile($file, 0x18ab0, 3);

	// ram1:deb2  Pharaoh
	//   18d53  rom6:4d53  fa b2 de  ld   a, (deb2)
	//   18d56  rom6:4d56  93        sub  e
	//   18d5a  rom6:4d5a  ea b2 de  ld   (deb2), a
	nopfile($file, 0x18d5a, 3);

	// ram1:deb4  BEAT
	//   1a065  rom6:6065  fa b4 de  ld   a, (deb4)
	//   1a068  rom6:6068  d6 08     sub  08
	//   1a071  rom6:6071  ea b4 de  ld   (deb4), a
	nopfile($file, 0x1a071, 3);

	// ram1:deb6  Rush Jet
	//   1a64a  rom6:664a  fa b6 de  ld   a, (deb6)
	//   1a64d  rom6:664d  d6 08     sub  08
	//   1a652  rom6:6652  ea b6 de  ld   (deb6), a
	nopfile($file, 0x1a652, 3);
	return;
}

function rockw5( &$file )
{
	// ram1:df3d  PChip/Shop
	//   5ae8f  rom16:6e8f  7e  ld   a, (hl)
	//   5ae90  rom16:6e90  93  sub  e
	//   5ae91  rom16:6e91  22  ldi  (hl), a
	$file[0x5ae91] = NOP;

	// ram1:df38  Small E-Tank
	// ram1:df39  E-Tank (4x small E-Tank)
	//   10c8  rom0:10c8  21 38 df  ld  hl, df38
	//   10cb  rom0:10cb  77        ld  (hl), a
	//   10d0  rom0:10d0  36 10     ld  (hl), 10
	nopfile($file, 0x10ce, 2);

	// ram1:dea4  Weapons
	//   18752  rom6:4752  1a  ld   a, (de)
	//   18753  rom6:4753  90  sub  b
	//   18757  rom6:4757  12  ld   (de), a
	$file[0x18757] = NOP;

	// ram1:dea8  Uranus
	//   1b3ef  rom6:73ef  ea a8 de  ld  (dea8), a
	nopfile($file, 0x1b3ef, 3);

	// ram1:dea9  Pluto
	//   1b97b  rom6:797b  fa a9 de  ld  a, (dea9)
	//   1b986  rom6:7986  ea a9 de  ld  (dea9), a
	nopfile($file, 0x1b986, 3);
	return;
}
//////////////////////////////
function nopfile( &$file, $off, $len )
{
	for ( $i=0; $i < $len; $i++ )
		$file[$off+$i] = NOP;
	return;
}

function gbcartsum( &$file )
{
	$len = strlen($file);
	$sum = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $i === 0x14e || $i === 0x14f )
			continue;
		$b = ord( $file[$i] );
		$sum = ($sum + $b) & BIT16;
	} // for ( $i=0; $i < $len; $i++ )

	$b1 = ($sum >> 8) & BIT8;
	$b2 = ($sum >> 0) & BIT8;
	$file[0x14e] = chr($b1);
	$file[0x14f] = chr($b2);
	return;
}

function gbfile( $fname )
{
	if ( is_file("$fname.bak") )
	{
		$bak  = file_get_contents("$fname.bak");
		$file = $bak;
	}
	else
	{
		$file = file_get_contents($fname);
		$bak  = $file;
	}
	if ( empty($file) || empty($bak) )
		return;

	$mgc = substr($file, 0x134, 0x10);
		$mgc = preg_replace('|[^0-9a-zA-Z ]|', ' ', $mgc);
	$sum = substr($file, 0x14d, 3);

	global $gp_data;
	foreach ( $gp_data as $func => $v )
	{
		if ( $v[0] === $mgc && $v[1] === $sum )
		{
			printf("detect [%s] = %s\n", $func, $fname);
			$func($file);
		}
	} // foreach ( $gp_data as $func => $ver )

	if ( $file === $bak )
		return;

	if ( ! is_file("$fname.bak") )
		file_put_contents("$fname.bak", $bak);

	gbcartsum($file);
	file_put_contents($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gbfile( $argv[$i] );
