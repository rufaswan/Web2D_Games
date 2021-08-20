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

function sect_map()
{
	$pos = 0;

	$b1 = ord( $sub[$pos+0] );
	$b2 = ord( $sub[$pos+1] );
		$pos += 2;

	$col = $b1 & BIT4;
	$row = $b1 >> 4;

	$tid = $b2 & BIT4;
	$cid = $b2 >> 4;


	return;
}
//////////////////////////////
function load_mex( $fname, $pfx )
{
	$mex = load_file($fname);
	if ( empty($mex) )
		return '';

	$off = array();
	$off[] = strlen($mex);

	for ( $i=0; $i < 0x38; $i += 4 )
	{
		$b = str2int($mex, $i, 4);
		if ( $b == 0 )
			continue;
		$off[] = $b;
	} // for ( $i=0; $i < 0x38; $i += 4 )
	sort($off);

	$data = array();
	for ( $i=0; $i < 0x38; $i += 4 )
	{
		$b = str2int($mex, $i, 4);
		if ( $b == 0 )
			continue;

		$i4 = $i / 4;
		$id = array_search($b, $off);
		$sz = $off[$id+1] - $off[$id];

		$s = substr($mex, $off[$id], $sz);
		printf("%6x , %6x , %d.mex\n", $off[$id], $sz, $i4);
		$data[$i4] = $s;
	} // for ( $i=0; $i < 0x38; $i += 4 )

	foreach ( $data as $k => $v )
		save_file("$pfx/$k.mex", $v);

	return $data;
}

function ralph( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$tim = load_tbltim("$pfx.tbl", "$pfx/tim");
	$mex = load_mex   ("$pfx.mex", "$pfx/mex");
	$pdt = load_pdt   ("$pfx.pdt");
	if ( empty($tim) || empty($mex) || empty($pdt) )
		return;

	$sect = array(
		5  => 0x04,
		6  => 0x14,
		7  => 0x0c,
		8  => 0x0c,
		9  => 0x10,
		10 => 0x0c,
	);
	$cex = $mex[1];
	ralph_tbl_cex ($cex, "$pfx/cex", $sect);
	//ralph_cex_cpdt($cex, $pdt, $cdt, $pfx, $sect);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ralph( $argv[$i] );

/*
st01a.mex = RAM 801619c0
	room = 16x16 tile

 */
