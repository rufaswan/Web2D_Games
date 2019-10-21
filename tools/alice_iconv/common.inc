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
require "funcs-sjis.php";
define("ROOT", dirname(__FILE__) );

define("SJIS_HALF", ROOT . "/sjis_half.inc");
define("SJIS_ASC",  ROOT . "/sjis_ascii.inc");
////////////////////////////////////////
define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

function substr0( &$str, &$pos )
{
	$s = "";
	while ( $str[$pos] != ZERO )
	{
		if ( $str[$pos] == "\n" )
			$s .= '\n';
		else
		if ( $str[$pos] == "\r" )
			$s .= '\r';
		else
		if ( $str[$pos] == "\t" )
			$s .= '\t';
		else
			$s .= $str[$pos];
		$pos++;
	}
	$pos++; // skip zero terminator
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
function sint16( &$file, &$st ) // max +- 32,000
{
	$n = str2int($file, $st, 2);
	if ( $n >> 15 )
		return ($n - BIT16 - 1);
	else
		return $n;
}
function sint32( &$file, &$st ) // max +- 2,000,000,000
{
	$n = str2int($file, $st, 4);
	if ( $n >> 31 )
		return ($n - BIT32 - 1);
	else
		return $n;
}
function utf8txt( &$file, &$st )
{
	$sjis = substr0( $file, $st );
	$sjis = sjistxt($sjis);
	$utf8 = iconv("MS932", "UTF-8", $sjis);
	return $utf8;
}
function utf8fnam( &$file, &$st )
{
	$utf8 = utf8txt( $file, $st );
	$utf8 = str_replace('\\', '/', $utf8);
	return $utf8;
}
////////////////////////////////////////
