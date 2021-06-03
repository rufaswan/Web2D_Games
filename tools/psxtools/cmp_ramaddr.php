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
define("PARTSIZE", 0x20);
$gp_file = array();

function partcmp( $pos )
{
	global $gp_file;
	if ( empty($gp_file) )
		return;

	$cnt = count($gp_file);
	$diff = false;
	for ( $c1=0; $c1 < ($cnt-1); $c1++ )
	{
		for ( $c2=$c1+1; $c2 < $cnt; $c2++ )
		{
			$b1 = substr($gp_file[$c1][1], $pos, PARTSIZE);
			$b2 = substr($gp_file[$c2][1], $pos, PARTSIZE);
			if ( $b1 != $b2 )
				$diff = printf("DIFF %x @ %s != %s\n", $pos, $gp_file[$c1][0], $gp_file[$c2][0]);
		}
	}
	if ( ! $diff )
		printf("ALL SAME %x\n", $pos);
	return;
}

for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( file_exists($opt) )
		$gp_file[] = array($opt, file_get_contents($opt));
	else
	{
		$pos = hexdec($opt);
		partcmp( $pos );
	}
}
