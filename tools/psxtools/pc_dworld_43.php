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
 *   ScummVM
 *   https://github.com/scummvm/scummvm/tree/master/engines/tinsel/graphics.cpp
 */
require 'common.inc';
require 'dworld.inc';

//////////////////////////////
function dwn_pal565( &$pal )
{
	$clr = '';
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$c = str2int($pal, $i, 2);

		// fedcba9876543210
		// rrrrrggggggbbbbb
		$r = ($c >> 8) & 0xf8; // >> 11 << 3
		$g = ($c >> 3) & 0xfc; // >>  5 << 2
		$b = ($c << 3) & 0xf8; // >>  0 << 3
		$clr .= chr($r) . chr($g) . chr($b) . BYTE;
	}
	return $clr;
}

function dwn_pak( &$file, $pos, $w, $h )
{
	$pix = '';
	for ( $y=0; $y < $h; $y++ )
	{
		$x = $w;
		while ( $x > 0 )
		{
			$b0 = str2int($file, $pos, 2);
				$pos += 2;

			if ( $b0 & 0x8000 )
			{
				$c = substr($file, $pos, 2);
					$pos += 2;
				$c = dwn_pal565($c);

				$b0 &= 0x7fff;
				$pix .= str_repeat($c, $b0);
			}
			else
			{
				$c = substr($file, $pos, $b0*2);
					$pos += ($b0*2);
				$pix .= dwn_pal565($c);
			}
			$x -= $b0;
		} // while ( $x > 0 )
	} // for ( $y=0; $y < $h; $y++ )
	return $pix;
}

function dwn_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[0x19]) )  return; //
	if ( ! isset($sect[   6]) )  return; // sprite data -> 19
	echo "== dwn_scn( $dir )\n";

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf('%s/%04d', $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = $b1;
			$h = $b2;

		$b1 = str2int($sub6,  8, 4);
		$b2 = str2int($sub6, 12, 4);
			$st19 = $b1 & 0x01ffffff;
			$rle  = ( $b2 & 1 ); // 0 raw , 1 rle , 40000 raw+locale

		$img = array(
			'w' => $w,
			'h' => $h,
		);
		if ( $rle === 0 )
		{
			$src = substr($file, $st19, $w*$h*2);
			$img['pix'] = dwn_pal565($src);
		}
		else
			$img['pix'] = dwn_pak($file, $st19, $w, $h);

		save_clutfile("$fn.rgba", $img);
	} // while ( $st6 < $ed6 )

	return;
}
//////////////////////////////
function dw2_pak( &$file, $pos, $w, $h, $ref )
{
	$clr = '';
	if ( $ref )
	{
		$cnt = ord( $file[$pos] );
		$clr = substr($file, $pos+1, $cnt);
			$pos += ($cnt + 1);
	}
	else
	{
		for ( $i=0; $i < 0x10; $i++ )
			$clr .= chr($i);
	}

	$pix = '';
	for ( $y=0; $y < $h; $y++ )
	{
		printf("new row [%x] = %x\n", $y, $y*$w);
		$cnt = ord( $file[$pos] );
			$pos++;
		$row = str_repeat(ZERO, $cnt);

		$crlf = false;
		while ( ! $crlf )
		{
			$b0 = ord( $file[$pos+0] );
			$b1 = ord( $file[$pos+1] );

			if ( $b0 === 0 && $b1 === 0 )
			{
				$pos += 2;
				$crlf = true;
				continue;
			}

			$b0a = ($b0 >> 4) & BIT4;
			$b0b = ($b0 >> 0) & BIT4;
			if ( $b0b !== 0 )
			{
				$row .= str_repeat($clr[$b0a], $b0b);
				$pos++;
				continue;
			}

			if ( $b1 < 0x10 )
				$row .= str_repeat(ZERO, $b0+$b1);
			else
				$row .= str_repeat($clr[$b0a], $b1);
			$pos += 2;
		} // while ( ! $crlf )

		while ( strlen($row) < $w )
			$row .= ZERO;

		$pix .= $row;
	} // for ( $y=0; $y < $h; $y++ )
	return $pix;
}

function dw2_rle( &$file, $pos, $w, $h )
{
	$pix = '';
	for ( $y=0; $y < $h; $y++ )
	{
		$x = $w;
		while ( $x > 0 )
		{
			$b0 = ord( $file[$pos+0] );
				$pos++;

			if ( $b0 & 0x80 )
			{
				$b0 &= 0x7f;
				$b1 = $file[$pos];
					$pos++;
				$pix .= str_repeat($b1, $b0);
			}
			else
			{
				$pix .= substr($file, $pos, $b0);
					$pos += $b0;
			}
			$x -= $b0;
		} // while ( $x > 0 )
	} // for ( $y=0; $y < $h; $y++ )
	return $pix;
}

