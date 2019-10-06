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
if ( $argc == 1 )  exit();

$buf = "";
for ( $i=1; $i < $argc; $i++ )
{
	$fn = $argv[$i];
	if ( stripos($fn, "-meta.txt") === false )
		continue;

	$file = file($fn);
	foreach ( $file as $line )
	{
		list($t1,$x,$y,$w,$h,$id) = explode(',', trim($line));
		//if ( $x == 0 && $y == 0 )
			//continue;

		$t1 = strpos($id, '/');
		if ( $t1 )  $id = substr($id, $t1+1);
		$t1 = strpos($id, '.');
		if ( $t1 )  $id = substr($id, 0, $t1);

		$log = sprintf("%d,%d,%d,%d,%d\n", $id, $x, $y, $w, $h);
		echo $log;
		$buf .= $log;
	}
}
file_put_contents("meta.txt", $buf);
