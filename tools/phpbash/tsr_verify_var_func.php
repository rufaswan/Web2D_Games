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

function match_count( &$m )
{
	$cnt = array();
	foreach ( $m as $v )
	{
		if ( ! isset($cnt[$v]) )
			$cnt[$v] = 0;
		$cnt[$v]++;
	}
	$m = $cnt;
	return;
}

function varfunc( $fname )
{
	printf("== varfunc( %s )\n", $fname);

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$m = array();
	preg_match_all('|\$[A-Z0-9_]+|', $file, $m);
	if ( ! empty($m[0]) )
	{
		printf("DETECTED %.1f uppercase var\n", count($m[0]));
		match_count($m[0]);
		print_r($m[0]);
	}

	$m = array();
	preg_match_all('|[A-Z_][A-Z0-9_]+ *\(|', $file, $m);
	if ( ! empty($m[0]) )
	{
		printf("DETECTED %.1f uppercase func\n", count($m[0]));
		match_count($m[0]);
		print_r($m[0]);
	}
	return;
}

function varfunc_dir( $dir )
{
	$dir = rtrim($dir, '/\\');
	if ( ! is_dir($dir) )
		return;

	foreach ( scandir($dir) as $ent )
	{
		$fn = "$dir/$ent";
		if ( $ent[0] === '.' ) // skip hidden files/dirs
			continue;
		if ( ! is_file($fn) ) // only files
			continue;
		if ( is_link($fn) ) // skip symlink files
			continue;

		if ( stripos($ent, '.php') !== false )
			varfunc($fn);
		else
		if ( stripos($ent, '.inc') !== false )
			varfunc($fn);
	}
	return;
}

varfunc_dir( __DIR__ );
