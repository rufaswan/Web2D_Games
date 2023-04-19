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
require 'gowcaiz.inc';

function gow_metafile( &$file, $dir )
{
	$meta = sectmeta($file);
	$txt = '';
	foreach ( $meta as $mk => $mv )
	{
		$txt .= sprintf("\n===== %s =====\n", $mk);
		$txt .= "$mv\n";
	}
	save_file("$dir/meta.txt", $txt);
	return;
}

function gow_tmgfile( &$file, $dir )
{
	$tmg = secttmg($file);
	foreach ( $tmg as $tk => $tv )
	{
		$fn = sprintf('%s/%s.clut', $dir, $tk);
		save_clutfile($fn, $tv);
	}
	return;
}

function gow_actfile( &$file, $dir )
{
	$tmg = substr($file, 0xc000);

	gow_metafile($file, $dir);
	gow_tmgfile ($tmg , $dir);
	return;
}

function gow_bgfile( &$file, $dir )
{
	$tim  = substr($file, 0, 0x3ea20);
	$meta = substr($file, 0x3ea20, 0x3f8d8-0x3ea20);
	$tmg  = substr($file, 0x3f8d8);

	$tim = psxtim($tim);
	save_clutfile("$dir/bgtim.clut", $tim);

	gow_metafile($meta, $dir);
	gow_tmgfile ($tmg , $dir);
	return;
}

function gowcaiz( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	if ( stripos($fname, '.tmg') !== false )
		return gow_tmgfile($file, $dir);
	if ( stripos($fname, '.act') !== false )
		return gow_actfile($file, $dir);
	if ( stripos($fname, '.bg' ) !== false )
		return gow_bgfile ($file, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gowcaiz( $argv[$i] );
