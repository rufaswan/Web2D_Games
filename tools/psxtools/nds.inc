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

define("NDS_RAM" ,  4 << 20);
define("NDSI_RAM", 16 << 20);

function nds_ramload( &$ram, $fname, $off )
{
	$bin = load_file($fname);
	if ( empty($bin) )
		return;
	$bin = substr($bin, $off & BIT16);
	str_update($ram, $off, $bin);
	return;
}

function nds_ram( $dir, $dsi=false )
{
	$head = load_file("$dir/head.bin");
	if ( empty($head) )
		return php_error("NDS_RAM %s/head.bin not found", $dir);

	$siz = ( $dsi ) ? NDSI_RAM : NDS_RAM;
	$ram = str_repeat(ZERO, $siz);
	$off9 = str2int($head, 0x28, 3);
	$off7 = str2int($head, 0x38, 3);

	nds_ramload($ram, "$dir/arm9/main.bin", $off9);
	nds_ramload($ram, "$dir/arm7/main.bin", $off7);
	return $ram;
}
//////////////////////////////
function nds_patch( $dir, $pfx )
{
	$head = load_file("$dir/head.bin");
	if ( empty($head) )
		return array();
	$NTR = substr($head, 12, 4);
	$ver = ord( $head[0x1e] );

	$txt = sprintf("%s_%s_%d.txt", $pfx, $NTR, $ver);
	return patchfile($txt);
}

function nds_overlay( &$ram, $dir, $id )
{
	if ( strpos($id, 'ov-') !== false )
		$id = (int)substr($id, 3);

	$bin = load_file("$dir/arm9/overlay.bin");
	$bin = substr($bin, $id*0x20, 0x20);
	if ( empty($bin) )
		return -1;
	$ov_ram = str2int($bin, 4, 3);

	$over = sprintf("%s/arm9/%06x/%04d_%x.overlay", $dir, $ov_ram, $id, $id);
	nds_ramload($ram, $over, $ov_ram);
	trace("load OVERLAY %x @ %s\n", $ov_ram, $over);
	return $ov_ram;
}

function nds_game( &$ram, $dir, $game )
{
	foreach ( $game as $g )
	{
		if ( strpos($g, 'ov-') === false )
			continue;
		nds_overlay( $ram, $dir, $g );
	}
	return;
}
