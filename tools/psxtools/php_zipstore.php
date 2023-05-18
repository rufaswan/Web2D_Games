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

$gp_unpack = true;

function save_zipfile( $zipn, $list, $skip )
{
	$fp = fopen($zipn, 'wb');
	if ( ! $fp )  return;

	$cent = '';
	$pos  = 0;
	$cnt  = 0;
	foreach ( $list as $k => $v )
	{
		list($fsize,$fname) = $v;

		$fnm = substr($fname, $skip);
		$fdt = file_get_contents($fname);
		$crc = crc32($fdt);
		//$crc = 0;

		$pk3  = "PK\x03\x04";            // 00  magic
		$pk3 .= chrint(10, 2);           // 04  ver 1.0
		$pk3 .= ZERO . ZERO;             // 06  flags
		$pk3 .= ZERO . ZERO;             // 08  compression
		$pk3 .= ZERO . ZERO;             // 0a  mod time
		$pk3 .= ZERO . ZERO;             // 0c  mod date
		$pk3 .= chrint($crc, 4);         // 0e  crc 32
		$pk3 .= chrint($fsize, 4);       // 12  fsize compressed
		$pk3 .= chrint($fsize, 4);       // 16  fsize uncompressed
		$pk3 .= chrint(strlen($fnm), 2); // 1a  fname len
		$pk3 .= ZERO . ZERO;             // 1c  extra field size
		$pk3 .= $fnm;
		$pk3 .= $fdt;
			fwrite($fp, $pk3);

		$pk1  = "PK\x01\x02";              // 00  magic
		$pk1 .= chr(10);                   // 04  made by ver 1.0
		$pk1 .= ZERO;                      // 05  made by host (msdos)
		$pk1 .= chrint(10, 2);             // 06  ver 1.0
		$pk1 .= ZERO . ZERO;               // 08  flags
		$pk1 .= ZERO . ZERO;               // 0a  compression
		$pk1 .= ZERO . ZERO;               // 0c  mod time
		$pk1 .= ZERO . ZERO;               // 0e  mod date
		$pk1 .= chrint($crc, 4);           // 10  crc 32
		$pk1 .= chrint($fsize, 4);         // 14  fsize compressed
		$pk1 .= chrint($fsize, 4);         // 18  fsize uncompressed
		$pk1 .= chrint(strlen($fnm), 2);   // 1c  fname len
		$pk1 .= ZERO . ZERO;               // 1e  extra field size
		$pk1 .= ZERO . ZERO;               // 20  comment len
		$pk1 .= ZERO . ZERO;               // 22  disk .z01 .z02 ...
		$pk1 .= ZERO . ZERO;               // 24  internal attr
		$pk1 .= ZERO . ZERO . ZERO . ZERO; // 26  external attr (made by host)
		$pk1 .= chrint($pos, 4);           // 2a  PK34 offset
		$pk1 .= $fnm;
			$cent .= $pk1;

		printf("%8x , %8x , %s @ %s\n", $pos, $fsize, $zipn, $fnm);
		$pos += strlen($pk3);
		$cnt++;
	} // foreach ( $list as $fsize => $flist )

	fwrite($fp, $cent);
	$len = strlen($cent);

	$pk5  = "PK\x05\x06";    // 00  magic
	$pk5 .= ZERO . ZERO;     // 04  count .z01 .z02...
	$pk5 .= ZERO . ZERO;     // 06  disk w/central dir
	$pk5 .= chrint($cnt, 2); // 08  disk  entry
	$pk5 .= chrint($cnt, 2); // 0a  total entry
	$pk5 .= chrint($len, 4); // 0c  central dir len
	$pk5 .= chrint($pos, 4); // 10  central dir offset
	$pk5 .= ZERO . ZERO;     // 14  comment len

	fwrite($fp, $pk5);
	fclose($fp);
	return;
}

function zip_pack( $dir )
{
	printf("== zip_pack( %s )\n", $dir);
	$dir = str_replace('\\', '/', $dir);
		$dir = rtrim ($dir, '/');
	$len = strlen($dir);

	// create DIRNAME.zip on different dir
	$p = strrpos($dir, '/');
	if ( $p === false )
		$base = $dir;
	else
		$base = substr($dir, $p + 1);

	$list = lsfile_bysize_r($dir);
	if ( empty($list) )
		return;

	if ( count($list) < 0xfff0 )
	{
		$fn = sprintf('%s.zip', $base);
		save_zipfile($fn, $list, $len+1);
	}
	else
	{
		$id = 0;
		while ( ! empty($list) )
		{
			$part = array_splice($list, 0, 0xfff0);
			$fn = sprintf('%s.%02d.zip', $base, $id);
				$id++;
			save_zipfile($fn, $part, $len+1);
		} // while ( ! empty($list) )
	}
	return;
}
//////////////////////////////
function zip_unpack( $fname )
{
	$dir = str_replace('.', '_', $fname);
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	global $gp_unpack;
	$pos = 0;
	while (1)
	{
		$pk3 = fp2str($fp, $pos, 0x1e);
			$pos += 0x1e;
		if ( substr($pk3,0,4) !== "PK\x03\x04" )
			return;
		if ( substr($pk3,8,2) !== "\x00\x00" )
			return;

		$crc = str2int($pk3, 0x0e, 4);
		$fsz = str2int($pk3, 0x16, 4);
		$fln = str2int($pk3, 0x1a, 2);
		$fnm = fp2str($fp, $pos, $fln);
			$pos += $fln;

		$fn = "$dir/$fnm";
		printf("%8x  %8x  %s\n", $pos, $fsz, $fn);

		if ( $gp_unpack )
		{
			$fdt = fp2str($fp, $pos, $fsz);
			if ( crc32($fdt) !== $crc )
				php_warning('%s crc32 [%8x] not matched [%8x]', $fn, crc32($fdt), $crc);
			save_file($fn, $fdt);
		}
		$pos += $fsz;
	} // while (1)
	return;
}
//////////////////////////////
function zipstore( $ent )
{
	if ( is_file($ent) )
		return zip_unpack($ent);
	if ( is_dir($ent) )
		return zip_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	zipstore( $argv[$i] );
