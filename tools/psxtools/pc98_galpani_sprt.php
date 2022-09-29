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
require 'pc98_galpani.inc';

define('NO_TRACE', true);

function pal_update( &$pal, $sub, $pid )
{
	if ( empty($sub) === 0 )
		return '';

	$len = int_ceil(strlen($sub), -3);
	$p1  = $pid * 4;
	for ( $i=0; $i < $len; $i += 3 )
	{
		$pal[$p1+0] = $sub[$i+0];
		$pal[$p1+1] = $sub[$i+1];
		$pal[$p1+2] = $sub[$i+2];
		$pal[$p1+3] = BYTE;
			$p1 += 4;
	}
	return;
}

function save_gpimg( $fn, $w, $h, &$pix, &$pal, $p40 )
{
	$img = array(
		'cc'  => 0x100,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	);
	if ( $p40 )
	{
		$img['cc' ] >>= 1;
		$img['pal'] = substr($img['pal'], 0x80*4);

		$len = strlen( $img['pix'] );
		for ( $i=0; $i < $len; $i++ )
		{
			$b = ord( $img['pix'][$i] ) & 0x7f;
			$img['pix'][$i] = chr($b);
		}
	}
	save_clutfile($fn, $img);
	return;
}
//////////////////////////////
function sect_dec( &$dec, &$ind, &$pal, $dir )
{
	foreach ( $ind as $ik => $ipos )
	{
		$w = str2int($dec, $ipos+0x20, 2);
		$h = str2int($dec, $ipos+0x24, 2);

		// 01 = ?ignore pix = 3?
		// 02
		// 04
		// 08
		// 10
		// 20 = multi-file packed pix data
		// 40 = pix &= 7f , pal = 2nd half
		// 80 = palette data
		$flg = ord( $dec[$ipos+0x30] );
		$num = ord( $dec[$ipos+0x32] );
		$fl2 = ord( $dec[$ipos+0x33] );
		$b = str2int($dec, $ipos+0x38, 2);
		if ( $b > $w )
			$w = $b;

		echo debug(substr($dec,$ipos+0x30,12));
			$ipos += 0x40;

		$pfx = sprintf('%s/%04d', $dir, $ik);
		printf("%8x , %4x x %4x , %2x , %s\n", $ipos, $w, $h, $num, $pfx);

		$pix = array();
		if ( $flg & 0x20 )
		{
			$base = $ipos;
			for ( $i=0; $i < $num; $i++ )
			{
				$p1 = str2int($dec, $ipos, 3);
					$ipos += 4;
				$ty = ord( $dec[$base+$p1+0] );
				$dt = substr($dec, $base+$p1+1);

				$pix[] = galpani_dectype($dt, $ty);
			} // for ( $i=0; $i < $num; $i++ )
		}
		else
		{
			$size = $w * $h;
			for ( $i=0; $i < $num; $i++ )
			{
				$pix[] = substr($dec, $ipos, $size);
				$ipos += $size;
			} // for ( $i=0; $i < $num; $i++ )
		}

		if ( $flg === 0x80 )
		{
			if ( $fl2 === 1 )
			{
				pal_update($pal, $pix[0], 0);
				//save_file("$pfx.pal", $pal);
			}
			continue;
		}

		if ( count($pix) === 1 )
		{
			//save_file ("$pfx.pix" , $pix[0]);
			save_gpimg("$pfx.clut", $w, $h, $pix[0], $pal, $flg & 0x40 );
			continue;
		}

		foreach ( $pix as $pk => $pv )
		{
			//save_file ("$pfx.$pk.pix" , $pv);
			save_gpimg("$pfx.$pk.clut", $w, $h, $pv, $pal, $flg & 0x40);
		} // foreach ( $pix as $pk => $pv )
	} // foreach ( $ind as $ik => $iv )
	return;
}

function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'sprt' )
		return;

	$dir = str_replace('.', '_', $fname);
	$pal = str_repeat(ZERO, 0x100*4);

	// palette data
	$pos = str2int($file, 0x18, 3);
	$siz = str2int($file, 0x1c, 3);
	$pid = ord( $file[0x35] );
	$sub = substr($file, $pos, $siz);
		pal_update($pal, $sub, $pid);

	// index data
	$pos = str2int($file, 0x20, 3);
	$siz = str2int($file, 0x24, 3);
	$ind = array();

	for ( $i=0; $i < $siz; $i += 4 )
		$ind[] = str2int($file, $pos+$i, 3);

	// decode pixel data
	$pos = str2int($file, 0x28, 3);
	$siz = str2int($file, 0x2c, 3);
	$sub = substr ($file, $pos, $siz);
	while ( strlen($sub) < $siz )
		$sub .= ZERO;

	$siz = str2int($file, 0x10, 3);
	if ( strlen($sub) === $siz )
	{
		printf("NO TYPE %s [%x]\n", $fname, $siz);
		save_file("$dir/dec.0", $sub);
		return sect_dec($sub, $ind, $pal, $dir);
	}
	else
	{
		$type = 3;
		while ( $type !== 0 )
		{
			$dec = galpani_dectype($sub, $type);
				$type--;

			if ( strlen($dec) === $siz )
			{
				$type++;
				printf("DETECT %s = type %x [%x]\n", $fname, $type, $siz);

				$fn = sprintf('%s/dec.%x', $dir, $type);
				save_file($fn, $dec);
				return sect_dec($dec, $ind, $pal, $dir);
			}
		} // while ( $type !== 0 )
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );

/*
NO TYPE/Uncompressed
	gp1 mgcnespr.dat
	gp2 il0000s.dat
	gp2 jn0000s.dat
	gp2 mm01[01/02/03/0b/0s]s.dat
	gp2 mm02[01/02/03/0b/0s]s.dat
	gp2 mm03[01/02/03/0b/0s]s.dat
	gp2 mm04[01/02/03/0b/0s]s.dat
	gp2 mm05[01/02/03/0b/0s]s.dat
	gp2 mm06[01/02/03/0b/0s]s.dat
	gp2 mm07[01/02/03/0b/0s]s.dat
	gp2 mm08[01/02/03/0b/0s]s.dat
	gp2 mm09[01/02/03/0b/0s]s.dat
	gp2 mm10[01/02/03/0b/0s]s.dat
	gp2 mm11[01/02/03/0b/0s]s.dat
	gp2 mm12[01/02/03/0b/0s]s.dat
	gp2 mm13[01/02/03/0b/0s]s.dat
	gp2 mm14[01/02/03/0b/0s]s.dat
	gp2 mm15[01/02/03/0b/0s]s.dat
	gp2 mygisspr.dat
	gp2 myjstspr.dat
	gp2 mypicspr.dat

FLAG
	gp1 gs[ma/mi/mm/nr/si/ss/tm]spr.dat = 0
	gp1 mgcomspr.dat = 40 60 20
	gp1 mgslmspr.dat = 60 40
	gp1 nsvspr.dat = 40 41
	gp1 pgpspr.dat = 40 41
	gp1 srndspr.dat = 0
	gp1 *.dat = 40


gp2 il01sspr.dat = 3
	 2ed8- 6208  104x [68 x 7e]
	 6248-145c8  104x [68 x 8c*4]
	14608-23cd4  117x [75 x 6c*5]
	23d14-25e80  124x [7c x 45]

gp1_ttlspr.dat
	c2 = #f81000
	c6 = #e01000
	fa = #907878

gp2_myttlspr.dat/20.pix = 9.pal
	4e = #f7429c
	4f = #ff73ce
	d8 = #ffffff
	dd = #94003a
 */
