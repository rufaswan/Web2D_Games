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

function jpgfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	//if ( substr($file, 0, 4) != "FILE" )
		//return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jpgfile( $argv[$i] );

/*
yuv444  3 bytes/1 pixel  Y1 U1 V1  x += 1  y += 1
yuv422  4 bytes/2 pixel  Y2 U1 V1  x += 2  y += 1
yuv420  6 bytes/4 pixel  Y4 U1 V1  x += 2  y += 2
 */
