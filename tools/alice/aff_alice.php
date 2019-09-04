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
$gp_key = array(
	0xc8,0xbb,0x8f,0xb7,
	0xed,0x43,0x99,0x4a,
	0xa2,0x7e,0x5b,0xb0,
	0x68,0x18,0xf8,0x88,
);

function affrip( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;
		printf("[$rem] $fname\n");

	$file = substr($file, 0x10);
	while ( strlen($file) < 0x10 )
		$file .= ' ';

	global $gp_key;
	for ( $i=0; $i < 0x10; $i++ )
	{
		$c = ord( $file[$i] );
		$c ^= $gp_key[$i];
		$file[$i] = chr($c);
	}
	$ext = substr($file, 0, 3);
	file_put_contents("$fname.$ext", $file);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	affrip( $argc-$i, $argv[$i] );
