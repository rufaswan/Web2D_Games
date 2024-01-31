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
require 'common-zlib.inc';

function unFCHN($dir, &$sub)
{
	if ( substr($sub, 0, 4) !== 'FCHN' )
		return save_file("$dir.bin", $sub);

	$hds = str2int($sub, 4, 2);
	$hdn = str2int($sub, 6, 2);
	for ( $i=0; $i < $hdn; $i++ )
	{
		$p = 8 + ($i * 8);
		$siz = str2int($sub, $p+0, 4);
		$off = str2int($sub, $p+4, 4);

		$fn = sprintf('%s/%04d.bin', $dir, $i);
		$s = substr($sub, $hds+$off, $siz);
		save_file($fn, $s);
	}
	return;
}

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FPAC' )
		return;

	$dir = str_replace('.', '_', $fname);
	$hdz = str2int($file, 4, 4);

	$pos = 8;
	$id  = 0;
	while (1)
	{
		$siz = str2int($file, $pos+0, 4);
		$zip = str2int($file, $pos+4, 2);
		$lba = str2int($file, $pos+6, 2);
			$pos += 8;
		if ( ($siz|$lba) == 0 )
			break;

		$fn = sprintf('%s/%04d', $dir, $id);
			$id++;
		$lba += $hdz;
		printf("%4x , %8x , %8x , %s\n", $lba, $lba*0x800, $siz, $fn);

		$sub = substr($file, $lba*0x800, $siz);
		if ( $zip )
		{
			$str = substr0($sub, 10);
			$fn  = "$dir/$str";
			$sub = zlib_decode($sub);
		}
		unFCHN($fn, $sub);
	} // while (1)
	return;
}

argv_loopfile($argv, 'lunar');
