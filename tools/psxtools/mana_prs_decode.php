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
require 'class-bakfile.inc';

define('NO_TRACE', true);

function mana_decode( &$file , $st )
{
	$dec = '';
	// sub_80014448-80014888 , SLPS_021.70
	// data loaded to 8001dc003 , decode to 8004fdd8
	trace("== begin sub_80014448()\n");

	$ed = strlen($file);
	while ( $st < $ed )
	{
		$b0 = ord( $file[$st] );
			$st++;

		$op = $b0 - 0xf0;
		switch ( $op )
		{
			case 0: // f0
				$b1 = ord( $file[$st] );
					$st++;
				$len = ($b1 & 0x0f) + 3;
				$s0 = chr($b1 >> 4);

				for ( $i=0; $i < $len; $i++ )
					$dec .= $s0;
				break;

			case 1: // f1 , 8-bpp DUP
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1];
					$st += 2;
				$len = $b1 + 4;

				for ( $i=0; $i < $len; $i++ )
					$dec .= $b2;
				break;

			case 2: // f2 , 4-bpp DUP
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$len = $b1 + 2;
				$s0 = chr($b2 & 0x0f);
				$s1 = chr($b2 >> 4);

				for ( $i=0; $i < $len; $i++ )
					$dec .= $s0 . $s1;
				break;

			case 3: // f3
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1] . $file[$st+2];
					$st += 3;
				$len = $b1 + 2;

				for ( $i=0; $i < $len; $i++ )
					$dec .= $b2;
				break;

			case 4: // f4
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1] . $file[$st+2] . $file[$st+3];
					$st += 4;
				$len = $b1 + 2;

				for ( $i=0; $i < $len; $i++ )
					$dec .= $b2;
				break;

			case 5: // f5
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1];
					$st += 2;
				$len = $b1 + 4;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $b2;
					$dec .= $file[$st];
						$st++;
				}
				break;

			case 6: // f6
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1] . $file[$st+2];
					$st += 3;
				$len = $b1 + 3;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $b2;
					$dec .= $file[$st];
						$st++;
				}
				break;

			case 7: // f7
				$b1 = ord( $file[$st+0] );
				$b2 = $file[$st+1] . $file[$st+2] . $file[$st+3];
					$st += 4;
				$len = $b1 + 2;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $b2;
					$dec .= $file[$st];
						$st++;
				}
				break;

			case 8: // f8 , INC
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$len = $b1 + 4;
				$s0 = $b2;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0++;
				}
				break;

			case 9: // f9 , DEC
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$len = $b1 + 4;
				$s0 = $b2;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0--;
				}
				break;

			case 10: // fa , INC VAL
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
				$b3 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 5;
				$s0 = $b2;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0 += $b3;
				}
				break;

			case 11: // fb
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
				$b3 = ord( $file[$st+2] );
				$b4 = ord( $file[$st+3] );
					$st += 4;
				$len = $b1 + 3;
				$s0 = $b2;
				$s1 = $b3;
				$s2 = ($b4 & 0x80) ? $b4 - 0x100 : $b4;

				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$dec .= chr($s1);
					$t0 = ($s1 << 8) | $s0;
					$t0 += $s2;
					$s0 =  ($t0 & BIT8);
					$s1 = (($t0 >> 8) & BIT8);
				}
				break;

			case 12: // fc
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$len =  ($b2 >> 4) + 4;
				$pos = (($b2 & 0x0f) << 8 ) | $b1;

				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 1;
					$dec .= $dec[$cur];
				}
				break;

			case 13: // fd
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;
				$len = $b2 + 20;
				$pos = $b1;

				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 1;
					$dec .= $dec[$cur];
				}
				break;

			case 14: // fe
				$b1 = ord( $file[$st] );
					$st++;
				$len = ($b1 & 0x0f) + 3;
				$pos = ($b1 & 0xf0) >> 1;

				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 8;
					$dec .= $dec[$cur];
				}
				break;

			case 15: // ff
				goto done;

			default:
				$len = $b0 + 1;
				$dec .= substr($file, $st, $len);
					$st += $len;
				break;
		} // switch ( $b1 - 0xf0 )
	} // while ( $st < $ed )
done:
	trace("== end sub_80014448()\n");
	$file = $dec;
	return;
}

function mana( $fname )
{
	// for /bin/*.bin
	// for /ana/etc_etc/*.prs
	// for /map/*/*.prs
	// for /wm/wmap/*.pim
	// for /wm/wmtim/wmapt*/wm_*.pim
	$bak = new BakFile;
	$bak->load($fname);
	if ( $bak->is_empty() )
		return;

	// file must starts with 01 and ends with FF
	$ed = $bak->filesize(1);
	if ( $bak->file[0] !== "\x01" || $bak->file[$ed-1] !== BYTE )
		return;
	mana_decode($bak->file, 1);

	printf("%8x -> %8x  %s\n", $bak->filesize(0), $bak->filesize(1), $fname);
	$bak->save();
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );
