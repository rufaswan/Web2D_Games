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

function gihren( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "MRG\x00" )
		return;

	$dir = str_replace('.', '_', $fname);
	$len = str2int($file, 4, 4);
	$cnt = str2int($file, 8, 4);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 12 + ($i * 4);
		$off1 = str2int($file, $p, 4);
		if ( ($i+1) == $cnt )
			$off2 = $len;
		else
			$off2 = str2int($file, $p+4, 4);

		$sub = substr($file, $off1, $off2-$off1);
		save_file("$dir/$i.bin", $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gihren( $argv[$i] );
