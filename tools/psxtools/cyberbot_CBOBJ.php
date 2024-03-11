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

function cyberbot_vim( &$off, $dir )
{
	if ( isset($off[10]) )
	{
		$len = strlen($off[10]);
		$vim = array(
			'cc'  => 0x10,
			'w'   => 0x100,
			'h'   => $len >> 7, // div 80
			'pal' => grayclut(0x10),
			'pix' => $off[10],
		);
		bpp4to8($vim['pix']);
		save_clutfile("$dir/vimpix.clut", $vim);
	}

	if ( isset($off[11]) )
	{
		$len = strlen($off[11]);
		$vim = array(
			'w'   => 0x10,
			'h'   => $len >> 5, // div 20
			'pix' => pal555($off[11]),
		);
		save_clutfile("$dir/vimpal.rgba", $vim);
	}
	return;
}

function cyberbot_obj( &$off, $dir )
{
	for ( $i0 = 0; $i0 < 0x100; $i0 += 2 )
	{
		$pos0 = str2int($off[0], $i0  , 2) << 1;
		$cnt0 = str2int($off[0], $pos0, 2);
			$pos0 += 2;

		printf("0 %2x = %x\n", $i0>>1, $cnt0);
		for ( $i1=0; $i1 < $cnt0; $i1++ )
		{
			$id1 = str2int($off[0], $pos0, 2, true);
				$pos0 += 2;
			if ( $id1 < 0 )
				printf("  1 %2x = -1\n", $i1);
			else
			{
				$sub1 = substr($off[1], $id1*8, 8);
				//printf("  1 %2x = %s\n", $i1, printhex($sub1));

				$lw1 = str2int($sub1, 0, 4);
					$b1 = ($lw1 >>  0) & 0x3ff;
					$b2 = ($lw1 >> 10) & 0x1fff;
					$b3 = ($lw1 >> 23) & 0x7f;
					$b4 = ($lw1 >> 30) & 3;
				$lw2 = str2int($sub1, 4, 4);
				printf("  1 %2x = %3x %4x %2x %1x %8x\n", $i1, $b1, $b2, $b3, $b4, $lw2);
			}
		} // for ( $i1=0; $i1 < $cnt0; $i1++ )
	} // for ( $i0 = 0; $i0 < 0x100; $i0 += 2 )
	return;
}
//////////////////////////////
function cyberbot_decode( &$file, $pos, $type )
{
	$dec = '';
	trace("begin sub_80038018()\n");

	trace("end sub_80038018()\n");
	return $dec;
}

function cyberbot_gfx( &$off, $dir )
{
	$len2 = strlen($off[2]);
	$cnt3 = strlen($off[3]) >> 3; // div 8
	if ( ($len2|$cnt3) === 0 )
		return;
	for ( $i2=0; $i2 < $len2; $i2 += 2 )
	{
		$id3a = str2int($off[2], $i2, 2);
		if ( ($i2+2) >= $len2 )
			$id3b = $cnt3;
		else
		{
			$id3b = str2int($off[2], $i2 + 2, 2);
			if ( $id3b === 0x5555 )
				$id3b = $cnt3;
		}

		$no3 = $id3b - $id3a;
		for ( $i3=0; $i3 < $no3; $i3++ )
		{
			$sub3 = substr($off[3], ($id3a + $i3)*8, 8);
			printf("2 %4x %4x = %s\n", $i2>>1, $i3, printhex($sub3));
		} // for ( $i3 = $id3a; $i3 < $id3b; $i3++ )
	} // for ( $i2=0; $i2 < $len2; $i2 += 2 )
	return;
}
//////////////////////////////
function cyberbot( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0,15) !== 'CB-OBJ Format 1' )
		return;

	// 10  0
	// 14  1
	// 18  2
	// 1c  3
	// 20  4  gfx data
	// 24  5  compress data
	// 28  6  vim pix pos  , 4-bpp
	// 2c  7  vim pix size
	// 30  8  vim pal pos  , 4-bpp
	// 34  9  vim pal size
	$dir = str_replace('.', '_', $fname);
	$off = array();
	for ( $i=0x10; $i < 0x28; $i += 4 )
	{
		$p1 = str2int($file, $i, 3);
		if ( $p1 === 0 )
			continue;
		$p2 = str2int($file, $i + 4, 3);
		$sz = $p2 - $p1;
		if ( $sz < 1 )
			continue;
		$id = ($i - 0x10) >> 2;
		printf("meta %x  %6x  %6x\n", $i, $p1, $sz);

		$s = substr($file, $p1, $sz);
		$off[$id] = $s;
		save_file("$dir/meta.$id", $s);
	} // for ( $i = 0x10; $i < 0x28; $i += 4 )

	for ( $i = 0x28; $i < 0x38; $i += 0x10 )
	{
		$ps = str2int($file, $i+0, 3);
		$sz = str2int($file, $i+4, 3);
		if ( $sz < 1 )
			continue;
		$id = 10 + (($i - 0x28) >> 4);

		$s = substr($file, $ps, $sz);
		$off[$id] = $s;
		save_file("$dir/meta.$id", $s);
	} // for ( $i = 0x28; $i < 0x38; $i += 0x10 )

	cyberbot_vim($off, $dir);
	cyberbot_obj($off, $dir);
	cyberbot_gfx($off, $dir);
	return;
}

argv_loopfile($argv, 'cyberbot');
