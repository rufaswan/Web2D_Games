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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');
tool::require('func-console');

function saga2( string $fname ) : void
{
	// only allchr.tcl
	if ( stripos($fname, 'allchr.tcl') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$px1 = tool::substr($file, 0     , 0xa800);
	$px2 = tool::substr($file, 0xa800, 0x800 );
	$pal = tool::substr($file, 0xb000, 0x800 );

	$px1 = psx::bpp4to8($px1);
	$px2 = psx::bpp4to8($px2);
	$pal = psx::pal555($pal);

	$canv = new clutdata;
	$canv->pal = '1234';
	$cid = 0;

	$canv->w = 0x100;
	$canv->h = 0x10;
	$canv->pix = $px2;
	// 10x1 = 10
	for ( $x=0; $x < 0x100; $x += 0x10 )
	{
		$img = clutfile::ripsrc($canv, $x, 0, 0x10, 0x10);
		$img->pal = tool::substr($pal, $cid*0x40, 0x40);
			$img->pal[3] = ZERO;

		$fn = sprintf('%s/%04d', $dir, $cid);
			$cid++;
		tool::trace('10x10', $fn);
		clutfile::save($fn, $img);
	} // for ( $x=0; $x < 0x100; $x += 0x20 )

	$canv->w = 0x100;
	$canv->h = 0x150;
	$canv->pix = $px1;
	// 8x6 = 30
	for ( $y=0; $y < 0x150; $y += 0x38 ) // 0 38 70 a8 e0 118 150
	{
		for ( $x=0; $x < 0x100; $x += 0x20 ) // 0 20 40 60 80 a0 c0 e0 100
		{
			$img = clutfile::ripsrc($canv, $x, $y, 0x20, 0x38);
			$img->pal = tool::substr($pal, $cid*0x40, 0x40);
				$img->pal[3] = ZERO;

			$fn = sprintf('%s/%04d', $dir, $cid);
				$cid++;
			tool::trace('20x38', $fn);
			clutfile::save($fn, $img);
		} // for ( $x=0; $x < 0x100; $x += 0x20 )
	} // for ( $y=0; $y < 0x150; $y += 0x38 )
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
