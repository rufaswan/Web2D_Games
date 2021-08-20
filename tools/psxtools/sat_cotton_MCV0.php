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
require "common-guest.inc";

function sect_map( &$tile, &$stack, $mw, $mh, $dir )
{
	printf("== sect_map( %x , %x , %s )\n", $mw, $mh, $dir);

	$w = $mw * 8;
	$h = $mh * 8;

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = canvpix($w,$h);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;
	$pix['src']['pal'] = $stack['pal'];

	$pos = 0;
	$map = '';
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b1 = str2big($tile, $pos+0, 4, true);
				$pos += 4;
			$map .= sprintf("%4x ", $b1 & BIT16);

			if ( $b1 < 0 )
				continue;

			$tid = $b1;

			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$pix['src']['pix'] = substr($stack['pix'], $tid*0x40, 0x40);

			copypix_fast($pix);
		} // for ( $x=0; $x < $w; $x += 8 )
		$map .= "\n";

	} // for ( $y=0; $y < $h; $y += 8 )
	echo "$map\n";

	savepix($dir, $pix, false);
	return;
}
//////////////////////////////
function cotton( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "MCV0" )
		return;

	$dir = str_replace('.', '_', $fname);
	$id = 0;
	$stack = array();

	$len = strlen($file);
	$pos = 0x50;
	while ( $pos < $len )
	{
		$bak = $pos;
		$siz = str2big($file, $pos, 4);
		$sub = substr ($file, $pos, $siz);
			$pos += $siz;

		$fn = sprintf("%s/%04d", $dir, $id);
			$id++;

		$head = substr($sub, 0, 0x18);
		$sub  = substr($sub, 0x18);
		echo debug($head, $fn);

		if ( empty($sub) )
			continue;

		$mid = str2big($head, 0x06, 2);
		$mw  = str2big($head, 0x14, 2);
		$mh  = str2big($head, 0x16, 2);

		$b1 = $mw * $mh * 4;
		$tile = substr($sub, 0, $b1);
		$sub  = substr($sub, $b1);

		$b1 = strlen($sub);
		if ( ! isset($stack[$mid]) )
		{
			if ( $b1 < 0x200 ) // no palette
				continue;
			$stack[$mid]['pix'] = substr($sub, 0, $b1-0x200);
			$b2 = substr($sub, $b1-0x200);
			$stack[$mid]['pal'] = pal555( big2little16($b2) );
		}
		else
		{
			$stack[$mid]['pix'] .= $sub;
		}

		sect_map($tile, $stack[$mid], $mw, $mh, $fn);
	} // while ( $pos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cotton( $argv[$i] );
