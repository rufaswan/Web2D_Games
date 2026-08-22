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

function movemagic( string $ent ) : void
{
	if ( ! is_file($ent) )
		return;

	// use fopen() in case 1 GB+ file
	// we only need the first 4 bytes only, not the whole file
	$fp = fopen($ent, 'rb');
	if ( ! $fp )
		return;

	$mgc = fread($fp, 4);
	fclose($fp);

	$mgc = preg_replace('|[^A-Za-z0-9]+|', '', $mgc);
	if ( strlen($mgc) < 3 ) // at least ABC\0
		return;

	$name = substr($ent, 0, strrpos($ent, '.'));
	$ext  = strtolower($mgc);
	$new  = "$name.$ext";
	if ( $new === $ent )
		return;

	tool::trace('[MOVE]', $ent, $new);
	tool::move($ent, $new);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	movemagic( $argv[$i] );

/*
non-alnum
60 01 01 80  PSX STR
10 00 00 00  PSX TIM
 */
