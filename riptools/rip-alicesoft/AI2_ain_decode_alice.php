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
declare( strict_types=1 );

require 'tool.inc';
tool::extension('zlib');

function ain_dec( string $fname ) : void
{
	$file = file_get_contents( $fname );
	if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
	$valid = [
		'AI2', // *.ain
		'ZLB', // *
		'ACX', // Data/*.acx
	];
	if ( ! in_array($mgc, $valid) )
		return;
	printf("%s , %s\n", $mgc, $fname);

	$dec = zlib_decode( substr($file, 0x10) );
	file_put_contents("$fname.dec", $dec);
}

tool::argv_callback($argv, 'ain_dec');
