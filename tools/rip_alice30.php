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
	$st = fgetint( $fp, 0, 2 );
	$ed = fgetint( $fp, 2, 2 );
	fseek( $fp, ($st-1)*0x100, SEEK_SET );
	$dat = fread( $fp, ($ed-$st)*0x100 );
	$dat = rtrim( $dat, ZERO );
	while ( (strlen($dat)%2) != 0 )
		$dat .= ZERO;
	return $dat;
}

if ( $argc == 1 )   exit();
if ( stripos($argv[1], "adisk.dat") !== FALSE )
	$dir = "adisk_dat";
else
if ( stripos($argv[1], "acg.dat") !== FALSE )
	$dir = "acg_dat";
else
if ( stripos($argv[1], "amus.dat") !== FALSE )
	$dir = "amus_dat";
else
	exit("UNKNOWN {$argv[1]}\n");

if ( ! is_dir($dir) )
	mkdir( $dir, 0755 );

$fp = array();
for ( $i=1; $i < $argc; $i++ )
	$fp[] = fopen( $argv[$i], "rb" );

$index = get_index( $fp[0] );
$disk = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$ed = strlen( $index );
$st = 0;
$id = 0;
$d2h = "dechex";
while ( $st < $ed )
{
	$b1 = ord( $index[$st+0] );
	$b2 = ord( $index[$st+1] );
		$id++;
		$st += 2;

	if ( $b1 == 0x1a )
		break;
	if ( $b1 == 0 )
		break;

	$arc = $b1 - 1;
	$aid = $b2;

	if ( ! isset($fp[$arc]) )
	{
		printf("FATAL : Missing DAT-%d @ %x\n", $arc+1, $st);
		exit();
	}

	// adisk has filesize but acg/amus dont
	$pos = fgetint( $fp[$arc], ($aid+0)*2, 2 );
	$nxt = fgetint( $fp[$arc], ($aid+1)*2, 2 );
	$fs  = ($nxt - $pos) * 0x100;
	$fn  = sprintf("%03d.dat", $id);

	fseek( $fp[$arc], ($pos-1)*0x100, SEEK_SET );
	$cg = fread( $fp[$arc], $fs );
	file_put_contents( "{$dir}/{$fn}", $cg );

	printf("%4x , %s-%3d , %8x , {$fn}\n", $id , $disk[$arc], $aid, $fs);

}
