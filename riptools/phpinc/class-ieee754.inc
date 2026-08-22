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
declare(strict_types=1);

class ieee754
{
	public static function ord( string $bin, bool $sign=false ) : int
	{
		// =big endian ord
		// 00 11 22 33 = 112233
		$bin = ltrim ($bin, ZERO); // skip ord zero
		$len = strlen($bin);
		$int = 0;
		for ( $i=0; $i < $len; $i++ )
		{
			$int <<= 8;
			$int |= ord( $bin[$i] );
		}

		$len = strlen($bin);
		if ( $sign )
		{
			$sra = (PHP_INT_SIZE - $len) * 8; // shift right arithmetic
			return ( ($int << $sra) >> $sra );
		}
		return $int;
	}

	public static function ordstr( string &$str, int $pos, int $byte=4, bool $sign=false ) : int
	{
		if ( PHP_INT_SIZE < $byte )
			return 0;
		$sub = substr($str, $pos, $byte);
		if ( strlen($sub) !== $byte )
			return 0;
		return self::ord($sub, $sign);
	}

	public static function chr( float $int, int $byte=4 ) : string
	{
		// =big endian chr
		// 112233 = 00 11 22 33
		$bin = '';
		$bit = $byte * 8;
		while ( $bit > 0 )
		{
			$bit -= 8;
			$c = ($int >> $bit) & 0xff;
			$bin .= chr($c);
		}
		return $bin;
	}
	//////////////////////////////
	public static function decode( int $int, int $exp_bit, int $man_bit, int $bias ) : float
	{
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
		if ( $exp === $e )
			return 0.0; // infinite or not-a-number
		if ( $exp === 0 && $man === 0 )
			return 0.0; // exact zero

		//   normalized = leading 1.0
		// denormalized = leading 0.0
		// man = -1mm mmmm mmmm
		$norm = ( $exp === 0 ) ? 0 : 1 << $man_bit;
		$man += $norm;

		// exp = 0 to 1f = -f to +f
		// flt = (man / (1 <<  man_bit)) * (2 ** exp)
		//     = (man * (2 ** -man_bit)) * (2 ** exp)
		//     =  man * (2 ** (-man_bit + exp))
		$exp -= $bias;
		$man *= (2 ** ($exp - $man_bit));
		return ( $sig ) ? -$man : $man;
	}

	public static function encode( float $flt, int $exp_bit, int $man_bit, int $bias ) : int
	{
		$sig = 0;
		if ( $flt < 0.0 )
		{
			$sig = 1;
			$flt = -$flt;
		}

		// bias = +f to -f
		$max = $bias;
		for ( $i=0; $i < $max; $i++ )
		{
			// normalize = leading 1.0
			$int = (int)$flt;
			if ( $int > 1 )
			{
				$bias++;
				$flt *= 0.5;
			}
			if ( $int < 1 )
			{
				$bias--;
				$flt *= 2.0;
			}
		} // for ( $i=0; $i < $max; $i++ )

		if ( (int)$flt > 1 )
			return tool::warning('float over bias', $flt, $bias);

		$m = 1 << $man_bit;
		$e = 1 << $exp_bit;
		$man = ($flt * $m) & ($m - 1);
		$exp =  $bias      & ($e - 1);

		$int = $sig;
		$int <<= $exp_bit;  $int |= $exp;
		$int <<= $man_bit;  $int |= $man;
		return $int;
	}
	//////////////////////////////
	public static function to( int $int, int $byte ) : float
	{
		if ( $int === 0 ) // zero decode shortcut
			return 0.0;

		$mbit = [
			// byte => [sign , exponent , mantissa]
			2  => [1 ,  5 ,  10], // half
			4  => [1 ,  8 ,  23], // single
			8  => [1 , 11 ,  52], // double
			16 => [1 , 15 , 112], // quadruple
			32 => [1 , 19 , 236], // octuple
		];
		if ( ! isset($mbit[$byte]) )
			tool::error('byte unknown', $byte);

		list($s,$e,$m) = $mbit[$byte];
		$bias = (1 << ($e-1)) - 1;
		return self::decode($int, $e, $m, $bias);
	}

	public static function from( float $flt, int $byte ) : int
	{
		$mbit = [
			// byte => [sign , exponent , mantissa]
			2  => [1 ,  5 ,  10], // half
			4  => [1 ,  8 ,  23], // single
			8  => [1 , 11 ,  52], // double
			16 => [1 , 15 , 112], // quadruple
			32 => [1 , 19 , 236], // octuple
		];
		if ( ! isset($mbit[$byte]) )
			tool::error('byte unknown', $byte);

		list($s,$e,$m) = $mbit[$byte];
		$bias = (1 << ($e-1)) - 1;
		return self::encode($flt, $e, $m, $bias);
	}

	public static function mtc1( int $int, int $byte ) : float
	{
		// MIPS asm = move to coprocessor 1 (FPU  Floating-Point Unit)
		return self::to($int, $byte);
	}
	public static function mfc1( float $flt, int $byte ) : int
	{
		// MIPS asm = move from coprocessor 1 (FPU  Floating-Point Unit)
		return self::from($flt, $byte);
	}

	// mixed $bin
	//   string '38000000'         , return float 1.0
	//   string "\x38\x00\x00\x00" , return float 1.0
	//   int    0x38000000         , return float 1.0
	//   any                       , return string "\x00\x00"
	public static function convert( $bin )
	{
		// if string
		if ( "$bin" === $bin )
		{
			// if string '38000000'
			$b = @hex2bin($bin);
			if ( $b !== false )
				$bin = $b;

			// if string "\x38\x00\x00\x00"
			$len = strlen($bin);
			$b   = self::ord($bin);
			return self::to($b, $len);
		}

		// if int 0x38000000 or 939524096
		if ( (int)$bin === $bin )
			return self::to($bin, PHP_INT_SIZE);

		// if float 1.0
		$int = self::from(0.0 + $bin, PHP_INT_SIZE);
		return self::chr($int, PHP_INT_SIZE);
	}
	//////////////////////////////
	public static function epsilon() : float
	{
		$x = 1.0;
		while (1)
		{
			$dx = $x * 0.5;
			if ( ($dx+1.0) === 1.0 )
				return $x;
			$x = $dx;
		} // while (1)
	}
} // class ieee754
