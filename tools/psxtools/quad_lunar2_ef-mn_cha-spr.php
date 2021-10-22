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
require "common-quad.inc";
require "quad.inc";
require "quad_lunar2.inc";

define("METAFILE", true);

function efchaspr( $fname, $dir )
{
	$fn2 = str_ireplace('efspr', 'efcha', $fname);
	$spr = loadpck($fname); // all 2 (??? , meta)
	$cha = loadpck($fn2);   // all 2 (pix , clut)
	if ( empty($spr) || empty($cha) )
		return;
	save_pck($spr, "$dir/meta/spr.%04d");
	save_pck($cha, "$dir/meta/cha.%04d");

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

	$json = load_idtagfile('lunar2');
	sectspr($json, $spr[1]);
	save_quadfile("$dir/pck", $json);
	return;
}

function mnchaspr( $fname, $dir )
{
	$fn2 = str_ireplace('mnspr', 'mncha', $fname);
	$spr = loadpck($fname); // all 15+ (??? , ??? , clut , ??? , ??? , ??? , meta ...)
	$cha = loadpck($fn2);   // all  1  (pix)
	if ( empty($spr) || empty($cha) )
		return;
	save_pck($spr, "$dir/meta/spr.%04d");
	save_pck($cha, "$dir/meta/cha.%04d");

	$pal = pal555($spr[2]);
		$pal[3] = ZERO; // bgzero
	$pix = $cha[0];
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => strlen($pix) >> 8,
		'h'   => 0x100,
		'pal' => $pal,
		'pix' => $pix,
	);

	$id = 6;
	while (1)
	{
		if ( ! isset($spr[$id]) )
			break;

		$json = load_idtagfile('lunar2');
		sectspr($json, $spr[$id]);
			$id++;

		if ( empty($json['Frame']) )
			continue;

		$b1 = $id - 1;
		save_clutfile("$dir/$b1/pck.0.clut", $img);
		save_quadfile("$dir/$b1/pck", $json);
	} // while (1)

	return;
}
//////////////////////////////
function pckcont( $fname, $dir )
{
	$pck = loadpck($fname); // 3 (pix , clut , meta)
	if ( empty($pck) )
		return;
	save_pck($pck, "$dir/meta/pck.%04d");

	$pal = pal555($pck[1]);
		$pal[3] = ZERO; // bgzero
	$pix = $pck[0];
	$img = array(
		'cc'  => strlen($pal) >> 2,
		'w'   => strlen($pix) >> 8,
		'h'   => 0x100,
		'pal' => $pal,
		'pix' => $pix,
	);
	save_clutfile("$dir/pck.0.clut", $img);

	$json = load_idtagfile('lunar2');
	sectspr($json, $pck[2]);
	save_quadfile("$dir/pck", $json);
	return;
}

function pcktitle( $fname, $dir )
{
	$pck = loadpck($fname); // 9 (pix*3 , clut*3 , meta*3)
	if ( empty($pck) )
		return;
	save_pck($pck, "$dir/meta/pck.%04d");

	for ( $i=0; $i < 3; $i++ )
	{
		$pal = pal555($pck[3+$i]);
			$pal[3] = ZERO; // bgzero
		$pix = $pck[0+$i];
		$img = array(
			'cc'  => strlen($pal) >> 2,
			'w'   => strlen($pix) >> 8,
			'h'   => 0x100,
			'pal' => $pal,
			'pix' => $pix,
		);
		save_clutfile("$dir/$i/pck.0.clut", $img);

		$json = load_idtagfile('lunar2');
		sectspr($json, $pck[6+$i]);
		save_quadfile("$dir/$i/pck", $json);
	} // for ( $i=0; $i < 3; $i++ )
	return;
}
//////////////////////////////
function lunar2( $fname )
{
	$dir = str_replace('.', '_', $fname);

	if ( stripos($fname, 'efspr') !== false )
		return efchaspr($fname, $dir);

	if ( stripos($fname, 'mnspr') !== false )
		return mnchaspr($fname, $dir);

	if ( stripos($fname, 'continue') !== false )
		return pckcont($fname, $dir);

	if ( stripos($fname, 'title') !== false )
		return pcktitle($fname, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
