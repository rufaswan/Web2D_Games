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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');
tool::require('class-zipstore');
tool::require('class-clutfile');
tool::extension('zlib');

function pngfilter( string &$pix, int $w, int $h, int $byte ) : void
{
	// add filter byte on the beginning of every row
	// 0 = none
	// 1 = Sub(x) + Raw(x-bpp)
	// 2 = Up(x) + Prior(x)
	// 3 = Average(x) + floor((Raw(x-bpp)+Prior(x))/2)
	// 4 = Paeth(x) + PaethPredictor(Raw(x-bpp), Prior(x), Prior(x-bpp))
	$idat = '';
	$row  = $w * $byte;
	for ( $y=0; $y < $h; $y++ )
		$idat .= ZERO . tool::substr($pix, $y*$row, $row);
	$pix = $idat;
}

function pngchunk( string $name, string $data, bool $zlib=false ) : string
{
	$sect = $name;
	if ( $zlib )
		$sect .= zlib_encode($data, ZLIB_ENCODING_DEFLATE, 9);
		//$sect .= zipstore::deflate($data);
	else
		$sect .= $data;

	$len = strlen($sect) - 4;
	//$crc = crc32 ($sect);

	$png = '';
	$png .= ieee754::chr($len, 4);
	$png .= $sect;
	//$png .= ieee754::chr($crc, 4);
	$png .= hash('crc32b', $sect, true);
	return $png;
}
//////////////////////////////
function clut2png( clutdata &$img, string $fname ) : void
{
	echo "== clut2png( $fname )\n";
	$plte = '';
	$trns = '';
	$pal = &$img->pal;
	$cc  = strlen($pal);
	for ( $i=0; $i < $cc; $i += 4 )
	{
		$plte .= $pal[$i+0] . $pal[$i+1] . $pal[$i+2];
		$trns .= $pal[$i+3];
	}
	$trns = rtrim($trns, BYTE);

	pngfilter($img->pix, $img->w, $img->h, 1);

	// PNG 8-bit CLUT
	$ihdr = '';
	$ihdr .= ieee754::chr($img->w, 4); // width
	$ihdr .= ieee754::chr($img->h, 4); // height
	$ihdr .= chr(8); // bit depth , 1 2 4 8 16
	$ihdr .= chr(3); // color type , +1=index  +2=rgb  +4=alpha  (invalid=1 1+4 1+2+4)
	$ihdr .= ZERO; // compression , 0=zlib
	$ihdr .= ZERO; // filter , 0=adaptive/5 type
	$ihdr .= ZERO; // interlace , 0=none , 1=adam7

	$png = PNG_MAGIC;
	$png .= pngchunk('IHDR', $ihdr);
	$png .= pngchunk('PLTE', $plte);
	if ( ! empty($trns) )
		$png .= pngchunk('tRNS', $trns);
	$png .= pngchunk('IDAT', $img->pix, true);
	$png .= pngchunk('IEND', '');

	file_put_contents("$fname.png", $png);
}

function rgba2png( clutdata &$img, string $fname ) : void
{
	echo "== rgba2png( $fname )\n";
	pngfilter($img->pix, $img->w, $img->h, 4);

	// PNG 8-bit RGBA
	$ihdr = '';
	$ihdr .= ieee754::chr($img->w, 4); // width
	$ihdr .= ieee754::chr($img->h, 4); // height
	$ihdr .= chr(8); // bit depth
	$ihdr .= chr(6); // color type , +1=index  +2=rgb  +4=alpha  (invalid=1 1+4 1+2+4)
	$ihdr .= ZERO; // compression , 0=zlib
	$ihdr .= ZERO; // filter , 0=adaptive/5 type
	$ihdr .= ZERO; // interlace , 0=none , 1=adam7

	$png = PNG_MAGIC;
	$png .= pngchunk('IHDR', $ihdr);
	$png .= pngchunk('IDAT', $img->pix, true);
	$png .= pngchunk('IEND', '');

	file_put_contents("$fname.png", $png);
}
//////////////////////////////
function img2png( string $fname ) : void
{
	$img = clutfile::load($fname);
	if ( empty($img) )  return;

	if ( $img->t === 'CLUT' )
		clut2png($img, $fname);
	if ( $img->t === 'RGBA' )
		rgba2png($img, $fname);
}

tool::argv_callback($argv, 'img2png');
