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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');

define('BIT8_INV', 1.0 / 0xff);

$gp_html = <<<_HTML
<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta charset='utf-8' />
	<meta name='viewport' content='width=device-width, initial-scale=1' />
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

function clut2html( string $fname ) : void
{
	$img = clutfile::load($fname);
	if ( empty($img) )
		return;

	// STYLE
	$style = '';
	if ( ! empty($img->pal) )
	{
		$pal = &$img->pal;
		$len = strlen($pal);
		$inv = 1.0 / 0xff;
		for ( $i=0; $i < $len; $i += 4 )
		{
			$r = ord($pal[$i+0]);
			$g = ord($pal[$i+1]);
			$b = ord($pal[$i+2]);
			$a = ord($pal[$i+3]) * BIT8_INV;
			$style .= sprintf('.lookup_%d { background-color : rgba(%3d , %3d , %3d , %.2f); }', $i >> 2, $r, $g, $b, $a);
			$style .= "\n";
		} // for ( $i=0; $i < $len; $i += 4 )
	}

	// TABLE
	$table = '';
	$pix = &$img->pix;
	$pos = 0;
	if ( ! empty($style) )
	{
		for ( $y=0; $y < $img->h; $y++ )
		{
			$table .= '<tr>';
			for ( $x=0; $x < $img->w; $x++ )
			{
				$ind = ord( $pix[$pos] );
					$pos++;
				$table .= sprintf('<td class="lookup_%d"></td>', $ind);
				$table .= "\n";
			} // for ( $x=0; $x < $clut['w']; $x++ )
			$table .= '</tr>';
		} // for ( $y=0; $y < $clut['h']; $y++ )
	}
	else
	{
		for ( $y=0; $y < $img->h; $y++ )
		{
			$table .= '<tr>';
			for ( $x=0; $x < $img->w; $x++ )
			{
				$r = ord($pix[$pos+0]);
				$g = ord($pix[$pos+1]);
				$b = ord($pix[$pos+2]);
				$a = ord($pix[$pos+3]) * BIT8_INV;
					$pos += 4;
				$table .= sprintf('<td style="background-color : rgba(%3d , %3d , %3d , %.2f);"></td>', $r, $g, $b, $a);
				$table .= "\n";
			} // for ( $x=0; $x < $clut['w']; $x++ )
			$table .= '</tr>';
		} // for ( $y=0; $y < $clut['h']; $y++ )
	}

	global $gp_html;
	$html = $gp_html;
	$html = str_replace('@@TITLE@@', $fname, $html);
	$html = str_replace('@@STYLE@@', $style, $html);
	$html = str_replace('@@TABLE@@', $table, $html);
	tool::save("$fname.html", $html);
}

tool::argv_callback($argv, 'clut2html');
