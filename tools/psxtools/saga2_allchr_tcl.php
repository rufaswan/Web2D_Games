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

function saga2( $fname )
{
	// only allchr.tcl
	if ( stripos($fname, "allchr.tcl") === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pix = "";
	for ( $i=0; $i < 0xb000; $i++ )
	{
		$b = ord( $file[$i] );
		$b1 = ($b >> 0) & BIT4;
		$b2 = ($b >> 4) & BIT4;
		$pix .= chr($b1) . chr($b2);
	}

	$clut = mstrpal555($file, 0xb000, 0x10, 64);
	$dir = str_replace('.', '_', $fname);

	foreach ( $clut as $k => $c )
	{
		if ( trim($c, ZERO.BYTE) == "" )
			continue;
		$fn = sprintf("$dir/%02d.clut", $k);

		$data = "CLUT";
		$data .= chrint(0x10, 4); // no clut
		$data .= chrint(256 , 4); // width
		$data .= chrint(352 , 4); // height
		$data .= $c;
		$data .= $pix;

		printf("%8x , %8x , $fn\n", $st, $siz);
		save_file($fn, $data);
	}

	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
