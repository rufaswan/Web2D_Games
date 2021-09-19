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
require "ralph.inc";

function load_tblcex( $pfx )
{
	$cex = load_tbl("$pfx.tbl");
	if ( ! empty($cex) )
		return $cex;

	$cex = load_tbl("$pfx.cex");
	if ( ! empty($cex) )
		return $cex;

	return '';
}

function load_cdt( $fname )
{
	$cdt = load_file($fname);
	if ( empty($cdt) )
		return '';

	$cn  = str2int($cdt, 0, 4);
	$pal = substr($cdt, 4, $cn*0x20);
	return pal555($pal);
}
//////////////////////////////
function ralph( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$cex = load_tblcex($pfx);
	$cdt = load_cdt("$pfx.cdt");
	$pdt = load_pdt("$pfx.pdt");
	if ( empty($cex) || empty($cdt) || empty($pdt) )
		return;

	$sect = array(
		5  => 0x04,
		6  => 0x14,
		7  => 0x0c,
		8  => 0x0c,
		9  => 0x10,
		10 => 0x0c,
	);

	ralph_tbl_cex ($cex, "$pfx/cex", $sect);
	ralph_cex_cpdt($cex, $pdt, $cdt, $pfx, $sect);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ralph( $argv[$i] );

/*
ferica
	tbl = RAM 80111508
	pdt = RAM 80183e1c

	cdt =  14
	pdt = b69-1
	s0  =  224/8  =   44+4
	s1  = 1a64/8  =  34c+4
	s2  = 1770/1c =   d6+8
	s5  =  ac8/4  =  2b2
	s6  = 15b8/14 =  116
	s7  = c6c0/c  = 1090
	s8  =  4e0/c  =   68
	s9  =  4d0/10 =   4d
	s10 = 19e0/c  =  228
	s11 =   b8/c  =    f+4

	s7 [+4] =  b67    => pdt
	s6 [+0] = 107d+13 => s7 , [+2] = 67 => s8 , [+6] = 225+3 => s10
	s5 [+0] =  115    => s6
	s2 [+8] =  2b1+1  => s5
	s0 [+4] =  338+18 =>

	b888b8 + 000000 = 584058
	c898c8 + 000000 = 604860

	alpha = ( COLOR >> 1 ) - 4

	b7 == 3
		151/aed , 255/fb7
	b7 == 2
		250/f54 , 251/f68 , 252/f80 , 253/f97 , 254/fa8
		255/fb7

	???
		45/3af , 46/3c4 , 47/3d9 , 48/3ed
		49/403 , 50/41b , 51/42e , 52/441
		68/54a , 69/550 , 70/558 , 71/562
		72/56d , 73/577

-      0 1 2 3 4 5 6 7 8 9 10 11 12 13
ferica 1 1 1 - 1 1 1 1 1 1  1  1  1  1
kid    1 1 - - 1 1 1 1 1 1  1  1  1  1
ralgo  1 1 1 1 1 1 1 1 1 1  1  1  1  1
tra    1 1 1 1 1 1 1 1 1 1  1  1  1  1
desta  1 1 - - 1 1 1 1 1 1  1  1  1  1
magic  1 1 1 1 1 1 1 1 1 1  1  1  1  1

*.tbl is combined *.adt and *.cex

VS mode = each char has 6 palettes

 */
