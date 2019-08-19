<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
//////////////////////////////
define("BIT8" , 0xff);

function str2int( &$str, $pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	return $int;
}
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
function vsp2pms( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$x = str2int( $file, 0, 2 );
	$y = str2int( $file, 2, 2 );
	$w = str2int( $file, 4, 2 ) - $x;
	$h = str2int( $file, 6, 2 ) - $y;

	$res = ord( $file[8] );
	if ( ! $res )  return;
	if ( $w < 0 )  return;
	if ( $h < 0 )  return;

	printf("VSP-1 $fname\n");

	$head  = "PM";
	$head .= int2str(1 , 2); // ver
	$head .= int2str(0x2c , 2); // head
	$head .= int2str(8 , 1); // bpp
	$head .= int2str(0 , 1); // shdw
	$head .= int2str(0 , 1); // flag
	$head .= int2str(0 , 1);
	$head .= int2str(0x100 , 2); // bank
	$head .= int2str(0 , 4);
	$head .= int2str($x , 4); // x
	$head .= int2str($y , 4); // y
	$head .= int2str($w , 4); // w
	$head .= int2str($h , 4); // h
	$head .= int2str(0x32c , 4); // dat
	$head .= int2str(0x2c  , 4); // pal
	$head .= int2str(0 , 4); // meta

	$data  = substr($file, 0x20);
	$pms   = $head . $data;
	file_put_contents("$fname.pms", $pms);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	vsp2pms( $argv[$i] );
