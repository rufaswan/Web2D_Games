<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
//////////////////////////////
define("BIT8" , 0xff);

function str2int( &$str, $pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	return $int;
}
function int2str( $int, $byte )
{
	$str = "";
	while ( $byte > 0 )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
		$byte--;
	} // while ( $byte > 0 )
	return $str;
}
//////////////////////////////
function data_pms8( &$file, &$pms )
{
	$data = array();
	$st = $pms['dat'];
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

				default:
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

function clut_pms8( &$file, &$pms )
{
	$clut = "";
	$st = $pms['pal'];
	for ( $i=0; $i < 0x100; $i++ )
	{
		$clut .= substr($file, $st, 3);
		$clut .= chr(BIT8); // alpha , 0 = trans , 255 = solid
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
		'ver'  => str2int( $file,    2, 2 ),
		'head' => str2int( $file,    4, 2 ),
		'bpp'  => str2int( $file,    6, 1 ),
		'shdw' => str2int( $file,    7, 1 ),
		'flag' => str2int( $file,    8, 1 ),
		'bank' => str2int( $file,  0xa, 2 ),
		'x'    => str2int( $file, 0x10, 4 ),
		'y'    => str2int( $file, 0x14, 4 ),
		'w'    => str2int( $file, 0x18, 4 ),
		'h'    => str2int( $file, 0x1c, 4 ),
		'dat'  => str2int( $file, 0x20, 4 ),
		'pal'  => str2int( $file, 0x24, 4 ),
		'meta' => str2int( $file, 0x28, 4 ),
	);

	switch ( $pms['bpp'] )
	{
		case 8:
			printf("PMS-8 , %3d , %3d , %3d , %3d , $fname\n",
				$pms['x'], $pms['y'], $pms['w'], $pms['h']
			);

			$head  = "CLUT";
			$head .= int2str(256 , 4);
			$head .= int2str($pms['w'] , 4);
			$head .= int2str($pms['h'] , 4);

			$clut  = clut_pms8($file , $pms);
			$data  = data_pms8($file , $pms);

			$file = $head . $clut . $data;
			file_put_contents("{$fname}.clut", $file);
			break;
		default:
			printf("UNK $fname : %d bpp\n", $pms['bpp']);
			break;
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	pms2clut( $argv[$i] );
