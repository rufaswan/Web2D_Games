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

function inc_type( &$s )
{
	$t = trim($s, '<>');
	if ( $t !== $s )
	{
		$s = $t;
		return '<>';
	}

	$t = trim($s, '[]');
	if ( $t !== $s )
	{
		$s = $t;
		return '[]';
	}

	return '==';
}

function inc_tag( $s )
{
	$s = trim($s, '@');
	$d = array(
		't' => '', // type
		'f' => '', // file content
		'e' => '', // extension
		's' => '', // ID
	);
	echo "@@ $s @@\n";
	$d['t'] = inc_type($s);

	$p = strrpos($s, '.');
	$d['e'] = strtolower( substr($s, $p+1) );
	$d['f'] = load_file($s);
	$d['s'] = preg_replace('|[^a-zA-Z0-9]|', '_', $s);
	return $d;
}

function trim_js_css( &$css )
{
	while (1)
	{
		$p1 = strpos($css, '/*');
		if ( $p1 === false )
			break;

		$p2 = strpos($css, '*/', $p1+2);
		if ( $p2 === false )
			$t = substr($css, $p1);
		else
			$t = substr($css, $p1, $p2+2-$p1);

		$css = str_replace($t, "\n", $css);
	} // while (1)
	return;

/*
	$css = str_replace("\r", "\n", $css);
	$t = explode("\n", $css);
	$css = '';
	foreach ( $t as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$cmt = strpos($line, '//');
		if ( $cmt === 0 )
			continue;
		if ( $cmt > 0 )
			$line = substr($line, 0, $cmt);

		$css .= "$line\n";
	} // foreach ( $t as $line )

	$css = preg_replace('| +|', ' ', $css);
	return;
*/
}

function html64( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$is_same = true;
	while (1)
	{
		$p1 = strpos($file, '@@');
		if ( $p1 === false )
			break;
		$p2 = strpos($file, '@@', $p1+2);
		if ( $p2 === false )
			break;

		$s = substr($file, $p1,   $p2+2-$p1); // @@style.css@@
		$d = inc_tag($s);

		$rep = '';
		switch ( $d['t'] )
		{
			case '<>': // <tag>%s</tag>
				switch ( $d['e'] )
				{
					case 'tpl':
					case 'htm':
					case 'html':
						$rep = $d['f'];
						break;
					case 'js':
						trim_js_css($d['f']);
						$rep = sprintf('<script id="%s">%s</script>', $d['s'], $d['f']);
						break;
					case 'css':
						trim_js_css($d['f']);
						$rep = sprintf('<style id="%s">%s</style>', $d['s'], $d['f']);
						break;
					case 'jpeg':
					case 'jpg':
						$rep = sprintf('<img id="%s" title="%s" alt="%s" src="data:image/jpeg;base64,%s">', $d['s'], $d['s'], $d['s'], base64_encode($d['f']));
						break;
					case 'png':
						$rep = sprintf('<img id="%s" title="%s" alt="%s" src="data:image/png;base64,%s">' , $d['s'], $d['s'], $d['s'], base64_encode($d['f']));
						break;
					default:
						$rep = $d['f'];
						break;
				} // switch ( $d['e'] )

				break;

			case '[]': // Uint8Array(%s)
				$b = array();
				$len = strlen($d['f']);
				for ( $i=0; $i < $len; $i++ )
					$b[$i] = ord( $d['f'][$i] );
				$rep .= sprintf('Uint8Array([%s])', implode(',', $b));
				break;

			case '==': // data:base64,%s
				switch ( $d['e'] )
				{
					case 'tpl':
					case 'htm':
					case 'html':
						$mime = 'text/html';
						break;
					case 'js':
						$mime = 'application/javascript';
						break;
					case 'css':
						$mime = 'text/css';
						break;
					case 'jpeg':
					case 'jpg':
						$mime = 'image/jpeg';
						break;
					case 'png':
						$mime = 'image/png';
						break;
					default:
						$mime = 'application/octet-stream';
						break;
				} // switch ( $d['e'] )

				$rep .= sprintf('data:%s;base64,%s', $mime, base64_encode($d['f']));
				break;
		} // switch ( $d['t'] )

		$file = str_replace($s, $rep, $file);
		$is_same = false;
	} // while (1)

	if ( ! $is_same )
		file_put_contents("$fname.html", $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	html64( $argv[$i] );
