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

function silhstry( $fname )
{
	// for *.ati only
	if ( stripos($fname, '.ati') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = ord($file[4]);
	$off1 = str2int($file,  8, 3);
	$off2 = str2int($file, 12, 3);

	$bpp8 = $b1 & 1;
	$siz = str2int($file, $off1+ 0, 3);
	$sub = substr ($file, $off1+12, $siz-12);
		$pal = pal555($sub);

	$siz = str2int($file, $off2+ 0, 3);
	$w   = str2int($file, $off2+ 8, 2);
	$h   = str2int($file, $off2+10, 2);
	$pix = substr ($file, $off2+12, $siz-12);
	if ( $bpp8 )
	{
		$cc = 0x100;
		$w <<= 1;
	}
	else
	{
		$cc = 0x10;
		$w <<= 2;
		bpp4to8($pix);
	}
	printf("[%x] %x x %x  %s\n", $cc, $w, $h, $fname);

	$count = array();
	$st = 0;
	$ed = strlen($pal);
	while ( $st < $ed )
	{
		$img = array(
			'cc'  => $cc,
			'w'   => $w,
			'h'   => $h,
			'pix' => $pix,
		);

		if ( $bpp8 )
		{
			$img['pal'] = substr($pal, $st, 0x400);
			$st += 0x400;
		}
		else
		{
			$img['pal'] = substr($pal, $st, 0x40);
			$st += 0x40;
		}

		$count[] = $img;
	} // while ( $st < $ed )


	$cnt = count($count);
	if ( $cnt === 0 )
		return;
	if ( $cnt === 1 )
		return save_clutfile("$fname.clut", $count[0]);

	$dir = str_replace('.', '_', $fname);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$fn = sprintf('%s/%04d.clut', $dir, $i);
		save_clutfile($fn, $count[$i]);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	silhstry( $argv[$i] );
