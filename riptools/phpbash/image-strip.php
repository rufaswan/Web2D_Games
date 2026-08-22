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

function if_save_file( string &$file, string $fname, int $size ) : void
{
	// save only file is smaller than original
	if ( strlen($file) < $size )
	{
		printf("[%x -> %x] %s\n", $size, strlen($file), $fname);
		file_put_contents($fname, $file);
	}
}
//////////////////////////////
function pngfile( string &$file, string $fname ) : bool
{
	if ( strlen($file) < (8 + 8+13+4 + 8+4 + 8+4) ) // magic + IHDR + IDAT + IEND
		return false;
	if ( substr($file,0,8) !== PNG_MAGIC )
		return false;
	tool::trace(__FUNCTION__, $fname);
	$size = strlen($file);

	$new = PNG_MAGIC;
	$pos = 8;
	while ( $pos < $size )
	{
		// chunk = [LEN] [NAME] [DATA...] [CRC32]
		$len = 8 + ieee754::ordstr($file, $pos+0, 4) + 4;
		$mgc = substr($file, $pos+4, 4);
		switch ( $mgc )
		{
			case 'IHDR':  case 'IDAT':  case 'IEND':
			case 'acTL':  case 'fcTL':  case 'fdAT':
			case 'PLTE':  case 'tRNS':
				$new .= tool::substr($file, $pos, $len);
				break;
		} // switch ( $mgc )

		$pos += $len;
	} // while ( $pos < $size )

	if_save_file($new, $fname, $size);
	return true;
}

function gif_sublen( string &$file, int $pos ) : int
{
	$len = 0;
	while (1)
	{
		if ( ! isset($file[$pos]) )
			break;

		$c = ord($file[$pos]);
			$pos++;
			$len++;

		if ( $c === 0 )
			break;

		$pos += $c;
		$len += $c;
	}
	return $len;
}

function giffile( string &$file, string $fname ) : bool
{
	if ( strlen($file) < (6+7+1) ) // magic + lsd + end
		return false;
	$mgc = substr($file, 0, 6);
	if ( $mgc !== 'GIF87a' && $mgc !== 'GIF89a' )
		return false;
	tool::trace(__FUNCTION__, $fname);
	$size = strlen($file);

	$new = $mgc;
	$pos = 6;

	// Logical Screen Descriptor
	$sub = tool::substr($file, 6, 7);
	$new .= $sub;
	$pos += 7;

	// Global Color Table
	$pal = ord($sub[4]);
	if ( $pal & 0x80 )
	{
		// 0=2  1=4  2=8  3=16  4=32  5=64  6=128  7=256
		$len = (1 << (($pal & 7) + 1)) * 3;
		$new .= tool::substr($file, $pos, $len);
		$pos += $len;
	}

	while ( $pos < $size )
	{
		switch ( $file[$pos] )
		{
			case "\x21":  // // Extension Marker
				// Graphic Control Extension
				if ( $file[$pos+1] === "\xf9" )
				{
					$new .= "\x21\xf9";
					$pos += 2;

					$len = gif_sublen($file, $pos);
					$new .= tool::substr($file, $pos, $len);
					$pos += $len;
				}
				else // SKIP
				{
					$pos += 2;

					$len = gif_sublen($file, $pos);
					$pos += $len;
				}
				break;

			case "\x2c":  // Image Descriptor Block
				$sub = tool::substr($file. $pos, 10);
				$new .= $sub;
				$pos += 10;

				// Local Color Table
				$pal = ord($sub[9]);
				if ( $pal & 0x80 )
				{
					// 0=2  1=4  2=8  3=16  4=32  5=64  6=128  7=256
					$len = (1 << (($pal & 7) + 1)) * 3;
					$new .= tool::substr($file, $pos, $len);
					$pos += $len;
				}

				// lzw size
				$new .= $file[$pos];
				$pos++;

				$len = gif_sublen($file, $pos);
				$new .= tool::substr($file, $pos, $len);
				$pos += $len;
				break;

			case "\x3b":  // File Terminator Block
				$new .= $file[$pos];
				$pos++;
				break 2;

			default:
				$pos++;
				break;
		} // switch ( $file[$pos] )
	} // while ( $pos < $size )

	if_save_file($new, $fname, $size);
	return true;
}

function jpegfile( string &$file, string $fname ) : bool
{
	if ( strlen($file) < 4 ) // ffd8  ffda
		return false;
	if ( substr($file,0,2) !== "\xff\xd8" )
		return false;
	tool::trace(__FUNCTION__, $fname);
	$size = strlen($file);

	$new = "\xff\xd8";
	$pos = 2;
	while ( $pos < $size )
	{
		if ( $file[$pos+0] !== "\xff" )
			return false;

		switch ( $file[$pos+1] )
		{
			case "\xd8":  case "\xda":
				$len  = 2;
				$new .= substr($file, $pos, 2);
				break;

			case "\xc0":  case "\xc1":  case "\xc2":  case "\xc3":
			case "\xc4":  case "\xc5":  case "\xc6":  case "\xc7":
			case "\xc8":  case "\xc9":  case "\xca":  case "\xcb":
			case "\xd9":  case "\xdb":
				$len  = ieee754::ordstr($file, $pos+2, 2);
				$new .= tool::substr($file, $pos, $len);
				break;
		} // switch ( $file[$pos+1] )

		$pos += $len;
	} // while ( $pos < $size )

	if_save_file($new, $fname, $size);
	return true;
}

function webpfile( string &$file, string $fname ) : bool
{
	if ( strlen($file) < (12+8) ) // RIFF + WEBP
		return false;
	if ( substr($file,0,4) !== 'RIFF' )
		return false;
	if ( substr($file,0,8) !== 'WEBP' )
		return false;
	tool::trace(__FUNCTION__, $fname);
	$size = strlen($file);

	$new = 'RIFF    WEBP';
	$pos = 12;
	while ( $pos < $size )
	{
		$mgc = substr($file, $pos+0, 4);
		$len = tool::ordstr($file, $pos+0, 4);
			$len += ($len & 1) + 8;

		switch ( $mgc )
		{
			case 'VP8 ':  case 'VP8L':
			case 'ALPH':  case 'ANIM':  case 'ANMF':
				$new .= tool::substr($file, $pos, $len);
				break;
			case 'VP8X':
				$sub = tool::substr($file, $pos, $len);

				// clear "Has Metadata" bit
				$c = ord($sub[8]);
				$c &= ~8;
				$sub[8] = chr($c);

				$new .= $sub;
				break;
		} // switch ( $mgc )

		$pos += $len;
	} // while ( $pos < $size )

	$len = ord::chr(strlen($new)-8, 4);
	tool::str_update($new, 4, $len);

	if_save_file($new, $fname, $size);
	return true;
}
//////////////////////////////
function imagefile( string $fname ) : void
{
	$file = file_get_contents($fname);
	$r = pngfile($file, $fname);
	if ( $r )  return;

	$r = giffile($file, $fname);
	if ( $r )  return;

	$r = jpegfile($file, $fname);
	if ( $r )  return;

	$r = webpfile($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'imagefile');
/*
chunk name
	-  uppercase     lowercase
	1  is critical / optional
	2  is public   / private
	3  *reserved*  / *invalid*
	4  is unsafe   / safe to copy by editor

PNG 1              png  IHDR -IDAT  IEND
PNG 2                        | png  IHDR -IDAT  IEND
PNG 3                        |           | png  IHDR -IDAT  IEND
                             |           |           |
APNG   png  IHDR  acTL  fcTL -IDAT  fcTL -fdAT  fcTL -fdAT  IEND

*/
