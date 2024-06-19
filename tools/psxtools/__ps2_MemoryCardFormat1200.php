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
 *
 * http://www.csclub.uwaterloo.ca:11068/mymc/ps2mcfs.html
 *   Ross Ridge
 * https://www.oocities.org/siliconvalley/station/8269/sma02/sma02.html#ECC
 */
require 'common.inc';

function rex_sma02_table()
{
	$key = array(
		0x00 , 0x11 , 0x22 , 0x33 ,
		0x44 , 0x55 , 0x66 , 0x77 ,
		0x87 , 0x96 , 0xa5 , 0xb4 ,
		0xc3 , 0xd2 , 0xe1 , 0xf0 ,
	);
	//  0  8  9  1
	// 10  2  3 11
	// 12  4  5 13
	//  6 14 15  7
	$idx = array(
		 0, 8, 9, 1 , 10, 2, 3,11 , 11, 3, 2,10 ,  1, 9, 8, 0 , // 00
		12, 4, 5,13 ,  6,14,15, 7 ,  7,15,14, 6 , 13, 5, 4,12 , // 10
		13, 5, 4,12 ,  7,15,14, 6 ,  6,14,15, 7 , 12, 4, 5,13 , // 20
		 1, 9, 8, 0 , 11, 3, 2,10 , 10, 2, 3,11 ,  0, 8, 9, 1 , // 30

		14, 6, 7,15 ,  4,12,13, 5 ,  5,13,12, 4 , 15, 7, 6,14 , // 40
		 2,10,11, 3 ,  8, 0, 1, 9 ,  9, 1, 0, 8 ,  3,11,10, 2 , // 50
		 3,11,10, 2 ,  9, 1, 0, 8 ,  8, 0, 1, 9 ,  2,10,11, 3 , // 60
		15, 7, 6,14 ,  5,13,12, 4 ,  4,12,13, 5 , 14, 6, 7,15 , // 70

		15, 7, 6,14 ,  5,13,12, 4 ,  4,12,13, 5 , 14, 6, 7,15 , // 80
		 3,11,10, 2 ,  9, 1, 0, 8 ,  8, 0, 1, 9 ,  2,10,11, 3 , // 90
		 2,10,11, 3 ,  8, 0, 1, 9 ,  9, 1, 0, 8 ,  3,11,10, 2 , // a0
		14, 6, 7,15 ,  4,12,13, 5 ,  5,13,12, 4 , 15, 7, 6,14 , // b0

		 1, 9, 8, 0 , 11, 3, 2,10 , 10, 2, 3,11 ,  0, 8, 9, 1 , // c0
		13, 5, 4,12 ,  7,15,14, 6 ,  6,14,15, 7 , 12, 4, 5,13 , // d0
		12, 4, 5,13 ,  6,14,15, 7 ,  7,15,14, 6 , 13, 5, 4,12 , // e0
		 0, 8, 9, 1 , 10, 2, 3,11 , 11, 3, 2,10 ,  1, 9, 8, 0 , // f0
	);

	$table = array();
	foreach ( $idx as $k => $v )
		$table[$k] = $key[$v];
	return $table;
}

function is_ecc_ps2card( &$sub, &$ecc )
{
	$all_ecc = array();
	$table = rex_sma02_table();
	for ( $i=0; $i < 0x200; $i += 0x80 )
	{
		$chunk = substr($sub, $i, 0x80);

		$cur_ecc = array(0,0,0);
		for ( $j=0; $j < 0x80; $j++ )
		{
			$b1 = ord($chunk[$j]);
			$b2 = $table[$b1];
			$cur_ecc[0] ^= $b2;
			if ( $b2 & 0x80 )
			{
				$cur_ecc[1] ^= ~$j;
				$cur_ecc[2] ^=  $j;
			}
		}
		$cur_ecc[0] = ~$cur_ecc[0] & 0x77;
		$cur_ecc[1] = ~$cur_ecc[1] & 0x7f;
		$cur_ecc[2] = ~$cur_ecc[2] & 0x7f;

		$all_ecc[] = chr($cur_ecc[0]) . chr($cur_ecc[1]) . chr($cur_ecc[2]);
	} // for ( $i=0; $i < 0x200; $i += 0x80 )

	$all_ecc = implode('', $all_ecc);
	printf("ecc calc : %s == %s\n", printhex($all_ecc), printhex($ecc));
	return ( $all_ecc === $ecc );
}

function verify_ps2card( &$file )
{
	$len = strlen($file);
	if ( $len === 0x800000 )
		return php_notice('ps2card no ECC data');

	if ( $len === 0x840000 )
	{
		$ps2 = '';
		for ( $i=0; $i < 0x840000; $i += 0x210 )
		{
			$sub = substr($file, $i, 0x200);
			$ecc = substr($file, $i + 0x200, 12); // 4 byte left unused
			if ( ! is_ecc_ps2card($sub, $ecc) )
				goto error;
			$ps2 .= $sub;
		} // for ( $i=0; $i < 0x840000; $i += 0x210 )
		$file = $ps2;
		return;
	}

error:
	$file = 0;
	return;
}

function ps2card( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr0($file,0) !== 'Sony PS2 Memory Card Format 1.2.0.0' )
		return;

	verify_ps2card($file);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
	return;
}

argv_loopfile($argv, 'ps2card');
