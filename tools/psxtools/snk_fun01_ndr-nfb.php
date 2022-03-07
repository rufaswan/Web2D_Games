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

function snkfun( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$ndr = load_file("$pfx.ndr");
	$nfb = load_file("$pfx.nfb");
	if ( empty($ndr) || empty($nfb) )
		return;

	$len = int_ceil(strlen($ndr), -0x20);
	for ( $p=0; $p < $len; $p += 0x20 )
	{
		$fn  = substr0($ndr, $p+0);
		$lba = str2int($ndr, $p+0x18, 4);
		$siz = str2int($ndr, $p+0x1c, 4);
			$fn = strtolower($fn);

		printf("%6x , %8x , %8x , %s\n", $lba, $lba*0x800, $siz, $fn);
		$dat = substr($nfb, $lba*0x800, $siz);
		save_file("$pfx/$fn", $dat);
	} // for ( $p=0; $p < $len; $p += 0x20 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	snkfun( $argv[$i] );
