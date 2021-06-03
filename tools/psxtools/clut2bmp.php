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
require "common.inc";

function bmp_header( $cw , $ch )
{
	$data_of = 0x7a;
	$data_sz = $cw * $ch * 4;

	$head  = "BM"; // magic
	$head .= chrint( $data_of + $data_sz , 4 ); // filesize
	$head .= chrint( 0 , 2 ); // unused
	$head .= chrint( 0 , 2 ); // unused
	$head .= chrint( $data_of , 4 ); // data offset

	// 38 = v3 undocumented , add alpha channel
	// 6c = v4 win 95+ , add colorspace + gamma
	// 7c = v5 win 98+ , add icc profile
	$head .= chrint( 0x6c , 4 ); // dib head size
	$head .= chrint(  $cw , 4 ); // width
	$head .= chrint(  $ch , 4 ); // height
	$head .= chrint(    1 , 2 ); // plane
	$head .= chrint(   32 , 2 ); // bit-per-pixel
	$head .= chrint(    3 , 4 ); // compression
	$head .= chrint( $data_sz , 4 ); // data size
	$head .= chrint(   72 , 4 ); // density x
	$head .= chrint(   72 , 4 ); // density y
	$head .= chrint(    0 , 4 ); // palette num
	$head .= chrint(    0 , 4 ); // palette num - important

	// RGBA order
	$head .= BYTE . ZERO . ZERO . ZERO; // bitmask red
	$head .= ZERO . BYTE . ZERO . ZERO; // bitmask green
	$head .= ZERO . ZERO . BYTE . ZERO; // bitmask blue
	$head .= ZERO . ZERO . ZERO . BYTE; // bitmask alpha

	$head .= "RGBs";
	for ($i=0; $i < 0x24; $i++)
		$head .= ZERO; // colorspace - unused

	$head .= chrint(0 , 4); // gamma red
	$head .= chrint(0 , 4); // gamma green
	$head .= chrint(0 , 4); // gamma blue

	return $head;
}
//////////////////////////////
function clut2bmp( &$clut, $fname )
{
	$cc = str2int($clut,  4, 4);
	$w  = str2int($clut,  8, 4);
	$h  = str2int($clut, 12, 4);
	printf("CLUT , $cc , $w , $h , %s\n", $fname);

	$head = bmp_header( $w , $h );

	$pal = array();
	$pos = 0x10;
	for ( $i=0; $i < $cc; $i++ )
	{
		$pal[] = substr($clut, $pos, 4);
		$pos += 4;
	} // for ($i=0; $i < $cc; $i++)

	$data = "";
	while ( $h > 0 )
	{
		$h--;
		$pos = 0x10 + ($cc * 4) + ($h * $w);
		$pix = substr($clut, $pos, $w);
		for ( $x=0; $x < $w; $x++ )
		{
			$px = ord( $pix[$x] );
			$data .= $pal[$px];
		}
	} // while ( $h > 0 )

	file_put_contents("$fname.bmp", $head.$data);
	return;
}

function rgba2bmp( &$rgba, $fname )
{
	$w = str2int($rgba, 4, 4);
	$h = str2int($rgba, 8, 4);
	printf("RGBA , $w , $h , %s\n", $fname);

	$head = bmp_header( $w , $h );

	$data = "";
	while ( $h > 0 )
	{
		$h--;
		$pos = 12 + ($h * $w * 4);
		$data .= substr($rgba, $pos, $w*4);
	} // while ( $h > 0 )

	file_put_contents("$fname.bmp", $head.$data);
	return;
}
//////////////////////////////
function img2bmp( $fname )
{
	$file = file_get_contents( $fname );
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc == "CLUT" )
		return clut2bmp( $file, $fname );
	if ( $mgc == "RGBA" )
		return rgba2bmp( $file, $fname );

	return;
}

for ( $i=1; $i < $argc; $i++ )
	img2bmp( $argv[$i] );
