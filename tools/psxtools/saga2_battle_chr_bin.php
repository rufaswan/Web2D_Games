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
/*
 * /battle/chr%03d.bin
 *   0     test
 *   1- 31 party chars
 *  32- 57 duel  chars
 *  94-138 party weapons
 * 158-202 duel  weapons
 * 257-344 party enemies
 * 388-447 duel  enemies
 * 452-460 boss
 * 466-471 final boss
 */
require 'common.inc';

define('CANV_S', 0x200);
$gp_pix = '';
$gp_clut = '';
$gp_dir  = '';

function sectpart( &$file, $nid, $st, $ed )
{
	printf("== sectpart( $nid, %x, %x )\n", $st, $ed);

	$cnt = str2int($file, $st, 4);
	$pos = $st + 0x14;

	$data = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$b1 = ord( $file[$pos] );
		if ( $b1 & 0x80 )
			$n = 12;
		else
		{
			$b2 = substr($file, $pos, 8);
			array_unshift($data, $b2);
			$n = 8;
		}
		$pos += $n;
	}
	if ( empty($data) )
		return;
	if ( $pos < $ed )
	{
		printf("adj_h  %x - %x\n", $pos, $ed);
		echo debug( substr($file, $pos, $ed-$pos) );
		foreach ( $data as $k => $v )
		{
			$b1 = ord( $v[3] );
			$data[$k][3] = $file[$pos+$b1];
		} // foreach ( $data as $k => $v )
	}

	$pix = copypix_def(CANV_S,CANV_S);

	global $gp_pix, $gp_clut;
	foreach ( $data as $k => $v )
	{
		// 0  1  2   3   4  5  6   7
		// -  -  sx  sy  w  h  dx  dy
		$sx = ord( $v[2] );
		$sy = ord( $v[3] ) * 0x10;
		$w  = ord( $v[4] );
		$h  = ord( $v[5] );

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $sx, $sy, $w, $h, 0x100, 0x100);
		$pix['src']['pal'] = $gp_clut;
		$pix['src']['pal'][3] = ZERO;
		//$pix['bgzero'] = 0;

		$dx = ord( $v[6] );
		$dy = ord( $v[7] );
		$pix['dx'] = $dx + (CANV_S / 4);
		$pix['dy'] = $dy + (CANV_S / 4);

		$p0 = ord( $v[0] );
		$pix['vflip'] = $p0 & 0x40;
		$pix['hflip'] = $p0 & 0x20;

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , %08b\n", $p0);
		copypix_fast($pix);
	} // foreach ( $data as $k => $v )

	savepix($nid, $pix, true);
	return;
}

function sect1( &$meta, $dir )
{
	if ( empty($meta) )
		return;
	printf("== sect1( $dir )\n");

	global $gp_pix;
	$pix_st = str2int($meta, 0, 4);
	$gp_pix = substr($meta, $pix_st);

	$pos1 = str2int($meta, 0x34 , 4);
	$pos2 = str2int($meta, $pos1, 4);

	$ed = $pos2 - 4;
	$st = $pos1;
	$nid = 1;
	$done = array();
	printf("while ( %x < %x )\n", $st, $ed);
	while ( $st < $ed )
	{
		$fn = sprintf('%s/%04d', $dir, $nid);

		$off1 = str2int($meta, $st+0, 3);
		$off2 = str2int($meta, $st+4, 3);
		if ( ! isset( $done[$off1] ) )
			sectpart($meta, $fn, $off1, $off2);

		$done[$off1] = 1;
		$st += 4;
		$nid++;
	}

	// final entry without $off2
	$fn = sprintf('%s/%04d', $dir, $nid);
	$off2 = str2int($meta, $st+0, 3);
	if ( ! isset( $done[$off2] ) )
		sectpart($meta, $fn, $off2, $pix_st);
	return;
}

function saga2( $fname )
{
	// only CHR???.BIN files
	if ( ! preg_match('|CHR[0-9]+\.BIN|i', $fname) )
		return;

	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	global $gp_clut;
	$clut_off = str2int($file, 0x1c, 4);
	$pal = substr($file, $clut_off, 0x200);
	$gp_clut = pal555($pal);

	$cnt = str2int($file, 0x08, 4);
	$pos = str2int($file, 0x18, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $pos + ($i * 4);
		$p1 = str2int($file, $p+0, 4);
		$p2 = str2int($file, $p+4, 4);
		printf("$i , %x , %x , %x\n", $p, $p1, $p2);

		$meta = substr($file, $p1, $p2-$p1);
		sect1($meta, "$dir/anim_{$i}");
		//save_file("$dir/anim_{$i}/meta", $meta);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );

/*
	/mout/battle.out is loaded to 801a0000
	data is loaded to 800ac000
		then append to 80102000
 */
