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
declare( strict_types=1 );

require 'tool.inc';

function mana_decode( string &$file ) : void
{
	// sub_80014448-80014888 , SLPS_021.70
	// data loaded to 8001dc003 , decode to 8004fdd8
	tool::trace('== begin sub_80014448()');
	$dec = '';

	$pos = 1;
	$len = strlen($file);
	while ( $pos < $len )
	{
		$b0 = ord( $file[$pos] );
			$pos++;

		$op = $b0 - 0xf0;
		switch ( $op )
		{
			case 0: // f0  4-bpp * f+3
				$b1 = ord( $file[$pos] );
					$pos++;
				$dlen = ($b1 & 0x0f) + 3;
				$dpix  = chr($b1 >> 4);

				for ( $i=0; $i < $dlen; $i++ )
					$dec .= $dpix;
				break;

			case 1: // f1  8-bpp * ff+4
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1];
					$pos += 2;
				$dlen = $b1 + 4;

				for ( $i=0; $i < $dlen; $i++ )
					$dec .= $dpix;
				break;

			case 2: // f2  2*4-bpp * ff+2
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dlen = $b1 + 2;
				$s0 = chr($b2 & 0x0f);
				$s1 = chr($b2 >> 4);
				$dpix = $s0 . $s1;

				for ( $i=0; $i < $dlen; $i++ )
					$dec .= $dpix;
				break;

			case 3: // f3  16-bit * ff+2
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1] . $file[$pos+2];
					$pos += 3;
				$dlen = $b1 + 2;

				for ( $i=0; $i < $dlen; $i++ )
					$dec .= $dpix;
				break;

			case 4: // f4  24-bit * ff+2
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1] . $file[$pos+2] . $file[$pos+3];
					$pos += 4;
				$dlen = $b1 + 2;

				for ( $i=0; $i < $dlen; $i++ )
					$dec .= $dpix;
				break;

			case 5: // f5  int16 pair * ff+4
				// 81 40 41 42 43 => 81 40 81 41 81 42 81 43... (SJIS TEXT)
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1];
					$pos += 2;
				$dlen = $b1 + 4;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec .= $dpix;
					$dec .= $file[$pos];
						$pos++;
				}
				break;

			case 6: // f6  int24 pair * ff+3
				// aa bb 11 22 33 => aa bb 11 aa bb 22 aa bb 33
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1] . $file[$pos+2];
					$pos += 3;
				$dlen = $b1 + 3;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec .= $dpix;
					$dec .= $file[$pos];
						$pos++;
				}
				break;

			case 7: // f7  int32 * ff+2
				// 80 18 00 11 22 33 => 80 18 00 11 80 18 00 22 80 18 00 33... (POINTERS)
				$b1 = ord( $file[$pos+0] );
				$dpix = $file[$pos+1] . $file[$pos+2] . $file[$pos+3];
					$pos += 4;
				$dlen = $b1 + 2;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec .= $dpix;
					$dec .= $file[$pos];
						$pos++;
				}
				break;

			case 8: // f8  int8 +1 * ff+4
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dlen = $b1 + 4;
				$dval = $b2;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec .= chr($dval);
					$dval = ($dval + 1) & BIT8;
				}
				break;

			case 9: // f9  int8 -1 * ff+4
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dlen = $b1 + 4;
				$dval = $b2;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec .= chr($dval);
					$dval = ($dval - 1) & BIT8;
				}
				break;

			case 10: // fa  int8 val * ff+5
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
				$dinc = ord( $file[$pos+2] );
					$pos += 3;
				$dlen = $b1 + 5;
				$dval = $b2;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec  .= chr($dval);
					$dval = ($dval + $dinc) & BIT8;
				}
				break;

			case 11: // fb  int16 val * ff+3
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
				$b3 = ord( $file[$pos+2] );
				$b4 = ord( $file[$pos+3] );
					$pos += 4;
				$dlen = $b1 + 3;
				$dval = ($b3 << 8) | $b2;
				$dinc = tool::sign($b4, 8);

				for ( $i=0; $i < $dlen; $i++ )
				{
					$dec  .= tool::chr($dval, 2);
					$dval = ($dval + $dinc) & BIT16;
				}
				break;

			case 12: // fc  -fff * f+4
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dlen =  ($b2 >> 4) + 4;
				$dpos = (($b2 & 0x0f) << 8 ) | $b1;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$cur = strlen($dec) - $dpos - 1;
					$dec .= $dec[$cur];
				}
				break;

			case 13: // fd  -ff * ff+20
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dlen = $b2 + 20;
				$dpos = $b1;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$cur = strlen($dec) - $dpos - 1;
					$dec .= $dec[$cur];
				}
				break;

			case 14: // fe  -f * f+3
				$b1 = ord( $file[$pos] );
					$pos++;
				$dlen = ($b1 & 0x0f) + 3;
				$dpos = ($b1 & 0xf0) >> 1;

				for ( $i=0; $i < $dlen; $i++ )
				{
					$cur = strlen($dec) - $dpos - 8;
					$dec .= $dec[$cur];
				}
				break;

			case 15: // ff  end
				goto done;

			default: // 00-ef  copy
				$dlen = $b0 + 1;
				$dec  .= tool::substr($file, $pos, $dlen);
					$pos += $dlen;
				break;
		} // switch ( $op )
	} // while ( $pos < $len )

done:
	tool::trace('== end sub_80014448()');
	$file = $dec;
}

function mana( string $fname ) : void
{
	// for /bin/*.bin
	// for /ana/etc_etc/*.prs
	// for /map/*/*.prs
	// for /wm/wmap/*.pim
	// for /wm/wmtim/wmapt*/wm_*.pim
	$file = tool::loadbak($fname);
	if ( empty($file) )
		return;

	// file must starts with 01 and ends with FF
	$len = strlen($file) - 1;
	$b1  = $file[0];
	$b2  = $file[$len];
	if ( $b1 !== "\x01" || $b2 !== "\xff" )
		return;

	mana_decode($file);
	file_put_contents($fname, $file);

	tool::trace('mana_decode', $fname, filesize($fname.'.bak'), filesize($fname));
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );
