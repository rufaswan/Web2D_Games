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

echo "<style>* { margin:0;padding:0; } img { border:3px #f00 solid; }</style>\n";
$list = array();
lsfile_r('.', $list);
foreach ( $list as $f )
{
	$e = substr($f, strrpos($f, '.'));
	if ( stripos($e, 'png') === false )
		continue;

	printf("<img src='%s' title='%s'>\n", $f, $f);
} // foreach ( $list as $f )
