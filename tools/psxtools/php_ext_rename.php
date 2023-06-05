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
function renext( $fname )
{
	$fp = fopen_file($fname);
	if ( ! $fp )   return;

	$bin = fread($fp, 16);
	fclose($fp);

	$mgc = substr($bin, 0, 4);
	$mgc = preg_replace('|[^0-9a-zA-Z]|', '', $mgc);
	if ( strlen($mgc) < 3 )
		return;

	$base = substr($fname, 0, strrpos($fname, '.'));
	$ext = strtolower($mgc);
	$new = "$base.$ext";
	printf("RENAME $fname -> $new\n");
	rename($fname, $new);
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	renext( $argv[$i] );

/*
non-alnum
60 01 01 80  PSX STR
10 00 00 00  PSX TIM
 */
