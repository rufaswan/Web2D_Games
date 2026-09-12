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

class bigint
{
	public static function ord( string $hex ) : array
	{
		if ( strlen($hex) & 1 )
			$bin = hex2bin('0'.$hex);
		else
			$bin = hex2bin($hex);

		if ( $bin === false )
			return [];

		$arr = [];
		$len = strlen($bin);
		while ( $len > 0 )
		{
			$len--;
			$arr[] = ord( $bin[$len] );
		}
		return $arr;
	}

	public static function chr( array $arr ) : string
	{
		self::norm($arr);
		$hex = '';
		while ( ! empty($arr) )
		{
			$b = array_pop($arr);
			$hex .= sprintf('%02x', $b);
		} // while ( ! empty($arr) )
		return $hex;
	}
	//////////////////////////////
	public static function norm( array &$array ) : void
	{
		$carry = 0;
		foreach ( $array as $ak => $av )
		{
			$array[$ak] += $carry;
			$carry = $array[$ak] >> 8;
			$array[$ak] &= BIT8;
		}
		// int overflow
		if ( $carry !== 0 )
			$array[] = $carry;
	}

	public static function size( array &$a, int $byte ) : void
	{
		while ( count($a) < $byte )
			$a[] = 0;
		array_splice($a, $byte);
	}

	public static function same_size( array &$a, array &$b ) : void
	{
		// $a same size as $b
		while ( count($a) < count($b) )
			$a[] = 0;
		while ( count($b) < count($a) )
			$b[] = 0;
	}
	//////////////////////////////
	public static function not( array $a ) : array
	{
		foreach ( $a as $k => $v )
			$a[$k] = (~$v) & BIT8;
		return $a;
	}

	public static function and() : array
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			self::same_size($a, $arg);
			foreach ( $a as $k => $v )
				$a[$k] &= $arg[$k];
		}
		return $a;
	}

	public static function xor() : array
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			self::same_size($a, $arg);
			foreach ( $a as $k => $v )
				$a[$k] ^= $arg[$k];
		}
		return $a;
	}

	public static function or() : array
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			self::same_size($a, $b);
			foreach ( $a as $k => $v )
				$a[$k] |= $arg[$k];
		}
		return $a;
	}
	//////////////////////////////
	public static function shift_left( array $a, int $bit ) : array
	{
		while ( $bit >= 8 )
		{
			$bit -= 8;
			array_unshift($a, 0);
		}
		foreach ( $a as $k => $v )
			$a[$k] <<= $bit;

		self::norm($a);
		return $a;
	}

	public static function shift_right( array $a, int $bit ) : array
	{
		while ( $bit >= 8 )
		{
			$bit -= 8;
			array_shift($a);
		}
		$mask = (1 << $bit) - 1;
		foreach ( $a as $k => $v )
		{
			$carry = $v & $mask;
			$a[$k] >>= $bit;
			if ( $k > 0 )
				$a[$k-1] |= ($carry << (8-$bit));
		}

		self::norm($a);
		return $a;
	}

	public static function rotate_left( array $a, int $bit, int $byte ) : array
	{
		$l = self::shift_left ($a, $bit);
		$r = self::shift_right($a, $byte*8-$bit);
		self::size($l, $byte);
		self::size($r, $byte);
		return self::or($l, $r);
	}

	public static function c_rotate_right( array $a, int $bit, int $byte ) : array
	{
		$l = self::shift_left ($a, $byte*8-$bit);
		$r = self::shift_right($a, $bit);
		self::size($l, $byte);
		self::size($r, $byte);
		return self::or($l, $r);
	}
	//////////////////////////////
	public static function add() : array
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			self::same_size($a, $arg);
			foreach ( $a as $k => $v )
				$a[$k] += $arg[$k];
			self::norm($a);
		}
		return $a;
	}

	public static function mult() : array
	{
		$all = func_get_args();
		$a   = array_shift($all);

		self::norm($a);
		$c_a = count($a);
		$res = [];
		foreach ( $all as $arg )
		{
			self::norm($arg);
			$c_b = count($arg);
			while ( count($res) < ($c_a+$c_b) )
				$res[] = 0;

			//      112233
			//*     332211
			//------------
			//     1234563
			//   2468ac6
			// 369d029
			//------------
			//=36c17b70b63
			foreach ( $arg as $bk => $bv )
			{
				foreach ( $a as $ak => $av )
					$res[$ak+$bk] += ($av*$bv);
			} // foreach ( $arg as $bk => $bv )

			self::norm($res);
		}
		return $res;
	}

} // class bigint
