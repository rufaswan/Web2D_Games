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

function xorsum( &$sub )
{
	// SCUS 946.05 , sub_80012598
	//   auto-detect if the 0x800 block has checksum
	$crc = str2int($sub, 0x7fc, 4);
	$sum = 0;
	for ( $i=0; $i < 0x7fc; $i += 4 )
		$sum ^= str2int($sub, $i, 4);
	return ( $sum == $crc ) ? 0x7fc : 0x800;
}

function disc2( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$lfi =  load_file("$pfx.lfi");
	$lfd = fopen_file("$pfx.lfd");
	if ( empty($lfi) || ! $lfd )
		return;

	$ed = strlen($lfi);
	$st = 0;
	while ( $st < $ed )
	{
		$fn = rtrim( substr($lfi,$st,12), ZERO );
		$sz = str2int($lfi, $st+12,  4);
		$of = str2int($lfi, $st+16,  4);
			$st += 20;

		if ( $sz > 0x7fc )
		{
			// auto-detect checksum
			$sub = fp2str($lfd, $of, 0x800);
			$sct = xorsum($sub);

			// no skipping
			if ( $sct == 0x800 )
				$sub = fp2str($lfd, $of, $sz);
			else
			{
				// on every 800 , read 7fc and skip 4
				$b1  = $sz;
				$b2  = $of;
				$sub = '';
				while ( $b1 > 0 )
				{
					$rsz  = ( $b1 > $sct ) ? $sct : $b1;
					$sub .= fp2str($lfd, $b2, $rsz);
						$b1 -= $rsz;
						$b2 += 0x800;
				} // while ( $b1 > 0 )
			}
		}
		else // under 1 sector
			$sub = fp2str($lfd, $of, $sz);

		$fn = strtolower($fn);
		printf("%8x , %8x , %s\n", $of, $sz, $fn);
		save_file("$pfx/$fn", $sub);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc2( $argv[$i] );

/*
0x7fc
	-> 80012598  lw a1, 0x7fc(a0)
 */
