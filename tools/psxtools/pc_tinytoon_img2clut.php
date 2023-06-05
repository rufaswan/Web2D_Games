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
require 'common-guest.inc';

function spr_rle_palpix( $str, &$pal, $alpha )
{
	$len = strlen($str);
	$pix = '';
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord($str[$i]);
		$c = substr($pal, $b*4, 4);
		if ( $alpha !== -1 )
		{
			$a = (int)($alpha * BIT8);
			$c[3] = chr($a);
		}
		$pix .= $c;
	}
	return $pix;
}

function spr_rle_decode( &$file, $pos, $size, &$pal )
{
	$dec = '';
	trace("== begin sub_408e60()\n");

	while ( $size > 0 )
	{
		$flg = ord( $file[$pos] );
			$pos++;
		$bycod = ($flg - 0xf6) & 0xff;
		//trace("%8x  %2x = %2x\n", $pos-1, $flg, $bycod);

		switch ( $bycod )
		{
			case 0: // f6 , dummy
			case 1: // f7 , dummy
			case 19: // 09 , dummy
				break;

			case 2: // f8 , alpha 0/7
			case 3: // f9 , alpha 1/7
			case 4: // fa , alpha 2/7
			case 5: // fb , alpha 3/7
			case 6: // fc , alpha 4/7
			case 7: // fd , alpha 5/7
			case 8: // fe , alpha 6/7
			case 9: // ff , alpha 7/7
			// sub_408dc0(op2, opedi, op1, u1, u2)
			//   ebx = op1 & 7
			//   ecx = ebx ^ 7 === 7 - ebx
			//   eax = [op2  *4] * ebx
			//   edx = [opedi*4] * ecx
			//
			// ??? 5-bit RGB ???
			//   pix = (eax + edx) / 38
			//   esi <<= 5 ,
			//   esi += pix
					$pos += 1; // lodsb
				$b1 = ($flg & 7) / 7;
				$b2 = $file[$pos-1];

				$dec .= spr_rle_palpix($b2, $pal, $b1);
				$size--;
				break;


			case 15: // 05 , duplicate
				$prev = substr($dec, strlen($dec)-4, 4);
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2; // lodsw
				$b = $b1 | ($b2 << 8);

				$dec .= str_repeat($prev, $b);
				$size -= $b;
				break;
			case 16: // 06 , duplicate
				$prev = substr($dec, strlen($dec)-4, 4);
				$b = ord( $file[$pos] );
					$pos += 1; // lodsb

				$dec .= str_repeat($prev, $b);
				$size -= $b;
				break;


			case 14: // 04 , skip
				$dec .= PIX_ALPHA;
				$size--;
				break;
			case 17: // 07 , skip
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2; // lodsw
				$b = $b1 | ($b2 << 8);

				$dec .= str_repeat(PIX_ALPHA, $b);
				$size -= $b;
				break;
			case 18: // 08 , skip
				$b = ord( $file[$pos] );
					$pos += 1; // lodsb

				$dec .= str_repeat(PIX_ALPHA, $b);
				$size -= $b;
				break;


			case 11: // 01 , copy
				$b = substr($file, $pos, 1);
					$pos += 1; // movsb

				$dec .= spr_rle_palpix($b, $pal, -1);
				$size -= 1;
				break;
			case 12: // 02 , copy
				$b = substr($file, $pos, 2);
					$pos += 2; // movsb * 2

				$dec .= spr_rle_palpix($b, $pal, -1);
				$size -= 2;
				break;
			case 13: // 03 , copy
				$b = substr($file, $pos, 3);
					$pos += 3; // movsb * 3

				$dec .= spr_rle_palpix($b, $pal, -1);
				$size -= 3;
				break;
			case 10: // 00 , copy
			default: // 0a-f5 , copy
				$b = substr($file, $pos-1, 4);
					$pos += 3; // movsd

				$dec .= spr_rle_palpix($b, $pal, -1);
				$size -= 4;
				break;
		} // switch ( $bycod )
	} // while ( $size > 0 )

	trace("== end sub_408e60()\n");
	return $dec;
}

