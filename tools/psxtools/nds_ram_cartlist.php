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
 *
 * Special Thanks
 *   GBATEK v2.8f (no$gba)
 *     Martin Korth
 */
require 'common.inc';
require 'nds.inc';

function ndsrom( $fname )
{
	$nds = new NDSList;
	$ntr = $nds->load($fname);
	if ( $ntr === -1 )
		return;

	if ( count($nds->list) < 2 ) // head.bin
		return;

	$dir = str_replace('.', '_', $fname);
	$txt = '';
	foreach ($nds->list as $lk => $lv )
	{
		// id  = -1 & BIT16
		// ram = -1 & BIT32
		$log = sprintf('%4x , %8x , %8x , %8x , %s', $lv['id'] & BIT16, $lv['pos'], $lv['siz'], $lv['ram'] & BIT32, $lk);
		echo "$log\n";
		$txt .= "$log\n";

		$sub = $nds->loadfile($lk);
		$fn  = sprintf('%s/%s', $dir, $lk);
		if ( $lv['ram'] > 0 )
		{
			$pad = str_repeat(ZERO, $lv['ram'] & BIT16);
			$sub = $pad . $sub;
		}
		save_file($fn, $sub);
	} // foreach ($nds->list as $lv )

	save_file("$dir/cartlist.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndsrom( $argv[$i] );
