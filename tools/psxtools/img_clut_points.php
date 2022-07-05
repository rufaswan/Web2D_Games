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

function clutpoints( &$point )
{
	$ceil = 0;
	foreach( $point as $v )
	{
		if ( abs($v[0]) > $ceil )  $ceil = abs($v[0]);
		if ( abs($v[1]) > $ceil )  $ceil = abs($v[1]);
	}

	$half = int_ceil($ceil+1, 2);
	$ceil = $half * 2;
	$canv = str_repeat(ZERO, $ceil*$ceil);

	$pal = '';
	$pal .= "\x00\x00\x00\xff";
	$pal .= "\xff\x00\x00\xff";
	$pal .= "\x00\xff\x00\xff";
	$pal .= "\x00\x00\xff\xff";
	$pal .= "\xff\xff\x00\xff";
	$pal .= "\xff\x00\xff\xff";
	$pal .= "\x00\xff\xff\xff";
	$pal .= "\xff\xff\xff\xff";

	// draw grid
	for ( $x=0; $x < $ceil; $x++ )
	{
		$dxx = ($half * $ceil) + $x;
		$canv[$dxx] = "\x02";
	}
	for ( $y=0; $y < $ceil; $y++ )
	{
		$dxx = ($y * $ceil) + $half;
		$canv[$dxx] = "\x02";
	}

	// draw points
	foreach( $point as $v )
	{
		$dx = $v[0] + $half;
		$dy = $v[1] + $half;

		$dxx = ($dy * $ceil) + $dx;
		$canv[$dxx] = "\x01";
	}

	$img = 'CLUT';
	$img .= chrint(8, 4);
	$img .= chrint($ceil, 4);
	$img .= chrint($ceil, 4);
	$img .= $pal;
	$img .= $canv;
	save_file('img-points.clut', $img);
	return;
}

$point = array();
for ( $i=1; $i < $argc; $i++ )
{
	list($x,$y) = explode(',', $argv[$i]);
	$point[] = array((int)$x, (int)$y);
}
clutpoints($point);
