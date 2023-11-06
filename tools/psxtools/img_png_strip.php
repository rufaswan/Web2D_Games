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
require 'common.inc';
require 'common-guest.inc';

define('PNG_MAGIC', "\x89PNG\x0d\x0a\x1a\x0a");

function png_recode( $level, &$png )
{
	if ( $level < 0 )  return;
	if ( $level > 9 )  return;
	if ( ! function_exists('zlib_decode') )
		return;

	$cnt = count($png);
	for ( $i=0; $i < $cnt; $i++ )
	{
		switch ( $png[$i]['name'] )
		{
			case 'IDAT':
				$dat = zlib_decode( $png[$i]['data'] );
				$png[$i]['data'] = zlib_encode($dat, ZLIB_ENCODING_DEFLATE, $level);
				break;
			case 'fdAT':
				$seq = substr($png[$i]['data'], 0, 4);
				$bin = substr($png[$i]['data'], 4);

				$dat = zlib_decode($bin);
				$png[$i]['data'] = $seq . zlib_encode($dat, ZLIB_ENCODING_DEFLATE, $level);
				break;
		} // switch ( $png[$i]['name'] )
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

// to strip off any optional PNG chunks (tIME,gAMA,iCCP,etc)
function png_strip( &$png )
{
	$valid = array(
		'IHDR','IDAT','IEND',
		'acTL','fcTL','fdAT',
		'PLTE','tRNS',
	);
	$cnt = count($png);
	while ( $cnt > 0 )
	{
		$cnt--;
		if ( array_search($png[$cnt]['name'], $valid) === false )
			array_splice($png, $cnt, 1);
	}
	return;
}
//////////////////////////////
function load_png( &$file )
{
	$chunk = array();
	$ed = strlen($file);
	$st = 8;
	while ( $st < $ed )
	{
		$len = str2big($file, $st + 0, 4);
		$nam = substr ($file, $st + 4, 4);
		$dat = substr ($file, $st + 8, $len);
		//$crc = str2big($file, $st + 8 + $len, 4);
		printf("%8x  %8x  %s\n", $st, strlen($dat), $nam);

		$chunk[] = array('name'=>$nam , 'data'=>$dat);
		$st += (8 + $len + 4);
	} // while ( $st < $ed )

	$cnt = count($chunk);
	while ( $cnt > 1 )
	{
		$cnt--;
		if ( $chunk[$cnt]['name'] === $chunk[$cnt-1]['name'] )
		{
			$chunk[$cnt-1]['data'] .= $chunk[$cnt]['data'];
			array_splice($chunk, $cnt, 1);
		}
	}
	return $chunk;
}

function save_png( $fname, &$png )
{
	$file = PNG_MAGIC;
	foreach ( $png as $chunk )
	{
		$len = strlen($chunk['data']);
		$crc = crc32( $chunk['name'] . $chunk['data'] );

		$file .= chrbig($len, 4);
		$file .= $chunk['name'];
		$file .= $chunk['data'];
		$file .= chrbig($crc, 4);
	}
	file_put_contents($fname, $file);
	return;
}

function pngfile( $level, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0,8) !== PNG_MAGIC )
		return;

	$png = load_png($file);
	png_recode($level, $png);

	png_strip($png);
	save_png($fname, $png);
	return;
}

$level = -1;
for ( $i=0; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		pngfile( $level, $argv[$i] );
	else
		$level = (int)$argv[$i];
}

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