function spr_lz_decode( &$file, $pos, $size )
{
	$dec = '';
	trace("== begin sub_40ff70()\n");

	$dict = str_repeat(' ', 0x1000);
	$doff = 0xfee;

	$bycod = 0;
	$bylen = 0;

	while ( $size > 0 )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$pos] );
				$pos++;
			$bylen = 8;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;
		if ( $flg )
		{
			$b1 = $file[$pos];
				$pos++;

			$dict[$doff] = $b1;
				$doff = ($doff + 1) & 0xfff;

			$dec .= $b1;
			$size--;
		}
		else
		{
			$b1 = ord($file[$pos+0]);
			$b2 = ord($file[$pos+1]);
				$pos += 2;

			$dpos = (($b2 & 0xf0) << 4) | $b1;
			$dlen =  ($b2 & 0x0f) + 2;
			for ( $i=0; $i <= $dlen; $i++ )
			{
				$b1 = $dict[$dpos];
					$dpos = ($dpos + 1) & 0xfff;
				$dict[$doff] = $b1;
					$doff = ($doff + 1) & 0xfff;

				$dec .= $b1;
				$size--;
			} // for ( $i=0; $i < $dlen; $i++ )
		}
	} // while ( $size > 0 )

	trace("== end sub_40ff70()\n");
	return $dec;
}

function vflip( &$pix, $w, $h )
{
	if ( ($w*$h) > strlen($pix) )
		return php_error('vflip() w*h > pix', $w, $h, strlen($pix));

	$bak = $pix;
	for ( $y=0; $y < $h; $y++ )
	{
		$dy  = $y * $w;
		$rdy = ($h - 1 - $y) * $w;

		$b = substr($bak, $dy, $w);
		str_update($pix, $rdy, $b);
	}
	return;
}

function tinytoon( $pal, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ty = ord( $file[0] );
	$w = str2int($file, 1, 2);
	$h = str2int($file, 3, 2);
	printf("ty %x  w %x  h %x  %s\n", $ty, $w, $h, $fname);

	switch ( $ty )
	{
		case 0xb1: // buster hansel  lz
			$img = array(
				'cc'  => strlen($pal) >> 2,
				'w'   => $w,
				'h'   => $h,
				'pal' => $pal,
				'pix' => spr_lz_decode($file, 13, $w*$h),
			);
			vflip($img['pix'], $w, $h);
			return save_clutfile("$fname.clut", $img);

		case 0x31: // buster  raw
			$img = array(
				'cc'  => strlen($pal) >> 2,
				'w'   => $w,
				'h'   => $h,
				'pal' => $pal,
				'pix' => substr($file, 5),
			);
			vflip($img['pix'], $w, $h);
			return save_clutfile("$fname.clut", $img);

		case 0x1d: // buster hansel  rle
		case 0x57: // buster hansel  rle
		case 0x17: // buster hansel  rle
		case 0x5d: // hansel  rle
			$pix = spr_rle_decode($file, 9, $w*$h, $pal);
			$img = array(
				'w'   => $w,
				'h'   => $h,
			);

			$clut = rgba2clut($pix);
			if ( $clut === -1 )
			{
				$img['pix'] = $pix;
				return save_clutfile("$fname.rgba", $img);
			}
			else
			{
				$img['cc' ] = strlen($clut[0]) >> 2;
				$img['pal'] = $clut[0];
				$img['pix'] = $clut[1];
				return save_clutfile("$fname.clut", $img);
			}
			break;

		default:
			return php_error('unknown spr type = %x', $ty);
	} // switch ( $ty )
	return;
}

printf("%s  PALETTE/IMAGE...\n", $argv[0]);
$pal = '';
for ( $i=1; $i < $argc; $i++ )
{
	$fn = $argv[$i];
	if ( ! is_file($fn) )
		continue;

	$ext = substr($fn, strrpos($fn,'.')+1);
		$ext = strtolower($ext);

	switch ( $ext )
	{
		case 'pal':
			$dat = file_get_contents($fn);
			if ( strlen($dat) === 0x400 )
				$pal = $dat;
			break;

		case 'img':
			if ( empty($pal) )
				continue;
			tinytoon($pal, $fn);
			break;
	} // switch ( $ext )
} // for ( $i=1; $i < $argc; $i++ )
