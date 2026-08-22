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
require 'lunar1.inc';

define('NO_TRACE', 1);

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$id = 0;
	$st = 0;
	while (1)
	{
		$b1 = str2int($file, $st+0, 2);
		$b2 = str2int($file, $st+2, 2);
		//$b3 = str2int($file, $st+4, 2);
		//$b4 = str2int($file, $st+6, 2);
			$st += 8;

		if ( $b2 === 0 )
			break;

		$fn = sprintf('%s/%04d.dec', $dir, $id);
			$id++;
		$siz = $b1 << 2;
		$off = $b2 << 2;
		$sub = substr($file, $off);
		$sub = lunar_decode($sub);

		printf("%4x , %8x , %8x -> %8x , %s\n", $st-8, $off, $siz, strlen($sub), $fn);
		save_file($fn, $sub);
	} // while (1)
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );

/*
RAM 80156000 = peo_000.dat
	80022b30  lw   a0[  3f13f7], 0(v0[80156000])
	800673a8  lhu  v1[  3f], 2(v0[80156000])
	800673b0  lhu  a0[13f7], 0(v0[80156000])
		v1 <<= 2
		a0 <<= 2
	800195f4  lhu  v0[1700], 4(v0)
		v1 = ceil( v0 / (1 << 6) )
		v0 = ceil( v1 / (1 << 3) )
	800196b8  lhu  v0[1700], 4(v1[80156000])
	800196d8  lhu  a0[ 2e9], 6(v1[80156000])
		a0 <<= 2

RAM 800fc240 = peo_000_dat/0000.dec
	80019734  lhu  a0, 8(s4)
	80019738  lhu  v0, a(s4)
		t0 = (v0 - a0) * aaaaaaab
		a1 = s4 + (a0 << 2)
	80067a94  lhu  v0, 0(v1) // 240
		a0[28] = v1 + (v0 << 2)
	80067aa8  lhu  v0, 2(v1) // 240
		a0[2c] = v1 + (v0 << 2)
	80067abc  lhu  v0, 4(v1) // 240
		a0[14] = v1 + (v0 << 2)
	80067ad0  lhu  v0, 6(v1) // 240
		a0[18] = v1 + (v0 << 2)
	80067ae4  lhu  v0, 8(v1) // 240
		a0[10] = v1 + (v0 << 2)

	v0 = (v0 << 2) + v1
	800692bc  lhu  v0, 0(v0) // +250
	8006997c  lhu  v1, 2(v0) // 258 254
	v0 = (v0 << 2) + a0
	80069994  lhu  v0, 0(v0) // +250

battle monster = 80024b98
- * a0 + 6
7 * 5
- * 5 + 20 + e
22 * 4
- * e + 60 + 3
7 * 3
fa * 1
f9 * 2
fa * 2
7 * 3
- * 2 + a0 +
 */
