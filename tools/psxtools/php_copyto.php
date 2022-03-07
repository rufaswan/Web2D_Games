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

printf("%s  SRC_DIR  DST_DIR\n", $argv[0]);
if ( $argc != 3 )  exit();
if ( ! is_dir($argv[1]) )  exit();
if ( ! is_dir($argv[2]) )  exit();

$src = rtrim($argv[1], '/\\');
$dst = rtrim($argv[2], '/\\');

$list = array();
lsfile_r($src, $list);
sort($list);

foreach ( $list as $f )
{
	$copy = "$dst/$f";
	if ( file_exists($copy) )
	{
		echo "SKIP $copy\n";
		continue;
	}

	$file = file_get_contents($f);
	printf("[%x] COPY %s -> %s\n", strlen($file), $f, $copy);
	save_file($copy, $file);
} // foreach ( $list as $f )
