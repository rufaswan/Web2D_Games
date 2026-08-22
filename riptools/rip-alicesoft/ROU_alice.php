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
declare( strict_types=1 );

require 'tool.inc';

// Pascha2/PaschaC++  Dungeon/field*_dtx/*.rou
function rou2rgba( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 3);
	if ( $mgc !== 'ROU' )  return;

	$hdsz = tool::ordstr($file, 8, 4);
	$w = tool::ordstr($file, 0x14, 4);
	$h = tool::ordstr($file, 0x18, 4);
	$pixsz = tool::ordstr($file, 0x24, 4);
	$alpsz = tool::ordstr($file, 0x28, 4);

	$t = 'ROU-';
	if ( $pixsz > 0 )  $t .= 'p';
	if ( $alpsz > 0 )  $t .= 'a';
	printf("$t , 0 , 0 , %4d , %4d , %s\n", $w, $h, $fname);

	$pix = tool::substr($file, $hdsz, $pixsz);
	$alp = tool::substr($file, $hdsz + $pixsz, $alpsz);

	$rgb = 'RGBA';
	$rgb .= tool::chr($w, 4);
	$rgb .= tool::chr($h, 4);

	$sz = $w * $h;
	for ( $i=0; $i < $sz; $i++ )
	{
		// $pix is BGR order
		$r = $pix[$i*3+2];
		$g = $pix[$i*3+1];
		$b = $pix[$i*3+0];
		$a = $alp[$i];
		$rgb .= $r . $g . $b . $a;
	}
	tool::save("$fname.rgba", $rgb);
}

tool::argv_callback($argv, 'rou2rgba');
