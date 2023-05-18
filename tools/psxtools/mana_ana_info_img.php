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

function mana( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = str2int($file, 0, 4);
	if ( $mgc !== 0x28 )  return;

	$dir = str_replace('.', '_', $fname);

	// talking portrait
	$pos = str2int($file, 0x20, 3);
	$num = str2int($file, $pos, 3);
		$pos += 4;

	// all in 48x48 , 4-bpp
	$size = (0x30 / 2) * 0x30;
	for ( $i=0; $i < $num; $i++ )
	{
		$pal = substr($file, $pos+0, 0x20);
		$pix = substr($file, $pos+0x20, $size);
			$pos += (0x20 + $size);

		bpp4to8($pix);
		$img = array(
			'cc'  => 0x10,
			'w'   => 0x30,
			'h'   => 0x30,
			'pal' => pal555($pal),
			'pix' => $pix,
		);

		$fn = sprintf('%s/talk-%04d.clut', $dir, $i);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < $num; $i++ )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );
