<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
//////////////////////////////
define("ZERO", chr(  0));

function fgetstr0( $fp, $pos, $in_hex = FALSE )
{
	fseek( $fp, $pos, SEEK_SET );
	$r = "";
	while(1)
	{
		$b = fread($fp, 1);
		if ( $b == ZERO )
			return $r;

		if ( $in_hex )
			$r .= '\\x'.dechex( ord($b) );
		else
			$r .= $b;
	}
}
function fgetint( $fp, $pos, $bytes )
{
	fseek( $fp, $pos, SEEK_SET );
	$res = 0;
	for ( $i=0; $i < $bytes; $i++ )
	{
		$b = fread( $fp, 1 );
		$res += ord($b) << ($i*8);
	}
	return $res;
}
//////////////////////////////
function get_index( $fp )
{
	$st = fgetint( $fp, 0, 3 );
	$ed = fgetint( $fp, 3, 3 );
	fseek( $fp, $st*0x100, SEEK_SET );
	$dat = fread( $fp, ($ed-$st)*0x100 );
	$dat = rtrim( $dat, ZERO );
	while ( (strlen($dat)%3) != 0 )
		$dat .= ZERO;
	return $dat;
}

if ( $argc == 1 )   exit();
if ( stripos($argv[1], "sa.ald") !== FALSE )
	$dir = "sa_ald";
else
if ( stripos($argv[1], "ga.ald") !== FALSE )
	$dir = "ga_ald";
else
if ( stripos($argv[1], "wa.ald") !== FALSE )
	$dir = "wa_ald";
else
if ( stripos($argv[1], "ma.ald") !== FALSE )
	$dir = "ma_ald";
else
if ( stripos($argv[1], "da.ald") !== FALSE )
	$dir = "da_ald";
else
if ( stripos($argv[1], "ba.ald") !== FALSE )
	$dir = "ba_ald";
else
if ( stripos($argv[1], "ra.ald") !== FALSE )
	$dir = "ra_ald";
else
	exit("UNKNOWN {$argv[1]}\n");

if ( ! is_dir($dir) )
	mkdir( $dir, 0755 );

$fp = array();
for ( $i=1; $i < $argc; $i++ )
	$fp[] = fopen( $argv[$i], "rb" );

$index = get_index( $fp[0] );

$ed = strlen( $index );
$st = 0;
$id = 0;
$hed = "";
while ( $st < $ed )
{
	$b1 = ord( $index[$st+0] );
	$b2 = ord( $index[$st+1] );
	$b3 = ord( $index[$st+2] );
		$id++;
		$st += 3;

	if ( ($b1|$b2|$b3) == 0 )
		continue;

	$arc = $b1 - 1;
	$aid = $b2 + ( $b3 << 8 );

	if ( ! isset($fp[$arc]) )
	{
		printf("FATAL : Missing ALD-%d @ %x\n", $arc+1, $st);
		exit();
	}

	// filesize from header
	$pos = fgetint( $fp[$arc], ($aid+0)*3, 3 );
	$nxt = fgetint( $fp[$arc], ($aid+1)*3, 3 );
	$hsz = fgetint( $fp[$arc], ($pos*0x100)+0, 4 );
	$fsz = (($nxt - $pos) * 0x100) - $hsz;

	$dn = sprintf("$dir/%03d", ($id >> 8));
	$fn = sprintf("%05d.dat", $id);
	@mkdir( $dn, 0755, true );

	fseek( $fp[$arc], ($pos*0x100)+$hsz, SEEK_SET );
	$cg = fread( $fp[$arc], $fsz );
	file_put_contents( "$dn/$fn", $cg );

	$fs = fgetint ( $fp[$arc], ($pos*0x100)+4, 4 );
	$fn = fgetstr0( $fp[$arc], ($pos*0x100)+16 );
	$ent = sprintf("%4x , %8x , %s\n", $id , $fs, $fn);
	echo $ent;
	$hed .= $ent;
}
file_put_contents("{$dir}.hed", $hed);
