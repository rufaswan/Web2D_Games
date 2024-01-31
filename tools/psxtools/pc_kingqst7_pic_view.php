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
 *
 * Special Thanks
 *   ScummVM
 *   https://github.com/scummvm/scummvm/tree/master/engines/sci/graphics/picture.cpp
 *   https://github.com/scummvm/scummvm/tree/master/engines/sci/graphics/view.cpp
 *   https://github.com/scummvm/scummvm/tree/master/engines/sci/graphics/palette.cpp
 */
require 'common.inc';

function viewpal( &$file, $off )
{
	if ( $off === 0 )
		return grayclut(0x100);

	// 0123456789abcdef 012345678  9  abc  de  f  0  1234
	// ---------------- ---------  s  ---  cn  -  t  ----
	$st  = str2int($file, $off + 0x19, 1);
	$cnt = str2int($file, $off + 0x1d, 2);
	$typ = str2int($file, $off + 0x20, 1);

	$pal = '';
	if ( $st > 0 )
		$pal .= str_repeat(ZERO, $st*4);

	$pos = $off + 0x25;
	if ( $typ ) // 1 constant
	{
		for ( $i=0; $i < $cnt; $i++ )
		{
			$pal .= $file[$pos+0]; // r
			$pal .= $file[$pos+1]; // g
			$pal .= $file[$pos+2]; // b
			$pal .= BYTE; // a
				$pos += 3;
		} // for ( $i=0; $i < $cnt; $i++ )
	}
	else // 0 variable
	{
		for ( $i=0; $i < $cnt; $i++ )
		{
			$pal .= $file[$pos+1]; // r
			$pal .= $file[$pos+2]; // g
			$pal .= $file[$pos+3]; // b
			$pal .= ( $file[$pos+0] === ZERO ) ? ZERO : BYTE; // a
				$pos += 4;
		} // for ( $i=0; $i < $cnt; $i++ )
	}
	return $pal;
}

function viewpix( &$file, $siz, $off1, $off2, $zero )
{
	$pix = '';
	while ( $siz > 0 )
	{
		$by = ord( $file[$off1] );
			$off1++;
		$b1 = $by >> 6;
		$b2 = $by & 0x3f;
		switch ( $b1 )
		{
			case 1:
				$b2 += 0x40;
			case 0:
				$pix  .= substr($file, $off2, $b2);
				$off2 += $b2;
				$siz  -= $b2;
				break;
			case 2:
				$by = $file[$off2];
					$off2++;
				$pix .= str_repeat($by, $b2);
				$siz -= $b2;
				break;
			case 3:
				$pix .= str_repeat($zero, $b2);
				$siz -= $b2;
				break;
		} // switch ( $b1 )
	} // while ( $siz > 0 )
	return $pix;
}

