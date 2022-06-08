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

function rgbachan( $fname )
{
	$file = load_clutfile($fname);
	if ( empty($file) )  return;

	// skip CLUT
	if ( isset($file['pal']) )
		return;

	$r = '';
	$g = '';
	$b = '';
	$a = '';
	$len = strlen($file['pix']);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$r .= $file['pix'][$i+0];
		$g .= $file['pix'][$i+1];
		$b .= $file['pix'][$i+2];
		$a .= $file['pix'][$i+3];
	}

	save_file("$fname.r", $r);
	save_file("$fname.g", $g);
	save_file("$fname.b", $b);
	save_file("$fname.a", $a);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rgbachan( $argv[$i] );