function dw2_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[0x19]) )  return; //
	if ( ! isset($sect[   5]) )  return; // palette
	if ( ! isset($sect[   6]) )  return; // sprite data -> 5
	echo "== dw2_scn( $dir )\n";

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf('%s/%04d', $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = $b1 & 0x3fff;
			$h = $b2 & 0x3fff;
			$pak = $b2 >> 14;

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st19 = $b1 & 0x3fffff;
			$st5  = $b2 & 0x3fffff;

		$fn = sprintf('%s/%d_%d/%04d.clut', $dir, ($st5 !== 0), $pak, $id6-1);
		$img = array('w' => $w , 'h' => $h);

		if ( $st5 !== 0 )
		{
			$img['cc' ] = 0x100;
			$img['pal'] = substr($file, $st5, 0x400);
			palbyte( $img['pal'] );
		}
		else
		{
			$img['cc' ] = 0x100;
			$img['pal'] = dw2_syspal($file, $sect[5], $sect[6]);
		}

		switch ( $pak )
		{
			case 0: // 00
				if ( $st5 !== 0 )
					$img['pix'] = substr($file, $st19, $w*$h);
				else
					$img['pix'] = dw2_rle($file, $st19, $w, $h);
				break;

			case 1: // 40 , rincewind
			case 2: // 80 , luggage
				$img['cc' ] = 0x10;
				$img['pal'] = grayclut(0x10);
				$img['pix'] = dw2_pak($file, $st19, $w, $h, false);
				break;

			case 3: // c0 , map shared - defer
				$img['pix'] = dw2_pak($file, $st19, $w, $h, true);
				break;

			default:
				return php_error('UNKNOWN pak %x', $pak);
		} // switch ( $pak )

		save_clutfile($fn, $img);
	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function dw1_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[3]) )  return; // tile data   -> 4
	if ( ! isset($sect[4]) )  return; // 4x4 pixel data
	if ( ! isset($sect[5]) )  return; // palette
	if ( ! isset($sect[6]) )  return; // sprite data -> 3,5
	echo "== dw1_scn( $dir )\n";

	// tile = 4 x 4 = 0x10
	$st48 = str2int($file, $sect[3]+0, 4);
	$b1   = str2int($file, $sect[3]+4, 4);
	$st44 = $st48 + $b1 * 16;

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf('%s/%04d', $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = int_ceil($b1, 4);
			$h = int_ceil($b2, 4);

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st3 = $b1 & 0xfffff;
			$st5 = $b2 & 0xfffff;

		$pal = substr($file, $st5, 0x400);
		palbyte($pal);

		$pix = copypix_def($w,$h);
		$pix['src']['w'] = 4;
		$pix['src']['h'] = 4;
		$pix['src']['pal'] = $pal;

		for ( $y=0; $y < $h; $y += 4 )
		{
			for ( $x=0; $x < $w; $x += 4 )
			{
				$b1 = str2int($file, $st3, 2);
					$st3 += 2;

				$bpp4 = $b1 & 0x8000;
				$tid  = $b1 & 0x7fff;
				if ( $bpp4 )
					$src = substr($file, $st44+$tid*16, 16);
				else
					$src = substr($file, $st48+$tid*16, 16);

				$pix['dx'] = $x;
				$pix['dy'] = $y;
				$pix['src']['pix'] = $src;

				copypix_fast($pix);
			} // for ( $x=0; $x < $w; $x += 4 )
		} // for ( $y=0; $y < $h; $y += 4 )

		savepix($fn, $pix, false);
	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function dworld( $tag, $fname )
{
	if ( empty($tag) )
		return php_error('NO TAG');

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir  = str_replace('.', '_', $fname);
	$sect = scnsect($file);

	// http://rewiki.regengedanken.de/wiki/.SCN
	//   DW1 3 4 5 - c d -  -  -  -  -  -  -  -  -  -  -
	//   DW2 - - 5 - c d f 12 13  - 19 1b 1c 1d 1e  -  -
	//   DWN - - - 9 - - f  -  - 18 19 1b 1c 1d  - 20 31
	// 3 = tile map   data
	// 4 = tile pixel data
	// 5 = palette data
	//save_txt($file, $sect, "{$dir}_txt");

	switch ( $tag )
	{
		case 'dw1':
			return dw1_scn($file, $sect, "{$dir}_gfx");
		case 'dw2':
			return dw2_scn($file, $sect, "{$dir}_gfx");
		case 'dwn':
			return dwn_scn($file, $sect, "{$dir}_gfx");
	}
	return;
}

printf("%s  [-dw1/-dw2/-dwn]  FILE\n", $argv[0]);
$tag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-dw1':  $tag = 'dw1'; break;
		case '-dw2':  $tag = 'dw2'; break;
		case '-dw3':
		case '-dwn':  $tag = 'dwn'; break;
		default:
			dworld( $tag, $argv[$i] );
			break;
	} // switch ( $argv[$i] )
}

/*
dw2/dw2.scn/3 = 3 x 3  4e6
	= 09 -- 09
	= -- d4 --
	= 09 -- 09
	02 09 d4
		--  01  -- 01  01 [-- --]
		01  11 [-- --]
		--  01  -- 01  01 [-- --]

dw2/dw2.scn/4 = 3 x 3  4d5
	= -- 09 --
	= 09 d4 09
	= -- 09 --
	02 09 d4
		01  01 [-- --]
		--  01  11  01 [-- --]
		01  01 [-- --]

dw2/dw2.scn/5 = 1 x 1  4fb
	= 09
	01 09
		-- 01 [-- --]

dw2/dw2.scn/6 = 5 x 5  47e
	= -- -- 07 -- --
	= -- -- d3 -- --
	= 07 d3 0b d3 07
	= -- -- d3 -- --
	= -- -- 07 -- --
	03 07 d3 0b
		02  01 [-- --]
		02  11 [-- --]
		--  01  11  21  11  01 [-- --]
		02  11 [-- --]
		02  01 [-- --]


dwn/unseobse.scn/296 = 1 x 1  1468041-1468045
	01 -- c1 79

dwn/unseobse.scn/126 = 1 x 4  89008f-89009f
	01 -- 20 --
	01 -- -- --
	01 -- -- --
	01 -- -- --

dwn/title.scn/62 = 2 x 3  11aade5-b1aadf5
	02 -- 21 -- 1f f8
	02 80 1f f8
	02 -- 1f f8 21 --

dwn/unseplea.scn/0 = 2 x 4  6043d8-6043ea
	02 -- -- -- 1f f8
	02 80 -- --
	02 80 -- --
	02 80 -- --
 */
