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

function rgbaclut( $fn, $clr, $rgba )
{
	echo "== rgbaclut( $fn )\n";
	$pix = '';
	$len = strlen( $rgba['pix'] );
	for ( $i=0; $i < $len; $i += 4 )
	{
		$c = substr($rgba['pix'], $i, 4);
		$p = array_search($c, $clr);
		$pix .= chr($p);
	}

	$pal = implode('', $clr);

	$rgba['cc']  = count($clr);
	$rgba['pal'] = $pal;
	$rgba['pix'] = $pix;
	save_clutfile($fn, $rgba);
	return;
}

function rgbabits( $fn, $bit, $rgba )
{
	echo "== rgbabits( $fn )\n";
	$len = strlen( $rgba['pix'] );

	$bit <<= 4;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $rgba['pix'][$i] );
		$b &= $bit;
		$b |= ($b   >> 4);
		$rgba['pix'][$i] = chr($b);
	}

	$clr = [];
	for ( $i=0; $i < $len; $i += 4 )
	{
		$c = substr($rgba['pix'], $i, 4);
		$clr[$c] = 1;
	}
	return rgbaclut($fn, array_keys($clr), $rgba);
}
//////////////////////////////
function rgba256( $fname )
{
	$rgba = load_clutfile($fname);
	if ( $rgba === 0 )  return;

	// already clut
	if ( isset($rgba['pal']) )
		return;

	// get color + count
	$clr = [];
	$len = strlen( $rgba['pix'] );
	for ( $i=0; $i < $len; $i += 4 )
	{
		$c = substr($rgba['pix'], $i, 4);
		$clr[$c] = 1;
	}

	if ( count($clr) <= 0x100 )
		return rgbaclut("$fname.clut", array_keys($clr), $rgba);

	// get AND bits
	foreach ( [0xf,0xe,0xc,0x8,0] as $bit )
	{
		printf("AND %2x\n", $bit);
		$new  = [];
		$mask = $bit * 0x10101010;
		foreach ( $clr as $ck => $cv )
		{
			$nc = ordint($ck) & $mask;
			$new[$nc] = 1;
		}

		if ( count($new) <= 0x100 )
			return rgbabits("$fname.clut", $bit, $rgba);
	} // foreach ( [0xf,0xe,0xc,0x8,0] as $bit )
	return;
}

printf("%s  RGBA_FILE...\n", $argv[0]);
for ( $i=1; $i < $argc; $i++ )
	rgba256( $argv[$i] );
