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

function tm2_dec_flag( &$file, &$pos, &$bycod, &$bylen )
{
	if ( $bylen == 0 )
	{
		$bycod = ord( $file[$pos] );
			$pos++;
		$bylen = 8;
	}

	$flag = $bycod & 1;
		$bycod >>= 1;
		$bylen--;
	return $flag;
}

function tm2_decode( &$file )
{
	// SLPM 805.50 , EVS 3 , sub_8001a6fc
	echo "== begin sub_8001a6fc\n";
	$bycod = 0;
	$bylen = 0;
	$dec = '';

	$len = strlen($file);
	$pos = 0;
	while ( $pos < $len )
	{
		trace("%8x  %8x  ", $pos, strlen($dec));

		$flg1 = tm2_dec_flag($file, $pos, $bycod, $bylen);
		if ( $flg1 ) // 1
		{
			$flg1 = tm2_dec_flag($file, $pos, $bycod, $bylen);
			if ( $flg1 ) // 11
			{
				$flg1 = tm2_dec_flag($file, $pos, $bycod, $bylen);
				$flg2 = tm2_dec_flag($file, $pos, $bycod, $bylen);

				$dlen = ($flg1 << 1) + 2 + $flg2;
				$dpos = ord( $file[$pos] );
					$pos++;
				if ( $dpos == 0 )
					$dpos = 0x100;
				trace("REF_11  POS %d  LEN %d\n", $dpos, $dlen);
			}
			else // 10
			{
				$b1 = ord( $file[$pos+0] ); // v0
				$b2 = ord( $file[$pos+1] ); // v1
					$pos += 2;

				$b2 |= ($b1 << 8);
				if ( $b2 == 0 )
					goto end;

				$dpos = $b2 >> 4;
				$dlen = $b2 & 0xf;
				if ( $dlen == 0 )
				{
					$b1 = ord( $file[$pos] );
						$pos++;
					$dlen = $b1 + 1;
				}
				else
					$dlen += 2;
				trace("REF_10  POS %d  LEN %d\n", $dpos, $dlen);
			}

			for ( $i=0; $i < $dlen; $i++ )
			{
				$p = strlen($dec) - $dpos;
				$dec .= $dec[$p];
			}
		}
		else // 0
		{
			$b1 = $file[$pos];
				$pos++;
			trace("COPY    %2x\n", ord($b1));
			$dec .= $b1;
		}
	} // while ( $pos < $len )

end:
	echo "== end sub_8001a6fc\n";
	return $dec;
}

function tm2( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dec = tm2_decode($file);
	save_file("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tm2( $argv[$i] );
