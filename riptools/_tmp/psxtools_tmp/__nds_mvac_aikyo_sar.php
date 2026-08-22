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

function cmp_decode( &$sub )
{
	if ( substr($sub, 0,4) !== 'cmp ' )
		return;
	$len = strlen($sub);
	$sub = substr($sub, 12, $len-16);

	if ( substr($sub,0,4) !== 'lz7 ' )
		return;

	$dec = '';
	$bycod = 0;
	$bylen = 0;

	$ed = str2int($sub, 8, 4);
	$st = 0x10;
	while ( $st < $ed )
	{
		if ( $bylen == 0 )
		{
			$bycod = ord( $sub[$st] );
				$st++;
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 0x80;
			$bycod <<= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = ord( $sub[$st+0] );
			$b2 = ord( $sub[$st+1] );
				$st += 2;
			printf("1 %2x %2x\n", $b1, $b2);

			//$dp = (($b1 & 0x0f) << 8) | $b2;
			$dl =  ($b1 >> 4) + 4;
			for ( $i=0; $i < $dl; $i++ )
			{
				//$p = strlen($dec) - $dp;
				//$dec .= $dec[$p];
				$dec .= 'X';
			}
		}
		else
		{
			$b = $sub[$st];
				$st++;
			printf("0 %2x\n", ord($b));
			$dec .= $b;
		}
	} // while ( $st < $ed )

	$sub = $dec;
	return;
}

function mvac( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0,4) !== 'sar ' )
		return;
	if ( substr($file,12,4) !== 'mbr ' )
		return;

	$dir = str_replace('.', '_', $fname);
	$off = str2int($file, 0x10, 4);
	$cnt = str2int($file, 0x14, 4);

	$file = substr($file, 0x18);
	//for ( $i=0; $i < $cnt; $i++ )
	{
		$i = 1372;
		$p = $off + ($i * 8);
		$pos = str2int($file, $p+0, 4);
		$siz = str2int($file, $p+4, 4);

		$fn  = sprintf("%s/%04d.dat", $dir, $i);
		printf("%6x , %6x , %s\n", $pos, $siz, $fn);

		$sub = substr ($file, $pos, $siz);
		cmp_decode($sub);
		save_file($fn, $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mvac( $argv[$i] );

/*
str "lz7 " @ 2061228
	-> ptr @ 200cee0
	-> 200ce8c  ldr  r1,=Lxx_#0x2061228

 */
