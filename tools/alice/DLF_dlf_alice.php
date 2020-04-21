<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
//////////////////////////////
// Rance 6  Data/DungeonData.dlf
function dlf( $fname )
{
	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$mgc = fp2str($fp, 0, 3);
	if ( $mgc != "DLF" )  return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$ed = fp2int($fp, 0x20, 4);
	$st = 0x20;
	$id = 0;
	while ( $st < $ed )
	{
		$loc = fp2int($fp, $st+0, 4);
		$siz = fp2int($fp, $st+4, 4);
			$st += 8;
			$id++;

		if ( $loc == 0 || $siz == 0 )
			continue;

		$fn = sprintf("$dir/%03d.dat", $id);
		printf("%8x , %8x , $fn\n", $loc, $siz);

		fseek($fp, $loc, SEEK_SET);
		file_put_contents($fn, fread($fp, $siz));
	}
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dlf( $argv[$i] );
