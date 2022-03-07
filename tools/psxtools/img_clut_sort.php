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

function clutsort( $fname )
{
	$img = load_clutfile($fname);
	if ( empty($img) )  return;

	// not dealing with RGBA
	if ( ! isset( $img['pal'] ) )
		return;

	// weight palette
	$len = $img['cc'] * 4;
	$pal = array();
	for ( $i=0; $i < $len; $i += 4 )
		$pal[] = array($i >> 2, substr($img['pal'], $i, 4));

	usort($pal, function($a, $b){
		$r1 = ord( $a[1][0] );
		$g1 = ord( $a[1][1] );
		$b1 = ord( $a[1][2] );
		$a1 = ord( $a[1][3] );
		$sum1 = $r1 + $g1 + $b1 + $a1;

		$r2 = ord( $b[1][0] );
		$g2 = ord( $b[1][1] );
		$b2 = ord( $b[1][2] );
		$a2 = ord( $b[1][3] );
		$sum2 = $r2 + $g2 + $b2 + $a2;

		if ( $sum1 != $sum2 )  return ($sum1 > $sum2); // ASC
		if ( $a1 != $a2 )  return ($a1 > $a2);
		if ( $r1 != $r2 )  return ($r1 > $r2);
		if ( $g1 != $g2 )  return ($g1 > $g2);
		if ( $b1 != $b2 )  return ($b1 > $b2);
		return 0;
	});

	$img['pal'] = '';
	$remap = array();
	foreach ( $pal as $k => $v )
	{
		$img['pal'] .= $v[1];

		$b1 = chr($v[0]);
		$b2 = chr($k);
		$remap[$b1] = $b2;
	} // foreach ( $pal as $v )

	// remap pix
	$len = strlen( $img['pix'] );
	for ( $i=0; $i < $len; $i++ )
	{
		$b = $img['pix'][$i];
		$img['pix'][$i] = $remap[$b];
	} // for ( $i=0; $i < $len; $i++ )

	save_clutfile($fname, $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	clutsort( $argv[$i] );
