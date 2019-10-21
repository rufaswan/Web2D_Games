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
define("ZERO", chr(0));
define("SJIS_HALF", "sjis_half.inc" );
define("SJIS_ASC",  "sjis_ascii.inc");
 */
////////////////////////////////////////
function sjistxt( $sjis )
{
	if ( ! defined("SJIS_HALF") )  return "";
	if ( ! defined("SJIS_ASC" ) )  return "";
	$sjis_half = file_get_contents(SJIS_HALF);
	$sjis_asc  = file_get_contents(SJIS_ASC );

	$stxt = "";
	$ed = strlen($sjis);
	$st = 0;
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
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$p = $b1 * 2;
			$stxt .= $sjis_half[$p+0];
			$stxt .= $sjis_half[$p+1];
			$st++;
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
				}
				else
					$stxt .= $sjis_asc[$p];
			}
			else
			{
				$stxt .= $sjis[$st+0];
				$stxt .= $sjis[$st+1];
			}
			$st += 2;
		}
		else
		{
			$stxt .= $sjis[$st];
			$st++;
		}
	} // while( $st < $ed )

	return $stxt;
}
////////////////////////////////////////
/*
# SJIS_HALF
	a0-df  half-width

# SJIS_ASC
	0-9  number     30-39  824f-8258
	A-Z  uppercase  41-5a  8260-8279
	a-z  lowercase  61-7a  8281-829a

	symbols
		   20  8140
		/  2f  815e
	(RANCE 1 SA/001.S351)
		.  2e  8144
		:  3a  8146
		?  3f  8148
		!  21  8149
		(  28  8169  )  29  816a
		[  5b  816d  ]  5d  816e
		-  2d  817c
		=  3d  8181
 */
////////////////////////////////////////
function utf8len( $utf8 )
{
	// 00-7f = as is
	// 80-   = multi-byte
	// length = c0-fd
	// if ( preg_match('|[\xc0-\xfd][\x80-\xbf][\x80-\xbf]|', $utf8) )

	// get $len of half-width chars
	$len = strlen($utf8);
	$asclen = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b1 = ord( $utf8[$i] );
		if ( $b1 < 0x80 )
			$asclen++;
	}

	// get $len of full-width chars
	$len = iconv_strlen($utf8, "utf-8");
	$utflen = $len - $asclen;

	$len = array(
		"ascii" => $asclen,
		"utf8"  => $utflen,
	);
	return $len;
}
////////////////////////////////////////
