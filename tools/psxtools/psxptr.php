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

// mips reg
//  0  1  2  3  4  5  6  7
//  0  1 v0 v1 a0 a1 a2 a3
//
//  8  9 10 11 12 13 14 15
// t0 t1 t2 t3 t4 t5 t6 t7
//
// 16 17 18 19 20 21 22 23
// s0 s1 s2 s3 s4 s5 s6 s7
//
// 24 25 26 27 28 29 30 31
// t8 t9 k0 k1 gp sp fp ra

function prevnl( &$prev, $bak )
{
	if ( ($bak - 4) != $prev )
		echo "\n";
	$prev = $bak;
	return;
}

function psxptr( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ed = strlen($file);
	$st = 0;
	$prev = 0;
	$preg = 0;
	$pimm = 0;
	$mips = array(
		0x08 => "addi", 0x09 => "addiu",
		0x0f => "lui",
		0x20 => "lb" , 0x21 => "lh" , 0x23 => "lw",
		0x24 => "lbu", 0x25 => "lhu",
	);
	while ( $st < $ed )
	{
		$bak = $st;
			$st += 4;
		$op = ord( $file[$bak+3] );

		// psx ram 80000000-801fffff
		// bios    80000000-8000ffff
		if ( $op == 0x80 )
		{
			$ptr = str2int($file, $bak, 3);
			if ( $ptr >= 0x10000 && $ptr <= 0x1fffff )
			{
				prevnl( $prev, $bak );
				printf("$fname , %8x , ptr %6x\n", $bak, $ptr);
			}
			continue;
		}

		$b1 = str2int($file, $bak+2, 2);
		$op = ($b1 >> 10) & 0x3f;
		$rs = ($b1 >>  5) & 0x1f;
		$rt = ($b1 >>  0) & 0x1f;
		switch ( $op )
		{
			case 0x0f: // lui
				$b1 = ord( $file[$bak+0] );
				$b2 = ord( $file[$bak+1] );
				if ( $b2 == 0x80 )
				{
					if ( $b1 >= 0x01 && $b1 <= 0x1f )
					{
						prevnl( $prev, $bak );
						$pimm = $b1 << 16;
						$preg = $rt;
						printf("$fname , %8x , %-6s %6x\n", $bak, $mips[$op], $pimm);
					}
				}
				break;
			case 0x20: // lb
			case 0x21: // lh
			case 0x23: // lw
			case 0x24: // lbu
			case 0x25: // lhu
				if ( $preg == $rs )
				{
					prevnl( $prev, $bak );
					$b1 = sint16( $file[$bak+0] . $file[$bak+1] );
					$b2 = $pimm + $b1;
					printf("$fname , %8x , %-6s %6x\n", $bak, $mips[$op], $b2);
				}
				break;
			case 0x08: // addi
			case 0x09: // addiu
				if ( $preg == $rs )
				{
					prevnl( $prev, $bak );
					$b1 = sint16( $file[$bak+0] . $file[$bak+1] );
					$b2 = $pimm + $b1;
					printf("$fname , %8x , %-6s %6x\n", $bak, $mips[$op], $b2);
					if ( $rs == $rt )
						$pimm = $b2;
				}
				break;
		} // switch ( $op )
	} // while ( $st < $ed )
	echo "\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxptr( $argv[$i] );
