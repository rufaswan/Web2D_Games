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

function sotn_decode( &$file, $pos )
{
	// sub_800eb398-800eb614 , SLPM_860.23/DRA.BIN
	// st/top/top.bin , 80183538 -> 801ffe18 -> VRAM
	$dec = '';
	trace("== begin sub_800eb398()\n");

	$a0 = array();
	for ( $i=0; $i < 8; $i++ )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$a0[$i] = array(
			($b >> 4) & BIT4,
			($b >> 0) & BIT4,
		);
	} // for ( $i=0; $i < 8; $i++ )

	$v0 = array();
	while (1)
	{
		while ( count($v0) < 8 )
		{
			$b = ord( $file[$pos] );
				$pos++;
			$v0[] = ($b >> 4) & BIT4;
			$v0[] = ($b >> 0) & BIT4;
		}

		$flg = array_shift($v0);
		switch ( $flg )
		{
			case 0:
				$b1 = array_shift($v0);
				$b2 = array_shift($v0);
				$b = ($b1 << 4) + $b2 + 19;

				$dec .= str_repeat(ZERO, $b);
				break;
			case 2:
				$b1 = array_shift($v0);
				$dec .= chr($b1) . chr($b1);
				break;

			case 4: // WATERFALL , COPY 3
				$b1 = array_shift($v0);
				$dec .= chr($b1);
			case 3: // COPY 2
				$b1 = array_shift($v0);
				$dec .= chr($b1);
			case 1: // COPY 1
				$b1 = array_shift($v0);
				$dec .= chr($b1);
				break;

			case 5:
				$b1 = array_shift($v0);
				$b2 = array_shift($v0);
				$dec .= str_repeat(chr($b1), $b2+3);
				break;
			case 6:
				$b1 = array_shift($v0);
				$dec .= str_repeat(ZERO, $b1+3);
				break;

			case 15: // f
				goto done;

			default: // 7-e
				$b = $a0[$flg-7];
				switch ( $b[0] )
				{
					case 2:
						$dec .= chr($b[1]) . chr($b[1]);
						break;
					case 1:
						$dec .= chr($b[1]);
						break;
					case 6:
						$dec .= str_repeat(ZERO, $b[1] + 3);
						break;
					default:
						php_error('UNKNOWN b[0] %x', $b[0]);
						break;
				}
				break;
		} // switch ( $flg )
	} // while (1)
done:
	trace("== end sub_800eb398()\n");
	return $dec;
}
//////////////////////////////
function bossoff( &$file, $pos )
{
	if ( $file[$pos+3] !== "\x80" )
		return -1;
	$off = str2int($file, $pos, 3);
	return $off - 0x180000;
}
//////////////////////////////
