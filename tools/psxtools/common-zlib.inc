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
// PHP 5.4.0+
php_req_extension('zlib_decode', 'zlib');
//php_req_extension('zlib_encode', 'zlib');

// PHP 7+
//php_req_extension('inflate_init', 'zlib'); // for custom dictionary
//php_req_extension('deflate_init', 'zlib'); // for custom dictionary

function zlib_raw_store( &$data )
{
	// +5 bytes for every 0xffff bytes data
	$zlib = '';
	$len  = strlen($data);
	$pos  = 0;
	while ( $len > BIT16 )
	{
		// 0   = 00 non end block
		// 1 2 = length LE
		// 3 4 = NOT length LE
		$zlib .= ZERO . BYTE . BYTE . ZERO . ZERO;
		$zlib .= substr($data, $pos, BIT16);
			$pos += BIT16;
			$len -= BIT16;
	} // while ( $len > BIT16 )

	// 1   = 01 end block
	// 1 2 = length LE
	// 3 4 = NOT length LE
	$b1a = ($len >> 0) & BIT8;
	$b1b = ($len >> 8) & BIT8;
	$b2a = $b1a ^ BIT8;
	$b2b = $b1b ^ BIT8;
	$zlib .= "\x01" . chr($b1a) . chr($b1b) . chr($b2a) . chr($b2b);
	$zlib .= substr($data, $pos);
	return $zlib;
}

function zlib_gzip_store( &$data )
{
	// +a bytes header
	//    *same as raw*
	// +8 bytes footer
	$zlib  = "\x1f\x8b\x08" . ZERO . ZERO . ZERO . ZERO . ZERO . "\x04\x03"; // magic
	$zlib .= zlib_raw_store($data);

	// crc LE
	$crc = crc32($data);
	for ( $i=0; $i < 4; $i++ )
	{
		$b = ($crc >> ($i*8)) & BIT8;
		$zlib .= chr($b);
	}
	// length LE
	$len = strlen($data);
	for ( $i=0; $i < 4; $i++ )
	{
		$b = ($len >> ($i*8)) & BIT8;
		$zlib .= chr($b);
	}
	return $zlib;
}

function zlib_deflate_store( &$data )
{
	// +2 bytes header
	//    *same as raw*
	// +4 bytes footer
	$zlib  = "\x78\x01"; // magic
	$zlib .= zlib_raw_store($data);

	$len = strlen($data);
	$sum1 = 0;
	$sum2 = 1;
	for ( $i=0; $i < $len; $i++ )
	{
		$b1 = ord( $data[$i] );
		$sum2 += $b1;
		while ( $sum2 >= 0xfff1 )
			$sum2 -= 0xfff1;
		$sum1 += $sum2;
		while ( $sum1 >= 0xfff1 )
			$sum1 -= 0xfff1;
	} // for ( $i=0; $i < $len; $i++ )

	// 0 1 = sum of sum bytes BE
	// 2 3 = sum bytes BE
	$b1a = ($sum1 >> 8) & BIT8;
	$b1b = ($sum1 >> 0) & BIT8;
	$b2a = ($sum2 >> 8) & BIT8;
	$b2b = ($sum2 >> 0) & BIT8;
	$zlib .= chr($b1a) . chr($b1b) . chr($b2a) . chr($b2b);
	return $zlib;
}

// enum
//   ZLIB_ENCODING_RAW     = -15
//   ZLIB_ENCODING_GZIP    =  31
//   ZLIB_ENCODING_DEFLATE =  15
// level = -1 to 9
//   -1 = auto (same as level 6)
//    0 = store
//    9 = best
// zip file methods
//  unzip -v ZIPFILE
//  zipinfo  ZIPFILE
//  0   = Stored
//  1-2 = Defl:F
//  3-7 = Defl:N
//  8-9 = Defl:X
if ( ! function_exists('zlib_encode') )
{
	// $encoding = ZLIB_ENCODING_DEFLATE
	// $level    = 0
	function zlib_encode($data, $encoding, $level=-1)
	{
		return zlib_deflate_store($data);
	}
}
