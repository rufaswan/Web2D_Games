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
 *   https://www.52pojie.cn/thread-1350052-1-1.html
 *     bihaiorg
 */
function sfduel( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 16) !== 'VEGATOPJOY000000' )
		return;

	$key = array(
		0x51,0x49,0x51,0x49,
		0x41,0x4e,0x52,0x41,
		0x4e,0x58,0x49,0x41,
		0x4f,0x42,0x45,0x49,
	);

	foreach ( $key as $k => $v )
	{
		$b = ord( $file[0x10+$k] );
		$b ^= $v;
		$file[0x10+$k] = chr($b);
	} // foreach ( $key as $k => $v )

	file_put_contents("$fname.astc", substr($file,0x10));
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sfduel( $argv[$i] );
