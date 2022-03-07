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
/*
 * has AKAO sound data
 */
require 'common.inc';

define('CANV_S', 0x200);

function sectparts( &$file, &$canv, $off, $dir )
{
	printf("== sectparts( %x , $dir )\n", $off);
	$len = strlen($file);
	while ( $off < $len )
	{
		$dx = str2int($file, $off+0, 2) * 2;
		$dy = str2int($file, $off+2, 2);
		$w  = str2int($file, $off+4, 2) * 2;
		$h  = str2int($file, $off+6, 2);
			$off += 8;

		printf("%6x , %3d , %3d , %3d , %3d\n", $off-8, $dx, $dy, $w, $h);
		if ( ($dx + $w*2) > CANV_S )
			php_error('OVER dx [%d,%d]', $dx, $w);
		if ( ($dy + $h  ) > CANV_S )
			php_error('OVER dy [%d,%d]', $dy, $h);

		$pix = substr($file, $off, $w*$h);
			$off += ($w * $h);
		bpp4to8($pix);

		for ( $y=0; $y < $h; $y++ )
		{
			$syy = $y * $w * 2;
			$dyy = ($y + $dy) * CANV_S + ($dx * 2);
			$b = substr($pix, $syy, $w*2);
			str_update($canv, $dyy, $b);
		}
	} // while ( $off < $siz )
	return;
}

function saga2( $fname )
{
	// only EFF????.BIN files
	if ( ! preg_match('|EFF[0-9]+\.BIN|i', $fname) )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$clut = mstrpal555($file, 0x54, 0x10, 0x10);

	$canv = canvpix(CANV_S,CANV_S);
	$off  = str2int($file, 0x04, 4);
	sectparts( $file, $canv, $off, $dir );

	// FIXME : unknown cid , pair all pal with pix
	foreach ( $clut as $k => $c )
	{
		if ( trim($c, ZERO.BYTE) == '' )
			continue;

		$data = 'CLUT';
		$data .= chrint(0x10, 4); // no clut
		$data .= chrint(CANV_S, 4); // width
		$data .= chrint(CANV_S, 4); // height
		$data .= $c;
		$data .= $canv;

		$fn = sprintf('%s/%04d.clut', $dir, $k);
		save_file($fn, $data);
	} // foreach ( $clut as $k => $c )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
