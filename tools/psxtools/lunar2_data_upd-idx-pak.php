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

function lunar2( $dir )
{
	$upd = load_file("$dir/data.upd");
	$idx = load_file("$dir/data.idx");
	$pak = load_file("$dir/data.pak");
	if ( empty($upd) || empty($idx) || empty($pak) )
		return;

	$b1 = str2int($upd,  0, 4); // toc base
	$b2 = str2int($upd,  4, 4); // num of files
	$b3 = str2int($upd,  8, 4); // padding
	$b4 = str2int($upd, 12, 4);
	$b5 = str2int($upd, 16, 4); // fname base
	$b6 = str2int($upd, 20, 4);

	$buf = '';
	for ( $i=0; $i < $b2; $i++ )
	{
		$p1 = $b1 + ($i * 0x14);
		$pb1 = str2int($upd, $p1+ 0, 4);
			$fn = substr0($upd, $b5+$pb1);
			$fn = strtolower($fn);

		$pb2 = str2int($upd, $p1+12, 4);
			$p2 = $pb2 * 5;
			$lba = ordint( $idx[$p2+2] . $idx[$p2+1] . $idx[$p2+0] );
			$siz = ordint( $idx[$p2+4] . $idx[$p2+3] );

		$pb3 = str2int($upd, $p1+16, 4);

		$log = sprintf("%6x , %8x , %8x , %s\n", $lba, $lba*0x800, $pb3, $fn);
		echo $log;
		$buf .= $log;

		$sub = substr($pak, $lba*0x800, $pb3);
		save_file("$dir/data/$fn", $sub);
	} // for ( $i=0; $i < $b2; $i++ )

	save_file("$dir/data/toc.txt", $buf);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
