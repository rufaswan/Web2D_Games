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

define('CANV_S', 0x300);

$gp_pix  = array();
$gp_clut = '';

function loadsrc( &$tml, $id, $pos )
{
	$type = ord( $tml[$pos+4] );
	printf("== loadsrc( $id , %x ) = $type\n", $pos);
		$pos += 8;

	global $gp_pix, $gp_clut;
	if ( $type == 9 )
	{
		$pal = substr($tml, $pos+12, 0x200);
		$gp_clut = pal555($pal);
		$pos += 0x20c;
	}
	if ( $type == 9 || $type == 1 )
	{
		$w = str2int($tml, $pos+ 8, 2) * 2;
		$h = str2int($tml, $pos+10, 2);
		$p = substr($tml, $pos+12, $w*$h);
		$gp_pix[$id] = array($p, $w, $h);
	}
	return;
}

function sectparts( &$tml, &$anim, &$layout, $dir, $id, $name, $cnt, $data)
{
	if ( $cnt == 0 )
		return;
	printf("== sectparts( $dir , $id , $name , $cnt , %x )\n", $data);

	global $gp_clut, $gp_pix;
	if ( $tml[$data] != ZERO )
	{
		$pix = COPYPIX_DEF(CANV_S,CANV_S);

		for ( $i=0; $i < $cnt; $i++ )
		{
			// 0  1    2   3   4  5  6 7  8 9  a b
			// -  tid  sx  sy  w  h  dx   dy   fps
			$p = $data + ($i * 12);

			$tid = ord( $tml[$p+1] );
			$sx = ord( $tml[$p+2] );
			$sy = ord( $tml[$p+3] );
			$w  = ord( $tml[$p+4] );
			$h  = ord( $tml[$p+5] );
			$dx = str2int($tml, $p+6, 2);
			$dy = str2int($tml, $p+8, 2);

			$pix['dx'] = $dx;
			$pix['dy'] = $dy;
			$pix['src']['w'] = $w;
			$pix['src']['h'] = $h;
			$pix['src']['pix'] = rippix8($gp_pix[$tid][0], $sx, $sy, $w, $h, $gp_pix[$tid][1], $gp_pix[$tid][2]);
			$pix['src']['pal'] = $gp_clut;

			printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
			printf(" , $tid\n");
			copypix_fast($pix);
		} // for ( $i=0; $i < $cnt; $i++ )

		savepix("$dir/$name", $pix);
		return;
	}
	else
	{
		$an   = array();
		$lay  = array();
		$done = array();
		$anim   .= "anim_{$name} = ";
		$layout .= "$name = ";
		for ( $i=0; $i < $cnt; $i++ )
		{
			// 0  1    2   3   4  5  6 7  8 9  a b
			// -  tid  sx  sy  w  h  dx   dy   fps
			$p = $data + ($i * 12);

			$w  = ord( $tml[$p+4] );
			$h  = ord( $tml[$p+5] );
			if ( $w == 0 || $h == 0 )
				continue;

			$tid = ord( $tml[$p+1] );
			$sx = ord( $tml[$p+2] );
			$sy = ord( $tml[$p+3] );
			$dx = str2int($tml, $p+6, 2);
			$dy = str2int($tml, $p+8, 2);

			$id = "$sx,$sy,$w,$h";
			if ( array_search($id, $done) === false )
				$done[] = $id;
			$pid = array_search($id, $done);

			$fps = str2int($tml, $p+10, 2);
			$an[] = "$pid-$fps";
			$lay[$pid] = sprintf('%s/%04d+%d+%d', $name, $pid, $dx, $dy);

			printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
			printf(" , $tid , $pid , $fps\n");

			$clut = 'CLUT';
			$clut .= chrint(0x100, 4);
			$clut .= chrint($w, 4);
			$clut .= chrint($h, 4);
			$clut .= $gp_clut;
			$clut .= rippix8($gp_pix[$tid][0], $sx, $sy, $w, $h, $gp_pix[$tid][1], $gp_pix[$tid][2]);

			$fn = sprintf('%s/%s/%04d.clut', $dir, $name, $pid);
			save_file($fn, $clut);
		} // for ( $i=0; $i < $cnt; $i++ )

		$anim   .= implode(' , ', $an ) . "\n";
		$layout .= implode(' , ', $lay) . "\n";
		return;
	}
	return;
}
//////////////////////////////
function tm2s( $fname )
{
	$tml = file_get_contents($fname);
	if ( empty($tml) )  return;

	if ( substr($tml, 0, 4) !== 'TML0' )
		return;

	$dir = str_replace('.', '_', $fname);

	$tmno = str2int($tml, 0x08, 3);
	$dtno = str2int($tml, 0x0c, 3);
	$tmst = str2int($tml, 0x10, 3);
	$dtst = str2int($tml, 0x14, 3);

	global $gp_clut, $gp_pix;
	$gp_pix  = array();
	$gp_clut = '';
	for ( $i=0; $i < $tmno; $i++ )
	{
		$p = 0x20 + ($i * 4);
		$p = str2int($tml, $p, 3);
		loadsrc($tml, $i, $p);
	}

	$anim   = '';
	$layout = '';
	for ( $i=0; $i < $dtno; $i++ )
	{
		$p = $dtst + ($i * 4);
		$p = str2int($tml, $p, 3);
		$b1 = str2int($tml, $p+0, 3);
			$b1 = substr0($tml, $b1);
		$b2 = str2int($tml, $p+4, 3);
		$b3 = str2int($tml, $p+8, 3);
		sectparts($tml, $anim, $layout, $dir, $i, $b1, $b2, $b3);
	}
	save_file("$dir/anim.txt"  , $anim);
	save_file("$dir/layout.txt", $layout);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tm2s( $argv[$i] );
