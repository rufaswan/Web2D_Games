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
define("SJIS_HALF", "sjis_half.inc" );
define("FUNC_ICONV",  "iconv");
 */
////////////////////////////////////////
function subsjis0( &$file, $pos )
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

function sjis2utf8( $sjis )
{
	req_define("FUNC_ICONV");
	req_define("SJIS_HALF");
	$sjis_half = file_get_contents(SJIS_HALF);
	$iconv = FUNC_ICONV;
	$charset = "CP932";

	$utf = "";
	$ed = strlen($sjis);
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = ord( $sjis[$st] );
		if ( $b1 >= 0xe0 ) // IBM/NEC extension
		{
			$s = $sjis[$st+0] . $sjis[$st+1];
				$st += 2;
			$r = '?';
			if ( $r == '?' )  $r = $iconv( $charset, "UTF-8//TRANSLIT", $s );
			if ( $r == '?' )  $r = 'X';
			$utf .= $r;
		}
		else
		if ( $b1 >= 0xa0 ) // half-width
		{
			$p = $b1 * 2;
			$s = $sjis_half[$p+0] . $sjis_half[$p+1];
				$st++;
			$r = '?';
			if ( $r == '?' )  $r = $iconv( $charset, "UTF-8//TRANSLIT", $s );
			if ( $r == '?' )  $r = 'X';
			$utf .= $r;
		}
		else
		if ( $b1 >= 0x80 ) // shift jis
		{
			$s = $sjis[$st+0] . $sjis[$st+1];
				$st += 2;
			$r = '?';
			if ( $r == '?' )  $r = $iconv( $charset, "ASCII//TRANSLIT", $s );
			if ( $r == '?' )  $r = $iconv( $charset, "UTF-8//TRANSLIT", $s );
			if ( $r == '?' )  $r = 'X';
			$utf .= $r;
		}
		else // ascii
		{
			$utf .= $sjis[$st];
			$st++;
		}
	}
	return $utf;
}

function utf8len( $utf8 )
{
	req_define("FUNC_ICONV");
	$iconv = FUNC_ICONV;
	$len = array(
		"asc" => 0,
		"utf" => 0,
	);

	$len = strlen($utf8);
	$pos = 0;
	while ( $pos < $len )
	{
		$b1 = ord( $utf8[$pos] );
		if ( $b1 & 0x80 )
		{
			$len['utf']++;
			if ( $b1 >= 0xf1 )
				$pos += 4;
			else
			if ( $b1 >= 0xe0 )
				$pos += 3;
			else
			if ( $b1 >= 0xc0 )
				$pos += 2;
		}
		else
		{
			$len['asc']++;
			$pos++;
		}
	}

	return $len;
}
////////////////////////////////////////
