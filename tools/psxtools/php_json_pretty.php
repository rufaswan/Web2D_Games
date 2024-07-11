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
if ( ! function_exists('json_encode') )
	exit("no json extension\n");

require 'common-json.inc';

function jsonfile( $pretty, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$json = json_decode($file, true);
	if ( empty($json) )  return;

	echo "JSON $fname\n";
	if ( $pretty )
	{
		//$file = json_encode($json, JSON_PRETTY_PRINT);
		//$file = str_replace('    ', "\t", $file);
		$file = json_pretty::encode($json);
	}
	else
		$file = json_encode($json);

	//echo "$file\n";
	file_put_contents($fname, $file);
	return;
}

$pretty = true;
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		jsonfile( $pretty, $argv[$i] );
	else
		$pretty = ( (int)$argv[$i] === 0 ) ? false : true;
}
