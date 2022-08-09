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
require 'common-guest.inc';

function sect2( &$file, $pos, $fn, &$pal )
{
	$b1 = str2big($file, $pos+ 0, 2);
	//$b2 = str2big($file, $pos+ 2, 2);
	//$b3 = str2big($file, $pos+ 4, 2);
	printf("== sect2( %x , %s ) = %x\n", $pos, $fn, $b1);
	if ( ($b1 & 0x8000) === 0 )
		return;

	$no = str2big($file, $pos+ 6, 2);
	$w  = str2big($file, $pos+ 8, 2);
	$h  = str2big($file, $pos+10, 2);
	printf("size = %x x %x\n", $w, $h);
		$pos += 12;

	$siz = $w * $h;
	$pix = '';
	while ( $siz > 0 )
	{
		$b1 = ord( $file[$pos] );
			$pos++;

		$flg = $b1 &  1;
		$clr = $b1 >> 1;
		if ( $flg )
		{
			$pix .= chr($clr);
			$siz--;
		}
		else
		{
			$b2 = ord( $file[$pos] );
				$pos++;
			$pix .= str_repeat(chr($clr), $b2);
				$siz -= $b2;
		}
	} // while ( $siz > 0 )

	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	save_clutfile($fn, $img);
	return;
}

function sect1( &$file, $pos, $dir, $id1, &$pal )
{
	$cnt = str2big($file, $pos, 2);
	printf("== sect1( %x , %s , %d ) = %x\n", $pos, $dir, $id1, $cnt);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $pos + 2 + ($i * 4);
		$b1 = str2big($file, $p, 4);

		$fn = sprintf('%s/%02d-%04d.clut', $dir, $id1, $i);
		sect2($file, $b1, $fn, $pal);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

function tengai4( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = str2big($file, 0, 4);
	if ( $b1 !== 0x84 )
		return;

	$dir = str_replace('.', '_', $fname);

	$b1 = str2big($file, 0x80, 4);
	$b2 = ord( $file[$b1] );
	$pal = substr($file, $b1+1, $b2*2);
	$pal = pal555( big2little16($pal) );
		$pal[3] = ZERO;

	for ( $i=0; $i < 0x20; $i++ )
	{
		$pos = $i * 4;
		if ( $file[$pos] === BYTE )
			continue;

		$b1 = str2big($file, $pos, 4);
		sect1($file, $b1, $dir, $i, $pal);
	} // for ( $i=0; $i < 0x21; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tengai4( $argv[$i] );
