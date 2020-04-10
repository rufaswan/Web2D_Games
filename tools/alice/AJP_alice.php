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

// ANIMATED JPEG
//   PLAYED LIKE MOVIE , BUT WITH ALPHA
//   FOR SPELL EFFECTS
$gp_key = array(
	0x5d,0x91,0xae,0x87,
	0x4a,0x56,0x41,0xcd,
	0x83,0xec,0x4c,0x92,
	0xb5,0xcb,0x16,0x34,
);
////////////////////////////////////////
function ajp2jpg( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
	if ( $mgc != "AJP" )
		return;

	$pw = str2int($file, 0x0c, 4);
	$ph = str2int($file, 0x10, 4);
	$jp = str2int($file, 0x14, 4);
	$jz = str2int($file, 0x18, 4);
	$ap = str2int($file, 0x1c, 4);
	$az = str2int($file, 0x20, 4);

	printf("AJP , %8x , %8x , %8x , %8x , $fname\n", $pw, $ph, $jz, $az);

	global $gp_key;
	for ( $i=0; $i < 16; $i++ )
	{
		$c = ord( $file[$jp+$i] );
		$k = $gp_key[ $i % 16 ];
		$c ^= $k;
		$file[$jp+$i] = chr($c);
	}
	file_put_contents("$fname.1.jpg", substr($file, $jp, $jz) );

	for ( $i=0; $i < 16; $i++ )
	{
		$c = ord( $file[$ap+$i] );
		$k = $gp_key[ $i % 16 ];
		$c ^= $k;
		$file[$ap+$i] = chr($c);
	}
	file_put_contents("$fname.2.jpg", substr($file, $ap, $az) );
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ajp2jpg( $argc-$i, $argv[$i] );
