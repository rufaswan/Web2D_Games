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

function pad800( &$file )
{
	while ( strlen($file) % 0x800 )
		$file .= ZERO;
	return;
}

function arc_pack( $dir )
{
	echo "== arc_pack( $dir )\n";
	$dir = rtrim($dir, '/\\');
	$len = strlen($dir);

	$list = array();
	lsfile_r($dir, $list);
	if ( empty($list) )
		return;

	$cnt = count($list);
	$file = chrint($cnt, 4);
	$file .= str_repeat(ZERO, $cnt * 0x18 );
	pad800($file);
	foreach ( $list as $k => $v )
	{
		$lba = strlen($file) / 0x800;
		$fdt = file_get_contents($v);
		$fsz = strlen($fdt);
		printf("%6x , %8x , %s\n", $lba, $fsz, $v);

		$b1 = substr($v, $len+1, 15);
			$b1 = strtoupper($b1);
		$b2 = chrint($fsz, 4);
		$b3 = chrint($lba, 4);

		$p = 4 + ($k * 0x18);
		str_update($file, $p+   0, $b1);
		str_update($file, $p+0x10, $b2);
		str_update($file, $p+0x14, $b3);

		$file .= $fdt;
		pad800($file);
	}
	save_file("$dir.arc", $file);
	return;
}

function arc_unpack( $fname )
{
	echo "== arc_unpack( $fname )\n";
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 0x18);
		$b1 = substr0($file, $p+0);
			$b1 = strtolower($b1);
		$b2 = str2int($file, $p+0x10, 4);
		$b3 = str2int($file, $p+0x14, 4);
		printf("%6x , %8x , %s\n", $b3, $b2, $b1);

		$fdt = substr($file, $b3*0x800, $b2);
		save_file("$dir/$b1", $fdt);
	}
	return;
}
//////////////////////////////
function tm2s( $ent )
{
	if ( is_file($ent) )
		return arc_unpack($ent);
	if ( is_dir($ent) )
		return arc_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tm2s( $argv[$i] );
