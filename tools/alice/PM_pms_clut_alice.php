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
//   xsystem35/src/pms.c
// Original License:
//   GNU GPL v2 or later
require 'common.inc';
//////////////////////////////
function data_pms16( &$file, &$pms, $st )
{
	$data = array();
	for ( $y=0; $y < $pms['h']; $y++ )
	{
		$x = 0;
		while ( $x < $pms['w'] )
		{
			$loc = $y * $pms['w'] + $x;
			$c0  = ord( $file[$st] );
				$st++;

			switch ( $c0 )
			{
				case 0xff:
					$b0 = ord( $file[$st] );
						$st++;
					$len = $b0 + 2;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - $pms['w'];
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfe:
					$b0 = ord( $file[$st] );
						$st++;
					$len = $b0 + 2;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - ($pms['w'] * 2);
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfd:
					$b0 = ord( $file[$st+0] );
					$b1 = str2int( $file, $st+1, 2 );
						$st += 3;
					$len = $b0 + 3;

					for ( $i=0; $i < $len; $i++ )
					{
						$data[ $loc+$i ] = $b1;
						$x++;
					}
					break;

				case 0xfc:
					$b0 = ord( $file[$st] );
					$b1 = str2int( $file, $st+1, 2 );
					$b2 = str2int( $file, $st+3, 2 );
						$st += 5;
					$len = ($b0 + 2) * 2;

					for ( $i=0; $i < $len; $i += 2 )
					{
						$data[ $loc+$i+0 ] = $b1;
						$data[ $loc+$i+1 ] = $b2;
						$x += 2;
					}
					break;

				case 0xfb:
					$data[ $loc ] = $data[ $loc-$pms['w']-1 ];
					$x++;
					break;

				case 0xfa:
					$data[ $loc ] = $data[ $loc-$pms['w']+1 ];
					$x++;
					break;

				case 0xf9:
					$b0 = ord( $file[$st+0] );
					$b1 = ord( $file[$st+1] );
						$st += 2;
					$len = $b0 + 1;

					// 76543210 -> fedcba9876543210
					// rrrggbbb    rrr--gg----bbb--
					$br = ($b1 >> 5) & 7;
					$bg = ($b1 >> 3) & 3;
					$bb = ($b1 >> 0) & 7;
					$tb1 = ($br << 13) | ($bg << 9) | ($bb << 2);

					for ( $i=0; $i < $len; $i++ )
					{
						$b2 = ord( $file[$st] );
							$st++;
						// 76543210 -> fedcba9876543210
						// rrggggbb    ---rr--gggg---bb
						$br = ($b2 >> 6) &  3;
						$bg = ($b2 >> 2) & 15;
						$bb = ($b2 >> 0) &  3;
						$tb2 = ($br << 11) | ($bg << 5) | ($bb << 0);
						$data[ $loc+$i ] = $tb1 | $tb2;
						$x++;
					}
					break;

				case 0xf8:
					$b0 = str2int( $file, $st, 2 );
					$st += 2;
					$data[$loc] = $b0;
					$x++;
					break;

				default: // c0 <= 0xf7
					$b0 = ord( $file[$st] );
					$st++;
					$data[$loc] = ($b0 << 8) + $c0;
					$x++;
					break;
			} // switch ( $c0 )

		} // while ( $x < $pms['w'] )
	} // for ( $y=0; $y < $pms['h']; $y++ )
	//////////////////////////
	foreach ( $data as $k => $v )
	{
		// rgb565
		// fedcba9876543210
		// rrrrrggggggbbbbb
		$r = ($v >> 8) & 0xf8; // <<  0 >> 8
		$g = ($v >> 3) & 0xfc; // <<  5 >> 8
		$b = ($v << 3) & 0xf8; // << 11 >> 8
		$data[$k] = array( chr($r) , chr($g) , chr($b) );
	}
	return $data;
}
//////////////////////////////
function data_pms8( &$file, &$pms, $st )
{
	$data = array();
	for ( $y=0; $y < $pms['h']; $y++ )
	{
		$x = 0;
		while ( $x < $pms['w'] )
		{
			$loc = $y * $pms['w'] + $x;
			$c0  = ord( $file[$st] );
			$st++;

			switch ( $c0 )
			{
				case 0xff:
					$b0 = ord( $file[$st] );
						$st++;
					$len = $b0 + 3;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - $pms['w'];
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfe:
					$b0 = ord( $file[$st] );
						$st++;
					$len = $b0 + 3;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - ($pms['w'] * 2);
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfd:
					$b0 = ord( $file[$st+0] );
					$b1 = ord( $file[$st+1] );
						$st += 2;
					$len = $b0 + 4;

					for ( $i=0; $i < $len; $i++ )
					{
						$data[ $loc+$i ] = $b1;
						$x++;
					}
					break;

				case 0xfc:
					$b0 = ord( $file[$st+0] );
					$b1 = ord( $file[$st+1] );
					$b2 = ord( $file[$st+2] );
						$st += 3;
					$len = ($b0 + 3) * 2;

					for ( $i=0; $i < $len; $i += 2 )
					{
						$data[ $loc+$i+0 ] = $b1;
						$data[ $loc+$i+1 ] = $b2;
						$x += 2;
					}
					break;

				case 0xfb:
				case 0xfa:
				case 0xf9:
				case 0xf8:
					$b0 = ord( $file[$st] );
						$st++;
					$data[$loc] = $b0;
					$x++;
					break;

				default: // c0 <= 0xf7
					$data[$loc] = $c0;
					$x++;
					break;
			} // switch ( $c0 )

		} // while ( $x < $pms['w'] )
	} // for ( $y=0; $y < $pms['h']; $y++ )
	//////////////////////////
	$len = $pms['w'] * $pms['h'];
	$img = '';
	for ( $i=0; $i < $len; $i++ )
		$img .= chr( $data[$i] );
	return $img;
}

