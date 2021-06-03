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

function xeno_decode( &$file, $st, $ed )
{
	trace("== begin sub_80032cac\n");

	$lw = str2int($file, $st, 3);
		$st += 4;
	$bycod = 0;
	$bylen = 0;
	$dec = '';
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] ); // t8
				$st++;
			trace("BYTECODE %2x\n", $bycod);
			$bylen = 8; // t9
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = ord( $file[$st+0] ); // t0
			$b2 = ord( $file[$st+1] ); // t4
				$st += 2;
			$pos = ($b2 & 0xf) << 8;
				$pos |= $b1;
			$len = ($b2 >> 4) + 3;
			trace("REF  POS -%d LEN %d\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = strlen($dec) - $pos;
				$dec .= $dec[$p];
			}
		}
		else
		{
			$b1 = $file[$st]; // t0
				$st++;
			trace("COPY %2x\n", ord($b1));
			$dec .= $b1;
		}
	} // while ( $st < $ed )
	trace("== end sub_80032cac\n");

	return $dec;
}
