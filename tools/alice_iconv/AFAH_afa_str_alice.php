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
////////////////////////////////////////
function afa_untbl( $fp, $fname, $tbl_sz )
{
	$tbl = "$fname.tbl";

	fseek($fp, 0x2c, SEEK_SET);
	$zip = fread($fp, $tbl_sz);
	$zip = zlib_decode($zip);
	file_put_contents($tbl, $zip);

	return $zip;
}

function afarip( $fname )
{
	$fp = fopen( $fname, "rb" );
		if ( ! $fp )   return;
	$head = fread($fp, 0x2c);

	$mgc = substr($head, 0, 4);
	if ( $mgc != "AFAH" )
		return;

	$mgc = substr($head, 8, 8);
	if ( $mgc != "AlicArch" )
		return;

	$mgc = substr($head, 0x1c, 4);
	if ( $mgc != "INFO" )
		return;

	$st  = 0x10;
	$ver = sint32($head, $st);

	$st  = 0x18;
	$dat = sint32($head, $st);

	$st  = 0x20;
	$tbl_zp = sint32($head, $st);
	$tbl_sz = sint32($head, $st);
	$cnt = sint32($head, $st);

	$dir = str_replace('.', '_', $fname);
	$tbl = afa_untbl($fp, $dir, $tbl_sz);
	@mkdir("$dir", 0755, true);

	$ed = strlen($tbl);
	$st = 0;
	$txt = "";
	while( $st < $ed )
	{
		$bak = $st;
		// 0     4  filename length
		// 4     4  ^ + NULL aligned to 4-bytes
		// 8     v  filename [=p]
		// (ver.1)  p+0  4  ???
		// p+0   8  ???
		// p+4   4  data start offset
		// p+8   4  data size
		$len = sint32($tbl, $st);
		$pad = sint32($tbl, $st);
		$fn  = utf8fnam($tbl, $st, $len);

		$st = ($bak + 8 + $pad + 8);
		if ( $ver == 1 )
			$st += 4;

		$ps = sint32($tbl, $st);
		$sz = sint32($tbl, $st);

		$sep = strrpos($fn, '/');
		if ( $sep )
		{
			$dn = substr($fn, 0, $sep);
			@mkdir("$dir/$dn", 0755, true);
		}

		fseek($fp, $dat+$ps, SEEK_SET);
		file_put_contents("$dir/$fn", fread($fp,$sz));

		$log  = sprintf("%8x , %8x , %s\n", $ps, $sz, $fn);
		$txt .= $log;
		echo $log;
	}

	file_put_contents("$dir.txt", $txt);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	afarip( $argv[$i] );
