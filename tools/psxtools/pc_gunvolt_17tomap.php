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

function gunvolt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( stripos($file,'.tga') === false )
		return;

	$bin1 = str2int($file, 0x0c, 4);
	$bin2 = str2int($file, 0x10, 4);
	$fnt1 = str2int($file, 0x1c, 4);
	$fnt2 = str2int($file, 0x20, 4);

	save_file("$fname.bin", substr($file, $bin1, $bin2));
	save_file("$fname.fnt", substr($file, $fnt1, $fnt2));
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );
