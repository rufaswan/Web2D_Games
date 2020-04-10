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

function sco35_cmds( $fname )
{
	$file = file($fname, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ( empty($file) )  return;

	$list = array();
	foreach ( $file as $line )
	{
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;
		$exp = explode(',', $line);
		$c = $exp[2];
		if ( ! isset($list[$c]) )
			$list[$c] = 0;
		$list[$c]++;
	}

	ksort($list);
	foreach ( $list as $k => $v )
		printf("%-12s %9d\n", $k, $v);
}

sco35_cmds("sco_code.txt");
