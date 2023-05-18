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

define('CANV_S', 0x200);

function mana( $fname )
{
	// JT????.DAT  = indexed 256 colors , 1 palette
	// ZUKAN1P.DAT = RGB
	if ( stripos($fname, ".dat") == false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;
	echo "$fname\n";

	$dat = substr($file, str2int($file, 12, 4));
	$tim = psxtim($dat);

	if ( $tim['t'] === 'RGBA' )
	{
		save_clutfile("$fname.rgba", $tim);
		return;
	}

	$pix = copypix_def(CANV_S,CANV_S);

	$cnt = ord( $file[0x14] );
	$st = 0x18;
	while ( $cnt > 0 )
	{
		$dx = str2int($file, $st+ 0, 2);
		$dy = str2int($file, $st+ 2, 2);
		$pix['dx'] = $dx;
		$pix['dy'] = $dy;

		$sx = str2int($file, $st+ 4, 2);
		$sy = str2int($file, $st+ 6, 2);
		$w  = str2int($file, $st+ 8, 2);
		$h  = str2int($file, $st+10, 2);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($tim['pix'], $sx, $sy, $w, $h, $tim['w'], $tim['h']);
		$pix['src']['pal'] = $tim['pal'];

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf("\n");
		copypix_fast($pix);

		$cnt--;
		$st += 0x10;
	} // while ( $cnt > 0 )

	savepix($fname, $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
rect over
  0429.dat
 */
