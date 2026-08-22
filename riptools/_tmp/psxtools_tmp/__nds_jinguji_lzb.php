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

function jinguji_decode( &$file )
{
	$dec = '';
	trace("== begin sub_2001044()\n");

	$len = strlen($file);
	$pos = 4;

	$b00 = str2int($file, 0, 4);
	$siz = $b00 >> 8;
	switch ( $b00 & 0xf0 )
	{
		case 0x10:
			$bycod = 0;
			$bylen = 0;

			while ( $siz > 0 )
			{
				if ( $bylen === 0 )
				{
					$bycod = ord( $file[$pos] );
						$pos++;
					$bylen = 8;
					continue;
				}

				$flg = $bycod & 0x80;
					$bycod <<= 1;
					$bylen--;

				if ( $flg )
				{
					$b1 = ord( $file[$pos+0] );
					$b2 = ord( $file[$pos+1] );
						$pos += 2;

					$dlen =  ($b1 >> 4) + 3;
					$dpos = (($b1 & 0x0f) << 8) | $b2;
					for ( $i=0; $i < $dlen; $i++ )
					{
						$dp = strlen($dec) - $dpos - 1;
						$dec .= $dec[$dp];
						$siz--;
					}
				}
				else
				{
					$dec .= $file[$pos];
						$pos++;
						$siz--;
				}
			} // while ( $siz > 0 )

			$file = $dec;
			return;
		case 0x20:
			return;
		case 0x30:
			return;
	} // switch ( $b00 & 0xf0 )
	trace("== end sub_2001044()\n");
	return;
}

function lzbfile( $fname )
{
	// for *.lzb only
	if ( stripos($fname, '.lzb') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	jinguji_decode($file);

	$len = strlen($file);
	switch ( $len )
	{
		//case 0x8200: // bg
			//return;
			//return;
		case 0x5100: // ch
		case 0x4500: // ch , j1-j5
			$pal = substr($file, 0, 0x80);
			$pix = substr($file, 0x80);
			$img = [
				'cc'  => 0x40,
				'w'   => 0x80,
				'h'   => 0x80,
				'pal' => pal555($pal),
				'pix' => $pix,
			];

			$pos = 0;
			for ( $y=0; $y < $img['h']; $y += 0x40 )
			{
				for ( $x=0; $x < $img['w']; $x += 0x40 )
				{

					for ( $ty=0; $ty < 0x40; $ty += 8 )
					{
						if ( ($y+$ty) >= $img['h'] )
							continue;
						for ( $tx=0; $tx < 0x40; $tx += 8 )
						{

							for ( $sy=0; $sy < 8; $sy++ )
							{
								$syy = ($y + $ty + $sy) * $img['w'];
								$sxx = $syy + $x + $tx;

								$b = substr($pix, $pos, 8);
									$pos += 8;
								str_update($img['pix'], $sxx, $b);
							} // for ( $sy=0; $sy < 8; $sy++ )

						} // for ( $tx=0; $tx < 0x40; $tx += 8 )
					} // for ( $ty=0; $ty < 0x40; $ty += 8 )

				} // for ( $x=0; $x < $img['w']; $x += 0x40 )
			} // for ( $y=0; $y < $img['h']; $y += 0x40 )
			return save_clutfile("$fname.clut", $img);
		default:
			return save_file("$fname.dec", $file);
	} // switch ( $len )
	return;
}

function jinguji( $ent )
{
	if ( is_file($ent) )
		return lzbfile($ent);
	if ( ! is_dir($ent) )
		return;

	$dir  = rtrim($ent, '/\\');
	$list = [];
	lsfile_r($dir, $list);
	foreach ( $list as $fn )
		lzbfile($fn);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jinguji( $argv[$i] );
