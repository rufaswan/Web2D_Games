<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
// Derived from Source:
//   xsystem35/src/qnt.c
// Original License:
//   GNU GPL v2 or later
declare( strict_types=1 );

require 'tool.inc';
tool::extension('zlib');

//define('DEBUG', false);
function save_debug( string $fname, string &$file ) : void
{
	if ( ! defined('DEBUG') )
		return;

	if ( empty($file) )
		return;

	$c = trim($file, $file[0]);
	if ( empty($c) )
		return;

	tool::save($fname, $file);
}
//////////////////////////////
function qnt_filter( array &$data, int $w, int $h ) : string
{
	// y=0     x=0     nop
	// y=0     x=1..w  LEFT - cur
	// y=1..h  x=0     TOP  - cur
	// y=1..h  x=1..w  ((TOP + LEFT) >> 1) - cur

	// y=0  x=1..w
	$row = &$data[0];
	for ( $x=1; $x < $w; $x++ )
	{
		$lp = ord( $row[$x-1] );
		$cp = ord( $row[$x  ] );
		$b  = $lp - $cp;
			$b &= BIT8;
		$row[$x] = chr($b);
	} // for ( $x=1; $x < $w; $x++ )

	for ( $y=1; $y < $h; $y++ )
	{
		$top = &$data[$y-1];
		$row = &$data[$y  ];

		// y=1..h  x=0
		$tp = ord( $top[0] );
		$cp = ord( $row[0] );
		$b  = $tp - $cp;
			$b &= BIT8;
		$row[0] = chr($b);

		// y=1..h  x=1..w
		for ( $x=1; $x < $w; $x++ )
		{
			$tp = ord( $top[$x  ] );
			$lp = ord( $row[$x-1] );
			$cp = ord( $row[$x  ] );
			$b  = (($lp + $tp) >> 1) - $cp;
				$b &= BIT8;
			$row[$x] = chr($b);
		} // for ( $x=1; $x < $w; $x++ )
	} // for ( $y=1; $y < $h; $y++ )

	// trim pad
	// odd width + odd height
	$pix = '';
	for ( $y=0; $y < $h; $y++ )
		$pix .= tool::substr($data[$y], 0, $w);
	return $pix;
}

function qnt_pixel( string &$dec, int $w, int $h ) : array
{
	$pix = [];
	$pos = 0;

	// [BBBB]...[GGGG]...[RRRR]... blocks
	for ( $i=0; $i < 3; $i++ )
	{
		// in 2x2 blocks
		//   | 0 2 |
		//   | 1 3 |
		// odd width  = pad 2 3
		// odd height = pad 1 3
		$data = [];
		for ( $y=0; $y < $h; $y += 2 )
		{
			$row1 = '';
			$row2 = '';
			for ( $x=0; $x < $w; $x += 2 )
			{
				$row1 .= $dec[$pos+0] . $dec[$pos+2];
				$row2 .= $dec[$pos+1] . $dec[$pos+3];
				$pos += 4;
			} // for ( $x=0; $x < $w; $x += 2 )

			$data[] = $row1;
			$data[] = $row2;
		} // for ( $y=0; $y < $h; $y += 2 )

		$pix[$i] = qnt_filter($data, $w, $h);
	} // for ( $i=0; $i < 3; $i++ )

	return $pix;
}

function qnt_alpha( string &$dec, int $w, int $h ) : string
{
	// also in 2x2 blocks
	$rw = ( $w & 1 ) ? $w + 1 : $w;

	$data = [];
	for ( $y=0; $y < $h; $y++ )
		$data[] = tool::substr($dec, $y*$rw, $rw);
	return qnt_filter($data, $w, $h);
}