function viewfile( $fname )
{
	printf("== viewfile( %s )\n", $fname);

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$head_siz = str2int($file,  0, 2);
	$head = substr($file, 2, $head_siz);
	printf("HEAD : %s\n", printhex($head,2));
	if ( $head_siz !== 0x10 && $head_siz !== 0x12 )
		return php_error('head size != 10 or 12 [%x]', $head_siz);

	// 0  1  2345  6789  a  b  cd  ef
	// c  f  ----  pal   z  z  w   h
	$anim_cnt = str2int($head,  0, 1);
	$pal_off  = str2int($head,  6, 4);
	$siz1 = str2int($head, 10, 1);
	$siz2 = str2int($head, 11, 1);

	$pal = viewpal($file, $pal_off);
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => -1,
		'h'   => -1,
		'pal' => $pal,
		'pix' => '',
	);

	$anim_pos = $head_siz + 2;
	for ( $ai=0; $ai < $anim_cnt; $ai++ )
	{
		// 01  2  3456789ab  cdef
		// p   c  ---------  pos
		$anim_sub = substr($file, $anim_pos, $siz1);
			$anim_pos += $siz1;
		printf("%2x : %s\n", $ai, printhex($anim_sub,4));

		$cnt = str2int($anim_sub,  2, 1);
		$pos = str2int($anim_sub, 12, 4);
		if ( $cnt < 1 )
			continue;

		for ( $ki=0; $ki < $cnt; $ki++ )
		{
			// 01  23  45  67  8  9abcdef  01234567  89ab  cdef  0123
			// w   h   dx  dy  z  -------  --------  off   off   ----
			$key_sub = substr($file, $pos, $siz2);
				$pos += $siz2;
			printf("  %2x : %s\n", $ki, printhex($key_sub,4));

			$w = str2int($key_sub, 0, 2);
			$h = str2int($key_sub, 2, 2);
			$siz = $w * $h;

			$zero = $key_sub[8];
			$off1 = str2int($key_sub, 0x18, 4); // rle
			$off2 = str2int($key_sub, 0x1c, 4); // literal

			$img['w'] = $w;
			$img['h'] = $h;
			$img['pix'] = '';
			if ( $off2 === 0 )
				$img['pix'] = substr($file, $off1, $siz);
			else
				$img['pix'] = viewpix($file, $siz, $off1, $off2, $zero);

			$fn = sprintf('%s/%04d-%04d.clut', $dir, $ai, $ki);
			save_clutfile($fn, $img);
		} // for ( $ki=0; $ki < $cnt; $ki++ )
	} // for ( $ai=0; $ai < $anim_cnt; $ai++ )
	return;
}
//////////////////////////////
function picfile( $fname )
{
	printf("== picfile( %s )\n", $fname);

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$head_siz = str2int($file, 0, 2);
	$head = substr($file, 0, $head_siz);
	printf("HEAD = %s\n", printhex($head,2));
	if ( $head_siz !== 0xe )
		return php_error('head size != e [%x]', $head_siz);

	// 01  2  3  45  6789  ab  cd
	// ps  c  p  sz  pal   w   h
	$dat_off = str2int($head,  0, 2);
	$dat_cnt = str2int($head,  2, 1);
	$dat_siz = str2int($head,  4, 2);
	$pal_off = str2int($head,  6, 4);
	$scr_w   = str2int($head, 10, 2);
	$scr_h   = str2int($head, 12, 2);

	$pal = viewpal($file, $pal_off);
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => -1,
		'h'   => -1,
		'pal' => $pal,
		'pix' => '',
	);

	for ( $i=0; $i < $dat_cnt; $i++ )
	{
		$dat_sub = substr($file, $dat_off, $dat_siz);
		printf("%x : %s\n", $i, printhex($dat_sub,4));
			$dat_off += $dat_siz;

		// 01  23  45  67  8  9abcdef 01234567  89ab  cdef 0123456789
		// w   h   dx  dy  z  ------- --------  off   off  ----------
		$w = str2int($dat_sub, 0, 2);
		$h = str2int($dat_sub, 2, 2);
		$siz = $w * $h;

		$zero = $dat_sub[8];
		$off1 = str2int($dat_sub, 0x18, 4); // rle
		$off2 = str2int($dat_sub, 0x1c, 4); // literal

		$img['w'] = $w;
		$img['h'] = $h;
		$img['pix'] = '';
		if ( $off2 === 0 )
			$img['pix'] = substr($file, $off1, $siz);
		else
			$img['pix'] = viewpix($file, $siz, $off1, $off2, $zero);

		$fn = sprintf('%s/%04d.clut', $dir, $i);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < $dat_cnt; $i++ )
	return;
}
//////////////////////////////
function kingquest7( $fname )
{
	if ( ! is_file($fname) )
		return;
	if ( stripos($fname, '.view') !== false )
		return viewfile($fname);
	if ( stripos($fname, '.pic') !== false )
		return picfile($fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kingquest7( $argv[$i] );
