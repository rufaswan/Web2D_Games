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

function lunar2( $fname )
{
	// for btlbk*.pck only
	if ( preg_match('|btlbk[0-9]+\.pck|', $fname) === false )
		return;

	$pck = file_get_contents($fname);
	if ( empty($pck) )  return;

	 // all 2 (pix , clut)
	if ( str2int($pck,0,4) !== 2 )
		return;

	$pos = 4;
	$sz1 = str2int($pck, $pos+0, 4);
	$pix = substr ($pck, $pos+4, $sz1);

	$pos += (4 + $sz1);
	$sz2 = str2int($pck, $pos+0, 4);
	$pal = substr ($pck, $pos+4, $sz2);
		$pal = pal555($pal);

	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => strlen($pix) >> 8,
		'h'   => 0x100,
		'pal' => $pal,
		'pix' => $pix,
	);
	save_clutfile("$fname.clut", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
