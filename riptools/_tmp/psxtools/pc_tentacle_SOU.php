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

function tentacle( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$ppos = strpos($file, 'Creative Voice File', 0);
	if ( $ppos === false )
		return;

	$id = 0;
	while ( $ppos < $len )
	{
		$cpos = strpos($file, 'Creative Voice File', $ppos+0x1f);
		if ( $cpos === false )
			$cpos = $len;

		$siz = $cpos - $ppos;
		$voc = substr($file, $ppos, $siz);

		$fn = sprintf('%s/%08d.voc', $dir, $id);
			$id++;
		printf("%8x  %8x  %s\n", $ppos, $siz, $fn);

		save_file($fn, $voc);
		$ppos = $cpos;
	} // while ( $ppos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tentacle( $argv[$i] );
