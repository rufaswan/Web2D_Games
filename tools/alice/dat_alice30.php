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
//////////////////////////////
define("ZERO", chr(  0));

function fgetint( $fp, $pos, $bytes )
{
	fseek( $fp, $pos, SEEK_SET );
	$data = fread($fp, $bytes);
	$res = 0;
	for ( $i=0; $i < $bytes; $i++ )
	{
		$b = ord( $data[$i] );
		$res += ($b << ($i*8));
	}
	return $res;
}
//////////////////////////////
$gp_dat = array(
	"adisk.dat" => array("disk", 1),
	"amus.dat"  => array("mus",  1),
	"amse.dat"  => array("mse",  1),
	"awav.dat"  => array("wav",  1),

	"amap.dat"  => array("map",  1),
	"agame.dat" => array("game", 1),
	"asnd.dat"  => array("snd",  1),

	"acg.dat"   => array("cg",   1),
	"bcg.dat"   => array("cg",   2),
	"ccg.dat"   => array("cg",   3),
	"dcg.dat"   => array("cg",   4),
	"ecg.dat"   => array("cg",   5),
	"fcg.dat"   => array("cg",   6),
	"gcg.dat"   => array("cg",   7),
	"hcg.dat"   => array("cg",   8),

	// gaiji.dat
	// ag00.dat
	// amus_[al3|all|amb|aym|bee|mgn|otm|oy|psg|r41|r42|rg2].dat
);
function datmeta( $fname )
{
	global $gp_dat;
	foreach ( $gp_dat as $dat => $meta )
	{
		if ( stripos($fname, $dat) !== FALSE )
			return $meta;
	}
	return array(0,0,0);
}

function datfile( $fname )
{
	list($dir,$ind) = datmeta($fname);
	if ( $ind == 0 )  return;

	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	@mkdir( $dir, 0755, true );

	$st = fgetint($fp, 0, 2) * 0x100 - 0x100;
	$ed = fgetint($fp, 2, 2) * 0x100 - 0x100;

	$id = 0;
	$hed = "";
	while ( $st < $ed )
	{
		$arc = fgetint($fp, $st+0, 1);
		$aid = fgetint($fp, $st+1, 1);
			$id++;
			$st += 2;

		if ( $arc != $ind )
			continue;

		// dat header
		$cur = fgetint( $fp, ($aid+0)*2, 2 ) * 0x100 - 0x100;
		$nxt = fgetint( $fp, ($aid+1)*2, 2 ) * 0x100 - 0x100;
		$fsz = $nxt - $cur;

		// extract file
		$dn = $dir;
		$fn = sprintf("%03d.dat", $id);
		@mkdir( $dn, 0755, true );

		fseek( $fp, $cur, SEEK_SET );
		file_put_contents( "$dn/$fn", fread($fp, $fsz) );

		// logging
		$ent = sprintf("%8x , %8x , %8x , %s\n", $st , $cur , $fsz , $fn);
		echo $ent;
		$hed .= $ent;
	}

	file_put_contents("{$dir}_dat.txt", $hed, FILE_APPEND);
	fclose($fp);
	return;
}

if ( $argc == 1 )  exit();
for ( $i=1; $i < $argc; $i++ )
	datfile( $argv[$i] );
