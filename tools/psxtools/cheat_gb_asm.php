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
require 'cheat_gb_asm.inc';
define('NOP', "\x00");

function nopfile( &$file, $off, $len )
{
	for ( $i=0; $i < $len; $i++ )
		$file[$off+$i] = NOP;
	return;
}

function gbcartsum( &$file )
{
	$len = strlen($file);
	$sum = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $i === 0x14e || $i === 0x14f )
			continue;
		$b = ord( $file[$i] );
		$sum = ($sum + $b) & BIT16;
	} // for ( $i=0; $i < $len; $i++ )

	$b1 = ($sum >> 8) & BIT8;
	$b2 = ($sum >> 0) & BIT8;
	$file[0x14e] = chr($b1);
	$file[0x14f] = chr($b2);
	return;
}

function gbfile( $fname )
{
	if ( is_file("$fname.bak") )
	{
		$bak  = file_get_contents("$fname.bak");
		$file = $bak;
	}
	else
	{
		$file = file_get_contents($fname);
		$bak  = $file;
	}
	if ( empty($file) || empty($bak) )
		return;

	$mgc = substr($file, 0x134, 0x10);
		$mgc = preg_replace('|[^0-9a-zA-Z ]|', ' ', $mgc);
	$sum = substr($file, 0x14d, 3);

	global $gp_data;
	foreach ( $gp_data as $func => $v )
	{
		if ( $v[0] === $mgc && $v[1] === $sum )
		{
			printf("detect [%s] = %s\n", $func, $fname);
			$func($file);
		}
	} // foreach ( $gp_data as $func => $ver )

	if ( $file === $bak )
		return;

	if ( ! is_file("$fname.bak") )
		file_put_contents("$fname.bak", $bak);

	gbcartsum($file);
	file_put_contents($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gbfile( $argv[$i] );
