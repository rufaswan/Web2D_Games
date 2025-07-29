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
require 'class-sh.inc';
sh::which('wget');

//////////////////////////////
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

function save_logfile( &$file )
{
	if ( empty($file) )
		return;

	$i = 0;
	while (1)
	{
		$fn = sprintf('phpwget.%d.log', $i);
		if ( ! file_exists($fn) )
			return file_put_contents($fn, $file);
		$i++;
	}
	return;
}

function phpwget()
{
	$is_done = false;
	$logfile = '';
	while ( ! $is_done )
	{
		$free = disk_free_space('.') >> 20;

		echo "> type 'q' to quit\n";
		echo "> Disk Free = $free MB\n";
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
		$logfile .= "$input\n";

		$fsz = sh::wget($input, $fname);
		if ( $fsz < 1 )
			printf("> ERROR : empty file. Deleted\n");
		else
			printf("> DONE : size %x bytes\n", $fsz);
	} // while ( ! $is_done )

	save_logfile($logfile);
	return;
}

phpwget();
