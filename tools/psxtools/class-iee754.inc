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

class iee754_decode
{
	static private function ord( $bin )
	{
		$len = strlen($bin);
		$int = 0;
		while ( $len > 0 )
		{
			$len--;
			$int <<= 8;
			$int |= ord( $bin[$len] );
		}
		return $int;
	}

	static public function dec( $bin, $exp_bit, $man_bit, $bias )
	{
		if ( "$bin" !== $bin )
			return 0;

		$hex = @hex2bin($bin);
		if ( $hex === false )
			$bin = self::ord($bin);
		else
			$bin = self::ord($hex);

		// https://en.m.wikipedia.org/wiki/IEEE_754
		$m = (1 << $man_bit) - 1;
		$e = (1 << $exp_bit) - 1;
		$man = $bin & $m;  $bin >>= $man_bit;
		$exp = $bin & $e;  $bin >>= $exp_bit;
		$sig = $bin;

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

	static public function float16( $bin )
	{
		// IEEE 754 half precision
		//   signed   bit  = 1
		//   exponent bits = 5
		//   mantissa bits = 10
		//   bias = 2^4-1 = 15
		return self::dec($bin, 5, 10, 0x0f);
	}

	static public function float32( $bin )
	{
		// IEEE 754 single precision
		//   signed   bit  = 1
		//   exponent bits = 8
		//   mantissa bits = 23
		//   bias = 2^7-1 = 127
		return self::dec($bin, 8, 23, 0x7f);
	}
} // class iee754_decode

class iee754_encode
{
	static private function chr( $int )
	{
		$bin = '';
		while ( $int > 0 )
		{
			$c = $int & 0xff;
			$int >>= 8;
			$bin .= chr($c);
		}
		return $bin;
	}

	static public function enc( $num, $exp_bit, $man_bit, $bias )
	{
		$sig = 0;
		if ( $num < 0.0 )
		{
			$sig = 1;
			$num = -$num;
		}

		while ( $num > 2.0 )
		{
			$bias++;
			$num *= 0.5;
		}
		while ( $num < 1.0 )
		{
			$bias--;
			$num *= 2.0;
		}

		$m = 1 << $man_bit;
		$e = 1 << $exp_bit;
		$man = ($num * $m) & ($m - 1);
		$exp =  $bias      & ($e - 1);

		$bin = 0;
		$bin |= $sig;  $bin <<= $exp_bit;
		$bin |= $exp;  $bin <<= $man_bit;
		$bin |= $man;
		return self::chr($bin);
	}

	static public function float16( $num )
	{
		// IEEE 754 half precision
		//   signed   bit  = 1
		//   exponent bits = 5
		//   mantissa bits = 10
		//   bias = 2^4-1 = 15
		return self::enc($num, 5, 10, 0x0f);
	}

	static public function float32( $num )
	{
		// IEEE 754 single precision
		//   signed   bit  = 1
		//   exponent bits = 8
		//   mantissa bits = 23
		//   bias = 2^7-1 = 127
		return self::enc($num, 8, 23, 0x7f);
	}
} // class iee754_decode

/*
// 43fa0000 ==  500.0
// 44fa0000 == 2000.0
echo "dec\n";
//$b = iee754_decode::float32("\x00\x00\xfa\x43");
$b = iee754_decode::float32('0000fa43');
var_dump($b, (int)$b);

echo "enc\n";
$b = iee754_encode::float32(500.0);
var_dump( bin2hex($b) );
*/
