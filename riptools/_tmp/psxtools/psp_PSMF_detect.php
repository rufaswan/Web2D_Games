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

function pspfile( $fname )
{
	if ( ! is_file($fname) )
		return;

	$fsz = filesize($fname);
	if ( $fsz < 1 )  return;

	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);
	$pos = 0;
	$end = $fsz >> 11; // div 800
	while ( $pos < $end )
	{
		$sub = fp2str($fp, $pos << 11, 0x10);

		if ( substr($sub,0,4) !== 'PSMF' )
		{
			$pos++;
			continue;
		}

		printf("%8x  %s\n", $pos << 11, substr($sub,0,8));
		$hsz = str2big($sub,  8, 4);
		$dsz = str2big($sub, 12, 4);
		if ( $hsz & 0x7ff || $dsz & 0x7ff )
		{
			printf("not 800 aligned = %x + %x\n", $hsz, $dsz);
			$pos++;
			continue;
		}

		$siz = $hsz + $dsz;
		$sub = fp2str($fp, $pos << 11, $siz);
		$fnm = sprintf('%s/%08d.pmf', $dir, $pos);
		save_file($fnm, $sub);

		$pos += ($siz >> 11);
	} // while ( $pos < $end )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pspfile( $argv[$i] );
//argv_loopfile($argv, 'pspfile');
