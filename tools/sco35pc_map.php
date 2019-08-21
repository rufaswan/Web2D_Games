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
if ( $argc < 4 )  exit();
$pc = json_decode( file_get_contents($argv[1]), true );
	if ( empty($pc) )  exit();

$map = $pc["var"][ $argv[2] ];
$len = count($map);
$brk = $argv[3];

for ( $i=0; $i < $len; $i++ )
{
	if ( $i > 1 && ($i%$brk) == 0 )
		echo "\n";

	printf("%3d ", $map[$i]);
}
