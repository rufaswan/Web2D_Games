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
require 'nds_kuma.inc';

function sect_list( &$file, $sid, $off, $ord, $byte )
{
	global $gp_list;
	$ed = strlen($file['d']);
	$st = 0;
	while ( $st < $ed )
	{
		$d = $ord($file['d'], $st+$off, $byte);
		if ( ! isset($gp_list[$sid][$off][$d]) )
			$gp_list[$sid][$off][$d] = 0;
		$gp_list[$sid][$off][$d]++;
		$st += $file['k'];
	} // while ( $st < $ed )
	return;
}

function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FMBS' )
		return;

	global $gp_data;
	$ord = $gp_data['nds_kuma']['ord'];
	if ( $ord($file,8,4) != 0xa0 )
		return;
	load_mbsfile($file, $gp_data['nds_kuma']['sect'], $ord);

	echo "== kuma( $fname )\n";
	sect_list($file[4], 4, 0x00, $ord, 2); // 0 2 4 6

	//sect_list($file[7], 7, 0x08, $ord, 4); // 0
	//sect_list($file[7], 7, 0x0c, $ord, 4); // 0
	//sect_list($file[7], 7, 0x10, $ord, 4); // 0
	//sect_list($file[7], 7, 0x14, $ord, 4); // 0

	sect_list($file[8], 8, 0x08, $ord, 4);
	sect_list($file[8], 8, 0x0c, $ord, 2);
	//sect_list($file[8], 8, 0x0e, $ord, 1); // 0
	//sect_list($file[8], 8, 0x11, $ord, 1); // 0
	//sect_list($file[8], 8, 0x12, $ord, 1); // 0
	//sect_list($file[8], 8, 0x13, $ord, 1); // 0
	//sect_list($file[8], 8, 0x14, $ord, 4); // 0
	//sect_list($file[8], 8, 0x18, $ord, 2); // 0
	sect_list($file[8], 8, 0x1a, $ord, 2); // 0 1
	sect_list($file[8], 8, 0x1c, $ord, 4);

	return;
}

$gp_list = array();
for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

ksort($gp_list);
foreach ( $gp_list as $sk => $sv )
{
	ksort($sv);
	foreach ( $sv as $ok => $ov )
	{
		printf("== s%x( %x )\n", $sk, $ok);
		ksort($ov);
		foreach ( $ov as $k => $v )
			printf("  %8x  %8x\n", $k, $v);
	} // foreach ( $sv as $ok => $ov )
} // foreach ( $gp_list as $sk => $sv )
