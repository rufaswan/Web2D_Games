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

//define("DRY_RUN", true);

function sotn( $fname )
{
	// for /bin/monster.bin
	if ( stripos($fname, "monster.bin") === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$ed = strlen($file);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$pix = strpal555($file, $st, 96*112);
		$rgba = "RGBA";
		$rgba .= chrint( 96, 4);
		$rgba .= chrint(112, 4);
		$rgba .= $pix;

		$fn = sprintf("$dir/%04d.rgba", $id);
		save_file($fn, $rgba);
		printf("%4x , %8x\n", $id, $st);
		$st += 0x5800;
		$id++;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
