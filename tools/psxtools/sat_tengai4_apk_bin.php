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
require 'common-guest.inc';

function tengai4( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$pos = 0;

	while ( $pos < 0x4000 )
	{
		$p = str2big($file, $pos, 4);
			$pos += 4;
		if ( $p === 0 )
			continue;
		if ( $p & 0x80000000 )
			continue;

		$p *= 0x800;
		$b1 = substr ($file, $p+0, 4);
		$b2 = str2big($file, $p+4, 4);
		if ( $b1 !== 'FORM' )
		{
			php_notice('%x !== FORM', $p);
			continue;
		}

		$sub = substr ($file, $p, $b2);
		$fn  = sprintf('%s/%04d.aiff', $dir, ($pos>>2)-1);
		save_file($fn, $sub);
	} // while ( $pos < 0x4000 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tengai4( $argv[$i] );
