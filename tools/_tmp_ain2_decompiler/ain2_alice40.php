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
require "ain2_tags.inc";
require "ain2_code.inc";
require "funcs-sjis.php";

define("SJIS_HALF", "sjis_half.inc");
define("SJIS_ASC",  "sjis_ascii.inc");
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

function get_text( &$file, &$st )
{
	$sjis = substr0( $file, $st );
	$sjis = sjistxt($sjis);
	$utf8 = iconv("MS932", "UTF-8", $sjis);
	return $utf8;
}

function codefunc( &$code , $dir )
{
	global $gp_pc, $gp_sysfunc;
	foreach( $gp_pc["FUNC"] as $f )
	{
		$buf = "";
		$st = $f["addr"];
		while (1)
		{
			//printf("%x code\n", $st);
			$r = code2inst($code, $st);
			switch ( count($r) )
			{
				case 4:
					switch ( $r[0] )
					{
						case "CALLHLL":
							$s1 = $gp_pc["HLL0"][ $r[2] ]["hll"];
							$s2 = $gp_pc["HLL0"][ $r[2] ][ $r[3] ]['n'];
							$buf .= sprintf("%8x , %s , %s , %s\n", $r[1], $r[0], $s1, $s2);
							break;
						default:
							$buf .= sprintf("%8x , %s , %x , %x\n", $r[1], $r[0], $r[2], $r[3]);
							break;
					}
					break;
				case 3:
					switch ( $r[0] )
					{
						case "FUNC":
						case "ENDFUNC":
						case "CALLFUNC":
						case "CALLMETHOD":
							$s1 = $gp_pc["FUNC"][ $r[2] ]["name"];
							$buf .= sprintf("%8x , %s , %s\n", $r[1], $r[0], $s1);
							break;
						case "CALLSYS":
							$s1 = $gp_sysfunc[ $r[2] ];
							$buf .= sprintf("%8x , %s , %s\n", $r[1], $r[0], $s1);
							break;
						case "EOF":
							$s1 = $gp_pc["FNAM"][ $r[2] ];
							$buf .= sprintf("%8x , %s , %s\n", $r[1], $r[0], $s1);
							break;
						case "S_PUSH":
							$s1 = $gp_pc["STR0"][ $r[2] ];
							$buf .= sprintf("%8x , %s , %s\n", $r[1], $r[0], $s1);
							break;
						case "SH_GLOBALREF":
							$s1 = $gp_pc["GLOB"][ $r[2] ]['n'];
							$buf .= sprintf("%8x , %s , %s\n", $r[1], $r[0], $s1);
							break;
						default:
							$buf .= sprintf("%8x , %s , %x\n", $r[1], $r[0], $r[2]);
							break;
					}
					break;
				case 2:
					$buf .= sprintf("%8x , %s\n", $r[1], $r[0]);
					break;
			}

			if ( $r[0] == "ENDFUNC" || $r[0] == "EOF" )
				break;
		} // while (1)
		file_put_contents("$dir/{$f["name"]}.txt", $buf);
	}
}

function ain2( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;
		printf("[$rem] $fname\n");

	$dir = str_replace('.', '_', $fname);
	@mkdir("$dir/ain", 0755, true);

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$bak = $st;
		ain2tags( $file, $st, $dir );
		if ( $bak == $st )
			return;
	} // while ( $st < $ed )

	if ( ! file_exists("$dir/CODE") )
		return;
	$file = file_get_contents("$dir/CODE");
	codefunc( $file, "$dir/ain" );
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain2( $argc-$i, $argv[$i] );
