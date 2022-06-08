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
 *
 * Special Thanks
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */
require 'common.inc';

function str2hex( &$str )
{
	$len = strlen($str);
	$hex = '';
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $str[$i] === ZERO )
			$hex .= '-- ';
		else
			$hex .= sprintf('%02x ', ord($str[$i]));
	} // for ( $i=0; $i < $len; $i++ )
	return $hex;
}

function strsub( &$file, &$st, $len )
{
	$sub = substr($file, $st, $len);
		$st += $len;
	return $sub;
}

function shex( $int )
{
	if ( $int < 0 )
		$s = sprintf('-%x', -$int);
	else
		$s = sprintf('%x' ,  $int);
	return $s;
}

function parse_jntfile( &$file )
{
	$dx = str2int($file, 0x22, 2, true);
	$dy = str2int($file, 0x24, 2, true);
	printf("XY = %s , %s\n", shex($dx), shex($dy));
	echo "\n";

	$cjnt = ord( $file[0x26] );
	$cjnt_inv = ord( $file[0x27] );
	$cjnt_vis = ord( $file[0x28] );
	$chit = ord( $file[0x29] );
	$cpss = ord( $file[0x2a] );
	$cpnt = ord( $file[0x2b] );
	$canm = ord( $file[0x2c] );

	$pos = 0x30;
	$inv = array();

	// joints
	printf("Joints = %x [inv] + %x [vis] = %x\n", $cjnt_inv, $cjnt_vis, $cjnt);
	for ( $i=0; $i < $cjnt; $i++ )
	{
		$s = strsub($file, $pos, 4);
		if ( $s[1] === BYTE )
		{
			printf("  %2x  %s [INV]\n", $i, str2hex($s));
			$inv[$i] = 1;
		}
		else
			printf("  %2x  %s\n", $i, str2hex($s));
	} // for ( $i=0; $i < $cjnt; $i++ )
	echo "\n";

	// Poses
	printf("Poses = %x\n", $cpss);
	for ( $i=0; $i < $cpss; $i++ )
	{
		$s = strsub($file, $pos, 2);
		printf("  %02x  %s\n", $i, str2hex($s));

		for ( $j=0; $j < $cjnt; $j++ )
		{
			$s = strsub($file, $pos, 4);
			if ( isset($inv[$j]) )
				printf("    %02x-%02x  %s [INV]\n", $i, $j, str2hex($s));
			else
				printf("    %02x-%02x  %s\n", $i, $j, str2hex($s));
		} // for ( $j=0; $j < $cjnt; $j++ )
	} // for ( $i=0; $i < $cpss; $i++ )
	echo "\n";

	// Hitboxes
	printf("Hitboxes = %x\n", $chit);
	for ( $i=0; $i < $chit; $i++ )
	{
		$s = strsub($file, $pos, 8);
		printf("  %02x  %s\n", $i, str2hex($s));
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";

	// Points
	printf("Points = %x\n", $cpnt);
	for ( $i=0; $i < $cpnt; $i++ )
	{
		$s = strsub($file, $pos, 4);
		printf("  %02x  %s\n", $i, str2hex($s));
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";

	// Draw
	$s = strsub($file, $pos, $cjnt_vis);
	printf("Draw = %s\n", str2hex($s));
	echo "\n";

	// Anims
	printf("Anims = %x\n", $canm);
	for ( $i=0; $i < $canm; $i++ )
	{
		$cnt = ord( $file[$pos] );
			$pos++;
		printf("  %02x  %02x\n", $i, $cnt);

		for ( $j=0; $j < $cnt; $j++ )
		{
			$s = strsub($file, $pos, 3);
			printf("    %02x-%02x  %s\n", $i, $j, str2hex($s));
		} // for ( $j=0; $j < $cnt; $j++ )
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";

	return;
}

function jntfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 2) !== "\x01\x23" )
		return;

	$opd = substr0($file, 3);
	if ( strpos($opd, '.opd') === false )
		return;

	ob_start();
	parse_jntfile($file);
	$txt = ob_get_clean();

	save_file("$opd-$fname.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jntfile( $argv[$i] );
