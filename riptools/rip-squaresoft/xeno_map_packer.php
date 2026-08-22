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
tool::require('xenogears');

function xeno_map_unpack( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$fsz = strlen($file);
	$dir = str_replace('.', '_', $fname);
	$ps  = tool::ordstr($file, 0x130, 4);

	$sub = tool::substr($file, 0, $ps);
	tool::save("$dir/head.bin", $sub);

	$list = [];
	for ( $i=0; $i < 9; $i++ )
	{
		$p = 0x130 + ($i * 4);
		$ps = tool::ordstr($file, $p + 0, 4);
		$nx = tool::ordstr($file, $p + 4, 4);
		if ( $nx === 0 )
			$nx = strlen($file);
		$sz = $nx - $ps;

		printf("unpack  %x  %6x  %6x\n", $i, $ps, $sz);
		$sub = tool::substr($file, $ps, $sz);
		xeno_decode($sub);
		$list[$i] = $sub;
	} // for ( $i=0; $i < 9; $i++ )

	foreach ( $list as $k => $v )
		tool::save("$dir/$k.dec", $v);
}
//////////////////////////////
function xeno_map_pack( string $dir ) : void
{
	$dir = rtrim($dir, '/\\');
	$map = file_get_contents("$dir/head.bin");
	if ( ! $map )  return;

	for ( $i=0; $i < 9; $i++ )
	{
		$dat = @file_get_contents("$dir/$i.dec");

		$p = 0x10c + ($i * 4);
		$sz = strlen($dat);
		tool::str_update($map, $p, chrint($sz,4));

		$p = 0x130 + ($i * 4);
		$ps = strlen($map);
		tool::str_update($map, $p, chrint($ps,4));

		printf("pack  %x  %6x  %6x\n", $i, $ps, $sz);
		xeno_encode($dat);
		$map .= $dat;
	} // for ( $i=0; $i < 9; $i++ )

	tool::save("$dir.bin", $map);
}
//////////////////////////////
function xeno( string $ent ) : void
{
	if ( is_file($ent) )
		xeno_map_unpack($ent);
	if ( is_dir($ent) )
		xeno_map_pack  ($ent);
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
