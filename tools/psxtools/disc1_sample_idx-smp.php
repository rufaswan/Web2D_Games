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

function disc1( $fname )
{
	// for *.idx only
	if ( stripos($fname, '.idx') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$fp = fopen("$pfx.smp", "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$ids = array();
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = str2int($file, $i, 4);
		if ( $b === 0 )
			continue;
		$ids[ $i/4 ] = $b;
	}

	foreach ( $ids as $k => $v )
	{
		$b = fp2str($fp, $v, 0x10);
		$size = str2int($b, 0, 4);
		$skip = 4;

		if ( $size > 0xfffff )
		{
			$size = str2int($b, 4, 4);
			$skip = 8;
		}

		if ( $size > 0xfffff )
			continue;

		$fn = sprintf("%s/%06d.vb", $dir, $k);
		printf("%8x , %8x , %s\n", $v, $size, $fn);

		$b = fp2str($fp, $v+$skip, $size);
		save_file($fn, $b);
	} // foreach ( $ids as $k => $v )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc1( $argv[$i] );
