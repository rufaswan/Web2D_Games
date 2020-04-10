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
require "common.inc";
//////////////////////////////
function kldrip( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 0x10 + ($i * 0x10);
		$sz = str2int($file, $pos+4, 4);
		$of = str2int($file, $pos+8, 4);
		$fn = sprintf("$dir/%03d.dat", $i+1);

		printf("%8x , %8x , $fn\n", $sz, $of);
		file_put_contents($fn, substr($file, $of, $sz));
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	kldrip( $argv[$i] );
