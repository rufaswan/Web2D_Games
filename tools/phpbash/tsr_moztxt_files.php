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

function moztxt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	preg_match_all('|<([^>]+)>|', $file, $match);
	if ( empty($match) )
		return;

	//print_r($match[1]);
	$list = array();
	foreach ( $match[1] as $url )
	{
		$http  = parse_url($url);
		if ( ! isset($http['path']) )
			continue;
		if ( isset($http['query']) )
			continue;

		$fname = substr($http['path'], strrpos($http['path'],'/')+1);
		if ( empty($fname) )
			continue;
		if ( strpos($fname, '.') === false )
			continue;

		$path = sprintf('<p><a href="%s">%s</a></p>', $url, urldecode($fname));
		$list[] = $path;
	} // foreach ( $match[1] as $url )

	sort($list);
	foreach ( $list as $p )
		echo "$p\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	moztxt( $argv[$i] );
