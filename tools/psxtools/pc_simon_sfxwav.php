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

function simon( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$pos = 0;
	$id  = 0;
	while (1)
	{
		$fn = sprintf("%s/%06d.wav", $dir, $id);
			$id++;

		$of1 = str2int($file, $pos+0, 4);
		$of2 = str2int($file, $pos+4, 4);
			$pos += 4;

		if ( $of1 == 0 )
			continue;
		if ( $of2 == 0x46464952 ) // RIFF
			$of2 = strlen($file);
		if ( $of1 == 0x46464952 ) // RIFF
			break;

		$sz  = $of2 - $of1;
		$sub = substr($file, $of1, $sz);
		printf("%8x , %8x , %s\n", $of1, $sz, $fn);
		save_file($fn, $sub);
	} // while (1)

	return;
}

for ( $i=1; $i < $argc; $i++ )
	simon( $argv[$i] );
