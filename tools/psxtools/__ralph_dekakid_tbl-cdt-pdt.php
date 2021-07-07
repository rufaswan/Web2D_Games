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

$gp_clut = '';
$gp_pix  = '';

function sect_tbl( &$tbl, $pfx )
{
	$data = array();

	$pos = 0;
	$siz = str2int($tbl, $pos+8, 4);
	$data[] = substr($tbl, $pos+12, $siz);
	printf("%6x , %6x , %d.meta\n", $pos+12, $siz, count($data)-1);
		$pos += (12 + $siz);

	if ( str2int($tbl,$pos+8,4) != 0x38 )
		return php_error("tbl-2 is NOT 38\n");

	$sub = substr($tbl, $pos+8);
	$sub[0] = "\x3c";

	$off = array(
		strlen($sub) => 1,
	);
	for ( $i=0; $i < 0x3c; $i += 4 )
	{
		$b = str2int($sub, $i, 4);
		if ( $b == 0 )
			continue;
		$off[$b] = 1;
	} // for ( $i=0; $i < 0x3c; $i += 4 )
	ksort($off);
	$off = array_keys($off);

	for ( $i=0; $i < 0x3c; $i += 4 )
	{
		$b = str2int($sub, $i, 4);
		$s = '';
		if ( $b != 0 )
		{
			$n = array_search($b, $off);
			$z = $off[$n+1] - $off[$n];
			$s = substr($sub, $off[$n], $z);
			printf("%6x , %6x , %d.meta\n", $pos+8+$b, $z, count($data));
		}
		$data[] = $s;
	} // for ( $i=0; $i < 0x38; $i += 4 )

	foreach ( $data as $k => $v )
		save_file("$pfx/meta/$k.meta", $v);

	$tbl = $data;
	return;
}

function ralph( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$tbl = load_file("$pfx.tbl");
	$cdt = load_file("$pfx.cdt");
	$pdt = load_file("$pfx.pdt");
	if ( empty($tbl) || empty($cdt) || empty($pdt) )
		return;

	if ( str2int($tbl,0,4) !== 2 )
		return;

	global $gp_clut, $gp_pix;
	bpp4to8($pdt);
	$gp_pix  = substr($pdt, 0x100);
	$gp_clut = pal555( substr($cdt,4) );
	$cdt = '';
	$pdt = '';

	sect_tbl($tbl, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ralph( $argv[$i] );

/*
ferica
	pdt    = (5b480-80)/80 =  b68
	8.meta =   c6c0    /c  = 1090
	7.meta =   15b8    /14 =  116

	7.meta [+0] = 107d+13 => 8.meta
	8.meta [+4] =  b67    => pdt

kid.tbl
	    c-  75c    ea*8
	  844-  abc    4f*8   [+ 0]  3aa+15
	  abc- 28b4   3bf*8
	 28b4- 4118    df*1c  [+ 0]  467+1
	 4118- 52b8   468*4
	 52b8- 7518   1b8*14  [+ 0] 1559+11
	 7518-17bec  15e7*c
	17bec-180fc    51*10
	180fc-19e6c   274*c
 */
