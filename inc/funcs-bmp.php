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

	// BGRA order
	$head .= ZERO . ZERO . BYTE . ZERO; // bitmask red
	$head .= ZERO . BYTE . ZERO . ZERO; // bitmask green
	$head .= BYTE . ZERO . ZERO . ZERO; // bitmask blue
	$head .= ZERO . ZERO . ZERO . BYTE; // bitmask alpha

	$head .= "BGRs";
	for ($i=0; $i < 0x24; $i++)
		$head .= ZERO; // colorspace - unused

	$head .= chrint( 0 , 4 ); // gamma red
	$head .= chrint( 0 , 4 ); // gamma green
	$head .= chrint( 0 , 4 ); // gamma blue

	return $head;
}
//////////////////////////////
function clut2bmp( $clut_fn , $bmp_fn , $num )
{
	$clut = file_get_contents( $clut_fn );
		if ( empty($clut) )  return;
	$mgc = substr($clut, 0, 4);
		if ( $mgc != "CLUT" )  return;

	$cn = str2int($clut,  4, 4);
	$cw = str2int($clut,  8, 4);
	$ch = str2int($clut, 12, 4);

	$bmp = bmp_header( $cw , $ch );

	$pal = array();
	for ($i=0; $i < $cn; $i++)
	{
		$pos = 0x10 + ($i * 4);
		$data = "";
		$data .= $clut[$pos+2]; // blue
		$data .= $clut[$pos+1]; // green
		$data .= $clut[$pos+0]; // red
		if ( $i == $num ) // alpha
			$data .= ZERO;
		else
			$data .= $clut[$pos+3];
		$pal[] = $data;
	}

	$data = "";
	while ( $ch > 0 )
	{
		$ch--;
		$pos = 0x10 + ($cn * 4) + ($cw * $ch);
		$pix = substr($clut, $pos, $cw);
		for ( $i=0; $i < $cw; $i++ )
		{
			$px = ord( $pix[$i] );
			$data .= $pal[$px];
		}
	}

	$bmp .= $data;
	file_put_contents( $bmp_fn, $bmp );
}
//////////////////////////////
/*
As RGBA already has alpha, there is no need to convert
a color to alpha on-the-fly by script.
The image is already PNG and the func is unused.

function rgba2bmp( $rgba_fn , $bmp_fn ) {}
 */
//////////////////////////////
