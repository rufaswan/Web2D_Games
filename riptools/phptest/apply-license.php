<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
//////////////////////////////
declare( strict_types=1 );

$APP_NAME = 'Web2D Games';
$AUTHOR   = 'Rufas Wan';
$GITHUB   = 'https://github.com/rufaswan/Web2D_Games';

$LICENSE = <<<_TXT
Copyright (C) 2019 by {$AUTHOR}

This file is part of {$APP_NAME}.
    <{$GITHUB}>

{$APP_NAME} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

{$APP_NAME} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {$APP_NAME}.  If not, see <http://www.gnu.org/licenses/>.
_TXT;

function set_license( string $fname ) : void
{
	if ( ! is_file($fname) )
		return;

	$file = file($fname);
	if ( empty($file) )
		return;

	$st = 0;
	$ed = 0;
	foreach ( $file as $k => $v )
	{
		$file[$k] = rtrim($v);
		if ( $file[$k] === '[license]'  )  $st++;
		if ( $file[$k] === '[/license]' )  $ed++;
	} // foreach ( $file as $k => $v )

	if ( $st < 1 )
		return;
	if ( $st !== $ed )
		return;
	$bak = implode("\n", $file);

	global $LICENSE;

	$new = [];
	while ( ! empty($file) )
	{
		$line = array_shift($file);
		if ($line === '[/license]')
			$new[] = $LICENSE;
		$new[] = $line;
	} // while ( ! empty($file) )

	$file = implode("\n", $new);
	if ( $bak !== $file )
		file_put_contents($fname, $file);
}

if ( $argc == 1 )  exit();
for ( $i=1; $i < $argc; $i++ )
	set_license( $argv[$i] );
