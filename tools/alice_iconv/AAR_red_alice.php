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
require "common_sco.inc";
//////////////////////////////
function fgetstr( &$fp, $pos, $byte )
{
	fseek($fp, $pos, SEEK_SET);
	$str = fread($fp, $byte);
	return $str;
}

function fgetint( $fp, $pos, $bytes )
{
	fseek( $fp, $pos, SEEK_SET );
	$data = fread($fp, $bytes);
	$p = 0;
	return sint32($data, $p);
}
//////////////////////////////
function fnstr( &$head, &$pos, $sub )
{
	$fn = substr0($head, $pos);
	$ed = strlen($fn);
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = ord( $fn[$st] );
		$b1 = ($b1 - $sub) & BIT8;
		$fn[$st] = chr($b1);
		$st++;
	}
	$fn = sjis2utf8($fn);
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

	$hsz  = fgetint($fp, 12, 4);
	$head = fgetstr($fp, 0, $hsz);

	$dir = str_replace('.', '_', $fname );
	$st = 4;
	$ver = sint32($head, $st);
	$sub = 0;
	if ( $ver == 2 )
		$sub = 0x60;

	$ed = $hsz;
	$st = 12;
	$txt = "\n";
	$id = 1;
	while ( $st < $ed )
	{
		$pos = sint32( $head, $st );
		$siz = sint32( $head, $st );
		$st += 4;
		$nam = fnstr( $head, $st, $sub );
		if ( $ver == 2 )
			$st++;

		printf("%8x , %8x , %s\n", $pos, $siz, $nam);
		$txt .= "$nam\n";

		$dn = sprintf("$dir/%03d", $id >> 8);
		@mkdir($dn, 0755, true);

		$ext = substr($nam, strrpos($nam,'.') + 1 );
		$ext = strtolower($ext);
		$fn = sprintf("$dn/%05d.$ext", $id);
			$id++;

		fseek($fp, $pos, SEEK_SET);
		file_put_contents($fn, fread($fp, $siz));
	}

	file_put_contents("$dir.txt", $txt);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	unred40( $argv[$i] );
