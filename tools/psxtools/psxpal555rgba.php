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

define("TILE_S", 16);

$gp_cc = 0; // 16 or 256

function paltile( $str )
{
	$len = strlen($str);
	$pix = "";
	for ( $i=0; $i < $len; $i += 4 )
	{
		$c = substr($str, $i, 4);
		$pix .= str_repeat($c, TILE_S);
	}
	return str_repeat($pix, TILE_S);
}

function palette( $fname )
{
	global $gp_cc;
	if ( $gp_cc < 1 )
		return printf("ERROR gp_cc is zero\n");

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$cc = $gp_cc * 2;
	while ( strlen($file) % $cc )
		$file .= ZERO;

	$cn = strlen($file) / $cc;
	$clut = mstrpal555($file, 0, $gp_cc, $cn);

	$rgba = "RGBA";
	$rgba .= chrint($gp_cc * TILE_S, 4);
	$rgba .= chrint($cn    * TILE_S, 4);

	foreach ( $clut as $cv )
		$rgba .= paltile($cv);

	save_file("$fname.rgba", $rgba);
	return;
}

echo "{$argv[0]}  [-16/-256]  PALETTE_FILE...\n";
for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt[0] == '-' )
		$gp_cc = $opt * -1;
	else
		palette( $opt );
}
