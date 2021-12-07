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

function extsize( &$all, $ext )
{
	$list = array();
	$sum  = 0;
	foreach ( $all as $fn )
	{
		$e = substr($fn, strrpos($fn, '.'));
		if ( stripos($e, $ext) !== false )
		{
			$siz = filesize($fn);
			if ( ! isset($list[$siz]) )
				$list[$siz] = array();

			$list[$siz][] = $fn;
			$sum += $siz;
		}
	} // foreach ( $all as $fn )

	ksort($list);
	$perc = 100 / $sum;
	foreach ( $list as $siz => $v )
	{
		foreach ( $v as $fn )
			printf("[%4.1f%%]  %8x  %s\n", $siz*$perc, $siz, $fn);
	} // foreach ( $list as $siz => $v )

	printf("sum [%s] = %x\n", $ext, $sum);
	return;
}

printf("%s  EXTENSION...\n", $argv[0]);
if ( $argc == 1 )   exit();

$all = array();
lsfile_r('.', $all);
for ( $i=1; $i < $argc; $i++ )
	extsize( $all, $argv[$i] );
