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

function ndsren( $fname )
{
	// for *.ext only
	if ( stripos($fname, '.nds') === false )
		return;

	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0x180);
	fclose($fp);

	// RAM address check
	if ( $head[0x27] != "\x02" )  return;
	if ( $head[0x2b] != "\x02" )  return;
	if ( $head[0x37] != "\x02" )  return;
	if ( $head[0x3b] != "\x02" )  return;

	$name = substr($head,  0, 12);
	$code = substr($head, 12,  4);
	$vers = ord( $head[0x1e] );
		$name = rtrim($name, ZERO);

	$new = sprintf("%s-%d_%s.nds", $code, $vers, $name);
		$new = strtolower($new);
		//$new = preg_replace('|[^0-9a-z._-]|', '_', $new);

	if ( $fname === $new )
		return;

	printf("RENAME %s -> %s\n", $fname, $new);
	rename($fname, $new);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndsren( $argv[$i] );
