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

function vfcg( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen($file);
	$w = 0;
	$h = 0;
	switch ( $len )
	{
		//case 0x:  $w = 0x; $h = 0x; break;
		case 0x4100:   $w = 0xa0;  $h = 0x34;  break;
		case 0x5100:   $w = 0xd8;  $h = 0x30;  break;
		case 0x5438:   $w = 0xc4;  $h = 0x37;  break;
		case 0x63b0:   $w = 0xe8;  $h = 0x37;  break;
		case 0xafc8:   $w = 0x96;  $h = 0x96;  break;
		case 0x13c68:  $w = 0x10e; $h = 0x96;  break;

		case 0x32000:  $w = 0x200; $h = 0xc8;  break;
		case 0x4c000:  $w = 0x200; $h = 0x130; break;
		case 0x4d000:  $w = 0x200; $h = 0x134; break;
		case 0x4d800:  $w = 0x200; $h = 0x136; break;
		case 0x50000:  $w = 0x200; $h = 0x140; break;
		case 0x57800:  $w = 0x200; $h = 0x15e; break;
		case 0x58000:  $w = 0x200; $h = 0x160; break;
		case 0x5a000:  $w = 0x200; $h = 0x168; break;
		case 0x5e000:  $w = 0x200; $h = 0x178; break;
		case 0x67800:  $w = 0x200; $h = 0x19e; break;
		case 0x6a000:  $w = 0x200; $h = 0x1a8; break;
		case 0x80000:  $w = 0x200; $h = 0x200; break;

		case 0x16000:  $w = 0x400; $h = 0x1c;  break;
		case 0x2d3f0:  $w = 0x4e;  $h = 0x4a4; break;
		default:
			return php_error("%x = %s", $len, $fname);
	} // switch ( $len )

	printf("%3x x %3x = %s\n", $w, $h, $fname);
	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => '',
	);

	for ( $i=0; $i < $len; $i += 2 )
		$img['pix'] .= rgb555( $file[$i+1] . $file[$i+0] );

	save_clutfile("$fname.rgba", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	vfcg( $argv[$i] );
