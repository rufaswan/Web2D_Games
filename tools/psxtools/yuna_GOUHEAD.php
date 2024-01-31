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
require 'common.inc';

function yuna( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 7) !== 'GOUHEAD' )
		return;
	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 12, 4);
	while ( $cnt > 0 )
	{
		$cnt--;
		$p = 0x20 + ($cnt * 0x20);
		$fn = substr0($file, $p+0);
		$of = str2int($file, $p+0x10, 4);
		$sz = str2int($file, $p+0x18, 4);

		printf("%8x , %8x , %s\n", $of, $sz, $fn);
		save_file("$dir/$fn", substr($file, $of, $sz));
	}
	return;
}

argv_loopfile($argv, 'yuna');
