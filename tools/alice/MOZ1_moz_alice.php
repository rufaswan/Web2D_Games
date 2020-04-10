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
//////////////////////////////
function moz1( $fname )
{
	$fp = fopen($fname, "rb");
		if ( ! $fp )  return;

	$mgc = fp2str($fp, 0, 4);
	if ( $mgc != "MOZ1" )
		return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$cnt = fp2int($fp, 4, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x14 + ($i * 8);
		$off = fp2int($fp, $p+0, 4);
		$len = fp2int($fp, $p+4, 4);
		printf("%8x , %8x , %8x , %05d.bin\n", $p, $off, $len, $i+1);

		$fn = sprintf("$dir/%05d.bin", $i+1);

		$zip = fp2str($fp, $off, $len);
		$zip = zlib_decode($zip);
		file_put_contents($fn, $zip);
	}

/*
	$ed = fp2int($fp, 12, 4);
	$st = 0x18;
	$dn = "";
	$n = 0;
	while ( $st < $ed )
	{
		$type = fp2int($fp, $st+8, 2);
		if ( $type == 0x2322 )
		{
			$b1 = fp2int($fp, $st+0, 2);
			$dn = "$dir/$b1";
			@mkdir($dn, 0755, true);
			$n = 1;
			$st += 12;
		}
		else
		{
			$ps = fp2int($fp, $st+2, 4);
			$sz = fp2int($fp, $st+6, 2);
			$nn = sprintf("$dn/%05d.bin", $n);
			printf("%8x , %8x , %8x , $nn\n", $st, $ps, $sz);

			fseek($fp, $ps, SEEK_SET);
			$zip = fread($fp, $sz);
			$zip = zlib_decode($zip);
			file_put_contents($nn, $zip);

			$n++;
			$st += 10;
		}
	} // while ( $st < $ed )
*/

	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	moz1( $argv[$i] );
