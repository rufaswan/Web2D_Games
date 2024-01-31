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

function cmp0_decode( &$file )
{
	$dec = '';
	// SLPS 028.20 , Kouryaku Shireisho , sub_80016fd0
	trace("== begin sub_80016fd0()\n");

	$ed = strlen($file);
	$st = 0;
	$pad = true;
	while ( $st < $ed )
	{
		$b1 = ord( $file[$st] );
			$st++;

		if ( $b1 > 0 )
		{
			if ( $pad )
				$dec .= str_repeat(ZERO, $b1);
			else
			{
				$len = ord( $file[$st] );
					$st++;
				$dec .= substr($file, $st, $len);
					$st += $len;
			}
		}

		$pad = ! $pad;
	} // while ( $st < $ed )

	trace("== end sub_80016fd0()\n");
	return;
}
//////////////////////////////
//////////////////////////////
function s_TX_MP16( &$TX, &$MP16, $dir )
{
	return;
}

function s_TX_PL_MP16( &$TX, &$PL, &$MP16, $dir )
{
	$w   = str2int($TX,  8, 2);
	$h   = str2int($TX, 10, 2);
	$src = substr ($TX, 12);
	$pal = substr ($PL, 12);
		$pal = pal555($pal);
	if ( strlen($src) != ($w*$h) )
		bpp4to8($src);

	$mapw = str2int($MP16,  8, 2) * 16;
	$maph = str2int($MP16, 10, 2) * 16;
	$pos  = 12;

	$tw = $w / 16;
	$th = $h / 16;

	$pix = copypix_def($mapw,$maph);
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;
	$pix['src']['pal'] = $pal;

	for ( $y=0; $y < $maph; $y += 16 )
	{
		for ( $x=0; $x < $mapw; $x += 16 )
		{
			$ind = str2int($MP16, $pos, 2);
				$pos += 2;

			$sy = (int)($ind / $tw);
			$sx = (int)($ind % $tw);
			$pix['src']['pix'] = rippix8($src, $sx*16, $sy*16, 16, 16, $w, $h);
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix_fast($pix);
		} // for ( $y=0; $y < $maph; $y += 16 )
	} // for ( $y=0; $y < $maph; $y += 16 )

	savepix($dir, $pix);
	return;
}
//////////////////////////////
function TX_PL_MP16( &$sect, $dir )
{
	s_TX_PL_MP16($sect[0][1], $sect[1][1], $sect[2][1], "$dir/0000");
	return;
}

function TX_TX_PL_PL_MP16_MP16( &$sect, $dir )
{
	s_TX_PL_MP16($sect[0][1], $sect[2][1], $sect[4][1], "$dir/0000");
	s_TX_PL_MP16($sect[1][1], $sect[3][1], $sect[5][1], "$dir/0001");
	return;
}

function MP16_TX_PL( &$sect, $dir )
{
	s_TX_PL_MP16($sect[1][1], $sect[2][1], $sect[0][1], "$dir/0000");
	return;
}

function TX_TX_TX_TX_TX_PL( &$sect, $dir )
{
	$pal = substr($sect[5][1], 12);
		$pal = pal555($pal);
	$cc = strlen($pal) >> 2;
	for ( $i=0; $i < 5; $i++ )
	{
		$w   = str2int($sect[$i][1],  8, 2);
		$h   = str2int($sect[$i][1], 10, 2);
		$src = substr ($sect[$i][1], 12);
		if ( strlen($src) != ($w*$h) )
			bpp4to8($src);

		$img = array(
			'cc'  => $cc,
			'w'   => $w,
			'h'   => $h,
			'pal' => $pal,
			'pix' => $src,
		);
		$fn = sprintf('%s/%04d.clut', $dir, $i);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < 5; $i++ )
	return;
}

function mrgfile( &$file, $dir )
{
	printf("== mrgfile( $dir )\n");
	//$len = str2int($file, 4, 4);
	$cnt = str2int($file, 8, 4);

	$sect = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 12 + ($i * 4);
		$off = str2int($file, $p, 4);

		$mgc = substr ($file, $off  , 4);
		$sz1 = str2int($file, $off-4, 4);
		$sz2 = str2int($file, $off+4, 4);
			$mgc = rtrim($mgc, ZERO);

		if ( $mgc === 'PTN' )
			$sz2 = str2int($file, $off+8, 4);

		printf("%8x , %8x , %s\n", $off, $sz1, $mgc);
		if ( $sz1 !== $sz2 )
			continue;

		$sub = substr($file, $off, $sz1);
		$sect[] = array($mgc,$sub);
	} // for ( $i=0; $i < $cnt; $i++ )

	$file = array();
	foreach ( $sect as $v )
		$file[] = $v[0];

	$func = implode('_', $file);
	echo "CALL $func( $dir )\n";
	if ( function_exists($func) )
		$func($sect, $dir);
	return;
}

function gihren( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	if ( substr($file, 0, 4) === "MRG\x00" )
		return mrgfile($file, $dir);
	return;
}

argv_loopfile($argv, 'gihren');
