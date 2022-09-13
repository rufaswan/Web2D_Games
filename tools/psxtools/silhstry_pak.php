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

function silhstry_decode( &$file )
{
	trace("== begin sub_8002b5b4\n");
	$dec = '';
	$st = 0;
	$ed = strlen($file);

	$bycod = 0;
	$bylen = 0;

	while ( $st < $ed )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord($file[$st]);
				$st++;
			$bylen = 8;
		}

		$flg = $bycod & 0x80;
			$bycod <<= 1;
			$bylen--;

		if ( $flg )
		{
			$b = $file[$st];
				$st++;
			$dec .= $b;
		}
		else
		{
			$b1 = ord($file[$st+0]);
			$b2 = ord($file[$st+1]);
				$st += 2;

			$b = ($b1 << 8) | $b2;
			$dpos =  $b >> 4;
			$dlen = ($b & BIT4) + 2;

			$dp = strlen($dec) - $dpos;
			for ( $i=0; $i < $dlen; $i++ )
				$dec .= $dec[$dp+$i];
		}
	} // while ( $st < $ed )

	trace("== end sub_8002b5b4\n");
	$file = $dec;
	return;
}

function silhstry( $fname )
{
	// for *.pak only
	if ( stripos($fname, '.pak') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$cnt = str2int($file, 8, 2);

	$pos = 12 + ($cnt * 0x20);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$off = 12 + ($i * 0x20);

		$fn = substr0($file, $off+0);
			$fn = strtolower($fn);

		$sz1 = str2int($file, $off+0x14, 4); // compressed size
		$sz2 = str2int($file, $off+0x18, 4); // decompressed size
		$sz3 = str2int($file, $off+0x1c, 2); // ceil compressed size to 4's
		printf("%8x , %8x , %s/%s\n", $pos, $sz1, $dir, $fn);

		$sub = substr($file, $pos, $sz1);
		if ( $sz1 !== $sz2 )
			silhstry_decode($sub);
		save_file("$dir/$fn", $sub);

		$pos += ($sz1 + $sz3);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	silhstry( $argv[$i] );

/*
RAM 800e3710 = decoded data
	8002b60c  lbu  v0[36], 0(a3[800e2d1e])
	8002b620  sb   v0[36], 0(t0[800e3710])

	inp = a0 = RAM 801973d9
	title.pak = RAM 801834e4 = FILE 13ef5

	len = a2 = 9a84 = title2c8.ati
	8002b4ac  lw  v0[    9a84], 18(s1[801835d0])

	-  a2    a0
	0  7d5c  183690/1ac + [2b5a]
	1
	2
	3
	4  6b58  1891e0/5cfc  + 1890[188d+3]
	5  f278  18aa70/758c  + af2c[af29+3]
	6  3d08  19599c/124bc + 1574[1572+2]
	7  9a84  196f10/13a30 + [1b74]
 */
