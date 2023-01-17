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
function rip_alk( $fname )
{
	$fp = fopen($fname, "rb");
		if ( ! $fp )  return;

	$mgc = fp2str($fp, 0, 4);
	if ( $mgc != "ALK0" )
		return;

	$dir = str_replace('.', '_', $fname);
	mkdir($dir, 0755);

	$cnt = fp2int($fp, 4, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 8 + ($i * 8);
		$off = fp2int($fp, $pos+0, 4);
		$siz = fp2int($fp, $pos+4, 4);

		if ( $siz == 0 )
			continue;

		$fn = sprintf("$dir/%03d.dat", $i+1);
		fseek($fp, $off, SEEK_SET);
		file_put_contents($fn, fread($fp, $siz));

	} // for ( $i=0; $i < $cnt; $i++ )

	fclose($fp);
}

if ( $argc == 1 )  exit();
for ( $i=1; $i < $argc; $i++ )
	rip_alk( $argv[$i] );
