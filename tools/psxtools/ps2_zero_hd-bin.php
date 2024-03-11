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

function tim2clut( &$file )
{
	$sz = str2int($file, 0x18, 4);
	$w = str2int($file, 0x24, 2);
	$h = str2int($file, 0x26, 2);

	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => '',
	);

	if ( ($w*$h) === $sz )
	{
		trace("detected TIM2 8-bpp\n");
		$img['cc' ] = 0x100;
		$img['pix'] = substr($file, 0x40, $sz);
		$img['pal'] = '';

		// swizzled in RGBA blocks
		//  0 1 2 3  4 5 6 7  10 11 12 13  14 15 16 17
		//  8 9 a b  c d e f  18 19 1a 1b  1c 1d 1e 1f
		//  ...
		$pal = substr($file, 0x40 + $sz, 0x400);
			ps2_alpha2x($pal);
		for ( $i=0; $i < 0x400; $i += 0x80 )
		{
			$b1 = substr($pal, $i+0x00, 0x20);
			$b2 = substr($pal, $i+0x20, 0x20);
			$b3 = substr($pal, $i+0x40, 0x20);
			$b4 = substr($pal, $i+0x60, 0x20);
			$img['pal'] .= $b1 . $b3 . $b2 . $b4;
		} // for ( $i=0; $i < 0x400; $i += 0x80 )
		$file = $img;
		return;
	}

	if ( ($w*$h) === ($sz << 1) )
	{
		trace("detected TIM2 4-bpp\n");
		$img['cc' ] = 0x10;
		$img['pix'] = substr($file, 0x40, $sz);
			bpp4to8($img['pix']);
		$img['pal'] = substr($file, 0x40 + $sz, 0x40);
			ps2_alpha2x( $img['pal'] );
		$file = $img;
		return;
	}

	if ( ($w*$h*3) === $sz )
	{
		trace("detected TIM2 RGB 24-bit\n");
		for ( $i=0; $i < $sz; $i += 3 )
		{
			$img['pix'] .= substr($file, 0x40 + $i, 3);
			$img['pix'] .= BYTE;
		}
		$file = $img;
		return;
	}

	php_error('unknown TIM2 %x x %x = %x', $w, $h, $sz);
	return;
}

function bmp76clut( &$file )
{
	$size = str2int($file, 2, 4) - 0x78;

	$pal = '';
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		$p = 0x36 + $i;
		$pal .= substr($file, $p, 3);
		$pal .= BYTE;
	} // for ( $i=0; $i < 0x40; $i += 4 )

	$pix = '';
	for ( $i=0; $i < $size; $i++ )
	{
		$p = 0x76 + $i;
		$b = ord( $file[$p] );
		$b1 = $b >> 4;
		$b2 = $b & BIT4;
		$pix .= chr($b1) . chr($b2);
	} // for ( $i=0; $i < $size; $i++ )

	$w = 0x400;
	$h = strlen($pix) / 0x400;

	$file = array(
		'cc'  => 0x10,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	return;
}
//////////////////////////////
function update_dict( &$dict, $lv, &$e )
{
	if ( $lv >= 5 )  $dict[5] = $dict[4]; // -69c0
	if ( $lv >= 4 )  $dict[4] = $dict[3]; // -69c4
	if ( $lv >= 3 )  $dict[3] = $dict[2]; // -69c8
	if ( $lv >= 2 )  $dict[2] = $dict[1]; // -69cc
	if ( $lv >= 1 )  $dict[1] = $dict[0]; // -69d0
	$dict[0] = $e; // -69d4
	return;
}

function zero_decode( &$file, $siz )
{
	$dec = '';
	trace("== begin sub_116048()\n");

	$dict = array(
		array(6, ZERO),
		array(6, ZERO),
		array(6, ZERO),
		array(6, ZERO),
		array(6, ZERO),
		array(6, ZERO),
	);
	$st = 8;
	while ( $siz > 0 )
	{
		$by = ord( $file[$st] );
			$st++;
		$cmd = $by >> 5;
		$len = $by & 0x1f;

		switch ( $cmd )
		{
			case 6: // c0
				$b = $file[$st];
					$st++;
				$e = array($cmd, $b);

				update_dict($dict, $cmd, $e);
				$dec .= str_repeat($b, $len + 2);
				$siz -= ($len + 2);
				break;
			case 7: // e0
				$b = substr($file, $st, $len + 1);
					$st += ($len + 1);
				$e = array($cmd, $b);

				update_dict($dict, $cmd, $e);
				$dec .= $b;
				$siz -= ($len + 1);
				break;
			default:
				$ref = $dict[$cmd];
				update_dict($dict, $cmd, $ref);
				switch ( $ref[0] )
				{
					case 6: // c0
						$dec .= str_repeat($ref[1], $len + 2);
						$siz -= ($len + 2);
						break;
					case 7: // e0
						$dp = $len >> 2;
						$dl = $len &  3;
						$b = substr($ref[1], $dp, $dl + 1);

						$dec .= $b;
						$siz -= ($dl + 1);
						break;
					default:
						goto done;
				} // switch ( $ref[0] )
				break;
		} // switch ( $cmd )
	} // while ( $siz > 0 )

done:
	trace("== end sub_116048()\n");
	$file = $dec;
	return;
}

function ps2zero( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$hd  = load_file("$pfx.hd");
	$bin = load_file("$pfx.bin");
	if ( empty($hd) || empty($bin) )
		return;

	$hdsz   = strlen($hd);
	$binpos = 0;
	for ( $i=0; $i < $hdsz; $i += 4 )
	{
		$sz = str2int($hd, $i, 4);
		if ( $sz < 1 )
			continue;

		$sub = substr ($bin, $binpos, $sz);
		$siz = str2int($sub, 0, 4);
		$cnt = str2int($sub, 4, 4);
		zero_decode($sub, $siz);

		if ( substr($sub,0,4) === 'TIM2' )
		{
			$fn = sprintf('%s/%04d.tim2', $pfx, $i >> 2);
			if ( $cnt !== 1 )
				return php_error('TIM2 %s  count > 1', $fn, $cnt);
			//save_file($fn, $sub);

			tim2clut($sub);
			save_clutfile("$fn.clut", $sub);
		}
		else
		if ( substr($sub,0,2) === 'BM' && str2int($sub,10,4) === 0x76 )
		{
			$fn = sprintf('%s/%04d.bmp', $pfx, $i >> 2);
			if ( $cnt !== 1 )
				return php_error('TIM2 %s  count > 1', $fn, $cnt);
			//save_file($fn, $sub);

			bmp76clut($sub);
			save_clutfile("$fn.clut", $sub);
		}
		else
		{
			$fn = sprintf('%s/%04d.%x', $pfx, $i >> 2, $cnt);
			save_file($fn, $sub);
		}

		printf("%8x , %8x , %x , %s\n", $binpos, $sz, $cnt, $fn);
		$binpos += int_ceil($sz, 0x800);
	} // for ( $i=0; $i < $hdsz; $i += 4 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2zero( $argv[$i] );

/*
gallery 02 = 222.tim , hd = 888 , bin = 2b6c800

gp = 2411f0

p261390 + 0  <= gp - 7a10 => gp - 69d4
p261390 + 4  <= gp - 7a08 => gp - 69d0
p261390 + 8  <= gp - 7a00 => gp - 69cc
p261390 + c  <= gp - 79f8 => gp - 69c8
p261390 + 10 <= gp - 79f0 => gp - 69c4
p261390 + 14 <= gp - 79e8 => gp - 69c0
*/
