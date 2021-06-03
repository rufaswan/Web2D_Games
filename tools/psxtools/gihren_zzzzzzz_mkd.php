<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";

//define("NO_TRACE", true);

function gihren_decode( &$file )
{
	// from SLPS 025.70 / Earth Federation disc
	trace("== begin sub_80016f28\n");
	$dec = "";
	$bycod = 0;
	$bylen = 0;

	$ed = strlen($file);
	$st = 12;
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
				$st++;

			trace("BYTECODE %2x\n", $bycod);
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg == 0 )
		{
			$b1 = $file[$st];
				$st++;

			trace("COPY %2x\n", ord($b1));
			$dec .= $b1;
		}
		else
		{
			$b1 = ord( $file[$st+0] ); // a3
			$b2 = ord( $file[$st+1] ); // v1
				$st += 2;

			$op = $b1 & BIT4;
			switch ( $op )
			{
				case 2:
					$len = (($b1 >> 4) | ($b2 << 4)) + 0x12;
					$b1 = substr($file, $st, $len);
						$st += $len;

					trace("%2x COPY %s\n", $op, debug($b1));
					$dec .= $b1;
					break;

				case 1:
					$len = ($b1 >> 4) + 3;

					trace("%2x DUPL %2x LEN %2x\n", $op, $b2, $len);
					$dec .= str_repeat(chr($b2), $len);
					break;

				case 0:
					$pos = ($b1 >> 4) | ($b2 << 4);
					$len = ord( $file[$st] ) + 0x10;
						$st++;

					trace("%2x REF  POS -%d LEN %d\n", $op, $pos, $len);
					for ( $i=0; $i < $len; $i++ )
					{
						$p = strlen($dec) - $pos;
						$dec .= $dec[$p];
					}
					break;

				default:
					$pos = ($b1 >> 4) | ($b2 << 4);
					$len = $op;

					trace("%2x REF  POS -%d LEN %d\n", $op, $pos, $len);
					for ( $i=0; $i < $len; $i++ )
					{
						$p = strlen($dec) - $pos;
						$dec .= $dec[$p];
					}
					break;
			} // switch ( $op )
		}

	} // while ( $st < $ed )

	trace("== end sub_80016f28\n");
	return $dec;
}

function gihren( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pos = strpos($file, "SD0\x00");
	if ( $pos === false )
		return;
	//$pos = 0x3df2000;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	$id  = 0;
	while ( $pos < $len )
	{
		$b1 = substr ($file, $pos+0, 4);
		$b2 = str2int($file, $pos+4, 4);
		if ( $b1 !== "SD0\x00" )
			return php_error("%6x not SD0", $pos);

		$fn = sprintf("%s/%04d.sd0", $dir, $id);
		printf("%4x , %8x , %8x , %s\n", $pos>>11, $pos, $b2, $fn);

		$b1 = substr($file, $pos, $b2);
		$b1 = gihren_decode($b1);
		save_file($fn, $b1);

		$id++;
		$pos += int_ceil($b2, 0x800);
	} // while ( $pos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gihren( $argv[$i] );

/*
earth federation title
	MRG   zzzzzzz0.mkd 3df2000 / 1751.sd0
	pQES  zzzzzzz1.mkd 3916000 / 1169.sd0
	pBAV  zzzzzzz1.mkd 3934000 / 1171.sd0
 */
