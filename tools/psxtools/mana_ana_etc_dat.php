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

function mana( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$ps = str2int($file, $p, 4);

		if ( $file[$ps] === ZERO ) // allface.dat
		{
			$pal = substr($file, $ps, 0x20);
				$ps += 0x20;

			$sz = 0x30 * 0x30 / 2;
			$pix = substr($file, $ps, $sz);
			bpp4to8($pix);

			$img = array(
				'cc'  => 0x10,
				'w'   => 0x30,
				'h'   => 0x30,
				'pal' => pal555($pal),
				'pix' => $pix,
			);

			$fn = sprintf('%s/%04d.clut', $dir, $i);
			save_clutfile($fn, $img);
			continue;
		}

		// TIM with CLUT
		$tim = psxtim($file, $ps);
		if ( $tim === -1 )
			continue;

		if ( $tim['t'] === 'RGBA' )
		{
			$fn = sprintf('%s/%04d.rgba', $dir, $i);
			save_clutfile($fn, $tim);
			continue;
		}

		if ( $tim['t'] === 'CLUT' )
		{
			// FIXME : unknown cid , pair all pal with pix
			$pal  = $tim['pal'];
			$ccsz = $tim['cc'] * 4;
			$ccps = 0;

			while (1)
			{
				$str = substr($pal, $ccps, $ccsz);
					$ccps += $ccsz;

				if ( empty($str) )
					break;
				if ( trim($str, ZERO.BYTE) === '' )
					continue;

				$tim['pal'] = $str;
				$fn = sprintf('%s/%04d_%d.clut', $dir, $i, $ccps/$ccsz);
				save_clutfile($fn, $tim);
			} // while (1)
			continue;
		}
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

