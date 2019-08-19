<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
function ain_dec( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
	switch ( $mgc )
	{
		case "AI2":
		case "ZLB":
			break;
		default:
			return;
	}

	printf("$mgc , $fname\n");

	$dec = zlib_decode( substr($file, 0x10) );
	file_put_contents("$fname.dec", $dec);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain_dec( $argv[$i] );
