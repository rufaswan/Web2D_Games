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

/*
function sectsprt( &$file, $fname )
{
	echo "== sectsprt( $fname )\n";

	$dir = str_replace('.', '_', $fname);

	$b1 = str2int($file, 0x18, 4); // palette
	$b2 = str2int($file, 0x1c, 4); // size
	$b3 = str2int($file, 0x20, 4); // offset (for decoded data)
	$b4 = str2int($file, 0x24, 4); // size
	$b5 = str2int($file, 0x28, 4); // data
	$b6 = str2int($file, 0x2c, 4); // size

	$pal = '';
	for ( $i=0; $i < $b2; $i += 3 )
		$pal .= substr($file, $b1+$i, 3) . BYTE;

	global $gp_bits, $gp_sub;
	$file .= ZERO . ZERO;

	// auto detect decompression type
	$pxsz = str2int($file, 0x10, 4);
	for ( $i=3; $i > 0; $i-- )
	{
		$pix = galpani_decode($file, $b5, $b5+$b6, $gp_bits[ $gp_sub[$i] ]);
		if ( strlen($pix) >= $pxsz )
		{
			$ty = $i;
			echo "TYPE : $ty , {$gp_sub[$ty]}\n";
			//file_put_contents("$fname.pix", $pix);
			break;
		}
	} // for ( $i=3; $i > 0; $i-- )

	$cnt = $b4 / 4;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $b3 + ($i*4);
		$p = str2int($file, $p, 4);

		$w  = str2int($pix, $p+0x38, 4);
		$sz = str2int($pix, $p+0x3c, 4);
		$h  = $sz / $w;

		$clut = 'CLUT';
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= grayclut(0x100);
		$clut .= substr($pix, $p+0x40, $sz);

		$fn = sprintf('%s/%04d.clut', $dir, $i);
		save_file($fn, $clut);
	}
	return;
}
*/

function sectsprt( &$file, $pal, $dir )
{
	//save_file("$dir/dec.bin", $file);
	$cc = strlen($pal) >> 2;

/*
	$img = 'CLUT';
	$img .= chrint($cc,4);
	$img .= chrint(16,4);
	$img .= chrint(16,4);
	$img .= $pal;
	$img .= $file;
	save_file("$dir/img.clut", $img);
	$i = 0;
	while ( $i < 3 )
	{
		$i++;
		$dec = galpani_dectype($pix, $i);
		save_file("$dir/$i.dec", $dec);

		$b1 = substr($dec, 0x08, 8);
		$b2 = substr($dec, 0x18, 8);
		if ( $b1 === $b2 )
			return sectsprt($dec, $pal, $dir);
	} // while ( $i < 3 )

	$img = array(
		'cc'  => $cc,
		'w'   => 16,
		'h'   => 16,
		'pal' => $pal,
		'pix' => $file,
	);
	save_clutfile("$dir/img.clut", $img);
*/
	return;
}

function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'sprt' )
		return;

	$dir = str_replace('.', '_', $fname);

	// palette
	$cc = str2int($file, 0x14, 4);
	$b1 = str2int($file, 0x18, 4);
	$b2 = str2int($file, 0x1c, 4);

	$pal = '';
	for ( $i=0; $i < $b2; $i += 3 )
		$pal .= substr($file, $b1+$i, 3) . BYTE;

	// index
	$b1 = str2int($file, 0x20, 4);
	$b2 = str2int($file, 0x24, 4);
	$ind = substr($file, $b1, $b2);

	// compressed image data
	$file .= ZERO . ZERO;
	$b1 = str2int($file, 0x28, 4);
	$b2 = str2int($file, 0x2c, 4);
	$pix = substr($file, $b1, $b2);

	$b1 = str2int($file, 0x10, 4);
	if ( $b1 == $b2 )
	{
		printf("%x == %x\n", $b1, $b2);
		return;
	}
	else
	{
		printf("%x != %x\n", $b1, $b2);
		$dec = galpani_dectype($pix, 3);
		save_file("$dir/pix.dec", $dec);
		save_file("$dir/pix.ind", $ind);
		save_file("$dir/pix.pal", $pal);

		$len = strlen($ind);
		for ( $i=0; $i < $len; $i += 4 )
		{
			$fn  = sprintf('%s/%04d.clut', $dir, $i>>2);
			$off = str2int($ind, $i, 4);
			//printf("%8x , %s\n", $off, $fn);

			$head = substr($dec, $off, 0x40);
			$w = str2int($head, 0x20, 4);
			$h = str2int($head, 0x24, 4);
			$cnt = ord( $head[0x32] );
			echo debug($head, $fn);

			$img = array(
				'cc' => $cc,
				'w'  => $w,
				'h'  => $h*$cnt,
				'pal' => $pal,
				'pix' => substr($dec, $off+0x40, $w*$h*$cnt),
			);
			save_clutfile($fn, $img);
		} // for ( $i=0; $i < $len; $i++ )
		return;
	}

	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );

/*
gp2 mm040bs.dat = 3
gp2 mm040ss.dat = 3
gp2 mm0403s.dat = 3
gp2 il04sspr.dat = 3
gp2 mm04sspr.dat = 3
gp2 myendspr.dat = 2
gp2 myttlspr.dat = 3
gp2 myjstspr.dat = 2

gp2 il01sspr.dat = 3
	 2ed8- 6208  104x [68 x 7e]
	 6248-145c8  104x [68 x 8c*4]
	14608-23cd4  117x [75 x 6c*5]
	23d14-25e80  124x [7c x 45]
 */
