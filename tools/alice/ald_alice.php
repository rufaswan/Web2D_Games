<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'common.inc';

$gp_ald = array(
	'sa.ald'    => array('sa', 1, false),
	'adisk.ald' => array('sa', 1, false),
	'ma.ald'    => array('ma', 1, false),
	'amidi.ald' => array('ma', 1, false),
	'da.ald'    => array('da', 1, false),
	'adata.ald' => array('da', 1, false),
	'ra.ald'    => array('ra', 1, false),
	'ares.ald'  => array('ra', 1, false),
	'ba.ald'    => array('ba', 1, false),
	'abgm.ald'  => array('ba', 1, false),

	'ga.ald'    => array('ga', 1, true),
	'gb.ald'    => array('ga', 2, true),
	'gc.ald'    => array('ga', 3, true),
	'acg.ald'   => array('ga', 1, true),
	'bcg.ald'   => array('ga', 2, true),
	'ccg.ald'   => array('ga', 3, true),
	'wa.ald'    => array('wa', 1, true),
	'wb.ald'    => array('wa', 2, true),
	'wc.ald'    => array('wa', 3, true),
	'awave.ald' => array('wa', 1, true),
	'bwave.ald' => array('wa', 2, true),
	'cwave.ald' => array('wa', 3, true),
);
//////////////////////////////
function aldmeta( $fname )
{
	global $gp_ald;
	foreach ( $gp_ald as $ald => $meta )
	{
		if ( stripos($fname, $ald) !== FALSE )
			return $meta;
	}
	return array(0,0,0);
}
//////////////////////////////
function aldfile( $fname )
{
	list($dir,$ind,$mul) = aldmeta($fname);
	if ( $ind == 0 )  return;

	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	@mkdir( $dir, 0755, true );

	$st = fp2int($fp, 0, 3) * 0x100;
	$ed = fp2int($fp, 3, 3) * 0x100;
	while ( ($ed%3) != 0 )
		$ed--;

	$id = 0;
	$hed = '';
	while ( $st < $ed )
	{
		$arc = fp2int($fp, $st+0, 1);
		$aid = fp2int($fp, $st+1, 2);
			$id++;
			$st += 3;

		if ( $arc != $ind )
			continue;

		// ald header
		$cur = fp2int( $fp, ($aid+0)*3, 3 ) * 0x100;
		$nxt = fp2int( $fp, ($aid+1)*3, 3 ) * 0x100;
		$fsz = $nxt - $cur;

		// file header
		$hsz = fp2int ( $fp, $cur+0, 4 );
			$fsz -= $hsz;
		$hfs = fp2int ( $fp, $cur+4, 4 );
		$hfn = fp2str0( $fp, $cur+16 );

		// extract file
		$ext = substr($hfn, strrpos($hfn, '.')+1);
		$ext = strtolower($ext);
		if ( $mul )
		{
			$dn = sprintf('$dir/%03d', ($id >> 8));
			$fn = sprintf('%05d.%s', $id, $ext);
		}
		else
		{
			$dn = $dir;
			$fn = sprintf('%03d.%s', $id, $ext);
		}
		@mkdir( $dn, 0755, true );

		fseek( $fp, $cur+$hsz, SEEK_SET );
		file_put_contents( "$dn/$fn", fread($fp, $fsz) );

		// logging
		$ent = sprintf("%4x , %8x , %s\n", $id , $hfs, $hfn);
		echo $ent;
		$hed .= $ent;
	}

	file_put_contents("$fname.txt", $hed);
	fclose($fp);
	return;
}

if ( $argc == 1 )  exit();
for ( $i=1; $i < $argc; $i++ )
	aldfile( $argv[$i] );
