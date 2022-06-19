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

function loadfile( $fn )
{
	$p = strrpos($fn, '+');
	if ( $p === false )
	{
		$file = file_get_contents($fn);
		$off  = 0;
		printf("FILE : %s + 0\n", $fn);
	}
	else
	{
		$s1 = substr($fn, 0, $p);
		$s2 = substr($fn, $p+1);
		$file = file_get_contents($s1);
		$off  = hexdec($s2);
		printf("FILE : %s + %x\n", $s1, $off);
	}
	return array($file, $off);
}
//////////////////////////////
function diffsort( $a, $b )
{
	if ( $a['d'] === $b['d'] )
		return ( $a['p'] > $b['p'] );
	else
		return ( $a['d'] > $b['d'] );
}

function listbyte( &$list, $pos, $fname )
{
	usort($list, 'diffsort');

	$txt = '';
	$txt .= sprintf("  %-8s  %2s  %2s  %3s\n", 'OFF', 'F1', 'F2', 'DIF');
	foreach ( $list as $v )
	{
		if ( $v['d'] < 0 )
			$txt .= sprintf("  %8x  %2x  %2x  -%2x\n", $v['p'], $v['f1'], $v['f2'], -$v['d']);
		else
			$txt .= sprintf("  %8x  %2x  %2x   %2x\n", $v['p'], $v['f1'], $v['f2'],  $v['d'] );
	}
	$txt .= sprintf("  found %d bytes different\n", count($list));

	save_file($fname, $txt);
	return;
}

function clutbyte( &$list, $size, $fname )
{
	$size = int_ceil($size, 0x100);
	$pix  = str_repeat(ZERO, $size);

	foreach ( $list as $v )
	{
		$d = abs($v['d']);
		$p = $v['p'];
		$pix[$p] = chr($d);
	}

	$img = array(
		'cc'  => 0x100,
		'w'   => 0x100,
		'h'   => $size >> 8,
		'pal' => grayclut(0x100),
		'pix' => $pix,
	);
	save_clutfile($fname, $img);
	return;
}
//////////////////////////////
function cmpbyte( $fn1, $fn2 )
{
	list($file1,$off1) = loadfile($fn1);
	list($file2,$off2) = loadfile($fn2);
	if ( empty($file1) || empty($file2) )
		return;

	$pos = ( $off1 < $off2 ) ? $off2 : $off1;
	$list = array();
	while (1)
	{
		if ( ! isset($file1[$pos]) || ! isset($file2[$pos]) )
			break;

		if ( $file1[$pos] !== $file2[$pos] )
		{
			$b1 = ord( $file1[$pos] );
			$b2 = ord( $file2[$pos] );
			$list[] = array(
				'p'  => $pos,
				'f1' => $b1,
				'f2' => $b2,
				'd'  => $b2 - $b1,
			);
		}
		$pos++;
	} // while (1)

	$pfx = preg_replace('|[^0-9A-Za-z]|', '_', "$fn1.$fn2");
	clutbyte($list, $pos, "$pfx.clut");
	listbyte($list, $pos, "$pfx.txt" );
	return;
}

printf("%s  FILE1[+off1]  FILE2[+off2]\n", $argv[0]);
if ( $argc !== 3 )  exit();
cmpbyte($argv[1], $argv[2]);
