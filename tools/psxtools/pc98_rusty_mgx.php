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
 *   Rusty English translation project
 *   http://46okumen.com/projects/rusty/
 *     46 Okumen
 */
require 'common.inc';
require 'common-guest.inc';
require 'pc98_rusty.inc';

//define('DRY_RUN', true);
//define('NO_TRACE', true);

$gp_clut = '';

function sectmgx( &$file, $fname, $pos )
{
	printf("== sectmag( $fname , %x )\n", $pos);
	echo debug( substr($file, $pos+0, 4) );

	$x1 = str2int($file, $pos+ 4, 2);
	$y1 = str2int($file, $pos+ 6, 2);
	$x2 = str2int($file, $pos+ 8, 2);
	$y2 = str2int($file, $pos+10, 2);
	$w = int_ceil($x2-$x1, 8);
	$h = int_ceil($y2-$y1, 8);
	if ( $w == 0 || $h == 0 )
		return;

	$b1 = str2int($file, $pos+12, 4);
	$b2 = str2int($file, $pos+16, 4);
	$b3 = str2int($file, $pos+20, 4); // size
	$b4 = str2int($file, $pos+24, 4);
	$b5 = str2int($file, $pos+28, 4); // size

	global $gp_clut;
	$gp_clut = '';
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$p = $pos + 32 + $i;
		// in GRB order
		$gp_clut .= $file[$p+1] . $file[$p+0] . $file[$p+2] . BYTE;
	}

	$pix = mag_decode($file, $w, $h, $pos+$b1, $pos+$b2, $pos+$b4 );
	//save_file("$fname.pix", $pix);

	while ( strlen($pix) % 2 )
		$pix .= ZERO;

	$data = 'CLUT';
	$data .= chrint(16, 4);
	$data .= chrint($w, 4);
	$data .= chrint($h, 4);
	$data .= $gp_clut;

	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$b0 = ord( $pix[$i+0] );
		$b1 = ord( $pix[$i+1] );

		$j = 4;
		while ( $j > 0 )
		{
			$j--;
			$b01 = $b0 >> ($j+4);
			$b02 = $b0 >> ($j+0);
			$b11 = $b1 >> ($j+4);
			$b12 = $b1 >> ($j+0);
			$bj = bits8(0,0,0,0, $b11,$b12,$b01,$b02);
			$data .= chr($bj);
		}
	} // for ( $i=0; $i < $len; $i += 2 )
	save_file("$fname.clut", $data);

	return;
}
//////////////////////////////
function rusty( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// for *.mgx
	$mgc = substr0($file, 0, chr(0x1a));
	if ( substr($mgc, 0, 6) === 'MAKI02' )
		return sectmgx($file, $fname, strlen($mgc)+1);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

/*
op.com
	staff0.mgx
	r_a11.mgx r_a11_1.mgx r_a11_2.mgx r_a11_3.mgx r_a11_4.mgx r_a11_5.mgx r_a11_6.mgx r_a11_7.mgx
	r_a21.mgx r_a21pal.mgx r_a21p_.mgx r_a23.mgx r_a24.mgx r_a26.mgx r_a26a.mgx
	r_a31.mgx r_a32.mgx r_a33.mgx r_a35.mgx r_a36.mgx
	r_b11.mgx r_b12.mgx r_b14.mgx r_b15.mgx r_b16a.mgx r_b16b.mgx
	r_b21a.mgx r_b21b.mgx r_b22.mgx r_b23.mgx r_b24.mgx
	r_b31.mgx r_b32_1.mgx r_b32_2.mgx r_b33.mgx r_b33a.mgx r_b33b.mgx r_b33c.mgx r_b33d.mgx r_b34_1.mgx r_b34_2.mgx
 */
