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

function psxtimfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// can also be 00
	//$mgc = str2int($file, 0, 4);
	//if ( $mgc != 0x10 )  return;

	$tim = psxtim($file);
	if ( empty( $tim['pix'] ) )
		return;

	$dir = str_replace('.', '_', $fname);

	if ( $tim['t'] == 'RGBA' )
	{
		$data = 'RGBA';
		$data .= chrint($tim['w'], 4);
		$data .= chrint($tim['h'], 4);
		$data .= $tim['pix'];
		save_file("$dir.rgba", $data);
		return;
	}

	if ( $tim['t'] == 'CLUT' )
	{
		$pal = array();
		if ( isset( $tim['pal'] ) )
			$pal = $tim['pal'];
		else
			$pal[] = grayclut( $tim['cc'] );

		$data = 'CLUT';
		$data .= chrint($tim['cc'], 4);
		$data .= chrint($tim['w'], 4);
		$data .= chrint($tim['h'], 4);

		$cnt = count($pal);
		foreach ( $pal as $ck => $cv )
		{
			// skip black/white only background
			if ( trim($cv, ZERO.BYTE) === '' )
				continue;

			$clut = $data;
			$clut .= $cv;
			$clut .= $tim['pix'];

			if ( $cnt == 1 )
				save_file("$dir.clut", $clut);
			else
				save_file("$dir/$ck.clut", $clut);
		} // foreach ( $pal as $ck => $cv )

		return;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxtimfile( $argv[$i] );

/*
 * Weird TIM files
 * - Legend of Mana /wm/wmap/wm1.tim
 *   - TIM 2 = 8-bpp CLUT gray
 *
*/
