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
require 'common-guest.inc';

function null_PSMF( $fname, &$file )
{
	$size = str2big($file, 8, 4);
	$sub  = substr ($file, 0, $size);

	$int = chrint(0, 4);
	str_update($sub, 12, $int);

	file_put_contents($fname, $sub);
	return;
}

function nullfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$func = 'null_' . substr($file, 0, 4);
	if ( ! function_exists($func) )
		return;

	printf("%s( %s )\n", $func, $fname);
	$func($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	nullfile( $argv[$i] );
//argv_loopfile($argv, 'prefix');
