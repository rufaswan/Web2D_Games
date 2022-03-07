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
require 'quad.inc';

function grid_addcolor( &$pal )
{
	while (1)
	{
		$r = (rand() & BIT8) | 0x80;
		$g = (rand() & BIT8) | 0x80;
		$b = (rand() & BIT8) | 0x80;
		$c = chr($r) . chr($g) . chr($b) . BYTE;
		if ( array_search($c, $pal) !== false )
			continue;

		$pal[] = $c;
		return;
	}
	return;
}

function draw_dot( &$pix, $c, $x, $y, $w, $h )
{
	$p = ($y * $w) + $x;
	$pix[$p] = $c;
	return;
}

function draw_grid( &$pix, $c, $x1, $y1, $x2, $y2, $w, $h )
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

function pix_grid( &$pix, $w, $h )
{
	for ( $x=0; $x < $w; $x += 10 )
	{
		$c = ( $x == ($w/2) ) ? "\x01" : "\x02";
		draw_grid($pix, $c, $x+9, 0, $x+9, $h-1, $w, $h);
	}
	for ( $y=0; $y < $h; $y += 10 )
	{
		$c = ( $y == ($h/2) ) ? "\x01" : "\x02";
		draw_grid($pix, $c, 0, $y+9, $w-1, $y+9, $w, $h);
	}
	return;
}
//////////////////////////////
function quad_anim( &$quad, $pfx )
{
	if ( ! isset($quad['Animation']) )
		return;
	return;
}

function quad_frame( &$quad, $pfx )
{
	if ( ! isset($quad['Frame']) )
		return;
	foreach ( $quad['Frame'] as $fk => $fv )
	{
		if ( empty($fv) )
			continue;

		$w = 0;
		$h = 0;
		foreach ( $fv as $fvv )
		{
			$dst = $fvv['DstQuad'];
			for ( $i=0; $i < 8; $i += 2 )
			{
				$x = abs($dst[$i+0]);
				$y = abs($dst[$i+1]);
				if ( $x > $w )  $w = $x;
				if ( $y > $h )  $h = $y;
			} // for ( $i=0; $i < 8; $i += 2 )
		} // foreach ( $fx as $fvv )

		$w = int_ceil($w, 10) << 1;
		$h = int_ceil($h, 10) << 1;
			$hw = $w >> 1;
			$hh = $h >> 1;

		$pix = str_repeat(ZERO, $w*$h);
		$pal = array(
			"\x00\x00\x00\xff",
			"\x44\x00\x00\xff",
			"\x00\x44\x00\xff",
			"\x00\x00\x44\xff",
		);
		pix_grid($pix, $w, $h);

		foreach ( $fv as $fvk => $fvv )
		{
			$dst = $fvv['DstQuad'];
			grid_addcolor($pal);
			$c = chr(4 + $fvk);
			for ( $i=0; $i < 8; $i += 2 )
			{
				$x = $hw + $dst[$i+0];
				$y = $hh + $dst[$i+1];
				draw_dot($pix, $c, $x, $y, $w, $h);
			} // for ( $i=0; $i < 8; $i += 2 )
		} // foreach ( $fx as $fvv )

		$img = array(
			'cc'  => count($pal),
			'w'   => $w,
			'h'   => $h,
			'pal' => implode('', $pal),
			'pix' => $pix,
		);
		$fn = sprintf('%s/dot/%04d.clut', $pfx, $fk);
		save_clutfile($fn, $img);
	} // foreach ( $file['Frame'] as $fk => $fv )
	return;
}

function quadfile( $fname )
{
	// for *.quad only
	if ( stripos($fname, '.quad') === false )
		return;
	srand( time() );

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$file = json_decode($file, true);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	quad_frame($file, $pfx);
	quad_anim ($file, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	quadfile( $argv[$i] );
