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
php_req_extension("json_encode", "json");

function load_idtagfile( $pfx )
{
	$fn = __DIR__ . "/idtag/$pfx.txt";
	if ( ! file_exists($fn) )
		return array();

	$data = array('TAG' => array());
	$head = '';
	foreach ( file($fn) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$line = htmlspecialchars($line, ENT_QUOTES|ENT_HTML5, 'UTF-8');
		if ( $line[0] == '[' )
			$head = trim($line, '[]');
		else
		{
			if ( ! isset( $data['TAG'][$head] ) )
				$data['TAG'][$head] = array();
			$data['TAG'][$head][] = $line;
		}
	} // foreach ( file($fn) as $line )

	foreach ( $data['TAG'] as $head => $line )
	{
		if ( count($line) == 1 )
			$data['TAG'][$head] = $line[0];
		else
			$data['TAG'][$head] = implode('<br>', $line);
	}
	return $data;
}

function save_quadfile( $pfx, &$json, $pretty=false )
{
	if ( empty($json) )
		return;

	// JSON_PRETTY_PRINT
	// JSON_FORCE_OBJECT
	if ( $pretty )
		$json = json_encode($json, JSON_PRETTY_PRINT);
	else
		$json = json_encode($json);

	save_file("$pfx.quad", $json);
	return;
}

function cmp_quadxy( &$data, $id1, $id2 )
{
	if ( $data[$id1] != $data[$id2] )
		php_notice("cmp %d [%7.2f] != %d [%7.2f]", $id1, $data[$id1], $id2, $data[$id2]);
	return;
}

function quad_ext1px( &$quad )
{
	// To fix Lunar 2 EB 1px gap between quads
	//    17, b   0, b   0, 0   17, 0  =>   18, c  0, c  0,0   18,0
	//    17,-c   0,-c   0,-1   17,-1  =>   18,-c  0,-c  0,0   18,0
	//   -18, b  -1, b  -1, 0  -18, 0  =>  -18, c  0, c  0,0  -18,0
	//   -18,-c  -1,-c  -1,-1  -18,-1  =>  -18,-c  0,-c  0,0  -18,0
	//
	// -18,0  - 0,c  - 18,c
	//          |
	// -18,0  - 0,0  - 18,0
	//          |
	// -18,-c - 0,-c - 18,-c

	// x1 y1  x2 y2  x3 y3  x4 y4
	// 0  1   2  3   4  5   6  7
	( $quad[0] > $quad[4] ) ? $quad[0]++ : $quad[4]++;
	( $quad[1] > $quad[5] ) ? $quad[1]++ : $quad[5]++;
	( $quad[2] > $quad[6] ) ? $quad[2]++ : $quad[6]++;
	( $quad[3] > $quad[7] ) ? $quad[3]++ : $quad[7]++;
	return;
}
