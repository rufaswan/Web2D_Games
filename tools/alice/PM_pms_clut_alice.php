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
require "common.inc";
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
					$len = ord( $file[$st] ) + 2;
					$st++;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - $pms['w'];
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfe:
					$len = ord( $file[$st] ) + 2;
					$st++;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - ($pms['w'] * 2);
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfd:
					$len = ord( $file[$st+0] ) + 3;
					$b0  = str2int( $file, $st+1, 2 );
					$st += 3;

					for ( $i=0; $i < $len; $i++ )
					{
						$data[ $loc+$i ] = $b0;
						$x++;
					}
					break;

				case 0xfc:
					$len = (ord( $file[$st] ) + 2) * 2;
					$b0  =  str2int( $file, $st+1, 2 );
					$b1  =  str2int( $file, $st+3, 2 );
					$st += 5;

					for ( $i=0; $i < $len; $i += 2 )
					{
						$data[ $loc+$i+0 ] = $b0;
						$data[ $loc+$i+1 ] = $b1;
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
					$len = ord( $file[$st+0] ) + 1;
					$b0  = ord( $file[$st+1] );
					$st += 2;

					// ff = 111- ---- ---- ----
					//        -- -11- ---- ----
					//             -- ---1 11--
					//    = e    6    1    c
					$tb0 = ( ($b0 & 0xe0) << 8 ) + ( ($b0 & 0x18) << 6 ) + ( ($b0 & 0x07) << 2 );

					for ( $i=0; $i < $len; $i++ )
					{
						$b1 = ord( $file[$st] );
						$st++;
						// ff =    1 1--- ---- ----
						//            --1 111- ----
						//                ---- --11
						//    = 1    9    e    3
						$tb1 = ( ($b1 & 0xc0) << 5 ) + ( ($b1 & 0x3c) << 3 ) + ($b1 & 0x03);
						$data[ $loc+$i ] = $tb0 + $tb1;
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
		$r = ($v & 0xf800) >> 8;
		$g = ($v & 0x07e0) >> 3;
		$b = ($v & 0x001f) << 3;
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
					$len = ord( $file[$st] ) + 3;
					$st++;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - $pms['w'];
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfe:
					$len = ord( $file[$st] ) + 3;
					$st++;

					for ( $i=0; $i < $len; $i++ )
					{
						$t0 = $loc + $i;
						$t1 = $loc + $i - ($pms['w'] * 2);
						$data[ $t0 ] = $data[ $t1 ];
						$x++;
					}
					break;

				case 0xfd:
					$by  = str2int( $file, $st, 2 );
					$st += 2;
					$len = (($by>> 0) & BIT8) + 4;
					$b0  =  ($by>> 8) & BIT8;

					for ( $i=0; $i < $len; $i++ )
					{
						$data[ $loc+$i ] = $b0;
						$x++;
					}
					break;

				case 0xfc:
					$by  = str2int( $file, $st, 3 );
					$st += 3;
					$len = ((($by>> 0) & BIT8) + 3) * 2;
					$b0  =   ($by>> 8) & BIT8;
					$b1  =   ($by>>16) & BIT8;

					for ( $i=0; $i < $len; $i += 2 )
					{
						$data[ $loc+$i+0 ] = $b0;
						$data[ $loc+$i+1 ] = $b1;
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
	$img = "";
	for ( $i=0; $i < $len; $i++ )
		$img .= chr( $data[$i] );
	return $img;
}

function clut_pms8( &$file, &$pms, $st )
{
	$clut = "";
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
		if ( "PM" != $mgc )  return;

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
			printf("PMS-8 , %3d , %3d , %3d , %3d , $fname\n",
				$pms['x'], $pms['y'], $pms['w'], $pms['h']
			);

			$data = "CLUT";
			$data .= chrint(256 , 4);
			$data .= chrint($pms['w'] , 4);
			$data .= chrint($pms['h'] , 4);

			$data .= clut_pms8($file , $pms, $pms['pal']);
			$data .= data_pms8($file , $pms, $pms['dat']);

			file_put_contents("{$fname}.clut", $data);
			return;

		case 16:
			$t = "PMS-16";
			if ( $pms["dat"] )  $t .= "p";
			if ( $pms["pal"] )  $t .= "a";

			printf("$t , %3d , %3d , %3d , %3d , $fname\n",
				$pms['x'], $pms['y'], $pms['w'], $pms['h']
			);

			$data = "RGBA";
			$data .= chrint($pms['w'] , 4);
			$data .= chrint($pms['h'] , 4);

			$pix = ( $pms['dat'] ) ? data_pms16($file , $pms, $pms['dat']) : "";
			$alp = ( $pms['pal'] ) ? data_pms8 ($file , $pms, $pms['pal']) : "";

			$len = $pms['w'] * $pms['h'];
			for ( $i=0; $i < $len; $i++ )
			{
				$r = ( empty($pix) ) ? ZERO : $pix[$i][0];
				$g = ( empty($pix) ) ? ZERO : $pix[$i][1];
				$b = ( empty($pix) ) ? ZERO : $pix[$i][2];
				$a = ( empty($alp) ) ? BYTE : $alp[$i];
				$data .= $r . $g . $b . $a;
			}

			file_put_contents("{$fname}.rgba", $data);
			return;

		default:
			printf("UNK $fname : %d bpp\n", $pms['bpp']);
			return;
	}
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	pms2clut( $argv[$i] );
