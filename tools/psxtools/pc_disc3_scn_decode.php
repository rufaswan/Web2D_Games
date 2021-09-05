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

define("NO_TRACE", true);

function getbit( &$file, &$pos, &$bycod, $byte)
{
	while ( count($bycod) < $byte )
	{
		$b1 = ord( $file[$pos] );
			$pos++;
		$b2 = 8;
		while ( $b2 > 0 )
		{
			$b2--;
			$bycod[] = ($b1 >> $b2) & 1;
		} // while ( $b2 > 0 )
	} // while ( count($bycod) < $byte )

	$bit = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$b1 = array_shift($bycod);
		$bit = ($bit << 1) | $b1;
	}
	return $bit;
}

function dwn_decode( &$file )
{
	$file .= ZERO.ZERO.ZERO.ZERO;

	$dec = '';
	$bycod = array(); // bit array
	$dict = str_repeat(ZERO, 0x1000);
	$dpos = 0;

	$len = strlen($file);
	$pos = 0;
	while ( $pos < $len )
	{
		$flg = getbit($file, $pos, $bycod, 1);

		if ( $flg )
		{
			$b1 = getbit($file, $pos, $bycod, 8);
			trace("1 COPY %2x\n", $b1);

			$b1 = chr($b1);
			$dict[$dpos] = $b1;
				$dpos = ($dpos + 1) & 0xfff;
			$dec .= $b1;
		}
		else
		{
			$b1 = getbit($file, $pos, $bycod, 12);
			if ( $b1 == 0 )
				break;
			$b2 = getbit($file, $pos, $bycod, 4);

			$b1 = ($b1 - 1) & 0xfff;
			$b2 += 2;
			trace("0 REF  POS -%x LEN %x\n", $b1, $b2);

			for ( $i=0; $i < $b2; $i++ )
			{
				$b = $dict[$b1];
					$b1 = ($b1 + 1) & 0xfff;
				$dict[$dpos] = $b;
					$dpos = ($dpos + 1) & 0xfff;
				$dec .= $b;
			} // for ( $i=0; $i < $b2; $i++ )
		}
	} // while ( $pos < $len )

	return $dec;
}

function dwn( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "\x81\x40\x26\x93" )
		return;

	$dec = dwn_decode($file);
	file_put_contents("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dwn( $argv[$i] );

/*
81       40         26        93         38
1 ------1-  1 -------- 1 --11-1--  1 --11--11  [1---]
  02          00         34          33

     44        00        00      c1         19       00       10
1 ----1--- 1 -------- - ---------11- ---- 1 ---11--1 - ----------1- ---
  08         00         6,0                 19         2,
 */
