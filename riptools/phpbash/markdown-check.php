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
declare(strict_types=1);

function del_inline( string &$line, string $char ) : void
{
	$cnt = strlen($char);
	while (1)
	{
		$p1 = strpos($line, $char);
		if ( $p1 === false )
			return;

		$p2 = strpos($line, $char, $p1+$cnt);
		if ( $p2 === false )
			return;

		$sub  = substr($line, 0, $p1);
		$sub .= substr($line, $p2+$cnt);
		$line = $sub;
	} // while (1)
}

function mdcheck( string $fname ) : void
{
	if ( ! is_file($fname) )
		return;
	$ext = substr($fname, strrpos($fname,'.'));
	if ( $ext !== '.md' )
		return;

	$file = file($fname, FILE_IGNORE_NEW_LINES);
	if ( empty($file) )
		return;
	echo "== $fname\n";

	$ed = count($file);
	$st = 0;
	while ( $st < $ed )
	{
		$line = $file[$st];
			$st++;

		if ( empty($line) )
			continue;

		// code block
		if ( $line === '```' )
		{
			while ( $file[$st] != '```' )
				$st++;
			$st++;
			continue;
		}

		// hr -> h1
		if ( substr($line,0,3) === '---' )
		{
			if ( ! empty($file[$st-2]) )
				printf('%d  --- become h1'."\n", $st);
			if ( empty($file[$st]) )
				printf('%d  --- \n'."\n", $st);
			continue;
		}

		// remove url
		while ( preg_match('|\(http[^\)]+\)|', $line, $m) )
			$line = str_replace($m[0], '', $line);
		while ( preg_match('|\[http[^\]]+\]|', $line, $m) )
			$line = str_replace($m[0], '', $line);

		del_inline($line, '`');
		del_inline($line, '$$');
		del_inline($line, '$');

		//echo $st .' = '. $line . "\n";
		if ( strlen($line) > 2 && strpos($line,'##', 2) !== false )
			printf('%d  double h2'."\n", $st);

		$word = explode(' ', $line);
		foreach ( $word as $w )
		{
			if ( $w !== '_' && strpos($w,'_') !== false )
				printf('%d  %s'."\n", $st, $w);
			if ( $w !== '*' && strpos($w,'*') !== false )
				printf('%d  %s'."\n", $st, $w);
			//if ( strpos($w,'()') !== false )
				//printf('%d  %s'."\n", $st, $w);
		} // foreach ( $word as $w )
	} // while ( $st < $ed )
}

foreach ( scandir('.') as $e )
{
	if ( $e[0] === '.' )
		continue;
	mdcheck($e);
}
