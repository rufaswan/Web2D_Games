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

function loopdir( $dir, &$list )
{
	if ( empty($dir) || ! is_dir($dir) )
		return;
	if ( is_link($dir) )
		return;

	$func = __FUNCTION__;
	$data = array(0, 0, $dir);
	foreach ( scandir($dir) as $en )
	{
		if ( $en[0] === '.' )
			continue;
		$fn = "$dir/$en";

		if ( is_link($fn) )
			continue;
		if ( is_file($fn) )
		{
			$data[0] += filesize($fn);
			$data[1]++;
			continue;
		}
		if ( is_dir($fn) )
		{
			list($b0,$b1,$b2) = $func($fn, $list);
			$data[0] += $b0;
			$data[1] += $b1;
			continue;
		}
	} // foreach ( scandir($dir) as $en )

	$list[] = $data;
	return $data;
}

function dirsort($a, $b)
{
	if ( $a[0] !== $b[0] )
		return ( $a[0] < $b[0] ) ? -1 : 1;
	if ( $a[1] !== $b[1] )
		return ( $a[1] < $b[1] ) ? -1 : 1;
	return 0;
}
//////////////////////////////
$list = array();
loopdir('.', $list);

usort($list, 'dirsort');
foreach( $list as $dir )
	printf("%8x/%-4x  %s\n", $dir[0], $dir[1], $dir[2]);

