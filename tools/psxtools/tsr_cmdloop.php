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

printf("%s  EXTENSION  \"COMMAND\"\n", $argv[0]);
echo "  note : remember to double-quote COMMAND\n";
if ( $argc != 3 )
	exit("not enough argument\n");

// https://docs.microsoft.com/en-us/troubleshoot/windows-client/shell-experience/command-line-string-limitation
// The maximum length of the string that you can use at the command prompt is 8191 characters.
//   8191 = 0x1fff
//
// This limitation applies to:
// - the command line
// - individual environment variables that are inherited by other processes, such as the PATH variable
// - all environment variable expansions
//
// this script is a workaround with PHP
$ext = trim( $argv[1] );
$cmd = trim( $argv[2] );
if ( empty($ext) || empty($cmd) )
	exit("empty EXTENSION or COMMAND\n");

$list = array();
lsfile_r('.', $list);
foreach ( $list as $f )
{
	$e = substr($f, strrpos($f, '.'));
	if ( stripos($e, $ext) === false )
		continue;

	// double-quote filename to handle spaces
	// single-quote in cmd.exe have no special meaning
	$c = escapeshellcmd("$cmd \"$f\"");
	echo "$c\n";

	$exit = -1;
	$out  = '';
	exec($c, $out, $exit);
	echo implode(PHP_EOL, $out);

	// Stop the script when error!
	// - when invalid COMMAND
	// - when run out of disk space
	// - when it went crazy
	if ( $exit !== 0 )
		exit("COMMAND encounter an error and cannot continue\n");
} // foreach ( $list as $f )
