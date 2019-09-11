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
//////////////////////////////
define("ZERO", chr(0));
define("BIT8", 0xff);

function int2str( $int, $byte )
{
	$str = "";
	while ( $byte > 0 )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
		$byte--;
	} // while ( $byte > 0 )
	return $str;
}
//////////////////////////////
function ain_zip( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc == "VERS" )
		$nmgc = "AI2";
	else
		$nmgc = "ZLB";

	printf("$nmgc , $fname\n");
	// ZLIB_ENCODING_RAW
	// ZLIB_ENCODING_DEFLATE
	// ZLIB_ENCODING_GZIP
	$zip = zlib_encode($file , ZLIB_ENCODING_DEFLATE );

	$len_d = strlen($file);
	$len_z = strlen($zip);

	$ain  = $nmgc . ZERO;
	$ain .= int2str(0,4);
	$ain .= int2str($len_z,4);
	$ain .= int2str($len_d,4);
	$ain .= $zip;

	file_put_contents("$fname.$ngmc", $ain);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain_zip( $argv[$i] );
