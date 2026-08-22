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
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-hexnum');

function findtext( string $fname, array &$word ) : void
{
	$fsz = filesize($fname);
	$dc  = hexnum::decdigit($fsz);

	$txt = '';
	foreach ( file($fname, FILE_IGNORE_NEW_LINES) as $lnum => $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$match = true;
		foreach ( $word as $w )
		{
			if ( stripos($line, $w) === false )
				$match = false;
		} // foreach ( $word as $w )

		if ( $match )
			$txt .= sprintf("%{$dc}d : %s\n", $lnum+1 , tool::substr($line,0,256));
	} // foreach ( file($f) as $lnum => $line )

	if ( ! empty($txt) )
	{
		echo "== $fname ==\n";
		echo "$txt\n";
	}
}

printf("%s  WORD...\n", $argv[0]);
if ( $argc == 1 )  exit();

$list = [];
tool::scan($list, '.');

$word = $argv;
array_shift($word);

foreach ( $list as $f )
	findtext($f, $word);
