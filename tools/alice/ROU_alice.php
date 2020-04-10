<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
//////////////////////////////
// Pascha2/PaschaC++  Dungeon/field*_dtx/*.rou
function rou2rgba( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$mgc = substr($file, 0, 3);
	if ( $mgc != "ROU" )  return;

	$hdz = str2int($file, 8, 4);
	$w = str2int($file, 0x14, 4);
	$h = str2int($file, 0x18, 4);
	$p = str2int($file, 0x24, 4);
	$a = str2int($file, 0x28, 4);

	$t = "ROU-";
	if ( $p )  $t .= "p";
	if ( $a )  $t .= "a";
	printf("$t , 0 , 0 , %4d , %4d , $fname\n", $w, $h);

	$pix = substr($file, $hdz, $p);
	$alp = substr($file, $hdz + $p, $a);

	$rgb = "RGBA";
	$rgb .= chrint($w, 4);
	$rgb .= chrint($h, 4);

	$sz = $w * $h;
	for ( $i=0; $i < $sz; $i++ )
	{
		$r = ( empty($pix) ) ? ZERO : $pix[$i*3+2];
		$g = ( empty($pix) ) ? ZERO : $pix[$i*3+1];
		$b = ( empty($pix) ) ? ZERO : $pix[$i*3+0];
		$a = ( empty($alp) ) ? BYTE : $alp[$i];
		$rgb .= $r . $g . $b . $a;
	}
	file_put_contents("$fname.rgba", $rgb);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	rou2rgba( $argv[$i] );
