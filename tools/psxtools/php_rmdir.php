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
function rm_empty_dir( $dir )
{
	if ( empty($dir) || is_link($dir) || is_file($dir) )
		return 1;

	$func = __FUNCTION__;
	$has_file = 0;
	foreach ( scandir($dir) as $en )
	{
		if ( $en === '.' || $en === '..' )
			continue;
		$fn = "$dir/$en";

		if ( is_file($fn) )
			$has_file = 1;
		else
			$has_file |= $func($fn);
	} // foreach ( scandir($dir) as $en )

	if ( ! $has_file )
	{
		echo "rmdir $dir\n";
		rmdir($dir);
	}
	return $has_file;
}

printf("%s  DIR...\n", $argv[0]);
for ( $i=1; $i < $argc; $i++ )
	rm_empty_dir( $argv[$i] );
