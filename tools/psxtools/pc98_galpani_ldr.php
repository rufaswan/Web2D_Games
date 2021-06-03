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

function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "LBRG" )
		return;
	$dir = str_replace('.', '_', $fname);

	$pos = str2int($file,  8, 4);
	$cnt = str2int($file, 12, 4);
	$list = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $pos + ($i * 0x10);
		$tit = substr($file, $p+0, 8);
			$tit = rtrim($tit, ' ');
		$ext = substr($file, $p+8, 3);
		$b1 = str2int($file, $p+11, 2);
		$b2 = str2int($file, $p+13, 2);
		$b3 = ord( $file[$p+15] );
			$b2 |= ($b3 << 13);

		$list[$b1] = array($tit, $ext, $b2);
	}
	ksort($list);
	foreach ( $list as $k => $v )
	{
		list($tit,$ext,$len) = $v;
		printf("%8x , %6x , %s.%s\n", $k*0x800, $len, $tit, $ext);
		save_file("$dir/$ext/$tit.$ext", substr($file, $k*0x800, $len));
	}
	printf("total %d == %d\n", $cnt, count($list));

	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );
