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
require 'common-json.inc';
require 'common-quad.inc';
require 'quad.inc';
require 'quad_lunar2.inc';

define('METAFILE', true);

$gp_pal = array();

// TODO animation

function syschaspr( $fname, $dir )
{
	$fn2 = str_ireplace('sysspr', 'syscha', $fname);
	$spr = loadpck($fname); // 18 (meta , ???*3 , clut , sjis*8 , ???*5)
	$cha = loadpck($fn2);   // 12 (pix , clut*10, pix)
	if ( empty($spr) || empty($cha) )
		return;
	save_pck($spr, "$dir/meta/spr.%04d");
	save_pck($cha, "$dir/meta/cha.%04d");

	global $gp_pal;
	$gp_pal = pal555($spr[4]);

	$pal = pal555($cha[1]);
		$pal[3] = ZERO; // bgzero
	$pix = $cha[0];
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => strlen($pix) >> 8,
		'h'   => 0x100,
		'pal' => $pal,
		'pix' => $pix,
	);
	save_clutfile("$dir/pck.0.clut", $img);

	$quad = load_idtagfile('lunar2');
	$quad['blend'] = array( blend_modes('normal') );
	sectspr($quad, $spr[0]);
	save_quadfile("$dir/pck", $quad);
	return;
}

function pckpc( $fname, $dir )
{
	$pck = loadpck($fname); // 3 (meta , meta , pix) OR 2 (meta , pix)
	if ( empty($pck) )
		return;
	save_pck($pck, "$dir/meta/pck.%04d");

	$cnt = count($pck) - 1;

	global $gp_pal;
	if ( empty($gp_pal) )
		return;

	$pal = $gp_pal;
		$pal[3] = ZERO; // bgzero
	$pix = $pck[$cnt];
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => strlen($pix) >> 8,
		'h'   => 0x100,
		'pal' => $pal,
		'pix' => $pix,
	);

	while ( $cnt > 0 )
	{
		$cnt--;
		save_clutfile("$dir/$cnt/pck.0.clut", $img);

		$quad = load_idtagfile('lunar2');
		$quad['blend'] = array( blend_modes('normal') );
		sectspr($quad, $pck[$cnt]);
		save_quadfile("$dir/$cnt/pck", $quad);
	} // while ( $cnt > 0 )

	return;
}
//////////////////////////////
function lunar2( $fname )
{
	$dir = str_replace('.', '_', $fname);

	if ( stripos($fname, 'sysspr') !== false )
		return syschaspr($fname, $dir);

	if ( stripos($fname, '_pc') !== false )
		return pckpc($fname, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );

/*
mnspr049.pck @ 40
	sx = 60,f0  18 x c
		 17, b   0, b   0, 0   17, 0
		 17,-c   0,-c   0,-1   17,-1
		-18, b  -1, b  -1, 0  -18, 0
		-18,-c  -1,-c  -1,-1  -18,-1

	-18, b  -1, b | 0, b  17, b
	-18, 0  -1, 0 | 0, 0  17, 0
	--------------+------------
	-18,-1  -1,-1 | 0,-1  17,-1
	-18,-c  -1,-c | 0,-c  17,-c
 */
