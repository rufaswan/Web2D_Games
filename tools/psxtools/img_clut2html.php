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

$gp_html = <<<_HTML
<!doctype html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<title>@@TITLE@@</title>
	<style>
	body {
		background-color : #444;
	}
	table {
		border           : 4px solid #000;
		border-collapse  : collapse;
		background-color : #ccc;
	}
	td {
		width  : 4px;
		height : 4px;
	}
@@STYLE@@
	</style>
</head>
<body>
	<table>@@TABLE@@</table>
</body>
</html>

_HTML;

function clut2html( $fname )
{
	$clut = load_clutfile($fname);
	if ( empty($clut) || ! isset($clut['pal']) )
		return;

	// STYLE
	$style = '';
	$len = strlen($clut['pal']);
	$inv = 1.0 / 0xff;
	for ( $i=0; $i < $len; $i += 4 )
	{
		$r = ord($clut['pal'][$i+0]);
		$g = ord($clut['pal'][$i+1]);
		$b = ord($clut['pal'][$i+2]);
		$a = ord($clut['pal'][$i+3]);
		$style .= sprintf('.lookup_%d { background-color : rgba(%3d , %3d , %3d , %.2f); }', $i >> 2, $r, $g, $b, $a*$inv);
		$style .= "\n";
	} // for ( $i=0; $i < $len; $i += 4 )

	// TABLE
	$table = '';
	$pos = 0;
	for ( $y=0; $y < $clut['h']; $y++ )
	{
		$table .= '<tr>';
		for ( $x=0; $x < $clut['w']; $x++ )
		{
			$ind = ord( $clut['pix'][$pos] );
				$pos++;
			$table .= sprintf('<td class="lookup_%d"></td>', $ind);
			$table .= "\n";
		} // for ( $x=0; $x < $clut['w']; $x++ )
		$table .= '</tr>';
	} // for ( $y=0; $y < $clut['h']; $y++ )

	global $gp_html;
	$html = $gp_html;
	$html = str_replace('@@TITLE@@', $fname, $html);
	$html = str_replace('@@STYLE@@', $style, $html);
	$html = str_replace('@@TABLE@@', $table, $html);
	save_file("$fname.html", $html);
	return;
}

argv_loopfile($argv, 'clut2html');