function clut_pms8( &$file, &$pms, $st )
{
	$clut = '';
	for ( $i=0; $i < 0x100; $i++ )
	{
		$clut .= substr($file, $st, 3);
		$clut .= BYTE; // alpha
		$st += 3;
	}
	return $clut;
}
//////////////////////////////////////////////////
function pms2clut( $fname )
{
	$file = file_get_contents( $fname );
	if ( empty($file) )  return;

	$mgc = substr($file , 0 , 2 );
	if ( 'PM' !== $mgc )  return;

	$pms = array(
		'ver'  => str2int( $file, 0x02, 2 ),
		'head' => str2int( $file, 0x04, 2 ),
		'bpp'  => str2int( $file, 0x06, 1 ),
		'shdw' => str2int( $file, 0x07, 1 ),
		'flag' => str2int( $file, 0x08, 1 ),
		'bank' => str2int( $file, 0x0a, 2 ),
		'x'    => str2int( $file, 0x10, 4 ),
		'y'    => str2int( $file, 0x14, 4 ),
		'w'    => str2int( $file, 0x18, 4 ),
		'h'    => str2int( $file, 0x1c, 4 ),
		'dat'  => str2int( $file, 0x20, 4 ), // pixel data
		'pal'  => str2int( $file, 0x24, 4 ), // 8=palette , 16=alpha
		'meta' => str2int( $file, 0x28, 4 ),
	);

	switch ( $pms['bpp'] )
	{
		case 8:
			printf("PMS-8 , %3d , %3d , %3d , %3d , %s\n",
				$pms['x'], $pms['y'], $pms['w'], $pms['h'], $fname
			);

			$clut = 'CLUT';
			$clut .= chrint(256 , 4);
			$clut .= chrint($pms['w'] , 4);
			$clut .= chrint($pms['h'] , 4);

			$clut .= clut_pms8($file , $pms, $pms['pal']);
			$clut .= data_pms8($file , $pms, $pms['dat']);

			file_put_contents("$fname.clut", $clut);
			return;

		case 16:
			$t = 'PMS-16';
			if ( $pms['dat'] )  $t .= 'p';
			if ( $pms['pal'] )  $t .= 'a';

			printf("$t , %3d , %3d , %3d , %3d , %s\n",
				$pms['x'], $pms['y'], $pms['w'], $pms['h'], $fname
			);

			$rgba = 'RGBA';
			$rgba .= chrint($pms['w'] , 4);
			$rgba .= chrint($pms['h'] , 4);

			// some PMS have only RGB, no A
			// some PMS have only A, no RGB (used with AJP/effects, KLD/video)
			$pix = ( $pms['dat'] ) ? data_pms16($file , $pms, $pms['dat']) : '';
			$alp = ( $pms['pal'] ) ? data_pms8 ($file , $pms, $pms['pal']) : '';

			$len = $pms['w'] * $pms['h'];
			for ( $i=0; $i < $len; $i++ )
			{
				$r = ( empty($pix) ) ? ZERO : $pix[$i][0];
				$g = ( empty($pix) ) ? ZERO : $pix[$i][1];
				$b = ( empty($pix) ) ? ZERO : $pix[$i][2];
				$a = ( empty($alp) ) ? BYTE : $alp[$i];
				$rgba .= $r . $g . $b . $a;
			}

			file_put_contents("$fname.rgba", $rgba);
			return;

		default:
			printf("UNK %s : %d bpp\n", $fname, $pms['bpp']);
			return;
	}
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	pms2clut( $argv[$i] );
