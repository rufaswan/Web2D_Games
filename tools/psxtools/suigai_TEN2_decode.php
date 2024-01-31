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
require 'common.inc';

function suigai_decode( &$file )
{
	$dec = '';
	// SLPM_866.37 , sub_80051e38
	trace("== begin sub_80051e38()\n");

	$NEW_DICT = str_repeat(ZERO, 0x400);
	for ( $i=0; $i < 0x100; $i++ )
		$NEW_DICT[ 0x200+$i ] = chr($i);

	$len = strlen($file);
	$pos = 0;

	// 80051e64 - 80051fb8
	$b1  = ord( $file[$pos] ); // a3
		$pos++;
	//trace("%8x 51e64 START\n", $pos);
	while ( $pos < $len )
	{
		$b2 = ord( $file[$pos] ); // t2
			$pos++;

		$dict = $NEW_DICT;
		$dpos = 0;

		// init dictionary
		// 80051e8c - 80051f2c
		//trace("%8x 51e8c init dict\n", $pos);
		while (1)
		{
			$cnt = 0;
			if ( $b1 & 0x80 )
				$dpos = ($dpos - 0x7f) + $b1;
			else
				$cnt = $b1;

			if ( $dpos == 0x100 )
				break;

			$cnt++;
			//trace("%8x 51ee0 , cnt %x\n", $pos, $cnt-1);
			while ( $cnt > 0 )
			{
				$dict[ $dpos+0x200 ] = chr($b2);
				if ( $b2 == $dpos )
				{
					$b2 = ord( $file[$pos] );
						$pos++;
				}
				else
				{
					$dict[ $dpos+0x300 ] = $file[$pos+0];
					$b2 = ord( $file[$pos+1] );
						$pos += 2;
				}
				$cnt--;
				$dpos++;
			} // while ( $cnt > 0 )

			if ( $dpos == 0x100 )
				break;

			$b1 = $b2;
			$b2 = ord( $file[$pos] );
				$pos++;
		} // while (1)

		$cnt = $b2 << 8;
		$b1 = ord( $file[$pos+0] ); // v0
		$b2 = ord( $file[$pos+1] ); // t2
			$pos += 2;

		$cnt |= $b1; // t3
		$dp  = 0; // a3

		// decompression
		// 80051f50 - 80051fa8
		//trace("%8x 51f50 , cnt %x\n", $pos, $cnt);
		while ( $cnt > 0 )
		{
			$dpos = $b2;
			$b2 = ord( $file[$pos] );
				$pos++;

			// expansion
			// 80051f60 - 80051fa0
			//trace("%8x 51f60 expansion\n", $pos);
			while (1)
			{
				$db1 = ord( $dict[ $dpos+0x200 ] ); // t0
				if ( $dpos == $db1 )
					$dec .= chr($dpos);
				else
				{
					$db2 = $dict[ $dpos+0x300 ]; // v0
					$dict[ $dp+0 ] = $db2;
					$dict[ $dp+1 ] = chr($db1);
						$dp += 2;
				}

				if ( $dp > 0 )
				{
					$dpos = ord( $dict[ $dp-1 ] );
					$dp--;
				}
				else
					break;
			} // while (1)

			$cnt--;
		} // while ( $cnt > 0 )

		$b1 = $b2;
	} // while ( $pos < $len )

	trace("== end sub_80051e38()\n");
	return $dec;
}

function suigai( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'TEN2' )
		return;

	$size = str2int($file,  4, 4);
	$file = substr ($file, 12, $size+1);

	$dec = suigai_decode($file);
	save_file("$fname.dec", $dec);
	return;
}

argv_loopfile($argv, 'suigai');
