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
function figfile( &$file )
{
	$c = count($file);
	$f = 0;
	while ( $c > 0 )
	{
		$c = (int)($c / 10);
		$f++;
	}
	return "%{$f}d";
}

function findstr( $path , $terms )
{
	if ( is_file($path) )
	{
		$file = file($path);
		$res = array();
		foreach ( $file as $no => $line )
		{
			$line = trim($line);
			if ( empty($line) )
				continue;

			$has_str = true;
			foreach ( $terms as $t )
			{
				if ( stripos($line, $t) === false )
					$has_str = false;
			}

			if ( $has_str )
				$res[$no] = $line;
		} // foreach ( $file as $no => $line )

		if ( empty($res) )
			return;

		echo "=== $path ===\n";
		$fig = figfile($file);
		foreach ( $res as $no => $line )
			printf("$fig : %s\n", $no+1 , $line);
		return;
	}
	else
	if ( is_dir($path) )
	{
		$func = __FUNCTION__;
		foreach ( scandir($path) as $dir )
		{
			if ( $dir[0] == '.' )
				continue;
			$func( "$path/$dir", $terms );
		}
		return;
	}
	return;
}

if ( $argc == 1 )  exit();
array_shift($argv);
findstr('.' , $argv);
