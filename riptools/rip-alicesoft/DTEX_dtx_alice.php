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

// Rance6  Data/DungeonData_dlf/*.dtex
// Pascha2/PaschaC++  Dungeon/field*.dtx
// Galzoo  Data/map*.dtx
function dtex( string $fname ) : void
{
	$file = file_get_contents( $fname );
	if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc !== 'DTEX' )
		return;

	$dir = str_replace('.', '_', $fname);
	$pad = tool::ordstr($file, 12, 4);

	$ed = strlen($file);
	$st = 0x14;
	$i  = 1;
	while ( $st < $ed )
	{
		$len = tool::ordstr($file, $st, 4);
			$st += 4;
		if ( $len == 0 || $len == $pad )
			continue;
		$sub = tool::substr($file, $st, $len);

		$fn = sprintf('%s/%03d.dat', $dir, $i);
			$i++;
		tool::trace($st, $len, $fn);
		tool::save( $fn, tool::substr($file, $st, $len) );

		$st += $len;
	} // while ( $st < $ed )
}

tool::argv_callback($argv, 'dtex');
