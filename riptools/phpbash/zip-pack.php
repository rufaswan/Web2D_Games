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
tool::require('class-zipstore');

function dir2zip( string $dir ) : void
{
	if ( ! is_dir($dir) )
		return;

	// use $dir name for zip file
	// .                 -> ./phpbash.zip
	// riptools/phpbash/ -> ./phpbase.zip
	$base = sh::realbase($dir);
	if ( empty($base) )
		return;

	$zip  = new zipstore;
	$list = $zip->scan($dir);
	if ( empty($list) )
		return;
	usort($list, [$zip,'sort_size_asc']);

	$zip->save($base.'.zip', $list, $dir);
}

for ( $i=1; $i < $argc; $i++ )
	dir2zip( $argv[$i] );
