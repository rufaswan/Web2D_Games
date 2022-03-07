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

function panzer( $fname )
{
	// for *.bg only
	if ( stripos($fname, '.bg') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// first offset = end header
	$st = 0;
	$ed = 0;
	while (1)
	{
		$ed = str2int($file, $st, 4);
		if ( $ed !== 0 )
			break;
		$st += 8;
	} // while (1)

	$dir = str_replace('.', '_', $fname);
	while ( $st < $ed )
	{
		$id  = $st >> 3;
		$off = str2int($file, $st+0, 4);
		$siz = str2int($file, $st+4, 4);
			$st += 8;
		if ( $off == 0 || $siz == 0 )
			continue;

		$sub = substr($file, $off, $siz);
		$fn = sprintf('%s/%04d.bin', $dir, $id);
		save_file($fn, $sub);
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	panzer( $argv[$i] );
