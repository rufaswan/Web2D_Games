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

function burst( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'Pak0' )
		return;

	$dir = str_replace('.', '_', $fname);

	$st  = 4;
	$ed  = str2int($file, 4, 2) << 11;
	while ( $st < $ed )
	{
		$off = str2int($file, $st+0, 2);
		$siz = str2int($file, $st+2, 2);
			$st += 4;
		if ( $off === 0 || $siz === 0 )
			continue;
		$off <<= 11;
		$siz <<= 11;
		$s = substr($file, $off, $siz);

		$fn  = sprintf('%s/%04d', $dir, ($st >> 2) - 2);
		$mgc = substr ($s, 0, 4);
		switch ( $mgc )
		{
			case 'TRP ':  $fn .= '.trp'; break;
			case 'pBAV':  $fn .= '.vh' ; break;
			case 'pQES':  $fn .= '.seq'; break;
			case ZERO.ZERO.ZERO.ZERO:
				$fn .= '.vb' ;
				break;

			case "\x10".ZERO.ZERO.ZERO:
				$fn .= '.tim' . bin2hex($s[4]);
				break;

			default:
				$fn .= '.unk';
				break;
		} //switch ( $mgc )

		printf("%6x , %8x , %8x , %s\n", $off >> 11, $off, $siz, $fn);
		save_file($fn, $s);
	} // while ( $st < $ed )
	return;
}

argv_loopfile($argv, 'burst');

/*
2d_all.bin TOC = RAM 800b8504-800b9a48
	8001d17c  addiu  a1, 1
	8001d180  sll    a1, 2
	8001d184  lw     v0, 1c(a0)  800efc60
	8001d18c  addu   a1, v0
	8001d190  lhu    a1,  0(a1)

	inn menu , talk
		c1
	woman enter , leave
		5cf  710
	inn menu

236
247
 */
