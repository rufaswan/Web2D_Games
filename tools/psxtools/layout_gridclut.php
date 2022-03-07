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

function drawline( &$pix, $c, $x1, $y1, $x2, $y2, $w, $h )
{
	for ( $y = $y1; $y <= $y2; $y++ )
	{
		$syy = $y * $w;
		for ( $x = $x1; $x <= $x2; $x++ )
		{
			$sxx = $syy + $x;
			$pix[$sxx] = $c;
		}
	}
	return;
}

function gridline( &$pix, $tile, $map )
{
	list($tw,$th) = $tile;
	$w = $tile[0] * $map[0];
	$h = $tile[1] * $map[1];

	// green lines for tile size
	for ( $x=0; $x < $w; $x += $tw )
	{
		$tx = $x + $tw - 1;
		drawline($pix, "\x03", $tx, 0, $tx, $h-1, $w, $h);
	}
	for ( $y=0; $y < $h; $y += $th )
	{
		$ty = $y + $th - 1;
		drawline($pix, "\x03", 0, $ty, $w-1, $ty, $w, $h);
	}

	// yellow lines for room size
	drawline($pix, "\x05",    0,    0, $w-1,    0, $w, $h);
	drawline($pix, "\x05",    0,    0,    0, $h-1, $w, $h);
	drawline($pix, "\x05",    0, $h-1, $w-1, $h-1, $w, $h);
	drawline($pix, "\x05", $w-1,    0, $w-1, $h-1, $w, $h);

	// magenta cross for center of the room (for symmetry)
	$cx = $w >> 1;
	$cy = $h >> 1;
	$ctw = $tw >> 1;
	$cth = $tw >> 1;
	drawline($pix, "\x06", $cx-1   , $cy-$cth, $cx     , $cy+$cth, $w, $h);
	drawline($pix, "\x06", $cx-$ctw, $cy-1   , $cx+$ctw, $cy     , $w, $h);
	return;
}

function gridclut( $tile, $map, $fname )
{
	if ( count($tile) != 2 )  return;
	if ( count($map ) != 2 )  return;
	if ( ($tile[0]|$tile[1]) == 0 )  return;
	if ( ($map [0]|$map [1]) == 0 )  return;

	$bin = '0000 0001 1001 0101 0011 1101 1011 0111 1111';
	$len = strlen($bin);
	$clr = '';
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $bin[$i] == ' ' )  continue;
		if ( $bin[$i] == '0' )  $clr .= ZERO;
		if ( $bin[$i] == '1' )  $clr .= BYTE;
	}

	$pix = str_repeat(ZERO, $tile[0]*$map[0]*$tile[1]*$map[1]);
	gridline($pix, $tile, $map);

	$img = 'CLUT';
	$img .= chrint(9, 4);
	$img .= chrint($tile[0]*$map[0], 4);
	$img .= chrint($tile[1]*$map[1], 4);
	$img .= $clr;
	$img .= $pix;
	save_file($fname, $img);
	return;
}

printf("%s  TWxTH  MWxMH\n", $argv[0]);
if ( $argc !== 3 )  exit();
if ( strpos($argv[1], 'x') === false )  exit();
if ( strpos($argv[2], 'x') === false )  exit();

$tile = explode('x', $argv[1]);
$map  = explode('x', $argv[2]);

gridclut($tile, $map, "{$argv[1]}_{$argv[2]}.clut");
