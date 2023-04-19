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

function shatower( $fname )
{
	// for *.ext only
	if ( stripos($fname, '.t') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$cnt = str2int($file, 0, 2);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 2 + ($i * 2);
		$p1 = str2int($file, $p+0, 2);
		$p2 = str2int($file, $p+2, 2);

		$sz = $p2 - $p1;
		if ( $sz < 1 )
			continue;

		$sub = substr($file, $p1*0x800, $sz*0x800);
		$fn  = sprintf('%s/%04d.bin', $dir, $i);
		save_file($fn, $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	shatower( $argv[$i] );
