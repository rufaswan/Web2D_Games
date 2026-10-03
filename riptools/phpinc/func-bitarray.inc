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

class bitarray
{
	public static function get( string &$bin, int $pos, int $bit ) : bool
	{
		if ( $pos < 0 )
			tool::error('pos < 0', $pos);
		$bypos = $pos >> 3;
		$btpos = $pos &  7;
		if ( ! isset( $bin[$bypos] ) )
			tool::error('strlen < pos', strlen($bin), $bypos);

		$c = ord( $bin[$bypos] );
		$c = ($c >> $btpos) & 1;
		return (bool)$c;
	}

	public static function get2( string &$bin, int $pos, int $bit, bool $sign=false ) : int
	{
		if ( $pos < 0 )
			tool::error('pos < 0', $pos);
		$bypos = $pos >> 3;
		$btpos = $pos &  7;
		if ( $bit < 1 )
			tool::error('bit < 1', $bit);
		$mask = (1 << $bit) - 1;

		$cnt = ($btpos + $bit + 7) >> 3;
		$int = 0;
		for ( $i=0; $i < $cnt; $i++ )
		{
			$p = $bypos + $i;
			if ( ! isset( $bin[$p] ) )
				tool::error('strlen < pos', strlen($bin), $p);
			$c = ord( $bin[$p] );

			$int |= ($c << ($i*8));
		} // for ( $i=0; $i < $cnt; $i++ )

		$dat = ($int >> $btpos) & $mask;
		if ( $sign )
		{
			$sra = (PHP_INT_SIZE * 8) - $bit;
			$dat = ($dat << $sra) >> $sra;
		}
		return $dat;
	}
	//////////////////////////////
	public static function set( string &$bin, bool $val, int $pos ) : void
	{
		if ( $pos < 0 )
			tool::error('pos < 0', $pos);
		$bypos = $pos >> 3;
		$btpos = $pos &  7;
		if ( ! isset( $bin[$bypos] ) )
			tool::error('strlen < pos', strlen($bin), $bypos);

		$c = ord( $bin[$bypos] );
		$c &= ~(1 << $btpos);
		$c |= ($val << $btpos);
		$bin[$bypos] = chr($c);
	}

	public static function set2( string &$bin, int $val, int $pos, int $bit ) : void
	{
		if ( $pos < 0 )
			tool::error('pos < 0', $pos);
		$bypos = $pos >> 3;
		$btpos = $pos &  7;
		if ( $bit < 1 )
			tool::error('bit < 1', $bit);
		$mask = (1 << $bit) - 1;

		$cnt = ($btpos + $bit + 7) >> 3;
		$int = 0;
		for ( $i=0; $i < $cnt; $i++ )
		{
			$p = $bypos + $i;
			if ( ! isset( $bin[$p] ) )
				tool::error('strlen < pos', strlen($bin), $p);
			$c = ord( $bin[$p] );

			$int |= ($c << ($i*8));
		} // for ( $i=0; $i < $cnt; $i++ )

		$dat = $int & ~($mask << $btpos);
		$val = ($val & $mask) << $btpos;
		$int = $dat | $val;

		$c = tool::chr($int, $cnt);
		tool::str_update($bin, $bypos, $c);
	}
} // class bitarray
