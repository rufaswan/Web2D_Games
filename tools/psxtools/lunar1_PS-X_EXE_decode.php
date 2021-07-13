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

function dec_getflag( &$file, &$st, &$bycod, &$bylen )
{
	if ( $bylen == 0 )
	{
		$bycod = ord( $file[$st] );
			$st++;
		$bylen = 8;
		trace("BYTECODE %2x\n", $bycod);
	}

	$flg = $bycod & 1;
		$bycod >>= 1;
		$bylen--;
	return $flg;
}

function lunar1_decode( &$file, $st )
{
	echo "== begin sub_801000c4\n";
	$dec = '';
	$bycod = 0;
	$bylen = 0;

	$ed = strlen($file);
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));

		if ( dec_getflag($file, $st, $bycod, $bylen) )
		{
			$b = $file[$st];
				$st++;
			trace("COPY %2x\n", ord($b));
			$dec .= $b;
		}
		else
		{
			if ( dec_getflag($file, $st, $bycod, $bylen) )
			{
				$b = ord( $file[$st] );
					$st++;
				// 76 543210
				// ll pppppp
				$dpos = $b - 0x40;
				$dlen = ($b >> 6) + 2;
				trace("REF  POS %d  LEN %d\n", $dpos, $dlen);

				for ( $i=0; $i < $dlen; $i++ )
				{
					$p = strlen($dec) + $dpos;
					$dec .= $dec[$p];
				}
			}
			else
			{
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$b = ($b1 << 8) | $b2;

				$dpos = $b - 0x1000;
				$dlen = $b >> 12;
				if ( $dlen == 0 )
				{
					$dlen = ord( $file[$st] );
						$st++;
					if ( $dlen == 0 )
						goto end;
				}
				$dlen += 2;
				trace("REF  POS %d  LEN %d\n", $dpos, $dlen);

				for ( $i=0; $i < $dlen; $i++ )
				{
					$p = strlen($dec) + $dpos;
					$dec .= $dec[$p];
				}
			}
		}
	} // while ( $st < $ed )

end:
	echo "== end sub_801000c4\n";
	$file = $dec;
	return;
}

function lunar1( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 8) != "PS-X EXE" )
		return;
	if ( substr($file, 0x1001, 8) != "PS-X EXE" )
		return;

	lunar1_decode($file, 0x1000);
	save_file("$fname.dec", $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar1( $argv[$i] );
