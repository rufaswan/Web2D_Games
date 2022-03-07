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
require 'common-guest.inc';

function kuma( $str )
{
	$str = preg_replace('|[^0-9a-fA-F]|', '', $str);
	if ( strlen($str) !== 8 )
		return;

	$f = float32( hexdec($str) );
	$i = (int)($f * 0x1000) & BIT32;
	printf("%s = (int) %8x , (float) %f\n", $str, $i, $f);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
float32 -> int32
	(float) 1.0 == (int) 0x1000

	0.333 = 3eaaaaab -> 0x0555
	3eaaaaab = sign 0  exp 7d/-2  man 2aaaab

	123.456 = 42f6e979 -> 0x07b74b
	42f6e979 = sign 0  exp 85/+6  man 76e979
 */
