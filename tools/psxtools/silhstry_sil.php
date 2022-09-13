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

function silhstry( $fname )
{
	// for *.sil only
	if ( stripos($fname, '.sil') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$w = str2int($file, 0, 4);
	$h = str2int($file, 4, 4);
	printf("%x x %x  %s\n", $w, $h, $fname);

	$img = array(
		'cc'  => 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => '',
		'pix' => '',
	);
	$img['pal'] .= ZERO . ZERO . ZERO . BYTE;
	$img['pal'] .= BYTE . BYTE . BYTE . BYTE;

	$ed = strlen($file);
	$st = 12;
	while ( $st < $ed )
	{
		$by = ord($file[$st]);
			$st++;
		for ( $i=0; $i < 8; $i++ )
		{
			$bt = ($by >> $i) & 1;
			$img['pix'] .= chr($bt);
		}
	} // while ( $st < $ed )

	save_clutfile("$fname.clut", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	silhstry( $argv[$i] );
