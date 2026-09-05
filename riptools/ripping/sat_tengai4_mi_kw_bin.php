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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');
tool::require('class-ieee754');
tool::require('func-console');

function tm4dec4( string &$sub, int $pos, int $siz ) : string
{
	$pix = '';
	while ( $siz > 0 )
	{
		if ( ! isset($sub[$pos]) )
			break;

		$b1 = $sub[$pos+0];
		$b2 = $sub[$pos+1];
			$pos += 2;

		if ( $b2 === ZERO )
		{
			$c = ord($b1);
			$pix .= str_repeat(ZERO, $c);
				$siz -= $c;
		}
		else
		{
			$c = ord($b2);
			$pix .= str_repeat($b1, $c);
				$siz -= $c;
		}
	} // while ( $siz > 0 )
	return $pix;
}

function tm4dec1( string &$sub, int $pos, int $siz ) : string
{
	$pix = '';
	while ( $siz > 0 )
	{
		$b1 = ord( $sub[$pos] );
			$pos++;

		$flg = $b1 &  1;
		$clr = $b1 >> 1;
		if ( $flg )
		{
			$pix .= chr($clr);
				$siz -= 1;
		}
		else
		{
			$b2 = ord( $sub[$pos] );
				$pos++;
			$pix .= str_repeat(chr($clr), $b2);
				$siz -= $b2;
		}
	} // while ( $siz > 0 )
	return $pix;
}

function sect2( string &$sub, string &$pal, string $fn ) : void
{
	tool::trace(__FUNCTION__, $fn);
	$b1 = ieee754::ordstr($sub, 0, 2);
	$x  = ieee754::ordstr($sub, 2, 2);
	$y  = ieee754::ordstr($sub, 4, 2);

	$len = strlen($sub);
	if ( $len < 12 )
	{
		tool::warning('sect2 not enough data', $b1, $x, $y);;
		return;
	}

	$no = ieee754::ordstr($sub,  6, 2);
	$w  = ieee754::ordstr($sub,  8, 2);
	$h  = ieee754::ordstr($sub, 10, 2);
	printf("%x , %x %x , %x , %x %x \n", $b1, $x, $y, $no, $w, $h);

	switch ( $no )
	{
		case 1:  $pix = tm4dec1($sub, 12, $w*$h); break;
		case 4:  $pix = tm4dec4($sub, 12, $w*$h); break;
		default:
			tool::error('UNKNOWN decode', $no);
	} // switch ( $no )

	$img = new clutdata;
	$img->w = $w;
	$img->h = $h;
	$img->pal = $pal;
	$img->pix = $pix;

	// saturn is 320x240
	$cx = -160 + $x;
	$cy = -120 + $y;
	clutfile::save($fn, $img);
/*
	$img = [
		'cc'  => strlen($pal) >> 2,
		'w'   => $w,
		'h'   => $h,
		'pal' => $pal,
		'pix' => $pix,
	];

	// saturn is 320x240
	$cx = -160 + $x;
	$cy = -120 + $y;
	center_clutfile($img, $cx, $cy);
	save_clutfile($fn, $img);
	return;
*/
}

function sect1( string &$file, string &$pal, int $pos, string $pfx ) : void
{
	$cnt = ieee754::ordstr($file, $pos, 2);
	tool::trace(__FUNCTION__, $pos, $pfx, $cnt);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p   = $pos + 2 + ($i * 4);
		$b1  = ieee754::ordstr($file, $p+0, 4);
		$b2  = ieee754::ordstr($file, $p+4, 4);
		$sub = tool::substr($file, $b1, $b2-$b1);

		$fn = sprintf('%s-%04d', $pfx, $i);
		sect2($sub, $pal, $fn);
	} // for ( $i=0; $i < $cnt; $i++ )
}

function tengai4( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = ieee754::ordstr($file, 0, 4);
	if ( $b1 !== 0x84 )
		return;

	$dir = str_replace('.', '_', $fname);

	$b1 = ieee754::ordstr($file, 0x80, 4);
	$b2 = ord( $file[$b1] );
	$pal = tool::substr($file, $b1+1, $b2*2);
	$pal = saturn::pal555($pal, 0);

	for ( $i=0; $i < 0x20; $i++ )
	{
		$pos = $i * 4;
		if ( $file[$pos] === BYTE )
			continue;

		$b1 = ieee754::ordstr($file, $pos, 4);
		$pfx = sprintf('%s/%02d', $dir, $i);
		sect1($file, $pal, $b1, $pfx);
	} // for ( $i=0; $i < 0x20; $i++ )
}

for ( $i=1; $i < $argc; $i++ )
	tengai4( $argv[$i] );
