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

function tim2clut( &$file, $pos, $out )
{
	$timh_sz = str2int($file, $pos + 8, 4);
	if ( $timh_sz != 0x20c )
		return 0;

	$sz = str2int($file, $pos + 8 + $timh_sz, 4);
	$w = str2int($file, $pos + 8 + $timh_sz + 8, 2);
	$h = str2int($file, $pos + 8 + $timh_sz + 10, 2);

	$data = "CLUT";
	$data .= chrint(0x100, 4); // no clut
	$data .= chrint($w*2, 4); // width
	$data .= chrint($h  , 4); // height
	$data .= strpal555($file, $pos + 0x14, 0x100);
	$data .= substr($file, $pos + 8 + $timh_sz + 12, $sz - 12);
	save_file($out, $data);
	return (8 + $timh_sz + $sz);
}

function saga2( $fname )
{
	// only MDL???.MDL files
	if ( ! preg_match("|MDL[0-9]+\.MDL|i", $fname) )
		return;

	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$n = 1;
	$st = str2int($file, 0x28, 4);
	$ed = strlen($file);
	while ( $st < $ed )
	{
		$out = sprintf("$dir/%04d.clut", $n);
		$sz = tim2clut( $file, $st, $out );
		if ( $sz == 0 )
			return;

		$st += $sz;
		$n++;
	}

	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
