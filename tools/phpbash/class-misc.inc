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
class misc
{
	public static function sec2time( $sec )
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

	public static function decdigit( $int )
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

	public static function hexdigit( $int )
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
} // class misc
