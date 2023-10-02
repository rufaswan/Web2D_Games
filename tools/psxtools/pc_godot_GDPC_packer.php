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

function godot_pack( $dir )
{
	echo "== godot_pack( $dir )\n";
	$dir = rtrim ($dir, '/\\');
	$len = strlen($dir);

	$list = lsfile_bysize_r($dir);
	if ( empty($list) )
		return;

	// reserve space for header + toc
	$dat_off = 0x58;
	foreach ( $list as $k => $v )
	{
		list($fsize,$fname) = $v;

		$ln = strlen($fname) - $len + 6;
		$dat_off += (4 + $ln + 0x20);
	} // foreach ( $list as $lk => $lv )


	// build header + toc
	$file = str_repeat(ZERO, $dat_off);
		str_update($file, 0, 'GDPC');
		$file[ 4] = "\x00"; // version
		$file[ 8] = "\x02"; // major
		$file[12] = "\x01"; // minor
		$file[16] = "\x00"; // revision
	$cnt = chrint(count($list), 4);
		str_update($file, 0x54, $cnt);

	// add files
	$toc_off = 0x58;
	foreach ( $list as $k => $v )
	{
		list($fsize,$fname) = $v;

		$res = strlen($fname) - $len + 6;
		$fdt = file_get_contents($fname);

		$b1 = chrint($res, 4);
		$b2 = 'res://' . substr($fname, $len+1);
		str_update($file, $toc_off + 0, $b1);
		str_update($file, $toc_off + 4, $b2);
			$toc_off += (4 + $res);

		$b1  = chrint($dat_off, 4);
		$b2  = chrint($fsize  , 4);
		$md5 = md5($fdt, true);
		str_update($file, $toc_off +  0, $b1);
		str_update($file, $toc_off +  8, $b2);
		str_update($file, $toc_off + 16, $md5);
			$toc_off += 0x20;

		$file .= $fdt;
		$dat_off += $fsize;
	} // foreach ( $list as $lk => $lv )

	save_file("$dir.pck", $file);
	return;
}

function godot_unpack( $fname )
{
	echo "== godot_unpack( $fname )\n";
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'GDPC' )
		return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0x54, 4);
	$pos = 0x58;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$len = str2int($file, $pos + 0, 4);
		$fnm = substr ($file, $pos + 4, $len);
			$pos += (4 + $len);
		$off = str2int($file, $pos + 0, 4);
		$siz = str2int($file, $pos + 8, 4);
		//md5 = substr ($file, $pos + 16, 16);
			$pos += 0x20;
		printf("%8x , %8x , %s\n", $off, $siz, $fnm);

		if ( substr($fnm,0,6) === 'res://' )
			$fnm = substr($fnm, 6);

		$sub = substr($file, $off, $siz);
		save_file("$dir/$fnm", $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

function godot( $ent )
{
	if ( is_file($ent) )
		return godot_unpack($ent);
	if ( is_dir($ent) )
		return godot_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	godot( $argv[$i] );
