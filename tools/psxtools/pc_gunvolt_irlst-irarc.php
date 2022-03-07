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
require 'common.inc';

define('NO_TRACE', true);

function gv_decode( &$sub )
{
	$bylen = 0;
	$bycod = 0;
	$dec = '';

	$st = 0x18;
	$ed = strlen($sub);
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $sub[$st] );
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
			$b1 = ord( $sub[$st+0] );
			$b2 = ord( $sub[$st+1] );
				$st += 2;

			$pos = (($b2 & 0x0f) << 8) | $b1;
			$len = ($b2 >> 4) + 2;
			trace("REF  POS -%3d LEN %d\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = strlen($dec) - 1 - $pos;
				$dec .= $dec[$p];
			}
		}
		else
		{
			$b1 = $sub[$st];
				$st++;
			trace("COPY %2x\n", ord($b1));

			$dec .= $b1;
		}
	} // while ( $st < $ed )

	$sub = $dec;
	return;
}

function gunvolt( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$lst = load_file("$pfx.irlst");
	$arc = load_file("$pfx.irarc");
	if ( empty($lst) || empty($arc) )
		return;

	$cnt = str2int($lst, 0, 4);
	$done = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 0x10);
		$id = str2int($lst, $p+ 0, 4);
		$of = str2int($lst, $p+ 4, 4);
		$sz = str2int($lst, $p+ 8, 4);
		$un = str2int($lst, $p+12, 4);

		if ( isset( $done[$id] ) )
			return php_error('DUPL ID %x = %s', $id, $pfx);
		$done[$id] = 1;

		// 07 IOBJ
		// 17 IOBJ compressed
		// 02 ???
		// 00 model
		// 12 model compressed
		$s = substr($arc, $of, $sz);
		if ( $un == 0x17 || $un == 0x12 )
			gv_decode($s);

		$fn = sprintf('%s/gv_%04d.%x', $pfx, $id, $un);
		printf("%8x , %8x , %s\n", $of, $sz, $fn);

		save_file($fn, $s);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );

/*
https://steamcommunity.com/app/388800/eventcomments/
https://steamcommunity.com/app/1034900/eventcomments/
https://steamcommunity.com/app/1065180/eventcomments/
https://steamcommunity.com/app/1085180/eventcomments/
 */