function data_qnt( string &$file, array &$qnt, string $fname ) : string
{
	if ( $qnt['t'] > 2 )
		return '';

	$pix = ['','',''];
	if ( $qnt['pix'] > 0 )
	{
		$dec = substr($file, $qnt['hdr']);
		$dec = zlib_decode($dec);
		save_debug("$fname.dec.pix", $dec);
		$pix = qnt_pixel($dec, $qnt['pw'], $qnt['ph']);
	}
	else
	{
		$pix[0] = str_repeat(ZERO, $qnt['pw']*$qnt['ph']);
		$pix[1] = $pix[0];
		$pix[2] = $pix[0];
	}


	$alp = '';
	if ( $qnt['alp'] > 0 )
	{
		$dec = substr($file, $qnt['hdr'] + $qnt['pix']);
		$dec = zlib_decode($dec);
		save_debug("$fname.dec.alp", $dec);
		$alp = qnt_alpha($dec, $qnt['pw'], $qnt['ph']);
	}
	else
		$alp = str_repeat(BYTE, $qnt['pw']*$qnt['ph']);

	save_debug("$fname.pix.b", $pix[0]);
	save_debug("$fname.pix.g", $pix[1]);
	save_debug("$fname.pix.r", $pix[2]);
	save_debug("$fname.pix.a", $alp   );

	$siz = $qnt['pw'] * $qnt['ph'];
	$data = '';
	for ( $i=0; $i < $siz; $i++ )
	{
		// $pix is in BGR order
		$r = $pix[2][$i];
		$g = $pix[1][$i];
		$b = $pix[0][$i];
		$a = $alp[$i];
		$data .= $r . $g . $b . $a;
	}
	return $data;
}
//////////////////////////////////////////////////
function qnt_header( string &$file, int $type ) : array
{
	$qnt = [];
	switch ( $type )
	{
		case 0:
			$qnt['t']   = $type;
			$qnt['hdr'] = 0x30;
			$qnt['px']  = tool::ordstr($file, 0x08, 4);
			$qnt['py']  = tool::ordstr($file, 0x0c, 4);
			$qnt['pw']  = tool::ordstr($file, 0x10, 4);
			$qnt['ph']  = tool::ordstr($file, 0x14, 4);
			$qnt['bpp'] = tool::ordstr($file, 0x18, 4);
			//$qnt['rsv'] = tool::ordstr($file, 0x1c, 4);
			$qnt['pix'] = tool::ordstr($file, 0x20, 4);
			$qnt['alp'] = tool::ordstr($file, 0x24, 4);
			return $qnt;
		case 1:
		case 2:
			$qnt['t']   = $type;
			$qnt['hdr'] = tool::ordstr($file, 0x08, 4);
			$qnt['px']  = tool::ordstr($file, 0x0c, 4);
			$qnt['py']  = tool::ordstr($file, 0x10, 4);
			$qnt['pw']  = tool::ordstr($file, 0x14, 4);
			$qnt['ph']  = tool::ordstr($file, 0x18, 4);
			$qnt['bpp'] = tool::ordstr($file, 0x1c, 4);
			//$qnt['rsv'] = tool::ordstr($file, 0x20, 4);
			$qnt['pix'] = tool::ordstr($file, 0x24, 4);
			$qnt['alp'] = tool::ordstr($file, 0x28, 4);
			return $qnt;
		default:
			return [];
	} // switch ( $type )
}
//////////////////////////////////////////////////
function qnt2rgba( string $fname ) : void
{
	$file = file_get_contents( $fname );
	if ( empty($file) )
		return;

	$mgc = substr($file, 0, 3);
	if ( $mgc !== 'QNT' )
		return;

	$type = tool::ordstr($file, 4, 4);
	$qnt  = qnt_header($file, $type);
	if ( empty($qnt) )
		return;

	if ( $qnt['pix'] > 0 )  $type .= 'p';
	if ( $qnt['alp'] > 0 )  $type .= 'a';
	printf("QNT-$type , %4d , %4d , %4d , %4d , $fname\n",
		$qnt['px'], $qnt['py'], $qnt['pw'], $qnt['ph']
	);

	$fn = substr($fname, 0, strrpos($fname,'.'));
	if ( $qnt['px'] !== 0 )  $fn .= '.x'.$qnt['px'];
	if ( $qnt['py'] !== 0 )  $fn .= '.y'.$qnt['py'];

	$rgba = 'RGBA';
	$rgba .= tool::chr($qnt['pw'], 4);
	$rgba .= tool::chr($qnt['ph'], 4);
	$rgba .= data_qnt($file, $qnt, $fname);
	tool::save("$fn.rgba", $rgba);
}

tool::argv_callback($argv, 'qnt2rgba');
