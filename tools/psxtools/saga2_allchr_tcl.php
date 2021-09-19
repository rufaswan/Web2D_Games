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

	$dir = str_replace('.', '_', $fname);

	$px1 = substr($file, 0     , 0xa800);
	$px2 = substr($file, 0xa800, 0x800 );
	$pal = substr($file, 0xb000, 0x800 );

	bpp4to8($px1);
	bpp4to8($px2);
	$pal = pal555($pal);

	$cid = 0;
	for ( $x=0; $x < 0x100; $x += 0x10 )
	{
		$img = array(
			'cc'  => 0x10,
			'w'   => 0x10,
			'h'   => 0x10,
			'pal' => substr($pal, $cid*0x40, 0x40),
			'pix' => rippix8($px2, $x, 0, 0x10, 0x10, 0x100, 0x10),
		);

		$fn = sprintf("%s/%04d.clut", $dir, $cid);
			$cid++;
		save_clutfile($fn, $img);
	} // for ( $x=0; $x < 0x100; $x += 0x20 )

	for ( $y=0; $y < 0x150; $y += 0x38 )
	{
		for ( $x=0; $x < 0x100; $x += 0x20 )
		{
			$img = array(
				'cc'  => 0x10,
				'w'   => 0x20,
				'h'   => 0x38,
				'pal' => substr($pal, $cid*0x40, 0x40),
				'pix' => rippix8($px1, $x, $y, 0x20, 0x38, 0x100, 0x150),
			);

			$fn = sprintf("%s/%04d.clut", $dir, $cid);
				$cid++;
			save_clutfile($fn, $img);
		} // for ( $x=0; $x < 0x100; $x += 0x20 )
	} // for ( $y=0; $y < 0x150; $y += 0x38 )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
