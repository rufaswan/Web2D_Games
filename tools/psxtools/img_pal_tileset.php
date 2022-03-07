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

define('TILE_S', 16);

function palfile( $cc, $fname )
{
	if ( $cc < 1 )
		return php_error('ERROR cc', $cc);

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ceil = int_ceil( strlen($file), $cc*4);
	while ( strlen($file) < $ceil )
		$file .= ZERO;

	$w = $cc;
	$h = $ceil / ($cc*4);

	$img  = 'RGBA';
	$img .= chrint($w * TILE_S, 4);
	$img .= chrint($h * TILE_S, 4);

	$p = 0;
	for ( $y=0; $y < $h; $y++ )
	{
		$row = '';
		for ( $x=0; $x < $w; $x++ )
		{
			$c = substr($file, $p, 4);
				$p += 4;
			$row .= str_repeat($c, TILE_S);
		} // for ( $x=0; $x < $w; $x++ )

		$img .= str_repeat($row, TILE_S);
	} // for ( $y=0; $y < $h; $y++ )

	save_file("$fname.tile.rgba", $img);
	return;
}

echo "{$argv[0]}  [-16/-256]  PALETTE_FILE...\n";
$cc = 0;
for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt[0] == '-' )
		$cc = (int)(-$opt);
	else
		palfile( $cc, $opt );
}
