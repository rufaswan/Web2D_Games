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
require 'class-sh.inc';
require 'class-zipstore.inc';

function zip_pack( $dir )
{
	printf("== zip_pack( %s )\n", $dir);
	$base = sh::realbase($dir);
	if ( empty($base) )
		return;

	$zip  = new zipstore;
	$list = $zip->scan($dir);
	usort($list, array($zip,'sort_size_asc'));
	$zip->save($base.'.zip', $list, $dir);
	return;
}
//////////////////////////////
function zip_unpack( $fname )
{
/*
	$dir = str_replace('.', '_', $fname);
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

$gp_unpack = true;

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

	if ( is_file($ent) )
		return zip_unpack($ent);
*/
	return;
}
//////////////////////////////
function zipfile( $ent )
{
	if ( is_dir($ent) )
		return zip_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	zipfile( $argv[$i] );
