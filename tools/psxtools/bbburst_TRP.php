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
require 'bbburst.inc';

function burst( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'TRP ' )
		return;

	$dir = str_replace('.', '_', $fname);

	$id  = 0;
	$pos = 0x10;
	while (1)
	{
		$siz = str2int($file, $pos, 3);
			$pos += 0x10;
		printf("%8x , %8x , %04d\n", $pos, $siz, $id);
		if ( $siz === 0 )
			return;

		$s  = substr ($file, $pos, $siz);
			$pos += $siz;
		$fn = sprintf('%s/%04d', $dir, $id);
			$id++;

		$mgc = substr($s, 0, 4);
		switch ( $mgc )
		{
			case "\x10".ZERO.ZERO.ZERO:
				$fn .= '.tim' . bin2hex($s[4]);
				break;
			default:
				$fn .= '.unk';
				break;
		} // switch ( $mgc )

		save_file($fn, $s);
	} // while (1)

	return;
}

for ( $i=1; $i < $argc; $i++ )
	burst( $argv[$i] );
