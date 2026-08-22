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

function prevnl( &$prev, $bak )
{
	if ( ($bak - 4) != $prev )
		echo "\n";
	$prev = $bak;
	return;
}

function ptr( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen($file);
	$prev = 0;
	for ( $i=0; $i < $len; $i += 4 )
	{
		// nds ram 2000000-23fffff
		if ( $file[$i+3] == "\x02" )
		{
			$ptr = str2int($file, $i, 3);
			if ( $ptr <= 0x3fffff )
			{
				prevnl( $prev, $i );
				printf("%s + %6x = %6x\n", $fname, $i, $ptr);
			}
		}
	} // for ( $i=0; $i < $len; $i += 4 )
	echo "\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ptr( $argv[$i] );
