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

function xda_pack( $dir )
{
	echo "== xda_pack( $dir )\n";
	return;
}

function xda_unpack_ent($xda, &$head, &$off1, &$off2, $ed, $dir)
{
	if ( $off1 >= $ed )
		return;
	$type = str2int($head, $off1, 4);
	$func = __FUNCTION__;

	//printf("== xda_unpack_ent( %x , %x , %s ) = %x\n", $off1, $off2, $dir, $type);
	switch ( $type )
	{
		case 0x3ea:
			$c1  = str2int($head, $off1+0x08, 2); // cnt dir
			$c2  = str2int($head, $off1+0x0a, 2); // cnt file
			$cnt = $c1 + $c2;
			$len = str2int($head, $off1+0x14, 4);
			$fn  = substr0($head, $off1+0x18);
				$off1 += (0x18 + $len);

			$dname = "$dir/$fn";
			//printf("%8x , DIR  , %s\n", $cnt, $dname);
			for ( $i=0; $i < $cnt; $i++ )
				$func($xda, $head, $off1, $off2, $ed, $dname);
			return;
		case 0x3eb:
			$len = str2int($head, $off1+0x0c, 4);
			$fn  = substr0($head, $off1+0x10);
				$off1 += (0x10 + $len);
			$pos = str2int($head, $off2+0, 4);
			$siz = str2int($head, $off2+4, 4);
				$off2 += 0x0c;

			$fname = "$dir/$fn";
			printf("%8x , %8x , FILE , %s\n", $pos, $siz, $fname);

			//$sub = fp2str($xda, $pos, $siz);
			//save_file($fname, $sub);
			return;
		default:
			return php_error("UNKNOWN %x , %x , %x", $type, $off1, $off2);
	}
	return;
}

function xda_unpack( $fname )
{
	echo "== xda_unpack( $fname )\n";
	$xda = fopen($fname, 'rb');
	if ( ! $xda )  return;

	$head = fp2str($xda, 0, 0x800);
	if ( substr($head, 0x10, 4) !== "XDA\x00" )
		return;

	$toc_st = str2int($head, 0x28, 4);
	$toc_fz = str2int($head, 0x2c, 4);
	$toc_dz = str2int($head, 0x34, 4);

	$head = fp2str($xda, $toc_st, $toc_fz+$toc_dz);
	$dir = str_replace('.', '_', $fname);

	$off1 = 0x10;
	$off2 = $toc_fz + 0x10;
	$ed = $toc_fz;
	xda_unpack_ent($xda, $head, $off1, $off2, $ed, $dir);
	return;
}

function smgr( $ent )
{
	if ( is_file($ent) )
		return xda_unpack($ent);
	if ( is_dir ($ent) )
		return xda_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	smgr( $argv[$i] );
