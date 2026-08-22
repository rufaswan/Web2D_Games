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

// Pascha3  Data/*.mad
function madrip( string $fname ) : void
{
	$file = file_get_contents( $fname );
	if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
	if ( $mgc !== 'MAD' )
		return;

	$dir = str_replace('.', '_', $fname);

	$ed = strlen($file);
	$st = 0x30;
	$i  = 1;
	while ( $st < $ed )
	{
		$len = tool::ordstr( $file, $st, 4 );
		$fn  = sprintf('%s/%03d.dat', $dir, $i);
		tool::trace($st, $len, $fn);

		tool::save( $fn, tool::substr($file, $st, $len) );
		$st += $len;

		$i++;
	} // while ( $st < $ed )
}

tool::argv_callback($argv, 'madrip');
