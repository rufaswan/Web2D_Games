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

function saturn_yuna( &$file, $fname )
{
	$w = ordint( $file[1] . $file[0] );
	$h = ordint( $file[3] . $file[2] );
	printf("SATURN , %4d , %4d , $fname\n", $w, $h);

	$ed = strlen($file);
	$st = 4;

	$rgba = 'RGBA';
	$rgba .= chrint($w, 4);
	$rgba .= chrint($h, 4);
	while ( $st < $ed )
	{
		$b = ord( $file[$st] );
		if ( $b == 0 || $b & 0x80 )
		{
			$rgba .= rgb555( $file[$st+1] . $file[$st+0] );
			$st += 2;
		}
		else
		{
			$cnt = $b;
			$b = rgb555( $file[$st+2] . $file[$st+1] );
			while ( $cnt > 0 )
			{
				$rgba .= $b;
				$cnt--;
			}
			$st += 3;
		}
	}
	$max = 12 + ($w * $h * 4);
	while ( strlen($rgba) < $max )
		$rgba .= ZERO;

	file_put_contents("$fname.rgba", $rgba);
	return;
}

function psx_yuna( &$file, $fname )
{
	$w = str2int($file, 0x10, 2);
	$h = str2int($file, 0x12, 2);
	printf("PSX , %4d , %4d , $fname\n", $w, $h);

	$ed = strlen($file);
	$st = 0x14;

	$rgba = 'RGBA';
	$rgba .= chrint($w, 4);
	$rgba .= chrint($h, 4);
	while ( $st < $ed )
	{
		$b = substr($file, $st+0, 2);
		//$rgba .= $b;
		$rgba .= rgb555($b);
		$st += 2;
	}
	file_put_contents("$fname.rgba", $rgba);
	return;
}

function yuna( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// playstation little endian
	if ( str2int($file, 0, 4) == 0x10 )
		psx_yuna( $file, $fname );
	// saturn big endian
	else
		saturn_yuna( $file, $fname );

	return;
}

for ( $i=1; $i < $argc; $i++ )
	yuna( $argv[$i] );
