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
function pngchunk( $name, $data, $zlib=false )
{
	$sect = $name;
	if ( $zlib )
		$sect .= zlib_encode($data, ZLIB_ENCODING_DEFLATE, 9);
	else
		$sect .= $data;

	$len = strlen($sect) - 4;
	$crc = crc32 ($sect);

	$png = '';
	$png .= chrbig($len, 4);
	$png .= $sect;
	$png .= chrbig($crc, 4);
	return $png;
}

function save_png( $fname, $w, $h, $rgba )
{
	// add filter byte on the beginning of every row
	// 0 = none
	// 1 = Sub(x) + Raw(x-bpp)
	// 2 = Up(x) + Prior(x)
	// 3 = Average(x) + floor((Raw(x-bpp)+Prior(x))/2)
	// 4 = Paeth(x) + PaethPredictor(Raw(x-bpp), Prior(x), Prior(x-bpp))
	$idat = "";
	for ( $y=0; $y < $h; $y++ )
		$idat .= ZERO . substr($rgba, $y*$w*4, $w*4);

	$ihdr = "IHDR";
	$ihdr .= chrbig($w, 4); // width
	$ihdr .= chrbig($h, 4); // height
	$ihdr .= chr(8); // bit depth
	$ihdr .= chr(6); // color type , 0=gray , 2=rgb , 3=index , 4=gray+a , 6=rgb+a
	$ihdr .= ZERO; // compression , 0=zlib
	$ihdr .= ZERO; // filter , 0=adaptive/5 type
	$ihdr .= ZERO; // interlace , 0=none , 1=adam7

	// PNG 8-bit RGBA
	$png = "\x89PNG\x0d\x0a\x1a\x0a";
	$png .= pngchunk("IHDR", $ihdr);
	$png .= pngchunk("IDAT", $idat, true);
	$png .= pngchunk("IEND", "");

	file_put_contents($fname, $png);
	return;
}
//////////////////////////////
function clut2png( $clut_fn , $png_fn , $num )
{
	$clut = file_get_contents( $clut_fn );
	if ( empty($clut) )  return;

	if ( substr($clut, 0, 4) != "CLUT" )
		return;

	$cc = str2int($clut,  4, 4);
	$w  = str2int($clut,  8, 4);
	$h  = str2int($clut, 12, 4);
	$sz = $w * $h;

	$pal = substr($clut, 0x10 , $cc*4);
	$pix = substr($clut, 0x10 + $cc*4, $sz);

	if ( $num >= 0 ) // alpha
	{
		$b = ($num * 4) + 3;
		$pal[$b] = ZERO;
	}

	$rgba = "";
	for ( $i=0; $i < $sz; $i++ )
	{
		$b = ord( $pix[$i] );
		$rgba .= substr($pal, $b*4, 4);
	}

	save_png($png_fn, $w, $h, $rgba);
	return;
}
//////////////////////////////
/*
As RGBA already has alpha, there is no need to convert
a color to alpha on-the-fly by script.
The image is already PNG and the func is unused.

function rgba2png( $rgba_fn , $png_fn ) { return; }
 */
//////////////////////////////
