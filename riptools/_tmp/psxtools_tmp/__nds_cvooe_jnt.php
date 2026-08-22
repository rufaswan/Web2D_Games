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
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */
require 'common.inc';

function drawline( &$img, $clr, $x1, $y1, $x2, $y2 )
{
	$hw = $img['w'] >> 1;
	$hh = $img['h'] >> 1;
	$clr = chr($clr);

	// draw the longest line
	if ( abs($x1-$x2) > abs($y1-$y2) )
	{
		// rearrange from left-to-right
		if ( $x1 > $x2 )
		{
			var_swap($x1, $x2);
			var_swap($y1, $y2);
		}

		$x2 -= $x1;
		$y2 -= $y1;
		$slope = ( abs($x2) > 0 ) ? $y2 / $x2 : 0;
		for ( $x=0; $x <= $x2; $x++ )
		{
			$y = $x * $slope;
			$dy = (int)($hh + $y1 + $y);
			$dx = (int)($hw + $x1 + $x);

			$dxx = ($dy * $img['w']) + $dx;
			$img['pix'][$dxx] = $clr;
		} // for ( $x=0; $x <= $x2; $x++ )
	}
	else
	{
		// rearrange from top-to-bottom
		if ( $y1 > $y2 )
		{
			var_swap($x1, $x2);
			var_swap($y1, $y2);
		}

		$x2 -= $x1;
		$y2 -= $y1;
		$slope = ( abs($y2) > 0 ) ? $x2 / $y2 : 0;
		for ( $y=0; $y <= $y2; $y++ )
		{
			$x = $y * $slope;
			$dy = (int)($hh + $y1 + $y);
			$dx = (int)($hw + $x1 + $x);

			$dxx = ($dy * $img['w']) + $dx;
			$img['pix'][$dxx] = $clr;
		} // for ( $y=0; $y <= $y2; $y++ )
	}
	return;
}
//////////////////////////////
function jntfile( &$file )
{
	$jnt = [];

	// 0x20
	// 0x21
	$jnt['dx'] = str2int($file, 0x22, 2, true);
	$jnt['dy'] = str2int($file, 0x24, 2, true);
	$jnt['cjnt'] = ord( $file[0x26] );
	$jnt['cjnt_inv'] = ord( $file[0x27] );
	$jnt['cjnt_vis'] = ord( $file[0x28] );
	$jnt['chit'] = ord( $file[0x29] );
	$jnt['cpss'] = ord( $file[0x2a] );
	$jnt['cpnt'] = ord( $file[0x2b] );
	$jnt['canm'] = ord( $file[0x2c] );
	// 0x2d
	// 0x2e
	// 0x2f
	$pos = 0x30;

	$sz = $jnt['cjnt'] * 4;
	$jnt['joint'] = substr($file, $pos, $sz);
		$pos += $sz;

	$sz = $jnt['cpss'] * (2 + ($jnt['cjnt'] * 4));
	$jnt['pose'] = substr($file, $pos, $sz);
		$pos += $sz;

	$sz = $jnt['chit'] * 8;
	$jnt['hitbox'] = substr($file, $pos, $sz);
		$pos += $sz;

	$sz = $jnt['cpnt'] * 4;
	$jnt['point'] = substr($file, $pos, $sz);
		$pos += $sz;

	$sz = $jnt['cjnt_vis'];
	$jnt['draw'] = substr($file, $pos, $sz);
		$pos += $sz;

	$jnt['anim'] = substr($file, $pos);

	$file = $jnt;
	return;
}

function cvooe( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 2) !== "\x01\x23" )
		return;
	$opd = substr0($file, 3);
	if ( strpos($opd, '.opd') === false )
		return;

	$dir = str_replace('.', '_', $fname);
	jntfile($file);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvooe( $argv[$i] );
