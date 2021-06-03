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

function save_disc1_scn( &$clut, $dir, $id )
{
	if ( trim($clut['pix']) === '' )
		return;
	printf("== save_disc1_scn( %s , %x )\n", $dir, $id);
	//var_dump($clut);

	// rearrange pix data
	$buf = $clut['pix'];
	$clut['pix'] = '';

	$pos = 0;
	$len = strlen($buf);
	while ( $pos < $len )
	{
		$row = array('','','','');
		for ( $x=0; $x < $clut['w']; $x += 4 )
		{
			$row[0] .= substr($buf, $pos+ 0, 4);
			$row[1] .= substr($buf, $pos+ 4, 4);
			$row[2] .= substr($buf, $pos+ 8, 4);
			$row[3] .= substr($buf, $pos+12, 4);
				$pos += 16;
		} // for ( $x=0; $x < $clut['w']; $x += 4 )

		$clut['pix'] .= implode('', $row);
	} // while ( $pos < $len )

	$fn = sprintf("%s/out/%04d.clut", $dir, $id);
	save_clutfile($fn, $clut);
	return;
}

function disc1_scn( &$sect, $dir )
{
	if ( ! isset($sect[3][0]) )  return; // meta
	if ( ! isset($sect[4][0]) )  return; // pixel
	if ( ! isset($sect[5][0]) )  return; // clut
	if ( ! isset($sect[6][0]) )  return; // size

	// from SCUS_946.00 , sub_8001588c
	$pos3 = 8;
	$len3 = strlen($sect[3][0]);

	$buf = '';
	$clut = array('pix' => '');
	$id = -1;
	$w = 0;
	$h = 0;
	while ( $pos3 < $len3 )
	{
		$b1 = str2int($sect[3][0], $pos3+0, 2);
		$b2 = str2int($sect[3][0], $pos3+2, 2);
		printf("%6x , %4x %4x\n", $pos3, $b1, $b2);

		// fedcba98 76543210
		// ffpppppp pppppppp
		if ( $b1 & 0x8000 )
			$flg = 1;
		else
		if ( $b1 & 0x4000 )
			$flg = 2;
		else
			$flg = 0;

		$b1 &= 0x3fff;

/*
		switch ( $b1 >> 12 )
		{
			case 12:
				// save and clear
				save_disc1_scn($clut, $dir, $id);

				$id++;
				$w = str2int($sect[6][0], $id*0x10+0, 2);
				$h = str2int($sect[6][0], $id*0x10+2, 2);
				$clut = array(
					'w'  => $w,
					'h'  => $h,
					'cc' => 0x100,
					'pal' => substr($sect[5][0], 0, 0x400),
					'pix' => '',
				);
				$pos3 += 2;
				break;
			case 8:
				$b1 = $b1 & 0xfff;
				$buf = substr($sect[4][0], $b2*0x10, 0x10);
				$clut['pix'] .= str_repeat($buf, $b1);
				$pos3 += 4;
				break;
			case 4:
				$b1 = $b1 & 0xfff;
				$clut['pix'] .= substr($sect[4][0], $b2*0x10, $b1*0x10);
				$pos3 += 4;
				break;
			case 0:
				$b1 = $b1 & BIT8;
				for ( $i=0; $i < $b1; $i++ )
				{
					$p = $pos3 + 2 + ($i * 2);
					$b2 = str2int($sect[3][0], $p, 2);
					$clut['pix'] .= substr($sect[4][0], $b2*0x10, 0x10);
				}
				$pos3 += (2 + $i*2);
				break;
			default:
				$pos3 += 2;
				break;
		} // switch ( $b1>>12 )
*/

	} // while ( $pos3 < $len3 )

	save_disc1_scn($clut, $dir, $id);
	return;
}

