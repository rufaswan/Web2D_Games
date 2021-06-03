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

function disc3( $fname )
{
	// for cdimage.elf only
	if ( stripos($fname, 'cdimage.elf') === false )
		return;

	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);

	$head = fp2str($fp, 0, 0x10);

	$cnt = str2int($head, 0, 4);
	$siz = str2int($head, 8, 4);
	$head = fp2str($fp, 0, $siz);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 0x10 + ($i * 0x10);
		$lba = str2int($head, $pos+4, 3);
		$siz = str2int($head, $pos+8, 4);
		$fn = sprintf("%s/%04d.bin", $dir, $i);
		printf("%6x , %8x , %8x , %s\n", $lba, $lba*0x800, $siz, $fn);

		$sub = fp2str($fp, $lba*0x800, $siz);
		save_file($fn, $sub);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc3( $argv[$i] );
