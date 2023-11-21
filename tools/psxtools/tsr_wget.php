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

function valid_name( $fnam, $fext )
{
	// filename limit 255 chars
	$new = ( strlen($fnam) < 0x40 ) ? $fnam : substr($fnam, 0, 0x40);
	$i = 0;
	while ( $i < 10000 )
	{
		$fn1 = sprintf('%s.%d', $new, $i);
		$fn2 = sprintf('%s.%s', $fn1, $fext);
		if ( ! is_file($fn2) )
			return $fn1;
		$i++;
	}
	return -1;
}

function phpwget()
{
	$is_done = false;
	while ( ! $is_done )
	{
		echo "> type 'q' to quit\n";
		echo "> URL =\n";
		$input = trim( fgets(STDIN) );
		if ( $input === 'q' )
		{
			$is_done = true;
			continue;
		}

		// http://127.0.0.1/path/file.mp4?k=v&a=b
		$url = parse_url($input);
		if ( ! isset( $url['host'] ) )
		{
			echo "> ERROR : not an URL\n";
			continue;
		}

		if ( ! isset( $url['path'] ) )
		{
			echo "> ERROR : no URL path\n";
			continue;
		}

		$p = strrpos($url['path'], '/');
		$fname = ( $p === false ) ? $url['path'] : substr($url['path'], $p+1);

		$p = strrpos($fname, '.');
		if ( $p === false )
		{
			echo "> ERROR : no filename on URL\n";
			continue;
		}

		$fext = strtolower( substr($fname, $p + 1) );
		$fnam =  urldecode( substr($fname, 0, $p) );

		$fnam = valid_name($fnam, $fext);
		if ( $fnam === -1 )
		{
			echo "> ERROR : unable to generate valid filename\n";
			continue;
		}

		$fname = $fnam . '.' . $fext;
		printf("> wget  '%s'\n", $fname);

		$wget  = 'wget';
		$wget .= ' --quiet';
		$wget .= ' --no-config';
		$wget .= ' --no-check-certificate';
		$wget .= ' --user-agent="Mozilla/5.0 (Linux; Android 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.66 Mobile Safari/537.36"';
		$cmd = sprintf("%s '%s' -O '%s'", $wget, $input, $fname);
		exec($cmd);

		$fsz = filesize($fname);
		if ( $fsz < 1 )
		{
			echo "> ERROR : empty file. Deleted\n";
			unlink($fname);
		}
		else
			printf("> DONE : size %x bytes\n", $fsz);
	} // while ( ! $is_done )
	return;
}

phpwget();
