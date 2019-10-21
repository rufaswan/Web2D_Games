<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
////////////////////////////////////////
function fpath( $rem, $fname )
{
	$sep = array(
		"\\", // utf-8 5c
		"¥", // utf-8 c2 a5
		"／", // utf-8 ef bc 8f
	);

	foreach ( $sep as $s )
	{
		$ps = strrpos($fname, $s);
		if ( $ps == false )
			continue;

		$new = str_replace($s, '/', $fname);
		$ps  = strrpos($new, '/');
		$dir = substr ($new, 0, $ps);
		@mkdir($dir, 0755, true);

		printf("[$rem] $fname -> $new\n");
		rename($fname , $new);
		return;
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	fpath( $argc-$i, $argv[$i] );
