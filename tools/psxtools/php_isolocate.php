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
require "common-iso.inc";

function isoloc( &$list, $hex )
{
	$hex = hexdec($hex);
	foreach ( $list as $v )
	{
		$lba = $v['lba'] * 0x800;
		$end = $lba + $v['size'];
		if ( $hex >= $lba && $hex < $end )
		{
			$pos = $hex - $lba;
			printf("%8x = %s + %x\n", $hex, $v['file'], $pos);
			return;
		}
	} // foreach ( $list as $ent )
	return;
}

printf("%s  ISOFILE  OFFSET...\n", $argv[0]);
if ( $argc < 3 )  exit();

$isop = fopen($argv[1], 'rb+');
if ( ! $isop )  exit();

$list = lsiso_r($isop);
if ( empty($list) )  exit();

for ( $i=2; $i < $argc; $i++ )
	isoloc( $list, $argv[$i] );

fclose($isop);
