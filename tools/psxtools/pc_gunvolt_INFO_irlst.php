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

$gp_list = array();

function gunvolt( $fname )
{
	// for *.irlst only
	if ( stripos($fname, '.irlst') === false )
		return;
	if ( is_link($fname) )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_list;
	$len = strlen($file);
	for ( $i=4; $i < $len; $i += 0x10 )
	{
		$id = str2int($file, $i, 4);
		if ( isset( $gp_list[$id] ) )
			printf("DUP %x = %s -> %s\n", $id, $gp_list[$id], $fname);

		$gp_list[$id] = $fname;
	} // for ( $i=4; $i < $len; $i += 0x10 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );
