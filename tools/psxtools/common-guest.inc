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
// non-PlayStation One (guest systems) functions

//////////////////////////////
function substrrev( &$str, $pos, $len )
{
	$sub = substr($str, $pos, $len);
	return strrev($sub);
}

function str2big( &$str, $pos, $byte, $sign=false )
{
	php_req_bytes($byte);
	if ( ! isset($str[$pos+$byte-1]) )
		return php_error('[%x] substr %x > strlen %x', $pos, $byte, strlen($str));

	$sub = substrrev($str, $pos, $byte);
	$int = ordint($sub);
	if ( $sign )
	{
		$sra = (PHP_INT_SIZE - $byte) * 8; // shift right arithmetic
		return ( ($int << $sra) >> $sra );
	}
	return $int;
}

function chrbig( $int, $byte )
{
	$t = chrint($int, $byte);
	return strrev($t);
}
//////////////////////////////
function big2little16( &$be )
{
	$le = '';
	$len = strlen($be);
	for ( $i=0; $i < $len; $i += 2 )
		$le .= $be[$i+1] . $be[$i+0];
	return $le;
}

function big2little32( &$be )
{
	$le = '';
	$len = strlen($be);
	for ( $i=0; $i < $len; $i += 4 )
		$le .= $be[$i+3] . $be[$i+2] . $be[$i+1] . $be[$i+0];
	return $le;
}
//////////////////////////////
function iee754_float( $int, $exp_bit, $man_bit, $bias )
{
	if ( "$int" === $int )
		$int = ordint($int);

	// https://en.m.wikipedia.org/wiki/IEEE_754
	$m = (1 << $man_bit) - 1;
	$e = (1 << $exp_bit) - 1;
	$man = $int & $m;  $int >>= $man_bit;
	$exp = $int & $e;  $int >>= $exp_bit;
	$sig = $int;

	// e=0 m=0 zero
	// e=0 m=1 denormalized
	// e=1 m=0 infinity
	// e=1 m=1 not a number
	if ( $exp == $e )
		return 0.0; // infinite or not-a-number
	if ( $exp == 0 && $man == 0 )
		return 0.0; // exact zero

	//   normalized = leading 1.0
	// denormalized = leading 0.0
	$norm = ( $exp == 0 ) ? 0 : 1 << $man_bit;
	$man += $norm;

	$exp -= $bias;
	$man *= (2 ** ($exp - $man_bit));
	return ( $sig ) ? -$man : $man;
}

function float16( $int )
{
	// IEEE 754 half precision
	//   signed   bit  = 1
	//   exponent bits = 5
	//   mantissa bits = 10
	//   bias = 2^4-1 = 15
	return iee754_float($int, 5, 10, 0x0f);
}

function float32( $int )
{
	// IEEE 754 single precision
	//   signed   bit  = 1
	//   exponent bits = 8
	//   mantissa bits = 23
	//   bias = 2^7-1 = 127
	return iee754_float($int, 8, 23, 0x7f);
}

function cmp_float( $fl1, $fl2, $dec=1 )
{
	$f1 = (int)($fl1 * (10 ** $dec));
	$f2 = (int)($fl2 * (10 ** $dec));
	//    10.01  10.001
	// 1  same
	// 2  diff
	return ( $f1 === $f2 );
}
//////////////////////////////
function clut2rgba( $pal, $pix, $bgzero = true )
{
	$rgba = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $pix[$i] == ZERO )
			$rgba .= ( $bgzero ) ? substr($pal, 0, 4) : PIX_ALPHA;
		else
		{
			$p = ord( $pix[$i] ) * 4;
			if ( ! isset( $pal[$p+3] ) )
				$rgba .= PIX_ALPHA;
			else
				$rgba .= substr($pal, $p, 4);
		}
	}
	return $rgba;
}

function rgba2clut( $rgba, $bg = PIX_BLACK )
{
	$clut = array();
	if ( ! empty($bg) )
		$clut[] = $bg;
	$pix  = '';
	$siz  = strlen($rgba);
	for ( $i=0; $i < $siz; $i += 4 )
	{
		$c = substr($rgba, $i, 4);
		if ( array_search($c, $clut) === false )
			$clut[] = $c;
		$p = array_search($c, $clut);

		if ( $p > BIT8 )
		{
			php_warning('rgba2clut() clut over 256 colors');
			return -1;
		}
		$pix .= chr($p);
	}
	$clut = implode('', $clut);
	return array($clut, $pix);
}

function riprgba( &$rgba, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") )
		return '';

	$rip = '';
	for ( $y=0; $y < $h; $y++ )
	{
		$sxx = ($sy + $y) * $sw + $sx;
		$rip .= substr($rgba, $sxx*4, $w*4);
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}
//////////////////////////////
function bits8( $bit7 , $bit6 , $bit5 , $bit4 , $bit3 , $bit2 , $bit1 , $bit0 )
{
	$b7 = ($bit7 & 1) << 7;
	$b6 = ($bit6 & 1) << 6;
	$b5 = ($bit5 & 1) << 5;
	$b4 = ($bit4 & 1) << 4;
	$b3 = ($bit3 & 1) << 3;
	$b2 = ($bit2 & 1) << 2;
	$b1 = ($bit1 & 1) << 1;
	$b0 = ($bit0 & 1) << 0;
	return ( $b7|$b6|$b5|$b4|$b3|$b2|$b1|$b0 );
}
//////////////////////////////
function ps2_alpha2x( &$pal )
{
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$a = ord( $pal[$i+3] );
		$a = int_clamp($a*2, 0, BIT8);
		$pal[$i+3] = chr($a);
	}
	return;
}
//////////////////////////////
