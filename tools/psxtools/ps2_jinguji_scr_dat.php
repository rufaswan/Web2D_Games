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

function sect_tim2( &$file, $pfx )
{
	$pixsiz = str2int($file, 0x18, 4);
	$w = str2int($file, 0x24, 2);
	$h = str2int($file, 0x26, 2);

	$siz = int_ceil($w*$h, 0x10);
	if ( $siz === $pixsiz ) // 8-bpp
	{
		$pal = substr($file, 0x40 + $pixsiz, 0x200);
		$img = array(
			'cc'  => 0x100,
			'w'   => $w,
			'h'   => $h,
			'pal' => pal555($pal),
			'pix' => substr($file, 0x40, $pixsiz),
		);
		return save_clutfile("$pfx.clut", $img);
	}

	$siz = int_ceil($w*0.5*$h, 0x10);
	if ( $siz === $pixsiz ) // 4-bpp
	{
		$pix = substr($file, 0x40, $pixsiz);
		$pal = substr($file, 0x40 + $pixsiz, 0x20);
			bpp4to8($pix);

		$img = array(
			'cc'  => 0x10,
			'w'   => $w,
			'h'   => $h,
			'pal' => pal555($pal),
			'pix' => $pix,
		);
		return save_clutfile("$pfx.clut", $img);
	}

	$siz = int_ceil($w*2*$h, 0x10);
	if ( $siz === $pixsiz ) // rgb555
	{
		$pix = substr($file, 0x40, $pixsiz);
		$img = array(
			'w'   => $w,
			'h'   => $h,
			'pix' => pal555($pix),
		);
		return save_clutfile("$pfx.rgba", $img);
	}

	save_file("$pfx.tim2", $file);
	return;
}

function sect_unpack( &$file, $pfx )
{
	$pos = 0;
	$len = 0;
	while (1)
	{
		$len = str2int($file, $pos, 4);
		if (  $len !== 0 )
			break;
		$pos += 0x10;
	}

	while ( $pos < $len )
	{
		$off = str2int($file, $pos + 0, 4);
		$siz = str2int($file, $pos + 4, 4);
			$pos += 0x10;
		if ( $off === 0 || $siz === 0 )
			continue;

		$sub = substr($file, $off, $siz);
		$fn  = sprintf('%s-%04d', $pfx, $pos >> 4);
		printf("%8x  %8x  %8x  %s\n", $pos-0x10, $off, $siz, $fn);

		if ( substr($sub,0,4) === 'TIM2' )
			sect_tim2($sub, $fn);
		else
			save_file("$fn.bin", $sub);
	} // while ( $pos < $len )
	return;
}

function jinguji_decode( &$file, $size )
{
	$dec = '';
	trace("== begin sub_17e948()\n");

	$dict = str_repeat(ZERO, 0x1000);
	$doff = 0xfee;

	$bycod = 0;
	$bylen = 0;

	$len = strlen($file);
	$pos = 0;
	while ( $pos < $len )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$pos] );
				$pos++;
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = $file[$pos];
				$pos++;

			$dict[$doff] = $b1;
				$doff = ($doff + 1) & 0xfff;
			$dec .= $b1;
		}
		else
		{
			$b1 = ord( $file[$pos+0] );
			$b2 = ord( $file[$pos+1] );
				$pos += 2;

			$dpos = (($b2 & 0xf0) << 4) | $b1;
			$dlen =  ($b2 & 0x0f) + 2;

			for ( $i=0; $i <= $dlen; $i++ )
			{
				$b1 = $dict[$dpos];
					$dpos = ($dpos + 1) & 0xfff;
				$dict[$doff] = $b1;
					$doff = ($doff + 1) & 0xfff;
				$dec .= $b1;
			} // for ( $i=0; $i <= $dlen; $i++ )
		}
	} // while ( $pos < $len )

	trace("== end sub_17e948()\n");
	$file = $dec;
	return;
}

function jinguji( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$pos = 0;
	$id  = 0;
	while ( $pos < $len )
	{
		$sz1 = str2int($file, $pos + 0, 4); // decompressed size
		$sz2 = str2int($file, $pos + 4, 4); // compressed size
		$sub = substr ($file, $pos + 8, $sz2);

		$pfx = sprintf('%s/%04d', $dir, $id);
		printf("%8x  %8x  %8x  %s\n", $pos, $sz2, $sz1, $pfx);

		jinguji_decode($sub, $sz1);
		//save_file("$pfx.dec", $sub);

		sect_unpack($sub, $pfx);

		$id++;
		$pos = int_ceil($pos + 8 + $sz2, 0x800);
	} // while ( $pos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jinguji( $argv[$i] );
