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
define('SHA1FILE', '2bfb39f9824c7efe01304498ef1a905bdd5904f0');

function dec_gethalf( &$file, &$st, &$hfcod, &$hflen )
{
	if ( $hflen == 0 )
	{
		$hfcod = str2int($file, $st, 4);
			$st += 4;
		$hflen = 32;
		trace("HALFCODE %8x\n", $hfcod);
	}

	$half = $hfcod & BIT16;
		$hfcod >>= 16;
		$hflen  -= 16;
	return $half;
}

function dec_getbyte( &$file, &$st, &$bycod, &$bylen )
{
	if ( $bylen == 0 )
	{
		$bycod = str2int($file, $st, 4);
			$st += 4;
		$bylen = 32;
		trace("BYTECODE %8x\n", $bycod);
	}

	$byte = $bycod & BIT8;
		$bycod >>= 8;
		$bylen  -= 8;
	return $byte;
}

function dec_getint( &$file, &$st, &$fgcod, &$fglen, $bit )
{
	$int = 0;
	for ( $i=0; $i < $bit; $i++ )
	{
		$int <<= 1;
		$b     = dec_getflag($file, $st, $fgcod, $fglen);
		$int  |= $b;
	}
	return $int;
}

function dec_getflag( &$file, &$st, &$fgcod, &$fglen )
{
	if ( $fglen == 0 )
	{
		$fgcod = str2int($file, $st, 4);
			$st += 4;
		$fglen = 32;
		trace("FLAGCODE %8x\n", $fgcod);
	}

	$flag = $fgcod & 0x80000000;
		$fgcod <<= 1;
		$fglen--;
	return ( $flag ) ? 1 : 0;
}
//////////////////////////////
function kenshin_decode( &$file, $st )
{
	echo "== begin sub_801e0028\n";
	$dec = '';
	$fgcod = 0; // v0 & 1
	$fglen = 0; // v1
	$bycod = 0; // t6 & BIT8
	$bylen = 0; // t7
	$hfcod = 0; // t8 & BIT16
	$hflen = 0; // t9

	$size = str2int($file, $st, 4); // decoded size
		$st += 4;
	$ed = strlen($file);
	while ( $size > 0 )
	{
		trace("%6x  %6x  ", $st, strlen($dec));

		if ( dec_getflag($file, $st, $fgcod, $fglen) ) // loc_801e0060
		{
			// loc_801e0090
			if ( dec_getflag($file, $st, $fgcod, $fglen) ) // loc_801e00a4
			{
				// loc_801e00f0
				$b = dec_gethalf($file, $st, $hfcod, $hflen);
				$dpos =  $b - 0x2000;
				$dlen = ($b >> 13) & 7;

				$b = ($dlen == 7);
				$dlen += 3;
				trace("REF-7+3  POS %d  LEN %d\n", $dpos, $dlen);
			}
			else
			{
				// loc_801e00ac
				$b = dec_getbyte($file, $st, $bycod, $bylen);
				$dpos = $b - 0x100;
				$dlen = dec_getint($file, $st, $fgcod, $fglen, 2);

				$b = ($dlen == 3);
				$dlen += 2;
				trace("REF-3+2  POS %d  LEN %d\n", $dpos, $dlen);
			}

			// loc_801e012c
			if ( $b )
			{
				$t0 = 0;
				while ( dec_getflag($file, $st, $fgcod, $fglen) )
					$t0++;
				if ( $t0 > 0 )
				{
					$n = dec_getint($file, $st, $fgcod, $fglen, $t0);
					trace("bit %d LEN %d + 1 + %d\n", $t0, $dlen, $n);
					$dlen += (1 + $n);
				}
			}

			// loc_801e0168
			$size -= $dlen;
			for ( $i=0; $i < $dlen; $i++ )
			{
				$p = strlen($dec) + $dpos;
				$dec .= $dec[$p];
			} // for ( $i=0; $i < $dlen; $i++ )
		}
		else
		{
			// loc_801e0068
			$b = dec_getbyte($file, $st, $bycod, $bylen);
			trace("COPY %2x\n", $b);
			$dec .= chr($b);
			$size--;
		}
	} // while ( $size > 0 )

	echo "== end sub_801e0028\n";
	return $dec;
}

function kenshin( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 8) !== 'PS-X EXE' )
		return;
	if ( sha1($file) != SHA1FILE )
		return php_error('sha1sum not match [%s]', sha1($file));

	$dec = kenshin_decode($file, 0xa48);
	save_file("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kenshin( $argv[$i] );
