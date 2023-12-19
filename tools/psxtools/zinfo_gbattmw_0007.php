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

function gbattmw_0007( &$file )
{
	$head = substr($file, 0, 0x10);
	printf("[%6x] HEAD : %s\n", 0, printhex($head));

	$cnt_layer = str2int($head, 0, 2); // 1c
	$cnt_anim  = str2int($head, 2, 2); // 63
	$c3 = str2int($head, 4, 2); // 10
	$pos = 0x10;
	for ( $i=0; $i < $cnt_layer; $i++ )
	{
		// 01   23  45  67  89  ab  cdef  0123  4567  89ab
		// par  cn  vr  --  --  --  p1    p2    p3    p4
		$sub = substr($file, $pos, 0x1c);
			$pos += 0x1c;
		printf("  %4x = %s\n", $i, printhex($sub));

		$c4 = str2int($sub, 2, 2);
		$c5 = str2int($sub, 4, 2);
		$c45 = $c4 * $c5;

		// 85c =  cc/2  = 64
		// 324 = 310/c  = 41 , 1b  4c  25  23
		// 818 =  44/4  = 10
		// 634 = 1e4/1e = 10
		$p1 = str2int($sub, 0x0c, 4);
		$p2 = str2int($sub, 0x10, 4);
		$p3 = str2int($sub, 0x14, 4);
		$p4 = str2int($sub, 0x18, 4);

		printf("  [%6x] anim id %x /2 -> [%6x] anim data %x /c\n", $p1, $i, $p2, $i);
		for ( $j=0; $j < $cnt_anim; $j++ )
		{
			$p2id = str2int($file, $p1, 2);
				$p1 += 2;

			// 012345  67  89  ab
			// ------  --  --  --
			$p2ps = $p2 + ($p2id * 0xc);
			$s = substr($file, $p2ps, 0xc);
			printf("    %4x,%4x,1 : %s\n", $i, $j, printhex($s));
		}

		printf("  [%6x] %x /4\n", $p3, $i);
		for ( $j=0; $j < $c45; $j++ )
		{
			// 0123
			//
			$s = substr($file, $p3, 4);
				$p3 += 4;
			printf("    %4x,%4x,2 : %s\n", $i, $j, printhex($s));
		}

		printf("  [%6x] %x texture UV/1e\n", $p4, $i);
		for ( $j=0; $j < $c45; $j++ )
		{
			// 01  23  45  67  89  ab  cd  ef  01  23  45  67  89  ab   cd
			// dx  dy  dw  dh  sx  sy  sw  sh  hx  hy  hw  hh  --  tid  --
			$s = substr($file, $p4, 0x1e);
				$p4 += 0x1e;
			printf("    %4x,%4x,3 : %s\n", $i, $j, printhex($s));
		}

		echo "==============================\n";
	} // for ( $i=0; $i < $cnt_layer; $i++ )

	return;
}

function gbattmw( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	ob_start();
	gbattmw_0007($file);
	$txt = ob_get_clean();

	file_put_contents("$fname.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gbattmw( $argv[$i] );
