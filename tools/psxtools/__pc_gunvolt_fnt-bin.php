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

//////////////////////////////
function load_tileset( $pfx, &$map )
{
	$set = load_clutfile("$pfx.tileset");
	if ( ! empty($set) )
		return $set;

	$tlsz = -1;
	$len1 = strlen( $map['tile'][0] );
	$len2 = strlen( $map['tile'][1] );
	if ( ($len1 >> 4) === ($len2 >> 2) )
		$tlsz = 8;
	else
	if ( ($len1 >> 2) === ($len2 >> 2) )
		$tlsz = 16;
	else
	if ( ($len1 >> 2) === ($len2 >> 1) )
		$tlsz = 16;

	if ( $tlsz === -1 )
		return '';
	printf("DETECT tile = %d x %d\n", $tlsz, $tlsz);

	$fnt = load_file("$pfx.fnt");

	return '';
}
//////////////////////////////
function mapdata_loop( &$file, $st, $ed, $sz )
{
	$map = array();
	while ( $st < $ed )
	{
		$map[] = substr($file, $st, $sz);
		$st += $sz;
	}
	return $map;
}

function load_mapdata( $pfx, $tag )
{
	$bin = load_file("$pfx.bin");
	if ( empty($bin) )
		return '';
	printf("== load_mapdata( %s , %s )\n", $pfx, $tag);

	$data = array();
	switch ( $tag )
	{
		case 'mgv':
			$data['room'] = array(16,15);
			$p1 = str2int($bin,  4, 3);
			$p2 = str2int($bin,  8, 3);
			$p3 = str2int($bin, 12, 3);

			$s1 = substr($bin, $p1, $p2-$p1);
			$s2 = substr($bin, $p2, $p3-$p2);

			// tile data
			$p1 = str2int($s1,  4, 3);
			$p2 = str2int($s1,  8, 3);
			$p3 = str2int($s1, 12, 3);
			$data['tile'][0] = substr($s1, $p1, $p2-$p1);
			$data['tile'][1] = substr($s1, $p2, $p3-$p2);
			$data['tile'][2] = substr($s1, $p3);
			break;

		case 'gv2':
			$data['room'] = array(25,15);
			$p1 = str2int($bin,  4, 3);
			$p2 = str2int($bin,  8, 3);
			$p3 = str2int($bin, 12, 3);

			$s1 = substr($bin, $p1, $p2-$p1);
			$s2 = substr($bin, $p2, $p3-$p2);

			// tile data
			$p1 = str2int($s1, 0, 3);
			$p2 = str2int($s1, 4, 3);
			$p3 = str2int($s1, 8, 3);
			$data['tile'][0] = substr($s1, $p1, $p2-$p1);
			$data['tile'][1] = substr($s1, $p2, $p3-$p2);
			$data['tile'][2] = substr($s1, $p3);
			break;

		case 'laix':
			$data['room'] = array(27,15);
			$p1 = str2int($bin,  8, 3);
			$p2 = str2int($bin, 16, 3);
			$p3 = str2int($bin, 24, 3);

			$s1 = substr($bin, $p1, $p2-$p1);
			$s2 = substr($bin, $p2, $p3-$p2);

			// tile data
			$p1 = str2int($s1, 0, 3);
			$p2 = str2int($s1, 4, 3);
			$p3 = str2int($s1, 8, 3);
			$data['tile'][0] = substr($s1, $p1, $p2-$p1);
			$data['tile'][1] = substr($s1, $p2, $p3-$p2);
			$data['tile'][2] = substr($s1, $p3);
			break;

		default:
			return '';
	} // switch ( $tag )

	// map data
	$sz1 = str2int($s2,  0, 3);
	$ps1 = str2int($s2,  4, 3);
	$sz2 = str2int($s2,  8, 3);
	$ps2 = str2int($s2, 12, 3);
	$sz3 = str2int($s2, 16, 3);
	$ps3 = str2int($s2, 20, 3);
	$sz4 = str2int($s2, 24, 3);
	$ps4 = str2int($s2, 28, 3);
	$ps5 = strlen ($s2);
	$data['map'][0] = mapdata_loop($s2, $ps1, $ps2, $sz1);
	$data['map'][1] = mapdata_loop($s2, $ps2, $ps3, $sz2);
	$data['map'][2] = mapdata_loop($s2, $ps3, $ps4, $sz3);
	$data['map'][3] = mapdata_loop($s2, $ps4, $ps5, $sz4);

	foreach ( $data['tile'] as $k => $v )
		save_file("$pfx/tile.$k", $v);

	foreach ( $data['map'] as $mk => $mv )
	{
		foreach ( $mv as $k => $v )
			save_file("$pfx/map.$mk.$k", $v);
	}
	return $data;
}

function gunvolt( $tag, $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$map = load_mapdata($pfx, $tag);
	if ( empty($map) )
		return;

	$set = load_tileset($pfx, $map);
	if ( empty($set) )
		return;

	return;
}

$tag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-mgv':
			$tag = 'mgv';
			break;
		case '-bsm':
		case '-bmz':
		case '-gv':
		case '-gv1':
		case '-gv2':
			$tag = 'gv2';
			break;
		case '-gva':
		case '-laix':
			$tag = 'laix';
			break;
		default:
			gunvolt( $tag, $argv[$i] );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )

/*
bin
	0   04
	4   tile data offset
		tileset data offset
		-
		room data offset
	8   map data offset
		map tile size/layer , offset
		map ??? size/layer , offset
		map ??? size/layer , offset
		map ??? size/layer , offset
	c   PTMT offset
	10  PTMT offset
	14  end/filesize
fnt
	0  01
	4  toc
		count
			fname offset , fsize , file offset , ??? ,
			w x h , 01 02 -- -- , 0 , mod_time
	8  file size
 */
