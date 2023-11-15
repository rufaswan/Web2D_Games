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
require 'xeno.inc';

function xeno_map_unpack( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$fsz = strlen($file);
	$dir = str_replace('.', '_', $fname);
	$ps  = str2int($file, 0x130, 4);

	$sub = substr($file, 0, $ps);
	save_file("$dir/head.bin", $sub);

	for ( $i=0; $i < 9; $i++ )
	{
		$p = 0x130 + ($i * 4);
		$ps = str2int($file, $p + 0, 4);
		$nx = str2int($file, $p + 4, 4);
		if ( $nx === 0 )
			$nx = strlen($file);
		$sz = $nx - $ps;

		printf("unpack  %x  %6x  %6x\n", $i, $ps, $sz);
		$sub = substr($file, $ps, $sz);
		xeno_decode($sub);
		save_file("$dir/$i.dec", $sub);
	} // for ( $i=0; $i < 9; $i++ )
	return;
}

function xeno_map_pack( $dir )
{
	$dir = rtrim($dir, '/\\');
	$map = file_get_contents("$dir/head.bin");
	if ( ! $map )  return;

	for ( $i=0; $i < 9; $i++ )
	{
		$dat = @file_get_contents("$dir/$i.dec");

		$p = 0x10c + ($i * 4);
		$sz = strlen($dat);
		str_update($map, $p, chrint($sz,4));

		$p = 0x130 + ($i * 4);
		$ps = strlen($map);
		str_update($map, $p, chrint($ps,4));

		printf("pack  %x  %6x  %6x\n", $i, $ps, $sz);
		xeno_encode($dat);
		$map .= $dat;
	} // for ( $i=0; $i < 9; $i++ )

	save_file("$dir.bin", $map);
	return;
}

function xeno( $ent )
{
	if ( is_file($ent) )
		return xeno_map_unpack($ent);
	if ( is_dir($ent) )
		return xeno_map_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
