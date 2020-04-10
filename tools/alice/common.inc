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
//////////////////////////////
define("BIT8" , 0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

define("ZERO" , chr(  0));
define("BYTE" , chr(255));

function str2int( &$str, $pos, $byte )
{
	$int = substr($str, $pos, $byte);
	return ordint($int);
}

function fp2int( $fp, $pos, $byte )
{
	fseek($fp, $pos, SEEK_SET);
	$str = fread($fp, $byte);
	return ordint($str);
}

function substr0( &$str, $pos )
{
	$len = 0;
	while ( $str[$pos+$len] != ZERO )
		$len++;
	return substr($str, $pos, $len);
}

function fp2str( $fp, $pos, $byte )
{
	fseek($fp, $pos, SEEK_SET);
	return fread($fp, $byte);
}

function fp2str0( $fp, $pos )
{
	$str = fp2str($fp, $pos, 0x800);
	return substr0($str, 0);
}

function ordint( $str )
{
	$len = strlen($str);
	$int = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$int += ($b << ($i*8));
	}
	return $int;
}

function chrint( $int, $byte )
{
	$str = "";
	while ( $byte > 0 )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
		$byte--;
	} // while ( $byte > 0 )
	return $str;
}
