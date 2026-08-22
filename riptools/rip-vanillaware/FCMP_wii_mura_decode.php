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

require 'tool.inc';

define('NO_TRACE', true);

function mura_decode( string &$file, int $st ) : void
{
	$dicz = 0xfff;
	$dicp = 0xfee;
	$dict = str_repeat(ZERO, $dicz+1);
	$dec = '';

	$ed = strlen($file);
	$bylen = 0;
	$bycod = 0;
	while ( $st < $ed )
	{
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
				$st++;
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

	$file = $dec;
}
//////////////////////////////
function mura( string $fname ) : void
{
	$file = tool::loadbak($fname);
	if ( empty($file) )
		return;

	if ( substr($file,0,4) !== 'FCMP' )
		return;

	mura_decode( $file, 12 );
	file_put_contents($fname, $file);

	tool::trace('mura_decode', $fname, filesize($fname.'.bak'), filesize($fname));
}

tool::argv_callback($argv, 'mura');
