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
require "common.inc";

function decdigit( $int )
{
	$dc = 0;
	while ( $int > 0 )
	{
		$dc++;
		$int = (int)($int / 10);
	}
	return $dc;
}

if ( $argc == 1 )  exit();

$list = array();
lsfile_r('.', $list);

foreach ( $list as $f )
{
	$fsz = filesize($f);
	$txt = '';
	$dc  = decdigit($fsz);

	foreach ( file($f) as $lnum => $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		for ( $i=1; $i < $argc; $i++ )
		{
			if ( stripos($line, $argv[$i]) !== false )
				$txt .= sprintf("%{$dc}d : %s\n", $lnum+1 , substr($line,0,256));
		} // for ( $i=1; $i < $argc; $i++ )
	} // foreach ( file($f) as $lnum => $line )

	if ( empty($txt) )
		continue;

	echo "== $f ==\n";
	echo "$txt\n";
} // foreach ( $list as $f )
