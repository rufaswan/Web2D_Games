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

$clut = array(
	'cc' => 0x10,
	'w' => 0,
	'h' => 0,
	'pal' => grayclut(0x10),
	'pix' => str_repeat(ZERO, 0x100*0x100),
);
for ( $i=0; $i < 0x10; $i++ )
	$clut['pix'][$i] = chr($i*0x11);
save_clutfile("gray-16.clut", $clut);

$clut = array(
	'cc' => 0x100,
	'w' => 0,
	'h' => 0,
	'pal' => grayclut(0x100),
	'pix' => str_repeat(ZERO, 0x100*0x100*4),
);
for ( $i=0; $i < 0x100; $i++ )
	$clut['pix'][$i] = chr($i);
save_clutfile("gray-256.clut", $clut);
