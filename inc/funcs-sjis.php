<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
////////////////////////////////////////
function sjistxt( $sjis , &$len = NULL )
{
	$slen = array( 'f' => 0 , 'h' => 0 );
	if ( ! is_null($len) )
		$len = $slen;

	if ( ! defined("SJIS_HALF") )  return "";
	if ( ! defined("SJIS_ASC" ) )  return "";
	$sjis_half = file_get_contents(SJIS_HALF);
	$sjis_asc  = file_get_contents(SJIS_ASC );

	$stxt = "";

	$st = 0;
	$ed = strlen($sjis);
	while ( $st < $ed )
	{
		$b1 = ord( $sjis[$st] );
		if ( $b1 == 0 || $b1 == 0xff ) // invalid
			return "";

		if ( $b1 >= 0xe0 )
		{
			$stxt .= $sjis[$st+0];
			$stxt .= $sjis[$st+1];
			$st += 2;
			$slen['f']++;
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$p = $b1 * 2;
			$stxt .= $sjis_half[$p+0];
			$stxt .= $sjis_half[$p+1];
			$st++;
			$slen['f']++;
		}
		else
		if ( $b1 >= 0x80 )
		{
			if ( $b1 == 0x81 || $b1 == 0x82 )
			{
				$b2 = ord( $sjis[$st+1] );
				$p = ($b1 - 0x81) * 0x100 + $b2;
				if ( $sjis_asc[$p] == ZERO )
				{
					$stxt .= $sjis[$st+0];
					$stxt .= $sjis[$st+1];
					$slen['f']++;
				}
				else
				{
					$stxt .= $sjis_asc[$p];
					$slen['h']++;
				}
			}
			else
			{
				$stxt .= $sjis[$st+0];
				$stxt .= $sjis[$st+1];
				$slen['f']++;
			}
			$st += 2;
		}
		else
		{
			$stxt .= $sjis[$st];
			$st++;
			$slen['h']++;
		}
	} // while( $st < $ed )

	if ( ! is_null($len) )
		$len = $slen;
	return $stxt;
}
////////////////////////////////////////

