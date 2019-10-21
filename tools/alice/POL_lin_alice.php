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
// Galzoo Data/PolyObj.lin
function polyobj( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
	if ( $mgc != "POL" )
		return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$polno = str2int( $file, 8, 4 );
	for ( $pn=0; $pn < $polno; $pn++ )
	{
		$pos = 12 + ($pn * 8);
		$no = str2int( $file, $pos+0, 4 );
		$sz = str2int( $file, $pos+4, 4 );

		$fn = sprintf("$dir/%03d.dat", $pn);
		printf("%8x , %8x , %s\n", $no, $sz, $fn);

		file_put_contents( $fn, substr($file, $no, $sz) );
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	polyobj( $argv[$i] );
