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
req_ext( "zlib_decode", "zlib" );
////////////////////////////////////////
function afa_untbl( $fp, $tbl, $tbl_sz )
{
	fseek($fp, 0x2c, SEEK_SET);
	$zip = fread($fp, $tbl_sz);
	$zip = zlib_decode($zip);
	file_put_contents($tbl, $zip);

	return $zip;
}

function afa_dir( $fname )
{
	$scan = array(
		"cg" => "ga",
		"sound" => "ba",
		"voice" => "wa",
	);
	foreach ( $scan as $k => $v )
	{
		if ( stripos($fname, "{$k}.afa" ) )
		{
			$data = array(
				"dir" => "{$v}",
				"tbl" => "{$k}_afa.tbl",
				"txt" => "{$k}_afa_{$v}.txt",
			);
			return $data;
		}
	}
	return array();
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

	$afa = afa_dir($fname);
	if ( empty($afa) )  return;

	$tbl = afa_untbl($fp, $afa["tbl"], $tbl_sz);
	@mkdir($afa["dir"], 0755, true);

	$ed = strlen($tbl);
	$st = 0;
	$txt = "\n";
	$id = 1;
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
		$fna = utf8fnam($tbl, $st, $len);

		$st = ($bak + 8 + $pad + 8);
		if ( $ver == 1 )
			$st += 4;

		$ps = sint32($tbl, $st);
		$sz = sint32($tbl, $st);

		$ext = strtolower( substr($fna, strrpos($fna, '.')+1) );
		$dn = sprintf("%s/%03d", $afa["dir"], $id >> 8);
		$fn = sprintf("%05d.$ext", $id);
			$id++;

		@mkdir($dn, 0755, true);

		fseek($fp, $dat+$ps, SEEK_SET);
		file_put_contents("$dn/$fn", fread($fp,$sz));

		//$log = sprintf("%4x , %s\n", $id-1, $fna);
		$txt .= "$fna\n";
		echo "$dn/$fn\n";
	}

	file_put_contents($afa["txt"], $txt);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	afarip( $argv[$i] );
