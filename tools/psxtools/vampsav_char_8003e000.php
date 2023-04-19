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
require 'common-guest.inc';

function gfx_decode( &$gfx, $gid )
{
	$pos = str2int($gfx, $gid << 2, 3);
	$flg = str2int($gfx, $pos, 1);
		$fb1 = ($flg & 0xf) << 1;
		$fb2 = ($flg >> 4) & 7;

	if ( $flg & 0x80 )
	{
		$size = str2big($gfx, $pos+1, 3);
		$pos += 4;
	}
	else
	{
		$size = str2big($gfx, $pos+4, 4);
		$pos += 8;
	}
	printf("gfx  %x  %x  %x\n", $pos, $flg, $size);

	$dec = str_repeat(ZERO, 0x100);
	$bycod = 0;
	$bylen = 0;
	if ( $fb2 === 0 )
	{
		// sub_8019fb50
		$ddat = array(12,3 , 11,3 , 10,3 , 9,3);
		$dpbit = $ddat[$fb1+0];
		$dladd = $ddat[$fb1+1];
		$dpmsk = (1 << $dpbit) - 1;

		while ( $size > 0 )
		{
			if ( $bylen === 0 )
			{
				$bycod = ord( $gfx[$pos] );
					$pos++;
				$bylen = 8;
			}

			$flg = $bycod & 0x80;
				$bycod <<= 1;
				$bylen--;
			if ( $flg )
			{
				$b = $gfx[$pos];
					$pos++;
				$dec .= $b;
				$size--;
			}
			else
			{
				$b1 = ord($gfx[$pos+0]);
				$b2 = ord($gfx[$pos+1]);
					$pos += 2;
				$b = ($b1 << 8) | $b2;

				$dlen = ($b >> $dpbit) + $dladd;
				$dpos = $b & $dpmsk;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
					$size--;
				}
			}
		} // while ( $size > 0 )
		bpp4to8($dec);
		return substr($dec, 0x200);
	}
	if ( $fb2 === 1 )
	{
		// sub_8019fc34
		$ddat = array(6,3 , 5,3);
		$dpbit = $ddat[$fb1+0];
		$dladd = $ddat[$fb1+1];
		$dpmsk = (1 << $dpbit) - 1;
		while ( $size > 0 )
		{
			if ( $bylen === 0 )
			{
				$bycod = ord( $gfx[$pos] );
					$pos++;
				$bylen = 8;
			}

			$flg = $bycod & 0x80;
				$bycod <<= 1;
				$bylen--;
			if ( $flg )
			{
				$b = $gfx[$pos];
					$pos++;
				$dec .= $b;
				$size--;
			}
			else
			{
				$b = ord($gfx[$pos]);
					$pos++;

				$dlen = ($b >> $dpbit) + $dladd;
				$dpos = $b & $dpmsk;
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
					$size--;
				}
			}
		} // while ( $size > 0 )
		bpp4to8($dec);
		return substr($dec, 0x200);
	}
	return $dec;
}

function dxdy_tdat( &$pix, &$tdat, $b4, $cen )
{
	$pix['dx'] = 0;
	$pix['dy'] = 0;
	$pix['hflip'] = 0;
	$pix['vflip'] = 0;
	$pos = 0;
	while (1)
	{
		if ( ! isset($tdat[$pos+5]) )
			return;

		// same texture position on VRAM
		//   spr put textures at fixed position , then
		//   tex use these position to draw onscreen
		$flag = str2int($tdat, $pos+0, 2);
		$tpage = substr($tdat, $pos+2, 2);

		if ( $b4 === $tpage )
			goto done;
		else
			$pos = ( $flag & 0x4000 ) ? $pos + 6 : $pos + 8;
	}
	return;

done:
	if ( $flag & 0x4000 )
	{
		$dx = sint8( $tdat[$pos+4] );
		$dy = sint8( $tdat[$pos+5] );
	}
	else
	{
		$dx = sint16( $tdat[$pos+4].$tdat[$pos+5] );
		$dy = sint16( $tdat[$pos+6].$tdat[$pos+7] );
	}

	if ( $flag & 0x20 )
	{
		//$pix['hflip'] = 1;
		$dx = -$dx;
	}
	if ( $flag & 0x40 )
	{
		//$pix['vflip'] = 1;
		$dy = -$dy;
	}

	$pix['dx'] = $dx + $cen;
	$pix['dy'] = $dy + $cen;
	return;
}

function sect_sprgfx( &$spr, &$gfx, &$tex, $dir )
{
	$spr_max = str2int($spr, 0, 3);
	for ( $sk=0; $sk < $spr_max; $sk += 4 )
	{
		$spos = str2int($spr, $sk  , 3);
		$scnt = str2int($spr, $spos, 2);
			$spos += 2;
		if ( $scnt === 0 )
			continue;

		$tpos = str2int($tex, $sk  , 3);
		$tcnt = str2int($tex, $tpos, 2);
		$tdat = substr ($tex, $tpos+2, $tcnt*6);

		$pix = copypix_def(0x200, 0x200);
		$pix['src']['cc']  = 0x10;
		$pix['src']['pal'] = grayclut(0x10);
		for ( $si=0; $si < $scnt; $si++ )
		{
			//$b1 = str2int($spr, $spos+0, 1); // texpage position
			$b2 = str2int($spr, $spos+1, 1);
			$b3 = str2int($spr, $spos+2, 2);
			$b4 = substr ($spr, $spos+0, 2);
				$spos += 4;

			dxdy_tdat($pix, $tdat, $b4, 0x100);
			$w  = ($b2 >> 0) & 0xf;
			$h  = ($b2 >> 4) & 0xf;
			//$flg = $b3 >> 14;
			$gid = $b3 & 0x3fff;

			$pix['src']['w'] = $w << 4;
			$pix['src']['h'] = $h << 4;

			$pix['src']['pix'] = gfx_decode($gfx, $gid);
			//save_clutfile("$dir/$gid.clut", $pix['src']);
			copypix_fast($pix, 1);
		} // for ( $si=0; $si < $scnt; $si++ )

		$fn = sprintf('%s/%04d', $dir, $sk >> 2);
		savepix($fn, $pix);
	} // for ( $sk=0; $sk < $spr_max; $sk += 4 )
	return;
}
//////////////////////////////
function ramoff( &$file, $pos )
{
	$off = str2int($file, $pos, 3);
	return $off - 0x3e000;
}

function vampsav( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,1,3) !== "\xe0\x03\x80" )
		return;

	$dir  = str_replace('.', '_', $fname);
	$last = ramoff($file, 0);

	$vab_off = ramoff($file, $last -  4);
	$gfx_off = ramoff($file, $last -  8);
	$spr_off = ramoff($file, $last - 12);
	$tex_off = ramoff($file, $last - 16);
	//$???_off = ramoff($file, $last - 20);

	$gfx = substr($file, $gfx_off, $vab_off - $gfx_off);
	$spr = substr($file, $spr_off, $gfx_off - $spr_off);
	$tex = substr($file, $tex_off, $spr_off - $tex_off);
	save_file("$dir/gfx.bin", $gfx);
	save_file("$dir/spr.bin", $spr);
	save_file("$dir/tex.bin", $tex);

	sect_sprgfx($spr, $gfx, $tex, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	vampsav( $argv[$i] );
