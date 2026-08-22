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

// 800004a  0
// 8000042  +97,4b800
// 800004a  +3ba8,1dd4000 (other ending)
// 8000002  +3cd4,1e6a000 (girl ending)
// 800004a  +45d4,22ea000 (credits roll)

// picevt.mrg
// loc 67a58         loc 67bc8
// sll  s0, 1 =   2  v0 = s1 - 1
// addu s0    =   3  sll  v0, 1 =   2
// sll  3     =  24  addu v0    =   3
// subu s0    =  23  sll  7     = 192
// sll  3     = 184
// = 97 + i*b8       = 3cd4 + i*c0

//////////////////////////////
function loopsect( &$mrg, $st, $ed, $bk, $callback )
{
	printf("== loopsect( %x , %x , %x )\n", $st, $ed, $bk);
	if ( $bk == 0 || ! function_exists($callback) )
		return;

	// 0x800 === 1 << 11
	$id = 0;
	while ( $st < $ed )
	{
		$meta = substr($mrg, $st<<11, $bk<<11);
		$callback($meta);

		$fn = sprintf("mrg/%s/%04d.meta", $callback, $id);
		save_file($fn, $meta);

		$st += $bk;
		$id++;
	}
	return;
}

function mkr( $fname )
{
	$mrg = load_file("picevt.mrg");
	if ( empty($mrg) )  return;

	$len = strlen($mrg);
	$ed = $len >> 11;
	loopsect($mrg, 0     , 0x97  , 0xb8, "picevt_0");
	loopsect($mrg, 0x97  , 0x3ba8, 0xb8, "picevt_97");
	loopsect($mrg, 0x3ba8, 0x3cd4, 0x0 , "picevt_3ba8");
	loopsect($mrg, 0x3cd4, 0x45d4, 0xc0, "picevt_3cd4");
	loopsect($mrg, 0x45d4, $ed   , 0x0 , "picevt_45d4");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkr( $argv[$i] );

/*
sub 8003099c
	(s2 >> 16) &  400 = mapdat.mrg
	(s2 >> 16) & 4000 = wa_mrg.mrg
	(s2 >> 16) & 2000 = merge.mrg
	(s2 >> 16) & 1000 = batdat.mrg
	(s2 >> 16) &  800 = picevt.mrg
	s2 & 8
	s2 |= 8000

164000 = 4b800/97  a7800/14f  103800/207 ... 1dc3800/3b87
	0 + n*b8 + 97 , n=53
1d0000 =
1d4800 =
1d8000 =
1db800 =
1dbc00 =
1dc000 = 48000/90  a4000/148  100000/200  ...  1dc0000/3b80
	0 + n*b8 + 90 , n=53
1df800 =

801dc000  80018e44
80164000  80067cd0
	s0 = (s0 << 1) + 80164000
	lh    v1, 0(s0)
	v0 =  v1 + 80164000
	sw    v0, 2c(a0[8016e000])

	801640a6  80068188
		lh    v1, 0(v0)
		v1 = (v1 << 2) + s0[8009d768]
		lw    v0, 0(v1)
		jalr  v0
	801640a8  80068740
		lh  s1, 0(v0)
		v0 += 2
	801640aa  8006874c
		lh  s0, 0(v0)
		v0 += 2


	801640ac  80068188
	801640ae  80068b18
	801640b0  80068b20  lh  t0,  0(v0)
	801640b2  80068b30  lh  t0,  2(v0)
	801640b4  80068b40  lh  t0,  4(v0)
	801640b6  80068b50  lh  fp,  6(v0)
	801640b8  80068b5c  lh  t0,  8(v0)
	801640ba  80068b6c  lh  s7,  a(v0)
	801640bc  80068b78  lh  s6,  c(v0)
	801640be  80068b84  lh  s4,  e(v0)
	801640c0  80068b90  lh  s3, 10(v0)
	801640c2  80068b9c  lh  s2, 12(v0)
	801640c4  80068ba8  lh  s5, 14(v0)
		v0 += 16
*/
