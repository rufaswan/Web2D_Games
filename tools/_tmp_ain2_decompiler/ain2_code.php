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
require("ain2_code.inc");
//////////////////////////////
define("BIT32", 0xffffffff);

function str2int( &$str, &$pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	$pos += $byte;
	return $int;
}
function sint32( &$file, &$st )
{
	$n = str2int($file, $st, 4);
	if ( $n >> 31 )
		return ($n - BIT32 - 1);
	else
		return $n;
}
//////////////////////////////
function trace()
{
	$args = func_get_args();
	$var  = array_shift($args);
		$var .= "\n";
	vprintf($var, $args);
}

//////////////////////////////
function ain2code( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$st = 0;
	$ed = strlen($file);
	while ( $st < $ed )
	{
		$bak = $st;
		$r = code2inst($file, $st);
		printf("%8x , %s\n", $bak, $r);

		if ( $r == "" )
			return;
	} // while ( $st < $ed )
}

ain2code("CODE");
