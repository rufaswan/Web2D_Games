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

function dworld2( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 8) !== '0VMBOFNI' ) // BMV 0 INFO
		return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$pos = str2int($file, 8, 4);
	$id = 0;

	$pos += 12;
	while ( $pos < $len )
	{
		$bak = $pos;
		$mgc = substr ($file, $pos, 4);
			$pos += 4;
		if ( $mgc === 'MARF' )
			continue;

		$siz = str2int($file, $pos, 4);
			$pos += 4;
		$sub = substr($file, $pos, $siz);
			$pos += $siz;
		$fn  = sprintf('%s/%04d.%s', $dir, $id, $mgc);
			$id++;

		printf("%6x , %6x , %s\n", $bak, $siz, $fn);
		save_file($fn, $sub);
	} // while ( $st < $pos )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dworld2( $argv[$i] );
