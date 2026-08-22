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

function domind_decode( &$file )
{
	// 80051024
	//   a3/8009bb8a -> t0/801a3f26
	//   HUFS + 16f252
	$dec = '';
	trace("== begin sub_80050ee4()\n");

	$cod1 = 0 & 0x0f;
	$cod2 = 0 & 0xf0;
		$cod2 |= ($cod2 >> 4);

	switch ( $cod1 )
	{
		case 0:
			$dec .= chr($cod2);
			break;
		case 1:
			$dec .= str_repeat(chr($cod2), 2);
			break;
		case 2:
			$dec .= str_repeat(chr($cod2), 4);
			break;
		case 3:
			$dec .= str_repeat(chr($cod2), 8);
			break;
		case 4:
			break;
		case 5:
			break;
		case 6:
			break;
		case 7:
			break;
		case 8:
			break;
		case 9:
			break;
		case 10:
			break;
		case 11:
			break;
		case 12:
			break;
		case 13:
			break;
		case 14:
			break;
		case 15:
			break;
	} // switch ( $cod1 )

	trace("== end sub_80050ee4()\n");
	$file = $dec;
	return;
}

function prefix( $fname )
{
	// for *.ext only
	//if ( stripos($fname, '.ext') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	//if ( substr($file, 0, 4) !== 'FILE' )
		//return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
	return;
}

for ( $i=1; $i < $argc; $i++ )
	prefix( $argv[$i] );
