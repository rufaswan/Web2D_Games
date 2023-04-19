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

function burst_decode( &$file, $pos )
{
	$dec = '';
	trace("== begin sub_800157e8()\n");

	while (1)
	{
		$bycod = ord( $file[$pos] );
			$pos++;
		$cod1 = ($bycod >> 0) & 0xf; // v1
		$cod2 = ($bycod >> 4) & 0xf; // a3

		if ( $cod1 === 0 )
		{
			$b1 = ord( $file[$pos] );
				$pos++;

			if ( $b1 === 0 )
			{
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$cod1 = ($b1 << 8) | $b2;
			}
			else
			if ( $b1 < 0x10 )
			{
				$b2 = ord( $file[$pos] );
					$pos++;
				$cod1 = ($b1 << 8) | $b2;
			}
			else
				$cod1 = $b1;
		}

		switch ( $cod2 )
		{
			case 0:
				$b1 = $file[$pos];
					$pos++;
				$dec .= str_repeat($b1, $cod1+2);
				break;

			case 1:
				$b1 = ord( $file[$pos] );
					$pos++;
				$dpos = $b1;
				$dlen = $cod1 + 2;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
				}
				break;

			case 2:
				if ( $cod1 === 1 )
					goto done;

				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;
				$dpos = ($b1 << 8) | $b2;
				$dlen = $cod1 + 2;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
				}
				break;

			case 3: case 4: case 5: case 6:
				$b1 = $cod1 & 8;
				$b2 = $cod2 - 3;
				$b3 = ord( $file[$pos] );
					$pos++;

				$dpos = (($b1 << 5) | ($b2 << 9) | $b3) + 0x1100;
				$dlen = ($cod1 & 7) + 2;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
				}
				break;

			case 7:
				$dec .= substr($file, $pos, $cod1);
					$pos += $cod1;
				break;

			default: // 8 9 a b c d e f
				$b1 = $bycod & 0x78;
				$b2 = ord( $file[$pos] );
					$pos++;

				$dpos = (($b1 << 5) | $b2) + 0x100;
				$dlen = ($cod1 & 7) + 2;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
				}
				break;
		} // switch ( $cod2 )
	} // while (1)

done:
	trace("== end sub_800157e8()\n");
	return $dec;
}
//////////////////////////////
function burst_tim_79( &$file )
{
	// 8-bpp
	$pos = 8;
		$palsz = str2int($file, 8, 3);
		$pal = substr($file, $pos + 12, $palsz - 12);
			$pal = pal555($pal);
		$pos += $palsz;

		$w = str2int($file, $pos+ 8, 2) << 1;
		$h = str2int($file, $pos+10, 2);
		$pos += 12;
	$dec = burst_decode($file, $pos);

	$pix = $dec;
	$pal[3] = ZERO;

	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	return $img;
}

function burst_tim_71( &$file )
{
	// 8-bpp , no palette
	$pos = 8;
		$w = str2int($file, $pos+ 8, 2) << 1;
		$h = str2int($file, $pos+10, 2);
		$pos += 12;
	$dec = burst_decode($file, $pos);

	$pix = $dec;
	$pal = grayclut(0x100);
	$pal[3] = ZERO;

	$img = array(
		'cc'  => 0x100,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	return $img;
}
//////////////////////////////
