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
require "common.inc";

//define("NO_TRACE", true);

function rusty_decode( &$file, $st )
{
	$dicz = 0xfff;
	$dicp = 0xfee;
	$dict = str_repeat(ZERO, $dicz+1);
	$dec = "";

	$ed = strlen($file);
	$bylen = 0;
	$bycod = 0;
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
				$st++;
			trace("BYTECODE %2x\n", $bycod);
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = $file[$st];
				$st++;
			trace("COPY %2x\n", ord($b1));

			$dec .= $b1;
			$dict[$dicp] = $b1;

			$dicp = ($dicp + 1) & $dicz;
		}
		else
		{
			$b1 = ord( $file[$st+0] );
			$b2 = ord( $file[$st+1] );
				$st += 2;
			$len =  ($b2 & 0x0f) + 3;
			$pos = (($b2 & 0xf0) << 4) | $b1;
			trace("DICT %3x LEN %2x\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = ($pos + $i) & $dicz;
				$b1 = $dict[$p];

				$dec .= $b1;
				$dict[$dicp] = $b1;

				$dicp = ($dicp + 1) & $dicz;
			}
		}
	} // while ( $st < $ed )

	return $dec;
}
//////////////////////////////
function rusty( $fname )
{
	$bak = file_exists("$fname.bak");
	if ( $bak )
		$file = file_get_contents("$fname.bak");
	else
		$file = file_get_contents($fname);

	if ( empty($file) )
		return;
	if ( substr($file, 0, 2) != "LZ" )
		return;

	if ( ! $bak )
		file_put_contents("$fname.bak", $file);

	$dec = rusty_decode( $file, 7 );
	file_put_contents($fname, $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

/*
Neko Project 2 GDC clock 5 MHz error
	- restart emulator and hold End/Help key -> BIOS screen
	- select DIP switch 2 settings
	- change GDC clock from 5 MHz to 2.5 MHz
	- exit
 */
