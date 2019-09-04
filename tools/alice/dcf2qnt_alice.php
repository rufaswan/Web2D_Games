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
function str2int( &$str, $pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	return $int;
}
//////////////////////////////
function dcf2qnt( $fname )
{
	$fp = fopen( $fname, "rb" );
		if ( ! $fp )  return;

	$fsz = filesize($fname);
	$st = 0;
		fseek($fp, $st, SEEK_SET);
		$dat = fread($fp, 8);
		$mgc = substr($dat, 0, 4);
		if ( $mgc != "dcf " )
			return;

	$st += 8;
	$st += str2int($dat, 4, 4);
		fseek($fp, $st, SEEK_SET);
		$dat = fread($fp, 8);
		$mgc = substr($dat, 0, 4);
		if ( $mgc != "dfdl" )
			return;

	$st += 8;
	$st += str2int($dat, 4, 4);
		fseek($fp, $st, SEEK_SET);
		$dat = fread($fp, 8);
		$mgc = substr($dat, 0, 4);
		if ( $mgc != "dcgd" )
			return;

	$st += 8;
		fseek($fp, $st, SEEK_SET);
		file_put_contents("$fname.qnt", fread($fp, $fsz-$st));
	printf("%8x , $fname.qnt\n", $st);

	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dcf2qnt( $argv[$i] );