function save_sectscn( &$sect, $dir )
{
	foreach ( $sect as $tk => $tv )
	{
		if ( count($tv) > 1 )
		{
			foreach ( $tv as $sk => $sv )
			{
				$fn = sprintf("%s/%s/%04d.bin", $dir, $tk, $sk);
				save_file($fn, $sv);
			} // foreach ( $tv as $sk => $sv )
		}
		else
		{
			$fn = sprintf("%s/dw.%s", $dir, $tk);
			save_file($fn, $tv[0]);
		}
	} // foreach ( $sect as $tk => $tv )
	return;
}
//////////////////////////////
function add_sectscn( &$sect, &$sub, $type )
{
	$len = strlen($sub);
	if ( ! isset( $sect[$type] ) )
		$sect[$type] = array();

	$pos = 0;
	switch ( $type )
	{
		case 1: // subtitle script
			while ( $pos < $len )
			{
				$b = ord( $sub[$pos] );
				$sub[$pos] = "\n";
				$pos += ($b + 1);
			}
			$sect[$type][] = $sub;
			return;

		case 3:
			$b1 = str2int($sub, $pos+0, 4); // case 4 data pointer
			$b2 = str2int($sub, $pos+4, 4); // case 4 data size (in 10s)

			$sect[$type][] = $sub;
			return;

		case 5: // palette
			while ( $pos < $len )
			{
				$sub[$pos+3] = BYTE;
				$pos += 4;
			}
			$sect[$type][] = $sub;
			return;

		default:
			$sect[$type][] = $sub;
			return;

/*
		case 4: // pixel
			$gp_pix['w'] = 0x140;
			$gp_pix['h'] = 0xc8;
			$gp_pix['pix'] = '';
			$pos = 0;
			for ( $y=0; $y < $gp_pix['h']; $y += 4 )
			{
				$row = array('','','','');
				for ( $x=0; $x < $gp_pix['w']; $x += 4 )
				{
					$row[0] .= substr($sub, $pos+ 0, 4);
					$row[1] .= substr($sub, $pos+ 4, 4);
					$row[2] .= substr($sub, $pos+ 8, 4);
					$row[3] .= substr($sub, $pos+12, 4);
						$pos += 16;
				} // for ( $x=0; $x < $gp_pix['w']; $x += 4 )

				$gp_pix['pix'] .= implode('', $row);
			} // for ( $y=0; $y < $gp_pix['h']; $y += 4 )

			save_clutfile("$fn.clut", $gp_pix);

   title_scn = f9c0 = 140*c8 8-bpp
psygnosi_scn = 55b8 =  9a*bb 4-bpp
			break;

		case 6: // meta
			$gp_pix['size'] = array();
			for ( $i=0; $i < $len; $i += 0x10 )
			{
				$w =
				$gp_clut .= $sub[$i+0];
				$gp_clut .= $sub[$i+1];
				$gp_clut .= $sub[$i+2];
				$gp_clut .= BYTE;
				$gp_pix['cc']++;
			}
			break;
*/

	} // switch ( $type )

	return;
}

function disc1( $fname )
{
	// for *.scn and english.txt
	//if ( stripos($fname, '.scn') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$ed = strlen($file);
	$st = 0;
	$sect = array();
	echo "== $fname\n";
	while ( $st < $ed )
	{
		$typ = str2int($file, $st+0, 4);
		$nxt = str2int($file, $st+4, 4);
		if ( substr($file, $st+2, 2) !== '43' )
			return php_error("43 not found = %x", $st);

		if ( $nxt === 0 )
			$nxt = $ed;
		$siz = $nxt - $st;
		printf("%8x , %8x , %8x\n", $typ, $st, $siz);

		$sub = substr($file, $st+8, $siz-8);
		add_sectscn($sect, $sub, $typ & BIT16);
		$st = $nxt;
	} // while ( $st < $ed )

	save_sectscn($sect, $dir);
	disc1_scn($sect, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc1( $argv[$i] );

/*
title.scn.4
	-       +0    +1    +2    +3
	0 +4    10    14    18    1c +500/4 = 140
	4 +4   510   514   518   51c
	8 +4   a10   a14   a18   a1c
	c +4   f10   f14   f18   f1c
	10    1400  1404  1408  140c
	14    1900  1904  1908  190c +4f0/4 = 13c
	18    1df0  1df4  1df8  1dfc
	1c    22e0  22e4  22e8  22ec
	20    27d0  27d4  27d8  27dc
	24    2cc0  2cc4  2cc8  2ccc +500/4 = 140
	28    31c0  31c4  31c8  31cc
psyhnosi.scn.4
	-        +0    +1    +2    +3
	0 +34    10    14    18    1c + d0/4 = 34
	4 +28    e0    e4    e8    ec +120/4 = 48
	8 +20   200   204   208   20c +160/4 = 58
	c +1c   360   364   368   36c +180/4 = 60
	10+18   4e0   4e4   4e8   4ec +1b0/4 = 6c
	14+14   690   694   698   69c +1d0/4 = 74
	18+10   860   864   868   86c +1f0/4 = 7c
	1c+c    a50   a54   a58   a5c +200/4 = 80
	20+8    c50   c54   c58   c5c +220/4 = 88
	24+8    e70   e74   e78   e7c +230/4 = 8c
	28+4   10a0  10a4  10a8  10ac +240/4 = 90
	2c+4   12e0  12e4  12e8  12ec +250/4 = 94
	30+4   1530  1534  1538  153c

title.scn => 8015b72c
	-> 80016d44
 */
