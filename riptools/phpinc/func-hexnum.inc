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

class hexnum
{
	// dump series
	public static function dump( string &$file ) : string
	{
		$txt = '';
		$len = strlen($file);
		for ( $i=0; $i < $len; $i++ )
		{
			$c = ord($file[$i]);
			if ( $c === 0 )
				$txt .= '-- ';
			else
				$txt .= sprintf('%2x ', $c);
		} // for ( $i=0; $i < $len; $i++ )
		return $txt;
	}

	public static function dump_r( string &$file, int $rowsz ) : string
	{
		$loop = (int)(strlen($file) / $rowsz);
		$dig  = self::hexdigit($loop);

		$txt = '';
		$p = 0;
		for ( $i=0; $i < $loop; $i++ )
		{
			$sub = substr($file, $p, $rowsz);
				$p += $rowsz;
			$txt .= sprintf("%{$dig}x : %s\n", $i, self::dump($sub));
		} // for ( $i=0; $i < $loop; $i++ )
		return $txt;
	}
	//////////////////////////////
	// digit series (for printf %{$digit}d and %0{$digit}d)
	public static function decdigit( int $int ) : int
	{
		$dig = 1;
		if ( $int < 0 )
		{
			$dig++;
			$int = -$int;
		}
		while ( $int > 0 )
		{
			$dig++;
			$int = (int)($int * 0.1);
		}
		return $dig;
	}

	public static function hexdigit( int $int ) : int
	{
		$dig = 1;
		if ( $int < 0 )
		{
			$dig++;
			$int = -$int;
		}
		while ( $int > 0 )
		{
			$dig++;
			$int >>= 4;
		}
		return $dig;
	}
	//////////////////////////////
	// bitmask series
	public static function swizzle_setbit( int $xy, int $mask ) : int
	{
		$int = 0;
		$sll = 0;
		while ( $xy > 0 )
		{
			// no more bits
			if ( $mask < 1 )
				break;

			$bit_mask = $mask & 1;
				$mask >>= 1;

			if ( $bit_mask )
			{
				$bit_xy = $xy & 1;
					$xy >>= 1;
				$int |= ($bit_xy << $sll);
			}

			$sll++;
		} // while ( $xy > 0 )
		return $int;
	}

	public static function swizzle_getbit( int $int, int $mask ) : int
	{
		$xy  = 0;
		$sll = 0;
		while ( $int > 0 )
		{
			// no more bits
			if ( $mask < 1 )
				break;

			$bit_int  = $int  & 1;
			$bit_mask = $mask & 1;
				$int  >>= 1;
				$mask >>= 1;

			if ( $bit_mask )
			{
				$xy |= ($bit_int << $sll);
				$sll++;
			}
		} // while ( $int > 0 )
		return $xy;
	}

	public static function swizzle_xy2id( int $x, int $y, int $mask_x, int $mask_y ) : int
	{
		if ( $x < 0 )  tool::error('x < 0', $x);
		if ( $y < 0 )  tool::error('y < 0', $y);

		$mask = $mask_x + $mask_y;
		if ( ($mask+1) & $mask ) // 100 & ff == 0
			tool::error('not mask', $mask, $mask_x, $mask_y);

		$int = 0;
		$int |= self::swizzle_setbit($x, $mask_x);
		$int |= self::swizzle_setbit($y, $mask_y);
		return $int;
	}

	public static function swizzle_id2xy( int $int, int $mask_x, int $mask_y ) : array
	{
		if ( $int < 0 )  tool::error('int < 0', $int);

		$mask = $mask_x + $mask_y;
		if ( ($mask+1) & $mask ) // 100 & ff == 0
			tool::error('not mask', $mask, $mask_x, $mask_y);

		$xy = [
			self::swizzle_getbit($int, $mask_x),
			self::swizzle_getbit($int, $mask_y),
		];
		return $xy;
	}

	public static function swizzle_map_dump( array &$map, int $w, int $h ) : void
	{
		$dgt = self::hexdigit($w * $h - 1);
		$p = 0;
		for ( $y=0; $y < $h; $y++ )
		{
			echo ' | ';
			for ( $x=0; $x < $w; $x++ )
			{
				if ( isset($map[$p]) )
					printf("%{$dgt}x ", $map[$p]);
				else
					printf("%{$dgt}s ", '-');

				$p++;
			} // for ( $x=0; $x < $w; $x++ )
			echo "\n";
		} // for ( $y=0; $y < $h; $y++ )
	}

	public static function swizzle_map( array &$map, int $w, int $h, bool $is_dump=false ) : int
	{
		$cnt = $w * $h;
		if ( count($map) < $cnt )
			return tool::warning('map count', count($map), $cnt);

		$sumkey = 0;
		$sumval = 0;
		while ( $cnt > 0 )
		{
			$cnt--;
			if ( ! isset($map[$cnt]) )
				return tool::warning('map has gap', $cnt);
			$val = $map[$cnt];
			if ( (int)$val !== $val )
				return tool::warning('map not int', $cnt, $val);
			$sumkey += $cnt;
			$sumval += $val;
		} // while ( $count > 0 )

		if ( $sumkey !== $sumval )
			return tool::warning('map val has gap', $sumkey, $sumval);

		if ( $is_dump )
			self::swizzle_map_dump($map, $w, $h);
		return 0;
	}
	//////////////////////////////
	// date series
	public static function sec2time( float $sec ) : string
	{
		// 01:39:20
		// 01:39:00
		// 01:00:20
		//    39:20
		//    39:00
		//       20
		$time = '';

		$t = 0;
		while ( $sec >= 3600 ) // hour
		{
			$t++;
			$sec -= 3600;
		}
		if ( $t > 0 || ! empty($time)  )
			$time .= sprintf('%02d:', $t);

		$t = 0;
		while ( $sec >= 60 ) // min
		{
			$t++;
			$sec -= 60;
		}
		if ( $t > 0 || ! empty($time) )
			$time .= sprintf('%02d:', $t);

		$time .= sprintf('%02d',$sec);
		return $time;
	}


} // class hexnum
