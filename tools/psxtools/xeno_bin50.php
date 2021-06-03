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

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	for ( $i=0; $i < $len; $i += 0x1000 )
	{
		$clut = "CLUT";
		$clut .= chrint(0x80, 4);
		$clut .= chrint(60, 4);
		$clut .= chrint(64, 4);
		$clut .= strpal555($file, $i+0, 0x80);
		$clut .= substr($file, $i+0x100, 60*64);

		$fn = sprintf("$dir/%04d.clut", $i/0x1000);
		save_file($fn, $clut);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
