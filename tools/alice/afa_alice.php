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
////////////////////////////////////////
define("ZERO", chr(0));

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
////////////////////////////////////////
function sjisstr( &$file, &$st )
{
	$sjis_half = file_get_contents("sjis_half.inc" );
	$sjis_asc  = file_get_contents("sjis_ascii.inc");
	$str = "";
	while(1)
	{
		$b1 = ord( $file[$st] );
		if ( $b1 == 0 )
			return $str;

		if ( $b1 >= 0xe0 )
		{
			$str .= $file[$st+0];
			$str .= $file[$st+1];
			$st += 2;
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$p = $b1 * 2;
			$str .= $sjis_half[$p+0];
			$str .= $sjis_half[$p+1];
			$st++;
		}
		else
		if ( $b1 >= 0x80 )
		{
			if ( $b1 == 0x81 || $b1 == 0x82 )
			{
				$b2 = ord( $file[$st+1] );
				$p = ($b1 - 0x81) * 0x100 + $b2;
				if ( $sjis_asc[$p] == ZERO )
				{
					$str .= $file[$st+0];
					$str .= $file[$st+1];
				}
				else
					$str .= $sjis_asc[$p];
			}
			else
			{
				$str .= $file[$st+0];
				$str .= $file[$st+1];
			}
			$st += 2;
		}
		else
		{
			$str .= $file[$st];
			$st++;
		}
	} // while(1)
}

function afatbl( $fp, $fname, $tbl_sz )
{
	// to fix invalid SJIS chars
	// Rance01CG.afa = 87 55
	$tbl = "$fname.tbl";
	if ( file_exists($tbl) )
		$zip = file_get_contents($tbl);
	else
	{
		fseek($fp, 0x2c, SEEK_SET);
		$zip = fread($fp, $tbl_sz);
		$zip = zlib_decode($zip);
		file_put_contents($tbl, $zip);
	}
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

	$ver = str2int($head, 0x10, 4);
	$dat = str2int($head, 0x18, 4);
	$tbl_zp = str2int($head, 0x20, 4);
	$tbl_sz = str2int($head, 0x24, 4);
	$cnt = str2int($head, 0x28, 4);

	$tbl = afatbl($fp, $fname, $tbl_sz);
	$dir = str_replace('.', '_', $fname);

	$st = 0;
	$ed = strlen($tbl);
	$txt = "";
	while( $st < $ed )
	{
		// 0     4  filename length
		// 4     4  ^ + NULL aligned to 4-bytes
		// 8     v  filename [=p]
		// (ver.1)  p+0  4  ???
		// p+0   8  ???
		// p+4   4  data start offset
		// p+8   4  data size
		$len = str2int($tbl, $st+0, 4);
		$pad = str2int($tbl, $st+4, 4);
		$fn = substr($tbl, $st+8, $len);
		//$fn = str_replace('/', '\\', $fn);
		//$fn = exec("printf \"{$fn}\" | iconv -f sjis -t utf-8");
		$ext = substr($fn, strrpos($fn, '.')+1);

		$s = 0;
		if ( $ver == 1 )
			$s = 4;

		$ps = str2int($tbl, $st+$pad+$s+0x10, 4);
		$sz = str2int($tbl, $st+$pad+$s+0x14, 4);

		$idd = sprintf("%03d"  , $ps >> 24);
		$idf = sprintf("%010d" , $ps);
		@mkdir("$dir/$idd", 0755, true);

		fseek($fp, $dat+$ps, SEEK_SET);
		file_put_contents("$dir/$idd/$idf.$ext", fread($fp,$sz));
		$log  = sprintf("%8x , %8x , $fn\n", $ps, $sz);
		$txt .= $log;
		echo $log;

		$st += (0x18 + $pad + $s);
	}

	file_put_contents("$fname.txt", $txt);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	afarip( $argv[$i] );
