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
 *
 * Special Thanks
 *   GBATEK v2.8f (no$gba)
 *     Martin Korth
 */
require 'common.inc';

function save_nfsfile( $fp, $fn, $off, $ram, $siz )
{
	$buf  = str_repeat(BYTE, $ram & BIT16);
	$buf .= fp2str($fp, $off, $siz);

	if ( trim($buf,BYTE.ZERO) === '' )
		return;

	save_file($fn, $buf);
	return;
}

function sect_overlay( $fp, $pfx, $off, $siz, &$fat )
{
	if ( $siz === 0 )
		return;
	$over = fp2str($fp, $off, $siz);
	save_file("$pfx/overlay.bin", $over);

	for ( $i=0; $i < $siz; $i += 0x20 )
	{
		$ov_id  = str2int($over, $i+0, 4);
		$ov_ram = str2int($over, $i+4, 3);
		$ov_siz = str2int($over, $i+8, 4);
		$ov_fid = str2int($over, $i+0x18, 4);

		$ov_off = str2int($fat, $ov_fid*8, 4);

		$fn = sprintf('%s/%06x/%04d_%x.overlay', $pfx, $ov_ram, $ov_id, $ov_id);
		printf("%4x , %8x , %8x , %s\n", $ov_fid, $ov_off, $ov_siz, $fn);

		save_nfsfile($fp, $fn, $ov_off, $ov_ram, $ov_siz);
	} // for ( $i=0; $i < $siz; $i += 0x20 )
	return;
}
//////////////////////////////
function sect_data( $fp, $pfx, &$fnt, &$fat, &$fnt_off, $fnt_hd )
{
	$st_id = str2int($fnt, $fnt_off+4, 2);

	if ( $fnt_off >= $fnt_hd )
		return;
	$off1 = str2int($fnt, $fnt_off+0, 4);
	$off2 = str2int($fnt, $fnt_off+8, 4);
	if ( ($fnt_off+8) >= $fnt_hd )
		$off2 = strlen($fnt);
	$fnt_off += 8;

	$func = __FUNCTION__;
	while ( $off1 < $off2 )
	{
		$b = ord( $fnt[$off1+0] );
		$isd = $b & 0x80;
		$siz = $b & 0x7f;
		if ( $siz == 0 )
			return;

		$nam = substr($fnt, $off1+1, $siz);
			$off1 += (1 + $siz);

		if ( $isd )
		{
			$off1 += 2;
			$func($fp, "$pfx/$nam", $fnt, $fat, $fnt_off, $fnt_hd);
		}
		else
		{
			$file_off1 = str2int($fat, $st_id*8+0, 4);
			$file_off2 = str2int($fat, $st_id*8+4, 4);
			$file_siz  = $file_off2 - $file_off1;

			$fn = "$pfx/$nam";
			printf("%4x , %8x , %8x , %s\n", $st_id, $file_off1, $file_siz, $fn);
			$sub = fp2str($fp, $file_off1, $file_siz);
			save_file($fn, $sub);

			$st_id++;
		}
	} // while ( $off1 < $off2 )
	return;
}
//////////////////////////////
function ndsrom( $fname )
{
	// for *.nds only
	if ( stripos($fname, '.nds') === false )
		return;

	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0x180);

	// RAM address check
	if ( $head[0x27] !== "\x02" )  return;
	if ( $head[0x2b] !== "\x02" )  return;
	if ( $head[0x37] !== "\x02" )  return;
	if ( $head[0x3b] !== "\x02" )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
		save_file("$pfx/head.bin", $head);

	$arm9_off = str2int($head, 0x20, 4);
	$arm9_ram = str2int($head, 0x28, 3);
	$arm9_siz = str2int($head, 0x2c, 4);
		save_nfsfile($fp, "$pfx/arm9/main.bin", $arm9_off, $arm9_ram, $arm9_siz);

	$arm7_off = str2int($head, 0x30, 4);
	$arm7_ram = str2int($head, 0x38, 3);
	$arm7_siz = str2int($head, 0x3c, 4);
		save_nfsfile($fp, "$pfx/arm7/main.bin", $arm7_off, $arm7_ram, $arm7_siz);

	$fnt_off  = str2int($head, 0x40, 4);
	$fnt_siz  = str2int($head, 0x44, 4);
	$fat_off  = str2int($head, 0x48, 4);
	$fat_siz  = str2int($head, 0x4c, 4);
		$fnt = fp2str($fp, $fnt_off, $fnt_siz);
		$fat = fp2str($fp, $fat_off, $fat_siz);
		$fnt_off = 0;

	ob_start();

	$ov9_off  = str2int($head, 0x50, 4);
	$ov9_siz  = str2int($head, 0x54, 4);
		sect_overlay($fp, "$pfx/arm9", $ov9_off, $ov9_siz, $fat);

	$ov7_off  = str2int($head, 0x58, 4);
	$ov7_siz  = str2int($head, 0x5c, 4);
		sect_overlay($fp, "$pfx/arm7", $ov7_off, $ov7_siz, $fat);

	$fnt_hd  = str2int($fnt, 0, 4);
		sect_data($fp, "$pfx/data", $fnt, $fat, $fnt_off, $fnt_hd);

	$list = ob_get_clean();
	$list = str_replace("$pfx/", '', $list);
	echo $list;
	save_file("$pfx/cartlist.txt", $list);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndsrom( $argv[$i] );
