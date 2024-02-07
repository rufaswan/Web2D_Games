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
require 'common-iso.inc';

$gp_patch = array(
	'/slps_023.68' => 'jp_marvel_vs_capcom',
);

function jp_marvel_vs_capcom( $dir, &$iso )
{
	$patch = '';

	$psx = load_file("$dir/slps_023.68");
	if ( empty($psx) )  return '';

	$patch .= "FILE   = /slps_023.68\n";
	$patch .= "OFFSET = MIN\n";
	$patch .= "SIZE   = BYTE\n";
	for ( $i = 0x15de4; $i < 0x16b44; $i += 8 )
	{
		$min = substr($psx, $i, 3);
		if ( $min === ZERO.ZERO.ZERO )
			continue;

		$lba = frame2lba($min);
		$ent = isolba($iso, $lba);
		if ( $ent === -1 )
			return php_error('[slps_023.68 @ %x] LBA %x not found', $i, $lba);
		$patch .= sprintf("%6x , %8x , %s\n", $i, $i+4, $ent['file']);
	} // for ( $i = 0x15de4; $i < 0x; $i += 8 )
	return $patch;
}

function psxloadlist( $dir )
{
	$list = load_file("$dir/__CDXA__/list.txt");
	if ( empty($list) )  return '';

	$iso = array();
	foreach ( explode("\n",$list) as $v )
	{
		$line = splitline(',', $v);
		if ( count($line) === 3 )
		{
			$iso[] = array(
				'lba'  => hexdec($line[0]),
				'size' => hexdec($line[1]),
				'file' => $line[2],
			);
		}
		else
		if ( count($line) === 5 ) // xa
		{
			$iso[] = array(
				'lba'  => hexdec($line[0]),
				'size' => hexdec($line[2]),
				'file' => $line[4],
			);
		}
	} // foreach ( explode("\n",$list) as $line )
	return $iso;
}

function psxpatch( $dir )
{
	$dir = rtrim($dir, '/\\');
	if ( ! is_dir($dir) )
		return;

	$iso = psxloadlist($dir);
	if ( empty($iso) )  return;

	$list = array();
	lsfile_r($dir, $list);

	global $gp_patch;
	foreach ( $list as $fn )
	{
		foreach ( $gp_patch as $pk => $pv )
		{
			if ( stripos($fn, $pk) !== false )
			{
				printf("[DETECT] %s( %s )\n", $pv, $dir);
				$patch = $pv($dir, $iso);
				return save_file("$dir/__CDXA__/patch.txt", $patch);
			}
		} // foreach ( $gp_patch as $pk => $pv )
	} // foreach ( $list as $ent )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxpatch( $argv[$i] );
