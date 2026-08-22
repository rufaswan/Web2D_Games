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
function saga2( $fname )
{
	$sep = strrpos($fname, '/');
	if ( $sep === false )
		$dir = '.';
	else
	{
		$dir = substr($fname, 0, $sep);
		$fname = substr($fname, $sep+1);
	}

	if ( substr($fname,0,3) !== 'map' )
		return;

	// map086_map002.png
	// map0134_map002.png
	if ( $fname[6] !== '_' )
		return;

	$suf = substr($fname, 6);
	$hex = substr($fname, 3, 3);
		$hex = hexdec($hex);

	$new = sprintf('map%04d%s', $hex, $suf);
	echo "$fname -> $new\n";
	rename("$dir/$fname", "$dir/$new");

	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
