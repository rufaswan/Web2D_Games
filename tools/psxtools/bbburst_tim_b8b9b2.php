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
require 'bbburst.inc';

function burst_tim_b8( &$file )
{
	// 4-bpp , 40/row , 400/tile , 10/th
	$pos = 8;
		$palsz = str2int($file, $pos, 3);
		$pal = substr($file, $pos + 12, $palsz - 12);
			$pal = pal555($pal);
		$pos += $palsz;

		$w = str2int($file, $pos +  8, 2) << 2;
		$h = str2int($file, $pos + 10, 2);
		$pos += 12;
	$dec = burst_decode($file, $pos);

	bpp4to8($dec);
	$pix = $dec;
	$pos = 0;

	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	return $img;
}

function burst_tim_b9( &$file )
{
	// 8-bpp , 40/row , 400/tile , 10/th
	$pos = 8;
		$palsz = str2int($file, $pos, 3);
		$pal = substr($file, $pos + 12, $palsz - 12);
			$pal = pal555($pal);
		$pos += $palsz;

		$w = str2int($file, $pos +  8, 2) << 1;
		$h = str2int($file, $pos + 10, 2);
		$pos += 12;
	$dec = burst_decode($file, $pos);

	$pix = $dec;
	$pos = 0;

	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	return $img;
}

function burst_tim_b2( &$file )
{
	// rgb555 , 80/row , 2000/tile , 40/th
	$pos = 8;
		$w = str2int($file, $pos+ 8, 2);
		$h = str2int($file, $pos+10, 2);
		$pos += 12;
	$dec = burst_decode($file, $pos);

	$dec = pal555($dec);
	$pix = $dec;
	$pos = 0;

	for ( $ty=0; $ty < $h; $ty += 0x40 )
	{
		for ( $tx=0; $tx < $w; $tx += 0x20 )
		{
			for ( $y=0; $y < 0x40; $y++ )
			{
				$s = substr($dec, $pos, 0x80);
					$pos += 0x80;

				$dyy = ($ty + $y) * $w;
				$dxx = $dyy + $tx;
				str_update($pix, $dxx*4, $s);
			} // for ( $y=0; $y < 0x20; $y++ )
		} // for ( $tx=0; $tx < $w; $tx += 0x20 )
	} // for ( $ty=0; $ty < $h; $ty += 0x40 )

	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => $pix,
	);
	return $img;
}
//////////////////////////////
function burst( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( str2int($file, 0, 4) !== 0x10 )
		return;

	$dir = str_replace('.', '_', $fname);

	$func = 'burst_tim_' . bin2hex($file[4]);
	if ( ! function_exists($func) )
		return php_error('%s() not found', $func);

	$img = $func($file, $dir);
	save_clutfile("$dir.clut", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	burst( $argv[$i] );
