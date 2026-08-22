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

function safepal()
{
	$b5 = ["\x00", "\x40", "\x80", "\xcc", "\xff"];
	$b2 = ["\x00", "\xff"];
	$pal = [];
	foreach ( $b5 as $r )
		foreach ( $b5 as $g )
			foreach ( $b5 as $b )
				foreach ( $b2 as $a )
					$pal[] = $r . $g . $b . $a;
	return $pal;
}

function rgba2clut( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'RGBA' )
		return;

	$pal = safepal();
	printf("safepal = %x\n", count($pal));

	//$dir = str_replace('.', '_', $fname);
	//$len = strlen($file);
	// code template
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rgba2clut( $argv[$i] );
