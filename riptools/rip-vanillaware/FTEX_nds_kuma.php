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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');
tool::require('func-console');

$gp_pix = [];

function sect_bit( string &$sub ) : void
{
	if ( substr($sub,0,3) !== 'BIT' )
		tool::error('not BIT');
	if ( substr($sub,8,8) !== 'TEXTURES' )
		tool::error('BIT no TEXTURES');

	$cnt = tool::ordstr($sub, 4, 4);

	global $gp_pix;
	$gp_pix = [];

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x10 + ($i * 0x30);
		if ( substr($sub,$p+8,8) !== 'PALETTES' )
			tool::error('BIT no PALETTES', $i);

		$pal = tool::substr($sub, $p+16, 32);

		$img = new clutdata;
		$img->w = tool::ordstr($sub, $p+0, 2);
		$img->h = tool::ordstr($sub, $p+2, 2);
		$img->pal = psx::pal555($pal, 0);

		$gp_pix[$i] = $img;
	} // for ( $i=0; $i < $cnt; $i++ )
}
//////////////////////////////
function kuma_ftex( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 4) !== 'FTEX' )
		return false;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$pos = tool::ordstr($file,  8, 4);
	$cnt = tool::ordstr($file, 12, 2);

	global $gp_pix;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = tool::substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $pos, 4) !== 'FTX0' )
			tool::error('not FTX0', $fname, $st);

		$sz1 = tool::ordstr($file, $pos+4, 4);
		$sz2 = tool::ordstr($file, $pos+8, 4);
		tool::trace('NTFT', $pos, $sz1, $fn);

		$sub = tool::substr($file, $pos+$sz2, $sz1);
		if ( $i === 0 )
			sect_bit($sub);
		else
		{
			$id  = $i - 1;
			$img = &$gp_pix[$id];
			$img->pix = psx::bpp4to8($sub);

			$fn = sprintf('%s.%d.tpl', $pfx, $id);
			clutfile::save($fn, $img);
		}

		$pos += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function kuma( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = kuma_ftex($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'kuma');
