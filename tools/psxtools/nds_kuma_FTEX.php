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

$gp_pix = array();

function sect_BIT( &$sub )
{
	if ( substr($sub,0,3) !== 'BIT' )
		return php_error('not BIT');
	if ( substr($sub,8,8) !== 'TEXTURES' )
		return php_error('BIT no TEXTURES');

	$cnt = str2int($sub, 4, 4);

	global $gp_pix;
	$gp_pix = array();

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x10 + ($i * 0x30);
		if ( substr($sub,$p+8,8) !== 'PALETTES' )
			return php_error('BIT[%x] no PALETTES', $i);

		$gp_pix[$i]['cc']  = 0x10;
		$gp_pix[$i]['w']   = str2int($sub, $p+0, 2);
		$gp_pix[$i]['h']   = str2int($sub, $p+2, 2);
		$gp_pix[$i]['pal'] = pal555( substr($sub, $p+16, 32) );
			$gp_pix[$i]['pal'][3] = ZERO; // bgzero
	}
	return;
}
//////////////////////////////
function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$pos = str2int($file,  8, 4);
	$cnt = str2int($file, 12, 2);

	global $gp_pix;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$sub = substr ($file, $pos+0, 4);
		$siz = str2int($file, $pos+4, 4);
		$hdz = str2int($file, $pos+8, 4);
		if ( $sub != 'FTX0' )
			return php_error('UNKNOWN %s @ %x', $sub, $pos);
		printf("%6x , %6x , %6x , %s\n", $pos, $siz, $hdz, $sub);

		$sub = substr($file, $pos+$hdz, $siz);
		if ( $i === 0 )
			sect_BIT($sub);
		else
		{
			$id = $i - 1;
			bpp4to8($sub);
			$gp_pix[$id]['pix'] = $sub;

			$fn = sprintf("%s.%d.tpl", $pfx, $id);
			save_clutfile($fn, $gp_pix[$id]);
		}

		$pos += ($siz + $hdz);

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );
