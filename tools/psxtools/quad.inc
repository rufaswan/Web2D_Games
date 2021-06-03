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
