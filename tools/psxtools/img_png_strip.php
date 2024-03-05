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
require 'common-zlib.inc';

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
			case 'fdAT':
			case 'IDAT':
				$dat = zlib_decode( $png[$i]['data'] );
				$png[$i]['data'] = zlib_encode($dat, ZLIB_ENCODING_DEFLATE, $level);
				break;
		} // switch ( $png[$i]['name'] )
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

// to strip off any optional PNG chunks (tIME,gAMA,iCCP,etc)
function png_strip( &$png )
{
	$cnt = count($png);
	while ( $cnt > 0 )
	{
		$cnt--;
		switch ( $png[$cnt]['name'] )
		{
			case 'IHDR':  case 'IDAT':  case 'IEND':
			case 'acTL':  case 'fcTL':  case 'fdAT':
			case 'PLTE':  case 'tRNS':
				break;
			default:
				printf("REMOVED %x %s\n", $cnt, $png[$cnt]['name']);
				array_splice($png, $cnt, 1);
		} // switch ( $png[$cnt]['name'] )
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
		$ent = array(
			'name' => substr ($file, $st + 4, 4) ,
			'data' => substr ($file, $st + 8, $len) ,
		);
		//$crc = str2big($file, $st + 8 + $len, 4);
		printf("%8x  %8x  %s\n", $st, strlen($ent['data']), $ent['name']);
			$st += (8 + $len + 4);

		switch ( $ent['name'] )
		{
			case 'fdAT':
				$seq = str2big($ent['data'], 0, 4);
				printf("APNG  %s  %x\n", $ent['name'], $seq);
				$ent['data'] = substr($ent['data'], 4);
			case 'IDAT':
				if ( $chunk[0]['name'] === $ent['name'] )
					$chunk[0]['data'] .= $ent['data'];
				else
					array_unshift($chunk, $ent);
				break;
			case 'fcTL':
				$seq = str2big($ent['data'], 0, 4);
				printf("APNG  %s  %x\n", $ent['name'], $seq);
				$ent['data'] = substr($ent['data'], 4);
			default:
				array_unshift($chunk, $ent);
				break;
		} // switch ( $ent['name'] )
	} // while ( $st < $ed )

	return $chunk;
}

function save_png( $fname, &$png )
{
	$file = PNG_MAGIC;
	$cnt  = count($png);
	$apng = 0;
	while ( $cnt > 0 )
	{
		$cnt--;
		$chunk = $png[$cnt];

		switch ( $chunk['name'] )
		{
			case 'fcTL':
			case 'fdAT':
				$chunk['data'] = chrbig($apng,4) . $png[$cnt]['data'];
					$apng++;
				break;
		} // switch ( $chunk['name'] )

		$len = strlen($chunk['data']);
		$crc = crc32( $chunk['name'] . $chunk['data'] );

		$file .= chrbig($len, 4);
		$file .= $chunk['name'];
		$file .= $chunk['data'];
		$file .= chrbig($crc, 4);
	} // while ( $cnt > 0 )

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
