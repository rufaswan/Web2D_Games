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
require "pc_gunvolt.inc";

function gunvolt( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$fnt = load_mapfnt("$pfx.fnt");
	$bin = load_mapbin("$pfx.bin");
	if ( empty($fnt) || empty($bin) )
		return;

	if ( count($fnt) > 1 )
		return php_error("%s.FNT has more than 1 texture", $pfx);

	$fn = sprintf("%s/%s.rgba", $pfx, $fnt[0]['fn']);
	save_clutfile($fn, $fnt[0]);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );

/*
mgv
	screen = 16x15 tile
	tile   = 16x16 pixel
bms
gv1
gv2
	screen = 25x15 tile
	tile   =  8x8  pixel , gv2/st01_a_XX = 16x16 pixel
gva
	screen = 27x15 tile
	tile   =  8x8  pixel
 */
