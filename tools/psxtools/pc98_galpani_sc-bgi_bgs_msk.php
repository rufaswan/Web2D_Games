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
require "pc98_galpani.inc";

//define("NO_TRACE", true);

function sectsc( &$file, $fname )
{
	echo "== sectsc( $fname )\n";
	$w  = str2int($file, 0x14, 2);
	$h  = str2int($file, 0x16, 2);
	$cc = str2int($file, 0x2a, 2);
		$cc += ord( $file[0x28] );
	printf("sc : %d x %d , %d color\n", $w, $h, $cc);

	$b1 = str2int($file, 0xb4, 4); // palette
	$b2 = str2int($file, 0xb8, 4); // data
	$b3 = str2int($file, 0xbc, 4); // size

	$pal = "";
	if ( $b1 == 0 )
	{
		$cc = 0x100;
		$pal = grayclut($cc);
	}
	else
	{
		$by = ord( $file[0x28] );
		$pal = str_repeat(ZERO, $by*4);
		for ( $i=$b1; $i < $b2; $i += 3 )
			$pal .= substr($file, $i, 3) . BYTE;
	}

	$file .= ZERO . ZERO;

	$ty = ord( $file[0xb0] );
	$pix = substr($file, $b2, $b3);
	$pix = galpani_dectype($pix, $ty);
	echo "TYPE : $ty\n";

	$img = array(
		'cc'  => $cc,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	save_clutfile("$fname.clut", $img);
	return;
}

function sectmsk( &$file, $fname )
{
	echo "== sectmsk( $fname )\n";
	$w  = str2int($file, 0x14, 2);
	$h  = str2int($file, 0x16, 2);
	$cc = 2;
	printf("msk : %d x %d , %d color\n", $w, $h, $cc);

	$b2 = str2int($file, 0xb8, 4); // data
	$b3 = str2int($file, 0xbc, 4); // size

	$pal = grayclut($cc);

	global $gp_bits, $gp_sub;
	$file .= ZERO . ZERO;

	$ty = ord( $file[0xb0] );
	$pix = substr($file, $b2, $b3);
	$pix = galpani_dectype($pix, $ty);
	echo "TYPE : $ty\n";

	$len = strlen($pix);
	$dat = "";
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $pix[$i] );
		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$b1 = ($b >> $j) & 1;
			$dat .= chr($b1);
		} // while ( $j > 0 )
	} // for ( $i=0; $i < $len; $i++ )

	$img = array(
		'cc'  => $cc,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $dat,
	);
	save_clutfile("$fname.clut", $img);
	return;
}
//////////////////////////////
function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// scXXXXXX.bgi
	// scXXXXXX.bgs
	// scXXXXXX.msk
	$mgc = substr0($file, 0);
	if ( strpos($file, '.msk') )  return sectmsk($file, $fname);
	if ( strpos($file, '.bgi') )  return sectsc ($file, $fname);
	if ( strpos($file, '.bgs') )  return sectsc ($file, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );
