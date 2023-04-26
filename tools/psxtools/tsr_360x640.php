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

// phone screen resolution is 360x640
// calculate crop rectangle size before resize
define('TARG_WIDTH' , 360);
define('TARG_HEIGHT', 640);

function newsize( $size )
{
	if ( strpos($size,'x') === false )
		return;

	list($w,$h) = explode('x', $size);
		$w = (int)$w;
		$h = (int)$h;
	if ( ($w|$h) === 0 )
		return;

	if ( $w === 0 )  $w = $h / TARG_HEIGHT * TARG_WIDTH;
	if ( $h === 0 )  $h = $w / TARG_WIDTH  * TARG_HEIGHT;
	printf("%dx%d = %dx%d\n", TARG_WIDTH, TARG_HEIGHT, $w, $h);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	newsize( $argv[$i] );
