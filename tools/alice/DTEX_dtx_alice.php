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
define("ZERO", chr(0));

function str2int( &$str, $pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	return $int;
}
////////////////////////////////////////
// Galzoo Data/map*.dtx
function dtex( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc != "DTEX" )
		return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$pad = str2int( $file, 12, 4 );

	$ed = strlen($file);
	$st = 0x14;
	$i  = 1;
	while ( $st < $ed )
	{
		$len = str2int( $file, $st, 4 );
			$st += 4;
		if ( $len == 0 || $len == $pad )
			continue;

		$fn  = sprintf("$dir/%03d.dat", $i);
		printf("%8x , %8x , %s\n", $st, $len, $fn);

		file_put_contents( $fn, substr($file, $st, $len) );
			$st += $len;

		$i++;
	} // while ( $st < $ed )
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dtex( $argv[$i] );
