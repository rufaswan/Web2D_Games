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

function add_head( &$head, &$e )
{
	$text = $e['text'];
	while (1)
	{
		$ok = true;
		$sha = md5($text);
		foreach ( $head as $h )
		{
			if ( $h['hash'] === $sha )
				$ok = false;
		}

		if ( $ok )
		{
			$e['hash'] = $sha;
			break;
		}
		else
			$text .= '.';
	} // while (1)

	$head[] = $e;
	return;
}

function markbody( &$file )
{
	$head = array();
	$body = array();

	$ed = count($file);
	$st = 0;
	while ( $st < $ed )
	{
		$line = trim($file[$st]);
			$st++;

		if ( empty($line) )
			continue;

		if ( $line[0] === '#' )
		{
			$level = strpos($line, ' ');
			$e = array(
				'type'  => 'h',
				'level' => $level,
				'text'  => substr($line, $level+1),
			);
			add_head($head, $e);
			$body[] = $e;
			continue;
		}

		if ( $line === '~~~' || $line === '```' )
		{
			$mark = $line;
			$text = '';
			while ( $file[$st] !== $mark )
			{
				$text .= $file[$st] . "\n";
					$st++;
			}
			$st++;

			$e = array(
				'type' => 'code',
				'text' => $text,
			);
			$body[] = $e;
			continue;
		}

		$e = array(
			'type' => 'p',
			'text' => $line,
		);
		$body[] = $e;
	} // while ( $st < $ed )

	$file = $body;
	return $head;
}
//////////////////////////////
function gethash( &$head, $text )
{
	foreach ( $head as $hv )
	{
		if ( $hv['text'] === $text )
			return $hv['hash'];
	}
	return '';
}

function markhash( &$body, &$head )
{
	foreach ( $body as $bk => $bv )
	{
		if ( ! isset( $bv['text'] ) )
			continue;

		while (1)
		{
			$pos = strpos($bv['text'], '[](#');
			if ( $pos === false )
				break;

			$pos += 4;
			$end = strpos($bv['text'], ')', $pos);
			$anc = substr($bv['text'], $pos, $end-$pos);
			echo "$anc\n";

			$sha = gethash($head, $anc);
			if ( empty($sha) )
				$a = '';
			else
				$a = sprintf('<a href="#%s">%s</a>', $sha, $anc);

			$bv['text'] = str_replace('[](#'.$anc.')', $a, $bv['text']);
		} // while (1)

		$body[$bk]['text'] = $bv['text'];
	}
	return;
}
//////////////////////////////
function markdown( $fname )
{
	$file = file($fname);
	if ( empty($file) )  return;

	foreach ( $file as $k => $v )
		$file[$k] = rtrim($v);

	$head = markbody($file);
	//print_r($head);
	//print_r($file);
	markhash($file, $head);

$html = <<<_HTML
<!doctype html>
<html><head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<title>{$fname}</title>
	<style>
		body {
			background : #eee;
			color      : #222;
		}
		article {
			margin : 0 5vw;
		}
		h1, h2, h3, h4, h5, h6 {
			border-bottom : 1px #222 solid;
		}
		.code {
			font-family : monospace;
			white-space : pre;
			border      : 1px #222 solid;
			background  : #ccc;
		}
	</style>
</head><body><article>

_HTML;

	foreach ( $file as $bk => $bv )
	{
		switch ( $bv['type'] )
		{
			case 'h':
				$html .= sprintf("<h%d id='%s'>%s</h%d>\n", $bv['level'], $bv['hash'], $bv['text'], $bv['level']);
				break;
			case 'code':
				$html .= sprintf("<div class='code'>%s</div>\n", $bv['text']);
				break;
			case 'p':
				$html .= sprintf("<p>%s</p>\n", $bv['text']);
				break;
		} // switch ( $bv['type'] )
	} // foreach ( $body as $bk => $bv )

	$html .= '</article></body></html>';
	file_put_contents("$fname.html", $html);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	markdown( $argv[$i] );
