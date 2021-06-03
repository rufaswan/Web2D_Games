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
require "common-atlas.inc";

function tsr( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$dir = rtrim($dir, '/\\');

	$list = array();
	lsfile_r($dir, $list);

	$files = array();
	foreach ( $list as $fname )
	{
		$img = load_clutfile($fname);
		if ( empty($img) )
			continue;

		printf("add image %x x %x = %s\n", $img['w'], $img['h'], $fname);
		$img['id'] = $fname;
		$files[] = $img;
	} // foreach ( $list as $fname )

	list($ind, $cw, $ch) = atlasmap($files);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $cw;
	$pix['rgba']['h'] = $ch;
	$pix['rgba']['pix'] = canvpix($cw,$ch);

	foreach ( $files as $img )
	{
		$pix['src'] = $img;
		$pix['dx'] = $img['x'];
		$pix['dy'] = $img['y'];

		if ( isset($img['cc']) )
			copypix_fast($pix, 1);
		else
			copypix_fast($pix, 4);
	} // foreach ( $files as $img )

	savepix("$dir.atlas", $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tsr( $argv[$i] );
