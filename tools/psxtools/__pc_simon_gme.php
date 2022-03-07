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

function simon( $fname )
{
	// for *.gme only
	if ( stripos($fname, '.gme') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$ed = str2int($file, 0, 4) - 8;

	for ( $i=0; $i < $ed; $i += 4 )
	{
		$of1 = str2int($file, $i+0, 4);
		$of2 = str2int($file, $i+4, 4);
		$siz = $of2 - $of1;

		$fn = sprintf('%s/%06d.bin', $dir, $i >> 2);
		printf("%8x  %8x  %s\n", $of1, $siz, $fn);

		$sub = substr($file, $of1, $siz);
		save_file($fn, $sub);
	} // for ( $i=0; $i < $ed; $i += 4 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	simon( $argv[$i] );
