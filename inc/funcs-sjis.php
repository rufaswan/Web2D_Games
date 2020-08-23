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
/*
REQUIRED defines
define("SJIS_HALF" , "sjis_half.inc" );
define("SJIS_ASCII", "sjis_ascii.inc");
 */
////////////////////////////////////////
function sjis_substr0( &$file, $pos )
{
	$len = 0;
	while (1)
	{
		$b1 = ord( $file[$pos+$len] );
		if ( $b1 >= 0xe0 )
			$len += 2;
		else
		if ( $b1 >= 0xa0 )
			$len++;
		else
		if ( $b1 >= 0x80 )
			$len += 2;
		else
		if ( $b1 == 0x20 )
			$len++;
		else
			break;
	}
	return substr($file, $pos, $len);
}

function sjis_strlen( $sjis )
{
	$len = array( 'a' => 0, 'j' => 0 );
	$ed = strlen($sjis);
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = ord( $sjis[$st] );
		if ( $b1 >= 0xe0 )
		{
			$st += 2;
			$len['j']++;
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$st += 1;
			$len['j']++;
		}
		else
		if ( $b1 >= 0x80 )
		{
			$st += 2;
			$len['j']++;
		}
		else
		{
			$st += 1;
			$len['a']++;
		}
	} // while ( $st < $ed )
	return $len;
}

function sjis_tidy( $sjis )
{
	// tidy up half-width katakana as full-width hiragana
	// and full-width ASCII as half-width ASCII
	req_define("SJIS_HALF");
	req_define("SJIS_ASCII");
	$sjis_half  = file_get_contents(SJIS_HALF);
	$sjis_ascii = file_get_contents(SJIS_ASCII);

	$ed = strlen($sjis);
	$st = 0;
	$jp = "";
	while ( $st < $ed )
	{
		$b1 = ord( $sjis[$st] );
		if ( $b1 >= 0xe0 )
		{
			$jp .= $sjis[$st+0] . $sjis[$st+1];
			$st += 2;
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$p = $b1 * 2;
			$jp .= $sjis_half[$p+0] . $sjis_half[$p+1];
				$st++;
		}
		else
		if ( $b1 >= 0x80 )
		{
			$p = ($b1 << 8) | ord( $sjis[$st+1] );
			$p = $sjis_ascii[ $p - 0x8100];
			if ( $p != "" || $p != ZERO )
				$jp .= $p;
			else
				$jp .= $sjis[$st+0] . $sjis[$st+1];
			$st += 2;
		}
		else
		{
			$jp .= $sjis[$st];
			$st += 1;
		}
	} // while ( $st < $ed )
	return $jp;
}
////////////////////////////////////////
