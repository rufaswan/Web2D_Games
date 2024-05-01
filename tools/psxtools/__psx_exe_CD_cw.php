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

define('MIPS_RETURN', "\x08\x00\xe0\x03");

function mips_align( &$array )
{
	$cnt = count($array);
	while ( $cnt > 0 )
	{
		$cnt--;
		if ( $array[$cnt] & 3 ) // non-4
			array_splice($array, $cnt, 1);
	} // while ( $cnt > 0 )
	return;
}

function mips_cdcw( &$file, &$sub, $off, $fname )
{
	$len = strlen($sub);
	switch ( $len )
	{
		default:
			return printf("unknown CD_cw version = %x\n", $len);
	} // switch ( $len )
	return;
}

function psxexe( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b18 = str2int($file, 0x18, 4);
	$b1c = str2int($file, 0x1c, 4);
		$b18 &= 0x7fffffff;
		$b1c &= 0x7fffffff;
	if ( $b18 < 0x10000 )
		return;
	if ( ($b18+$b1c) >= 0x200000 )
		return;

	$len = strlen($file);
	if ( ($b1c+0x800) > $len )
		return;

	$cdcw = strpos_all($file, 'CD_cw'    , 0x800);
	$retn = strpos_all($file, MIPS_RETURN, 0x800);
	mips_align($cdcw);
	mips_align($retn);
	if ( empty($cdcw) )  return;
	if ( empty($retn) )  return;

	$ram_cdcw = $cdcw[0] - 0x800 + $b18;
		$half_low = $ram_cdcw & BIT16;
		$half_upp = ($ram_cdcw >> 16) | 0x8000;
		if ( $half_low & 0x8000 ) // is negative , so lui+1
			$half_upp++;

		$half_low = chrint($half_low, 2);
		$half_upp = chrint($half_upp, 2);

	$cnt = count($retn) - 1;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$off1 = $retn[$i+0] + 8;
		$off2 = $retn[$i+1] + 8;
		$sub  = substr($file, $off1, $off2 - $off1);

		$p1 = strpos($sub, $half_low);
		$p2 = strpos($sub, $half_upp);
		if ( $p1 === false || $p2 === false )
			continue;
		if ( $p1 & 3 || $p2 & 3  )
			continue;
		printf("function CD_cw() = RAM %x , FILE %x\n", $off1 - 0x800 + $b18, $off1);
		return mips_cdcw($file, $sub, $off1, $fname);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxexe( $argv[$i] );
