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
require("ain2_tags.inc");
require("ain2_code.inc");
//////////////////////////////
define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT32", 0xffffffff);

function substr0( &$str, &$pos )
{
	$s = "";
	while ( $str[$pos] != ZERO )
	{
		if ( $str[$pos] == "\n" )
			$s .= '\n';
		else
			$s .= $str[$pos];
		$pos++;
	}
	$pos++;
	return $s;
}
function str2int( &$str, &$pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	$pos += $byte;
	return $int;
}
function sint32( &$file, &$st )
{
	$n = str2int($file, $st, 4);
	if ( $n >> 31 )
		return ($n - BIT32 - 1);
	else
		return $n;
}
//////////////////////////////
$gp_pc = array();
$gp_datatype = array(
	0x00 => "void",

	0x0a => "int",    0x0e => "array@int",    0x12 => "&int",    0x16 => "&array@int",
	0x0b => "float",  0x0f => "array@float",  0x13 => "&float",  0x17 => "&array@float",
	0x0c => "string", 0x10 => "array@string", 0x14 => "&string", 0x18 => "&array@string",
	0x0d => "struct", 0x11 => "array@struct", 0x15 => "&struct", 0x19 => "&array@struct",

	0x1a => "main",

	0x1b => "func", 0x1e => "array@func", 0x1f => "&func", 0x20 => "&array@func",
	0x2f => "bool", 0x32 => "array@bool", 0x33 => "&bool", 0x34 => "&array@bool",
);

function get_datatype( &$file, &$st )
{
	global $gp_datatype;
	$d = sint32($file, $st);
	if ( isset( $gp_datatype[$d] ) )
		return $gp_datatype[$d];
	else
		return $d;
}

function savetags( $dir )
{
}

function ain2( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;
		printf("[$rem] $fname\n");

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$bak = $st;
		ain2tags( $file, $st, $dir );
		if ( $bak == $st )
			return;
	} // while ( $st < $ed )

	savetags( $dir );
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain2( $argc-$i, $argv[$i] );
