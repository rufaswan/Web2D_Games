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
require 'lunar1.inc';

define('NO_TRACE', 1);

$gp_clut = '';

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_clut;
	if ( stripos($fname, 'peo.pal') !== false )
	{
		$gp_clut = pal555($file);
		return;
	}

	if ( substr($file, 0, 4) !== 'FACE' )
		return;
	if ( empty($gp_clut) )
		$gp_clut = grayclut(0x100);

	$sub = substr($file, 0x20);
	$sub = lunar_decode($sub);
	//save_file("$fname.dec", $sub);

	$img = array(
		'cc'  => 0x100,
		'w'   => 0x40,
		'h'   => (int)(strlen($sub) / 0x40),
		'pal' => $gp_clut,
		'pix' => $sub,
	);
	save_clutfile("$fname.clut", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );

/*
RAM 801a9b00 = face_005.tiz
RAM 801acb00 = face_022.tiz
RAM 801afb00 = face_001.tiz
	8001cfc0  lw  v0, 8(a0[801adb00])

RAM 801b2b00 = face_012.tiz
RAM 801b5b00 = face_002.tiz
RAM 801b8b00 = face_004.tiz
RAM 801bbb00 = face_003.bin
 */
