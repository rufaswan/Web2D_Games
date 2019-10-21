<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
require "aini_tags.inc";

$gp_pc = array();
////////////////////////////////////////
function aini_dec( &$file, $fname )
{
	$ed = strlen($file);
	$st = 4;
	while ( $st < $ed )
	{
		$b1 = ord( $file[$st] );
		$p1 = $b1 >> 6;
		$p2 = $b1 << 2;
		$b1 = ($p1 | $p2) & BIT8;
		$file[$st] = chr($b1);
		$st++;
	}
	file_put_contents("$fname.dec", $file);
}

function aini( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc != "AINI" )
		return;

	$dir = str_replace('.', '_', $fname);
	@mkdir("$dir", 0755, true);

	aini_dec( $file, $dir );

	$st = 8;
	while ( $st < $ed )
	{
		$bak = $st;
		ainitags( $file, $st, $dir );
		if ( $bak == $st )
			return;
	} // while ( $st < $ed )
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	aini( $argv[$i] );
