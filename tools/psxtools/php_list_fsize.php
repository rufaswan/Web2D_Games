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
require "common.inc";

$list = array();
lsfile_r('.', $list);

if ( empty($list) )
	exit("no file\n");

$fsz = array();
$ext = array();
$all = 0;
foreach ( $list as $f )
{
	$sz  = filesize($f);
	$all += $sz;

	if ( ! isset($fsz[$sz]) )
		$fsz[$sz] = array();
	$fsz[$sz][] = $f;

	$e = substr($f, strrpos($f, '.')+1);
	if ( $e[0] === '/' )
		$e = ' ';
	if ( ! isset($ext[$e]) )
		$ext[$e] = 0;
	$ext[$e] += $sz;
} // foreach ( $list as $f )

if ( empty($fsz) )
	exit("no file\n");
ksort($fsz);

foreach ( $fsz as $fz => $list )
{
	foreach ( $list as $f )
		printf("[%4.1f%%]  %8x  %s\n", $fz/$all*100, $fz, $f);
} // foreach ( $fsz as $fz => $list )

foreach ( $ext as $e => $sz )
	printf("[%4.1f%%]  %8x  EXT %s\n", $sz/$all*100, $sz, $e);
