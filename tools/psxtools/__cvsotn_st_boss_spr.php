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
require 'cvsotn.inc';

define('BYTE4', BYTE.BYTE.BYTE.BYTE);

function sectgfx( &$file, $b24 )
{
	$gfx = array();
	for ( $i=0;; $i++ )
	{
		$pos = $b24 + ($i * 4);
		$off = bossoff($file, $pos);
		if ( $off < 0 )
			continue;
		if ( $off > $pos )
			break;

		if ( substr($file,$off,4) === BYTE4 )
			continue;
		printf("%6x  gfx %d\n", $off, $i);
			$off += 4;

		for ( $j=0;; $j++ )
		{
			$s = substr($file, $off, 12);
				$off += 12;
			echo debug($s, $j);
			if ( substr($s,0,4) === BYTE4 )
				break;

			$spr_off = bossoff($s, 8);
			if ( $spr_off < 0 )
				continue;
			$dec = sotn_decode($file, $spr_off);

			$x = str2int($s, 0, 2);
			$y = str2int($s, 2, 2);
			$w = str2int($s, 4, 2);
			$h = str2int($s, 6, 2);
			$img = array(
				'cc'  => 0x10,
				'w'   => $w,
				'h'   => $h,
				'pal' => grayclut(0x10),
				'pix' => $dec,
				'x'   => $x,
				'y'   => $y,
			);

			$gfx[$i][$j] = $img;
		} // for ( $j=0;; $j++ )
	} // for ( $i=0;; $i++ )
	return $gfx;
}

function sectspr( &$file, $b14 )
{
	$spr = array();
	for ( $i=1;; $i++ )
	{
		$pos = $b14 + ($i * 4);
		$off = bossoff($file, $pos);
		if ( $off < 0 )
			break;

		for ( $j=1;; $j++ )
		{
			$pos = $off + ($j * 4);
			$of2 = bossoff($file, $pos);
			if ( $of2 < 0 )
				break;

			$cnt = str2int($file, $of2, 2);
				$of2 += 2;
			printf("%6x  %6x  spr %d %d = %x\n", $off, $of2 - 2, $i, $j, $cnt);
			for ( $k=0; $k < $cnt; $k++ )
			{
				$s = substr($file, $of2, 0x16);
					$of2 += 0x16;
				echo debug($s, $k);

				$spr[$i][$j][$k] = $s;
			} // for ( $k=0; $k < $cnt; $k++ )
		} // for ( $j=1;; $j++ )
	} // for ( $i=1;; $i++ )
	return $spr;
}

function sectpal( &$file, $b18 )
{
	$pal = array();
	for ( $i=0;; $i++ )
	{
		$pos = $b18 + ($i * 4);
		$off = bossoff($file, $pos);
		if ( $off < 0 )
			break;

		if ( substr($file,$off,4) === BYTE4 )
			continue;
		printf("%6x  pal %d\n", $off, $i);
			$off += 4;

		for ( $j=0;; $j++ )
		{
			$s = substr($file, $off, 12);
				$off += 12;
			echo debug($s, $j);
			if ( substr($s,0,4) === BYTE4 )
				break;

			$pal_off = bossoff($s, 8);
			$p = '';
			while (1)
			{
				$c = substr($file, $pal_off, 0x20);
					$pal_off += 0x20;
				if ( trim($c,"\x00\x80") === '' )
					break;
				$p .= $c;
			} // while (1)

			$pal[$i][$j] = pal555($p);
		} // for ( $j=0;; $j++ )
	} // for ( $i=0;; $i++ )
	return $pal;
}
//////////////////////////////
function psxsotn( $fname )
{
	$bin = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$b00 = bossoff($bin, 0x00); // mips func
	$b04 = bossoff($bin, 0x04); // mips func
	$b08 = bossoff($bin, 0x08); // mips func
	$b0c = bossoff($bin, 0x0c); // mips func
	if ( $b00 < 0 || $b04 < 0 || $b08 < 0 || $b0c < 0 )
		return;

	$b10 = bossoff($bin, 0x10);
	$b14 = bossoff($bin, 0x14); // spr
	$b18 = bossoff($bin, 0x18); // palettes
	$b1c = bossoff($bin, 0x1c);
	$b20 = bossoff($bin, 0x20); // map gfx
	$b24 = bossoff($bin, 0x24); // spr gfx
	$b28 = bossoff($bin, 0x28); // mips func

	printf("== %s\n", $fname);
	$gfx = sectgfx($bin, $b24);
	$spr = sectspr($bin, $b14);

	foreach ( $gfx as $gk => $gv )
	{
		foreach ( $gv as $gvk => $gvv )
		{
			$fn = sprintf('%s/gfx/%d-%d.clut', $dir, $gk, $gvk);
			save_clutfile($fn, $gvv);
		}
	}
/*
	$pal = sectpal($bin, $b18);
	foreach ( $pal as $pk => $pv )
	{
		foreach ( $pv as $pvk => $pvv )
		{
			$fn = sprintf('%s/pal/%d-%d.pal', $dir, $pk, $pvk);
			save_file($fn, $pvv);
		}
	}
*/
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxsotn( $argv[$i] );

/*
b14
	2c  bo[01235]  rbo[023478]  no[01234]  rno[0123]  np3  nz0  te[12345]
		mar  are  cat  dai  lib  rare  rcat  rchi  rdai  rlib
	34  bo[46]  rbo5
		top
	40  bo7  rbo[16]  nz1  rno4  rnz[01]  st0
		cen  chi  dre  mad  wrp  rcen  rtop  rwrp
	3e4  sel
 */
