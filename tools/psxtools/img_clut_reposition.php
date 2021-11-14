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

define("TRIM_SZ", 4);
$gp_adj = array(0,0);

function canvas_rect( &$pix, $byte )
{
	printf("== canvas_rect( $byte )\n");
	global $gp_adj;
	$ow = $pix['w'] / 2;
	$oh = $pix['h'] / 2;
	$nw = int_ceil( $ow + abs($gp_adj[0]), TRIM_SZ);
	$nh = int_ceil( $oh + abs($gp_adj[1]), TRIM_SZ);
	printf("ORIGIN %x,%x -> %x,%x\n", $ow, $oh, $nw, $nh);

	if ( $byte == 4 )
		$b1 = ZERO;
	else
	if ( $byte == 1 )
	{
		$b1 = $pix['pix'][0];
		$b2 = substr($pix['pal'], ord($b1)*4, 4);
		if ( $b2[3] != ZERO )
			return 0;
	}

	$canvas = str_repeat($b1, $nw*2*$nh*2*$byte);
	$dx = ($nw + $gp_adj[0]) - $ow;
	$dy = ($nh + $gp_adj[1]) - $oh;
	printf("dx,dy = %x,%x  SIZE = %x\n", $dx, $dy, $nw*2*$nh*2*$byte);

	for ( $y=0; $y < $pix['h']; $y++ )
	{
		$syy = $y * $pix['w'] * $byte;
		$row = substr($pix['pix'], $syy, $pix['w']*$byte);

		$dyy = ($dy+$y) * ($nw*2) * $byte;
		$dxx = $dyy + ($dx*$byte);
		str_update($canvas, $dxx, $row);
	}
	//save_file("pix", $canvas);

	$pix['w'] = $nw*2;
	$pix['h'] = $nh*2;
	$pix['pix'] = $canvas;
	return $b1;
}

function trim_rect( &$pix, $byte, $ZERO )
{
	printf("== trim_rect( $byte )\n");
	echo debug($ZERO);
	$x1 = 0;
	$x2 = $pix['w'];
	$y1 = 0;
	$y2 = $pix['h'];

	$row = $pix['w'] * $byte;

	// trim height
	while (1)
	{
		$b = "";
		$p = $y1 * $row;
		$b .= substr($pix['pix'], $p, $row*TRIM_SZ);

		$p = ($y2 - TRIM_SZ) * $row;
		$b .= substr($pix['pix'], $p, $row*TRIM_SZ);

		if ( trim($b, $ZERO) != '' )
			break;

		$y1 += TRIM_SZ;
		$y2 -= TRIM_SZ;
		if ( $y1 >= $y2 )
			break;
	} // while (1)
	printf("TRIM y %x - %x\n", $y1, $y2);

	// trim width
	while (1)
	{
		$b = "";
		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + ($x1 * $byte);
			$b .= substr($pix['pix'], $p, TRIM_SZ*$byte);
		}
		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + (($x2-TRIM_SZ) * $byte);
			$b .= substr($pix['pix'], $p, TRIM_SZ*$byte);
		}

		if ( trim($b, $ZERO) != '' )
			break;

		$x1 += TRIM_SZ;
		$x2 -= TRIM_SZ;
		if ( $x1 >= $x2 )
			break;
	} // while (1)
	printf("TRIM x %x - %x\n", $x1, $x2);

	$w = $x2 - $x1;
	$h = $y2 - $y1;
	$canv = "";
	for ( $y=$y1; $y < $y2; $y++ )
	{
		$p = $y * $pix['w'] + $x1;
		$canv .= substr($pix['pix'], $p*$byte, $w*$byte);
	}
	$pix['w'] = $w;
	$pix['h'] = $h;
	$pix['pix'] = $canv;
	return;
}
//////////////////////////////
function repos_clut( &$pix, $fname )
{
	echo "== repos_clut( $fname )\n";
	$b1 = canvas_rect($pix, 1);
	if ( $b1 === 0 )
		return;
	trim_rect($pix, 1, $b1);
	save_clutfile($fname, $pix);
	return;
}

function repos_rgba( &$pix, $fname )
{
	echo "== repos_rgba( $fname )\n";
	$b1 = canvas_rect($pix, 4);
	if ( $b1 === 0 )
		return;
	trim_rect($pix, 4, $b1);
	save_clutfile($fname, $pix);
	return;
}

function repos( $fname )
{
	$pix = load_clutfile($fname);
	if ( $pix === 0 )
		return;

	if ( isset( $pix['cc'] ) )
		return repos_clut($pix, $fname);
	else
		return repos_rgba($pix, $fname);
	return;
}
//////////////////////////////
$ERROR = "{$argv[0]}  x,y  FILE...\n";
if ( $argc < 3 )
	exit($ERROR);
if ( strpos($argv[1], ',') === false )
	exit($ERROR);

$b1 = explode(',', $argv[1]);
$gp_adj = array( (int)$b1[0] , (int)$b1[1] );
printf("Reposition sprites by %d x %d\n", $gp_adj[0], $gp_adj[1]);

for ( $i=2; $i < $argc; $i++ )
	repos( $argv[$i] );
