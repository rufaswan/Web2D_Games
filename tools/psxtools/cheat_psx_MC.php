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
require 'class-bakfile.inc';
require 'cheat_psx_MC.inc';

function int_update( &$file, $pos, $int, $byte )
{
	for ( $i=0; $i < $byte; $i++ )
	{
		$b = $int & BIT8;
			$int >>= 8;
		$file[$pos+$i] = chr($b);
	}
	return;
}

function lbu_sum_add( &$file, $st, $ed )
{
	$sum = 0;
	while ( $st < $ed )
	{
		$b = ord( $file[$st] );
			$st++;
		$sum = ($sum + $b) & BIT8;
	}
	return $sum;
}

function lbu_sum_xor( &$file, $st, $ed )
{
	$sum = 0;
	while ( $st < $ed )
	{
		$b = ord( $file[$st] );
			$st++;
		$sum ^= $b;
	}
	return $sum;
}

function mcrfile( $fname )
{
	$bak = new BakFile;
	$bak->load($fname);
	if ( $bak->is_empty() )
		return;

	if ( $bak->filesize(1) !== 0x20000 ) // 128 KB
		return;
	if ( substr($bak->file,0,2) !== 'MC' )
		return;

	// 1 + 15 blocks
	for ( $i=1; $i < 16; $i++ )
	{
		$p = $i * 0x80;

		// -        dummy  first  middle  last
		// in use   -      51     52      53
		// deleted  a0     a1     a2      a3
		$flag = ord( $bak->file[$p+0] );
		if ( $flag !== 0x51 )
			continue;

		$size = str2int($bak->file, $p+ 4,  3);
		$func = substr ($bak->file, $p+10, 12);
			$func = str_replace('-', '_', $func);

		printf("%2x  %4x  %s\n", $i, $p, $func);
		if ( function_exists($func) )
		{
			printf("== %s( %6x )\n", $func, $i*0x2000);
			$func($bak->file, $i*0x2000);
		}
	} // for ( $i=1; $i < 16; $i++ )

	$bak->save();
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mcrfile( $argv[$i] );
