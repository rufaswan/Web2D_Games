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
define("ZERO", chr(0));
define("BIT8", 0xff);

function fgetstr0( &$fp, $pos )
{
	fseek($fp, $pos, SEEK_SET);
	$str = "";
	while(1)
	{
		$b1 = fread($fp,1);
		if ( $b1 == ZERO )
			break;
		$str .= $b1;
	}
	return $str;
}

function fgetstr( &$fp, $pos, $byte )
{
	fseek($fp, $pos, SEEK_SET);
	$str = fread($fp, $byte);
	return $str;
}

function fgetint( &$fp, $pos, $byte )
{
	fseek($fp, $pos, SEEK_SET);
	$no = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$b1 = ord( fread($fp,1) );
		$no += ($b1 << ($i*8));
	}
	return $no;
}
//////////////////////////////
function fnstr( &$fp, $pos, $sub )
{
	$fn = fgetstr0($fp, $pos);
	$ed = strlen($fn);
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = ord( $fn[$st] );
		$b1 = ($b1 - $sub) & BIT8;
		$fn[$st] = chr($b1);
		$st++;
	}
	return $fn;
}
//////////////////////////////
function unred40( $fname )
{
	$fp = fopen( $fname, "rb" );
		if ( ! $fp )   return;

	$mgc = fgetstr($fp, 0, 3);
	if ( $mgc != "AAR" )
		return;

	$dir = str_replace('.', '_', $fname );
	$ver = fgetint($fp, 4, 4);
	$sub = 0;
	if ( $ver == 2 )
		$sub = 0x60;

	$ed = fgetint( $fp, 0xc, 4 );
	$st = 0xc;
	$buf = "";
	while ( $st < $ed )
	{
		$pos = fgetint( $fp, $st+0, 4 );
		$siz = fgetint( $fp, $st+4, 4 );
		$nam = fnstr( $fp, $st+12, $sub );
		$len = strlen($nam) + 1;
		if ( $ver == 2 )
			$len++;

		$log = sprintf("%8x , %8x , %8x , %s\n", $st, $pos, $siz, $nam);
		echo $log;
		$buf .= $log;

		$st += (12 + $len);
/*
		$sep = strrpos($nam, '/');
		if ( $sep )
		{
			$dn = $dir .'/'. substr($nam, 0, $sep);
			$fn = substr($nam, $sep+1);
		}
		else
		{
			$dn = $dir;
			$fn = $nam;
		}

		@mkdir($dn, 0755, true);

		fseek($fp, $pos, SEEK_SET);
		file_put_contents("$dn/$fn", fread($fp, $siz));
*/

	}

	file_put_contents("$dir.txt", $buf);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	unred40( $argv[$i] );
