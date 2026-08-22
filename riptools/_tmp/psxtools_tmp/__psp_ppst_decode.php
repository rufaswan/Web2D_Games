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
 *
 * Special Thanks
 *   PPSSPP
 *   ppsspp-1.16.6/Common/Serialize/Serializer.h
 */
require 'common.inc';

function snappy_decode( &$file )
{
	$dec = '';

	$file = $dec;
	return;
}

function zstd_decode( &$file )
{
	$dec = '';

	$file = $dec;
	return;
}

function ppst( $fname )
{
	// for *.ppst only
	if ( stripos($fname, '.ppst') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// ppsspp-1.16.6/Common/Serialize/Serializer.h
	// struct ShunkHeader
	$shead = [
		// v1.1.1+ = 4
		// v1.2.2+ = 5
		'revision' => str2int($file,  0, 4),

		// 0 = none , 1 = snappy , 2 = zstd
		// v1.1.1+  = 0,1
		// v1.12.3+ = 0,1,2
		'cimpress' => str2int($file,  4, 4),

		'size_enc' => str2int($file,  8, 4),
		'size_dec' => str2int($file, 12, 4),
		'version'  => rtrim( substr($file,16,0x20), ZERO ),
		'bin'      => substr($file, 0, 0x30),
	];

	$len   = strlen($file);
	$pos   = 0x30;

	// revision 5+
	$title = 'PSP_HOMEBREW';
	if ( $shead['revision'] < 4 )
		return;
	if ( $shead['revision'] >= 5 )
	{
		$title = rtrim( substr($file,$pos,0x80), ZERO );
		$pos += 0x80;
	}

	// compression
	if ( ($pos + $shead['size_enc']) !== $len )
		return;
	$file = substr($file, $pos);
	switch ( $shead['compress'] )
	{
		case 0:
			return;
		case 1:
			snappy_decode($file);
			file_put_contents("$fname.dec", $file);
			break;
		case 2:
			zstd_decode($file);
			file_put_contents("$fname.dec", $file);
			break;
		default:
			return;
	} // switch ( $shead['compress'] )
	return;
}

argv_loopfile($argv, 'ppst');
