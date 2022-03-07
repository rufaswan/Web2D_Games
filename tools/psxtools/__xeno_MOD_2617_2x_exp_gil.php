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

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "== $fname\n";
	$len = str2int($file, 0, 2);
	for ( $i = 0x7e; $i < $len; $i += 0x170 )
	{
		$hp  = str2int($file, $i+0, 2);
		$mhp = str2int($file, $i+2, 2);

		$gear = ( ($hp|$mhp) == 0 );
		if ( $gear )
			$data = array(0xb8,0xbc,0x100,0x10a);
		else
			$data = array(0,2,0x100,0x10a);

		$exp = str2int($file, $i+$data[2], 4);
		$gil = str2int($file, $i+$data[3], 2);

		if ( $gear )
		{
			$hp  = str2int($file, $i+$data[0], 4);
			$mhp = str2int($file, $i+$data[1], 4);
			printf("%4x GEAR = HP %6x/%6x  EXP %8x GIL %4x\n", $i, $hp, $mhp, $exp, $gil);
		}
		else
		{
			printf("%4x CHAR = HP %4x/%4x  EXP %8x GIL %4x\n", $i, $hp, $mhp, $exp, $gil);
		}

		$sexp = chrint($exp * 2, 4);
		$sgil = chrint($gil * 2, 2);
		str_update($file, $i+$data[2], $sexp);
		str_update($file, $i+$data[3], $sgil);
	} // for ( $i = 0x7e; $i < $len; $i += 0x170 )

	file_put_contents($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
