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

	$img = array();
	for ( $i=0; $i < 4; $i++ )
	{
		$img[$i] = array(
			'cc'  => 0x100,
			'w'   => $file['w'],
			'h'   => $file['h'],
			'pal' => grayclut(0x100),
			'pix' => '',
		);
	}

	$len = strlen($file['pix']);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$img[0]['pix'] .= $file['pix'][$i+0];
		$img[1]['pix'] .= $file['pix'][$i+1];
		$img[2]['pix'] .= $file['pix'][$i+2];
		$img[3]['pix'] .= $file['pix'][$i+3];
	}

	save_clutfile("$fname.r.clut", $img[0]);
	save_clutfile("$fname.g.clut", $img[1]);
	save_clutfile("$fname.b.clut", $img[2]);
	save_clutfile("$fname.a.clut", $img[3]);
	return;
}

echo "to seperate RGBA to its own channel\n";
for ( $i=1; $i < $argc; $i++ )
	rgbachan( $argv[$i] );
