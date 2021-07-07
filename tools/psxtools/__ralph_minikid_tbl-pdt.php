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

$gp_pix  = array();
$gp_clut = '';

function sect_tbl( &$tbl, $pfx )
{
	$pos = 0;
	$b2  = str2int($tbl, $pos+ 8, 4);
	$pix = substr ($tbl, $pos+12, $b2);
		$pos += (12 + $b2);
	$pal = substr($tbl, $pos+12);

	global $gp_pix, $gp_clut;
	$gp_clut = pal555($pal);
	$gp_pix  = array();

	$len = strlen($pix);
	$pos = 0;
	while ( $pos < $len )
	{
		$bak = $pos;
		$b = str2int($pix, $pos+4, 4); $pos += $b; // TIM header size
		$b = str2int($pix, $pos+0, 4); $pos += $b; // TIM clut size
		$b = str2int($pix, $pos+0, 4); $pos += $b; // TIM pixel size

		$sub = substr($pix, $bak, $pos-$bak);
		$gp_pix[] = psxtim($sub);
	} // while ( $pos < $len )

	foreach ( $gp_pix as $k => $v )
	{
		foreach ( $v['pal'] as $pk => $pv )
		{
			$img = array(
				'cc' => $v['cc'],
				'w'  => $v['w' ],
				'h'  => $v['h' ],
				'pix' => $v['pix'],
				'pal' => $pv,
			);
			$fn = sprintf("%s/%04d-%02d.clut", $pfx, $k, $pk);
			save_clutfile($fn, $img);
		}
	}
	return;
}

function ralph( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$tbl = load_file("$pfx.tbl");
	$pdt = load_file("$pfx.pdt");
	if ( empty($tbl) || empty($pdt) )
		return;

	if ( str2int($tbl,0,4) !== 2 )
		return;

	sect_tbl($tbl, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ralph( $argv[$i] );
