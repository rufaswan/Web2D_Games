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

function save_sgfile( $fn, &$sub )
{
	$fn = str_replace('.', '_', $fn);
	$fn = strtolower($fn);
	if ( substr($sub,0,4) == 'TEN2' )
		$fn .= '.ten2';

	save_file($fn, $sub);
	return;
}
//////////////////////////////
function z2arc( &$file, $dir )
{
	$len = strlen($file);
	$pos = 0;
	while (1)
	{
		$off1 = str2int($file, $pos+ 0, 3);
		$off2 = str2int($file, $pos+16, 3);
		if ( $off1 == 0 )
			return;
		if ( $off2 == 0 )
			$off2 = $len;

		$fn = substr0($file, $pos+3);
			$pos += 16;

		$siz = $off2 - $off1;
		$sub = substr($file, $off1, $siz);

		$s = "$dir/$fn";
		printf("%8x , %8x , %s\n", $off1, $siz, $s);
		save_sgfile($s, $sub);
	} // while (1)

	return;
}

function r2arc( &$file, $dir )
{
	if ( substr0($file,8) !== 'Copyright by TENKY 1996' )
		return;

	$base = str2int($file, 0, 3);
	$cnt  = str2int($file, 4, 3);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x20 + ($i * 0x20);
		$fn = substr0($file, $p+ 0);
		$b1 = str2int($file, $p+16, 3);
		$b2 = str2int($file, $p+20, 3);
			$ps = ($base + $b1) * 0x800;
			$sz =  $b2 * 0x800;

		$sub = substr($file, $ps, $sz);
		$s = "$dir/$fn";
		printf("%8x , %8x , %s\n", $ps, $sz, $s);

		if ( stripos($s, '.z2') !== false )
			z2arc($sub, $s);
		else
			save_sgfile($s, $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

function suigai( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( stripos($fname, '.r2') !== false )
		return r2arc($file, $fname);
	if ( stripos($fname, '.z2') !== false )
		return r2arc($file, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	suigai( $argv[$i] );
