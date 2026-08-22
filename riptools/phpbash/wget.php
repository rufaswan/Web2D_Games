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
tool::require('func-sh');
sh::which('wget');

function phpwget_url( string $input ) : int
{
	// http://127.0.0.1/path/file.mp4?k=v&a=b
	// [
	//   scheme => http
	//   host   => 127.0.0.1
	//   path   => /path/file.mp4
	//   query  => k=v&a=b
	// ]
	$url = parse_url($input);
	if ( ! isset( $url['host'] ) )
		return tool::warning('not an URL', $input);

	if ( ! isset( $url['path'] ) )
		return tool::warning('no URL path');

	$fname = tool::safepath( $url['path'], 8, 0x40 );
	if ( empty($fname) )
		return tool::warning('unable to generate valid filename');
	printf("> wget  '%s'\n", $fname);

	$file = sh::wget($input, $fname);
	if ( empty($file) )
		return tool::warning('empty file. Deleted');
	else
		return tool::notice('> DONE : size', filesize($fname));
}

function phpwget() : void
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

		$r = phpwget_url($input);
		if ( $r >= 0 )
			$logfile .= "$input\n";
	} // while ( ! $is_done )

	tool::save('phpwget.log', $logfile, 1);
}

phpwget();
