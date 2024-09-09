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

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$cnt = str2int($file, 0, 1);
	if ( $cnt !== 0xb )  return;

	$pal8 = '';
	$pal4 = '';

	$pos = str2int($file,  4, 3);
	if ( str2int($file,$pos+ 0,2) !== 0x1101  )  return;
	if ( str2int($file,$pos+12,3) !== 0x20100 )  return;
	$sub  = substr($file, $pos + 16, 0x400);
	$pal8 .= pal555($sub);

	$pos = str2int($file,  8, 3);
	if ( str2int($file,$pos+ 0,2) !== 0x1101  )  return;
	if ( str2int($file,$pos+12,3) !== 0x10100 )  return;
	$sub  = substr($file, $pos + 16, 0x200);
	$pal4 .= pal555($sub);

	$pos = str2int($file, 12, 3);
	if ( str2int($file,$pos+ 0,2) !== 0x1101  )  return;
	if ( str2int($file,$pos+12,3) !== 0x10040 )  return;
	$sub  = substr($file, $pos + 16, 0x80);
	$pal4 .= pal555($sub);

	$len8 = strlen($pal8);
	$len4 = strlen($pal4);
	$cnt -= 3;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = str2int($file, 16+$i*4, 3);

		if ( str2int($file,$pos,2) !== 0x1100 )  return;
		//$x = str2int($file, $pos +  4, 2);
		//$y = str2int($file, $pos +  6, 2);
		$w = str2int($file, $pos + 12, 2) << 1;
		$h = str2int($file, $pos + 14, 2);

		$pix = substr($file, $pos + 16, $w*$h);
		$img = array(
			'cc'  => 0x100,
			'w'   => $w,
			'h'   => $h,
			'pal' => '',
			'pix' => $pix,
		);
		$id = 0;
		for ( $j=0; $j < $len8; $j += 0x400 )
		{
			$img['pal'] = substr($pal8, $j, 0x400);
			$fn = sprintf('%s/%02d-8-%d.clut', $dir, $i, $id);
			save_clutfile($fn, $img);
			$id++;
		}

		bpp4to8($pix);
		$img = array(
			'cc'  => 0x10,
			'w'   => $w << 1,
			'h'   => $h,
			'pal' => '',
			'pix' => $pix,
		);
		$id = 0;
		for ( $j=0; $j < $len4; $j += 0x40 )
		{
			$img['pal'] = substr($pal4, $j, 0x40);
			$fn = sprintf('%s/%02d-4-%d.clut', $dir, $i, $id);
			save_clutfile($fn, $img);
			$id++;
		}
	} // for ( $i=0; $i < $cnt; $i++ )

/*
	$len = strlen($file);

	for ( $i=0; $i < $len; $i += 0x1000 )
	{
		$pal = substr($file, $i+0,     0x100);
		$pix = substr($file, $i+0x100, 60*64);

		$img = array(
			'cc'  => 0x80,
			'w'   => 60,
			'h'   => 64,
			'pal' => pal555($pal),
			'pix' => $pix,
		);

		$fn = sprintf('%s/%04d.clut', $dir, $i/0x1000);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < $len; $i += 0x1000 )
*/

	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
