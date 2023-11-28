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
require 'xeno.inc';

function xeno_unpak( &$file )
{
	$list = array();
	$cnt  = str2int($file, 0, 3);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 4 + ($i * 4);
		$p1 = str2int($file, $pos + 0, 3);
		$p2 = str2int($file, $pos + 4, 3);
		$sz = $p2 - $p1;

		$list[] = substr($file, $p1, $sz);
	}
	return $list;
}

function lzpak_unpack( $type, $fname )
{
	if ( empty($type) )
		return;
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	switch ( $type )
	{
		case 'pak':
			$list = xeno_unpak($file);
			foreach ( $list as $k => $v )
			{
				$fn = sprintf('%s/%04d.%s', $dir, $k, detect_ext($v));
				save_file($fn, $v);
			}
			return;
		case 'lz-pak':
			xeno_decode($file);
			$list = xeno_unpak($file);
			foreach ( $list as $k => $v )
			{
				$fn = sprintf('%s/%04d.%s', $dir, $k, detect_ext($v));
				save_file($fn, $v);
			}
			return;
		case 'pak-lz':
			$list = xeno_unpak($file);
			foreach ( $list as $k => $v )
			{
				$fn = sprintf('%s/%04d.%s', $dir, $k, detect_ext($v));
				xeno_decode($v);
				save_file($fn, $v);
			}
			return;
	} // switch ( $type )
	return;
}
//////////////////////////////
function xeno_pak( &$data )
{
	$cnt  = count($data);
	$pos  = 8 + ($cnt * 4);

	$head  = chrint($cnt, 4);
	$head .= chrint($pos, 4);
	$body  = '';
	foreach ( $data as $sub )
	{
		// align 4-bytes
		while ( strlen($sub) & 3 )
			$sub .= ZERO;
		$len  = strlen($sub);
		$pos  += $len;
		$head .= chrint($pos, 4);
		$body .= $sub;
	}
	return $head . $body;
}

function lzpak_pack( $type, $dir )
{
	if ( empty($type) )
		return;
	$dir  = rtrim($dir, '/\\');
	$list = array();
	lsfile_r($dir, $list);

	$data = array();
	foreach ( $list as $fn )
		$data[] = file_get_contents($fn);

	switch ( $type )
	{
		case 'pak':
			$file = xeno_pak($data);
			save_file("$dir.pak", $file);
			return;
		case 'lz-pak':
			$file = xeno_pak($data);
			xeno_encode($file);
			save_file("$dir.lzpak", $file);
			return;
		case 'pak-lz':
			foreach ( $data as $k => $v )
				xeno_encode($data[$k]);
			$file = xeno_pak($data);
			save_file("$dir.paklz", $file);
			return;
	} // switch ( $type )
	return;
}
//////////////////////////////
function xeno( $type, $ent )
{
	if ( is_dir ($ent) )
		return lzpak_pack($type, $ent);
	if ( is_file($ent) )
		return lzpak_unpack($type, $ent);
	return;
}

echo <<<_MSG
{$argv[0]}  type  FILE/DIR...
type
  pak
    unpack a FILE to a DIR
    pack a DIR to a FILE
  lz-pak
    decode the FILE , then unpack a FILE to a DIR
    pack a DIR to a FILE , then encode the FILE
  pak-lz
    unpack a FILE to a DIR , then decode each DIR/SUBFILE
    encode each DIR/SUBFILE , then pack a DIR to a FILE

_MSG;

$type = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case 'lz-pak':
		case 'pak-lz':
		case 'pak':
			$type = $argv[$i];
			break;
		default:
			xeno( $type, $argv[$i] );
			break;
	} // switch( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )
