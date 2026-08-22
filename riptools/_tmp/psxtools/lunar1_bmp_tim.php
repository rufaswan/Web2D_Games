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
require 'lunar1.inc';

//define('NO_TRACE', 1);

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$id  = 0;

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$siz = str2int($file, $st+4, 4);
		if ( $siz < 1 )
			break;

		$fn  = sprintf('%s/%04d', $dir, $id);
			$id++;
		printf("%8x  %8x  %s\n", $st+8, $siz, $fn);

		$sub = substr($file, $st+8, $siz);
		$dec = lunar_decode($sub);
		save_file("$fn.dec", $dec);

		$st += (8 + $siz);
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );
