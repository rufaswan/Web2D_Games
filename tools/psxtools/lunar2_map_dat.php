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

function updmap( &$canvas, &$src, $dx, $dy, $w, $h )
{
	for ( $y=0; $y < 8; $y++ )
	{
		$row = substr($src, $y*8, 8);
		$dxx = ($dy + $y) * $w + $dx;
		str_update($canvas, $dxx, $row);
	}
	return;
}

function sectmap( &$file, &$pix, &$pal, $pos, $dir, $mw, $mh)
{
	printf("== sectmap( %x , $dir , %x , %x )\n", $pos, $mw, $mh);
	if ( $pos == 0 )
		return;
	$map_w = $mw * 8;
	$map_h = $mh * 8;
	echo "map : $map_w x $map_h\n";

	$canv1 = str_repeat(ZERO, $map_w*$map_h);
	$canv2 = str_repeat(ZERO, $map_w*$map_h);
	$buf = "";
	for ( $y=0; $y < $map_h; $y += 8 )
	{
		for ( $x=0; $x < $map_w; $x += 8 )
		{
			$dat = str2int($file, $pos, 4);
				$pos += 4;
			$buf .= sprintf("%8x ", $dat);

			$tid = ($dat >> 0) & 0xfff;
			$c = $tid % 0x10;
			$r = (int)($tid / 0x10);
			$sx = $c * 8;
			$sy = $r * 8;
			$src = rippix8($pix, $sx, $sy, 8, 8, 0x80, 0x2000);
			updmap($canv1, $src, $x, $y, $map_w, $map_h);

			$tid = ($dat >> 12) & 0xfff;
			$c = $tid % 0x10;
			$r = (int)($tid / 0x10);
			$sx = $c * 8;
			$sy = $r * 8;
			$src = rippix8($pix, $sx, $sy, 8, 8, 0x80, 0x2000);
			updmap($canv2, $src, $x, $y, $map_w, $map_h);

		} // for ( $x=0; $x < $map_w; $x++ )
		$buf .= "\n";

	} // for ( $y=0; $y < $map_h; $y++ )
	echo "$buf\n";

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($map_w, 4);
	$clut .= chrint($map_h, 4);
	$clut .= $pal;
	$clut .= $canv1;
	save_file("{$dir}-1.clut", $clut);

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($map_w, 4);
	$clut .= chrint($map_h, 4);
	$clut .= $pal;
	$clut .= $canv2;
	save_file("{$dir}-2.clut", $clut);
	return;
}

function lunar2( $fname )
{
	// for mp*.dat only
	if ( ! preg_match('|mp.*\.dat|i', $fname) )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = str2int($file,  0, 4); // filesize
	$b2 = str2int($file,  4, 4); // pix offset
	//$b3 = str2int($file,  8, 4); // map 1 offset
	//$b4 = str2int($file, 12, 4); // map 2 offset
	$b5 = str2int($file, 16, 4); // pal offset
	if ( strlen($file) != $b1 )
		return;

	$dir = str_replace('.', '_', $fname);
	$pal = pal555( substr($file, $b5, 0x200) );
	$pix = substr($file, $b2);

	// tbl/pp*.dat reuse palette from map/mp*.dat
	$paln = str_ireplace(array('mp','.dat'), array('pp','.pal'), $fname);
	file_put_contents($paln, $pal);

	// tileset
		$w = 0x80;
		$h = strlen($pix) / 0x80;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $pal;
		$clut .= $pix;
		save_file("$dir/pix.clut", $clut);

	// background layer
		$pos = str2int($file, 8, 4);
		$w = str2int($file, 0x28, 2);
		$h = str2int($file, 0x2a, 2);
		sectmap($file, $pix, $pal, $pos, "$dir/0", $w, $h);

	// foreground layer
		$pos = str2int($file, 12, 4);
		$w = str2int($file, 0x2c, 2);
		$h = str2int($file, 0x2e, 2);
		sectmap($file, $pix, $pal, $pos, "$dir/1", $w, $h);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
