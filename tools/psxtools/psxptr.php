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

	$list = array();
	$mips = array(
		0x08 => "addi", 0x09 => "addiu",
		0x0f => "lui",
		0x20 => "lb" , 0x21 => "lh" , 0x23 => "lw",
		0x24 => "lbu", 0x25 => "lhu",
	);

	$ed = strlen($file);
	$st = 0;
	$prev = 0;
	$preg = 0;
	$pimm = 0;
	while ( $st < $ed )
	{
		$bak = $st;
			$st += 4;
		$op = ord( $file[$bak+3] );

		// psx ram 80000000-801fffff
		// bios    80000000-8000ffff
		if ( $op == 0x80 )
		{
			$ptr = str2int($file, $bak, 4);
			if ( $ptr >= 0x80010000 && $ptr <= 0x801f0000 )
			{
				prevnl( $prev, $bak );
				printf("$fname , %8x , ptr %8x\n", $bak, $ptr);
				$list[$ptr] = 1;
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
				if ( $file[$bak+1] === "\x80" )
				{
					$b1 = str2int($file, $bak+0, 2);

					// 80xx == RAM
					// 1fxx == IO
					prevnl( $prev, $bak );
					$pimm = $b1 << 16;
					$preg = $rt;
					printf("$fname , %8x , %-6s %8x\n", $bak, $mips[$op], $pimm);
					$list[$pimm] = 1;
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
					printf("$fname , %8x , %-6s %8x\n", $bak, $mips[$op], $b2);
					$list[$b2] = 1;
				}
				break;
			case 0x08: // addi
			case 0x09: // addiu
				if ( $preg == $rs )
				{
					prevnl( $prev, $bak );
					$b1 = sint16( $file[$bak+0] . $file[$bak+1] );
					$b2 = $pimm + $b1;
					printf("$fname , %8x , %-6s %8x\n", $bak, $mips[$op], $b2);
					$list[$b2] = 1;

					if ( $rs == $rt )
						$pimm = $b2;
				}
				break;
		} // switch ( $op )
	} // while ( $st < $ed )
	echo "\n";

	echo "=== list ===\n";
	ksort($list);
	foreach ( $list as $k => $v )
	{
		if ( $k < 0x80010000 )  continue;
		if ( $k > 0x801f0000 )  continue;
		printf("  %6x\n", $k);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxptr( $argv[$i] );
