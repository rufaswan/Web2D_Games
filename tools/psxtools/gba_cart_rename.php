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
require 'common.inc';

define('LOGO_MD5', 'e0434707845307679464ae1c22f0ec2d');

function gbaren( $fname )
{
	// for *.gba only
	if ( stripos($fname, '.gba') === false )
		return;

	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0xc0);
	fclose($fp);

	$logo = substr($head, 4, 0x9c);
	if ( md5($logo) !== LOGO_MD5 )
		return;

	$name = substr($head, 0xa0, 12);
	$code = substr($head, 0xac,  4);
	$vers = ord( $head[0xbc] );
		$name = rtrim($name, ZERO);

	$new = sprintf('%s-%d_%s.gba', $code, $vers, $name);
		$new = strtolower($new);

	if ( $fname === $new )
		return;

	printf("RENAME %s -> %s\n", $fname, $new);
	rename($fname, $new);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gbaren( $argv[$i] );
