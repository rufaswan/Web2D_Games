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
require "common.inc";
require "xeno.inc";

function xeno( $fname, $dec )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$cnt = str2int($file, 0, 4);
	$len = strlen($file);

	$p = 4 + ($cnt * 4);
	if ( str2int($file,$p,4) > $len )
		return;

	$dir = str_replace('.', '_', $fname);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$of1 = str2int($file, $p+0, 4);
		$of2 = str2int($file, $p+4, 4);
		printf("%2d : %x - %x\n", $i, $of1, $of2);

		$sub = substr($file, $of1, $of2-$of1);
		if ( $dec )
			xeno_decode($sub);

		$fn = sprintf("%s/%04d.bin", $dir, $i);
		save_file($fn, $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

$dec = true;
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i]  )
	{
		case '+d':  $dec =  true; break;
		case '-d':  $dec = false; break;
		default:
			xeno($argv[$i], $dec);
			break;
	}
}
