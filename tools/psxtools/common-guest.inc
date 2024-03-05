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
function asm_haslabels( &$label, $p )
{
	$list = array();
	foreach ( $label as $k => $v )
	{
		if ( $v === $p )
			$list[] = $k;
	}
	return $list;
}

function asm_labelrange( &$label )
{
	$min = (1 << 31) - 1;
	$max = 0;
	foreach ( $label as $k => $v )
	{
		if ( $v < $min )  $min = $v;
		if ( $v > $max )  $max = $v;
	}
	return array($min,$max);
}

function asm_trace( &$label, &$oplist )
{
	if ( defined('NO_TRACE') )
		return;

	// check for invalid labels
	foreach ( $label as $k => $v )
	{
		if ( ! isset($oplist[$v]) )
			php_warning('opcode not stop @ %4x [%s]', $v, trim($k));
	}

	// to print opcodes with labels
	foreach ( $oplist as $ok => $ov )
	{
		$lab = asm_haslabels($label, $ok);
		if ( ! empty($lab) )
		{
			foreach ( $lab as $lv )
				printf("%s:\n", $lv);
		}
		printf("      %4x : %s\n", $ok, printhex($ov));
	} // foreach ( $oplist as $ok => $ov )

	return;
}
//////////////////////////////
function utf8_binhex( $bin )
{
	$len = strlen($bin);

	if ( $len < 1 )  return '';
	$b1  = ord($bin[0]);
	if ( ($b1 & 0x80) === 0 )
		return sprintf('U+%02x', $b1 & 0x7f); // U+00-U+7f

	if ( $len < 2 )  return '';
	$b2  = ord($bin[1]);
	if ( ($b1 & 0xe0) === 0xc0 )
	{
		if ( ($b2 & 0xc0) !== 0x80 )  return '';
		$b1 &= 0x1f; // 5-bit
		$b2 &= 0x3f; // 6-bit
		$hex = ($b1 << 6) | $b2;
		return sprintf('U+%04x', $hex); // U+80-U+7ff
	}

	if ( $len < 3 )  return '';
	$b3 = ord($bin[2]);
	if ( ($b1 & 0xf0) === 0xe0 )
	{
		if ( ($b2 & 0xc0) !== 0x80 )  return '';
		if ( ($b3 & 0xc0) !== 0x80 )  return '';
		$b1 &= 0x0f; // 4-bit
		$b2 &= 0x3f; // 6-bit
		$b3 &= 0x3f; // 6-bit
		$hex = ($b1 << 12) | ($b2 << 6) | $b3;
		return sprintf('U+%04x', $hex); // U+800-U+ffff
	}

	if ( $len < 4 )  return '';
	$b4  = ord($bin[3]);
	if ( ($b1 & 0xf8) === 0xf0 )
	{
		if ( ($b2 & 0xc0) !== 0x80 )  return '';
		if ( ($b3 & 0xc0) !== 0x80 )  return '';
		if ( ($b4 & 0xc0) !== 0x80 )  return '';
		$b1 &= 0x07; // 3-bit
		$b2 &= 0x3f; // 6-bit
		$b3 &= 0x3f; // 6-bit
		$b4 &= 0x3f; // 6-bit
		$hex = ($b1 << 18) | ($b2 << 12) | ($b3 << 6) | $b4;
		return sprintf('U+%06x', $hex); // U+10000-U+10ffff
	}

	// invalid
	return '';
}

function utf8_hexbin( $hex )
{
	if ( empty($hex) )
		return '';
	if ( substr($hex,0,2) !== 'U+' )
		return '';
	$hex = ltrim($hex, 'U+0');
	$dec = hexdec($hex);

	if ( $dec < 0x80 )
		return chr($dec);

	if ( $dec < 0x800 )
	{
		$b1 = ($dec >> 6) & 0x1f;  $b1 |= 0xc0;
		$b2 = ($dec >> 0) & 0x3f;  $b2 |= 0x80;
		return chr($b1) . chr($b2);
	}

	if ( $dec < 0x10000 )
	{
		$b1 = ($dec >> 12) & 0x0f;  $b1 |= 0xe0;
		$b2 = ($dec >>  6) & 0x3f;  $b2 |= 0x80;
		$b3 = ($dec >>  0) & 0x3f;  $b3 |= 0x80;
		return chr($b1) . chr($b2) . chr($b3);
	}

	if ( $dec < 0x110000 )
	{
		$b1 = ($dec >> 18) & 0x07;  $b1 |= 0xf0;
		$b2 = ($dec >> 12) & 0x3f;  $b2 |= 0x80;
		$b3 = ($dec >>  6) & 0x3f;  $b3 |= 0x80;
		$b4 = ($dec >>  0) & 0x3f;  $b4 |= 0x80;
		return chr($b1) . chr($b2) . chr($b3) . chr($b4);
	}

	// invalid
	return '';
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
	$pal = array();
	if ( ! empty($bg) )
		$pal[] = $bg;

	$pix = '';
	$siz = strlen($rgba);
	for ( $i=0; $i < $siz; $i += 4 )
	{
		$c = substr($rgba, $i, 4);
		$p = array_search($c, $pal);
		if ( $p === false )
		{
			$p = count($pal);
			$pal[] = $c;
		}

		if ( $p > BIT8 )
		{
			php_warning('rgba2clut() clut over 256 colors');
			return -1;
		}
		$pix .= chr($p);
	} // for ( $i=0; $i < $siz; $i += 4 )
	return array(implode('',$pal), $pix);
}

function riprgba( &$rgba, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined('DRY_RUN') )
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

function swizzle_bitmask( $int, $mask )
{
	$bit = 0;
	$sll = 0;
	while (1)
	{
		// use < 1 to handle negatives
		if ( $mask < 1 )  break; // no more bitwise AND
		if ( $int  < 1 )  break; // all zero anyway

		$bi = $int  & 1;
		$bm = $mask & 1;
			$int  >>= 1;
			$mask >>= 1;

		if ( $bm )
		{
			$bit |= ($bi << $sll);
			$sll++;
		}
	} // while (1)
	return $bit;
}

function pixdec_copy44( &$bc, $dx, $dy )
{
	if ( $dx >= $bc['w'] )  return;
	if ( $dy >= $bc['h'] )  return;

	// in 4x4 pixels , for DXT1-5 or BC1-7 swizzled
	$row = 4 * $bc['bpp']; // 4 pixel
	for ( $y=0; $y < 4; $y++ )
	{
		$dyy = ($dy*4 + $y  ) * $bc['w']*4;
		$dxx =  $dx*4 + $dyy;
		$s = substr($bc['pix'], $bc['pos'], $row);
			$bc['pos'] += $row;
		str_update($bc['dec'], $dxx*$bc['bpp'], $s);
	} // for ( $y=0; $y < 4; $y++ )
	return;
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
