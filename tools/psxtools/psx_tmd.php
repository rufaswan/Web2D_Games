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
php_req_extension("json_encode", "json");

function tmd_prim( &$file, $off, $cnt )
{
	$prim = array();
	$prim_mode = array('Polygon', 'Line', 'Sprite');
	for ( $j=0; $j < $cnt; $j++ )
	{
		$s = substr($file, $off, 4);
			$off += 4;
		$olen = ord( $s[0] );
		$ilen = ord( $s[1] );
		$flag = ord( $s[2] );
		$mode = ord( $s[3] );

		$data = array();
		$data['Mode'] = $prim_mode[$mode & 3];
		$data['Gradient'] = ($flag & 4) != 0;
		$data['Face'    ] = ($flag & 2) != 0;
		$data['Light'   ] = ($flag & 1) != 0;

	} // for ( $j=0; $j < $cnt; $j++ )
	return $prim;
}

function psxtmd( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( str2int($file, 0, 4) != 0x41 )
		return;

	$flg = str2int($file, 4, 4);
	$cnt = str2int($file, 8, 4);
	if ( $flg & 1 ) // fixed RAM address
		return;

	$tmd = array();
	$tmd['Object'] = array();
	$pos = 12;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$vert_off = str2int($file, $pos+ 0, 4) + 12;
		$vert_cnt = str2int($file, $pos+ 4, 4);
		$norm_off = str2int($file, $pos+ 8, 4) + 12;
		$norm_cnt = str2int($file, $pos+12, 4);
		$prim_off = str2int($file, $pos+16, 4) + 12;
		$prim_cnt = str2int($file, $pos+20, 4);
		$scale    = str2int($file, $pos+24, 4);
			$pos += 28;
		printf("%6x , %4x , vert $i\n", $vert_off, $vert_cnt);
		printf("%6x , %4x , norm $i\n", $norm_off, $norm_cnt);
		printf("%6x , %4x , prim $i\n", $prim_off, $prim_cnt);

		$vert = array();
		for ( $j=0; $j < $vert_cnt; $j++ )
		{
			$s = substr($file, $vert_off + $j*8, 8);
			$x = sint16( $s[0] . $s[1] );
			$y = sint16( $s[2] . $s[3] );
			$z = sint16( $s[4] . $s[5] );
			//$w = sint16( $s[6] . $s[7] );
			$vert[$j] = array($x,$y,$z);
		}

		$norm = array();
		for ( $j=0; $j < $norm_cnt; $j++ )
		{
			$s = substr($file, $norm_off + $j*8, 8);
			$x = sint16( $s[0] . $s[1] );
			$y = sint16( $s[2] . $s[3] );
			$z = sint16( $s[4] . $s[5] );
			//$w = sint16( $s[6] . $s[7] );
			$norm[$j] = array($x,$y,$z);
		}

		$prim = tmd_prim($file, $prim_off, $prim_cnt);

		$tmd['Object'][$i] = array(
			'Vertex' => $vert,
			'Normal' => $norm,
			'Primitive' => $prim,
			'Scale'  => $scale,
		);
	} // for ( $i=0; $i < $cnt; $i++ )

	// JSON_PRETTY_PRINT
	save_file("$fname.json", json_encode($tmd, JSON_PRETTY_PRINT));
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxtmd( $argv[$i] );
