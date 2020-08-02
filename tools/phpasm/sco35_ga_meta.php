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
function add_meta( $fname , &$gp_meta )
{
	if ( stripos($fname, "-meta.txt") == false )
		return;

	foreach ( file($fname, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
	{
		$line = preg_replace("|[\s]+|", "", $line);
		list($t1,$x,$y,$w,$h,$id) = explode(',', $line);

		// remove extension
		$id = substr($id, 0, strrpos($id, '.'));

		// num = 123.png
		// num = 001/00333.png
		// str = ./thumb.png
		// str = ./Background/Park/Night.png
		if ( $id[0] == '.' )
			$id = substr($id, 2);
		else
			$id = 0 + substr($id, strrpos($id, '/')+1);

		$data = array($x+0,$y+0,$w+0,$h+0);
		$gp_meta[$id] = $data;
	}
}

if ( $argc == 1 )  exit();

$gp_meta = array();
for ( $i=1; $i < $argc; $i++ )
	add_meta( $argv[$i] , $gp_meta );

file_put_contents("meta.phpstr", serialize($gp_meta));
