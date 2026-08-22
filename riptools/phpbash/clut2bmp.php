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
tool::require('class-clutfile');

define('ZERO2', "\x00\x00");
define('ZERO3', "\x00\x00\x00");
define('ZERO4', "\x00\x00\x00\x00");

function bmp_header( int $cw , int $ch ) : string
{
	$data_of = 0x7a;
	$data_sz = $cw * $ch * 4;

	$head  = 'BM'; // magic
	$head .= tool::chr( $data_of + $data_sz , 4 ); // filesize
	$head .= ZERO2; // unused
	$head .= ZERO2; // unused
	$head .= tool::chr( $data_of            , 4 ); // data offset

	// 38 = v3 undocumented , add alpha channel
	// 6c = v4 win 95+ , add colorspace + gamma
	// 7c = v5 win 98+ , add icc profile
	$head .= "\x6c"  . ZERO3; // dib head size
	$head .= tool::chr($cw , 4); // width
	$head .= tool::chr($ch , 4); // height
	$head .= chr( 1) . ZERO ; // plane
	$head .= chr(32) . ZERO ; // bit-per-pixel
	$head .= chr( 3) . ZERO3; // compression
	$head .= tool::chr( $data_sz , 4 ); // data size
	$head .= chr(72) . ZERO3; // density x
	$head .= chr(72) . ZERO3; // density y
	$head .= ZERO4; // palette num
	$head .= ZERO4; // palette num - important

	// RGBA order
	$head .= BYTE . ZERO . ZERO . ZERO; // bitmask red
	$head .= ZERO . BYTE . ZERO . ZERO; // bitmask green
	$head .= ZERO . ZERO . BYTE . ZERO; // bitmask blue
	$head .= ZERO3              . BYTE; // bitmask alpha

	$head .= 'RGBs';
	for ($i=0; $i < 0x24; $i++)
		$head .= ZERO; // colorspace - unused

	$head .= ZERO4; // gamma red
	$head .= ZERO4; // gamma green
	$head .= ZERO4; // gamma blue
	return $head;
}
//////////////////////////////
function clut2bmp( clutdata &$img, string $fname ) : void
{
	tool::trace(__FUNCTION__, $fname);
	clutfile::clut2rgba($img);
	rgba2bmp($img, $fname);
}

function rgba2bmp( clutdata &$img, string $fname ) : void
{
	tool::trace(__FUNCTION__, $fname);
	tool::trace('RGBA', $img->w, $img->h, $fname);

	$head = bmp_header( $img->w , $img->h );

	// BMP is left-to-right , then bottom-to-top order
	$data = '';
	$row  = $img->w * 4;
	$h    = $img->h;
	while ( $h > 0 )
	{
		$h--;
		$data .= tool::substr($img->pix, $h*$row, $row);
	} // while ( $h > 0 )

	file_put_contents("$fname.bmp", $head.$data);
}
//////////////////////////////
function img2bmp( string $fname ) : void
{
	$img = clutfile::load( $fname );
	if ( empty($img) )  return;

	if ( $img->t === 'CLUT' )
		clut2bmp( $img, $fname );
	if ( $img->t === 'RGBA' )
		rgba2bmp( $img, $fname );
}

tool::argv_callback($argv, 'img2bmp');
