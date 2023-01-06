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
//   xsystem35/src/vsp.c
// Original License:
//   GNU GPL v2 or later
require "common.inc";

function get_4bit( $bit, $b0, $b1, $b2, $b3 )
{
	$t0 = ($b0 >> $bit) & 1;
	$t1 = ($b1 >> $bit) & 1;
	$t2 = ($b2 >> $bit) & 1;
	$t3 = ($b3 >> $bit) & 1;
	return ($t0 | ($t1 << 1) | ($t2 << 2) | ($t3 << 3));
}

function swapval( &$n1, &$n2 )
{
	$t1 = $n1;
	$n1 = $n2;
	$n2 = $t1;
}
//////////////////////////////////////////////////
function data_vsp0( &$file, &$vsp , $st )
{
	// image is in 8-pixel width blocks
	// data is column-based , from top to bottom , then left-to-right
	// one column is 4-bit
	// one loop is 4*h
	$data = array();
	$bc   = array();
	$bp   = array();
	$mask = 0;
	for ( $x=0; $x < $vsp["pw"]; $x++ )
	{
		for ( $pl=0; $pl < 4; $pl++ )
		{
			$y = 0;
			while ( $y < $vsp["ph"] )
			{
				$c0 = ord( $file[$st] );
				$st++;
				switch ( $c0 )
				{
					case 0:
						$b0 = ord( $file[$st] );
							$st++;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y] = $bp[$pl][$y];
							$y++;
						}
						break;
					case 1:
						$b0 = ord( $file[$st+0] );
						$b1 = ord( $file[$st+1] );
							$st += 2;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y] = $b1;
							$y++;
						}
						break;
					case 2:
						$b0 = ord( $file[$st+0] );
						$b1 = ord( $file[$st+1] );
						$b2 = ord( $file[$st+2] );
							$st += 3;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y+0] = $b1;
							$bc[$pl][$y+1] = $b2;
							$y += 2;
						}
						break;
					case 3:
						$b0 = ord( $file[$st] );
							$st++;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y] = ($bc[0][$y] ^ $mask);
							$y++;
						}
						$mask = 0;
						break;
					case 4:
						$b0 = ord( $file[$st] );
							$st++;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y] = ($bc[1][$y] ^ $mask);
							$y++;
						}
						$mask = 0;
						break;
					case 5:
						$b0 = ord( $file[$st] );
							$st++;
						$len = $b0 + 1;

						for ( $i=0; $i < $len; $i++ )
						{
							$bc[$pl][$y] = ($bc[2][$y] ^ $mask);
							$y++;
						}
						$mask = 0;
						break;
					case 6:
						$mask = BIT8;
						break;
					case 7:
						$b0 = ord( $file[$st] );
							$st++;
						$bc[$pl][$y] = $b0;
						$y++;
						break;
					default:
						$bc[$pl][$y] = $c0;
						$y++;
						break;
				}
			} // while ( $y < $ph )
		} // for ( $pl=0; $pl < 4; $pl++ )

		for ( $y=0; $y < $vsp["ph"]; $y++ )
		{
			$loc = ( $y * $vsp["pw"] + $x ) * 8;
			$b0 = $bc[0][$y];
			$b1 = $bc[1][$y];
			$b2 = $bc[2][$y];
			$b3 = $bc[3][$y];

			// b0 = 01234567
			// b1 = 01234567
			// b2 = 01234567
			// b3 = 01234567
			$data[ $loc + 0 ] = get_4bit(7, $b0, $b1, $b2, $b3);
			$data[ $loc + 1 ] = get_4bit(6, $b0, $b1, $b2, $b3);
			$data[ $loc + 2 ] = get_4bit(5, $b0, $b1, $b2, $b3);
			$data[ $loc + 3 ] = get_4bit(4, $b0, $b1, $b2, $b3);
			$data[ $loc + 4 ] = get_4bit(3, $b0, $b1, $b2, $b3);
			$data[ $loc + 5 ] = get_4bit(2, $b0, $b1, $b2, $b3);
			$data[ $loc + 6 ] = get_4bit(1, $b0, $b1, $b2, $b3);
			$data[ $loc + 7 ] = get_4bit(0, $b0, $b1, $b2, $b3);
		}

		swapval( $bc[0], $bp[0] );
		swapval( $bc[1], $bp[1] );
		swapval( $bc[2], $bp[2] );
		swapval( $bc[3], $bp[3] );

	} // for ( $x=0; $x < $vsp["pw"]; $x++ )
	//////////////////////////
	$len = $vsp["pw"]*8 * $vsp["ph"];
	$img = "";
	for ( $i=0; $i < $len; $i++ )
		$img .= chr( $data[$i] );
	return $img;
}

function clut_vsp0( &$file , $st )
{
	$clut = '';
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$cb = ord( $file[$st+$i+0] );
		$cr = ord( $file[$st+$i+1] );
		$cg = ord( $file[$st+$i+2] );

		$r = int_clamp($cr * 0x11, 0, BIT8);
		$g = int_clamp($cg * 0x11, 0, BIT8);
		$b = int_clamp($cb * 0x11, 0, BIT8);
		$clut .= chr($r) . chr($g) . chr($b) . BYTE;
	}
	return $clut;
}
//////////////////////////////////////////////////
function vsp2clut( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )  return;

	$vsp = array();
	$vsp['px'] = str2int( $file, 0, 2 );
	$vsp['py'] = str2int( $file, 2, 2 );
	$vsp['pw'] = str2int( $file, 4, 2 ) - $vsp["px"];
	$vsp['ph'] = str2int( $file, 6, 2 ) - $vsp["py"];

	$res = ord( $file[8] );
	if ( $res )
	{
		echo "VSP-1 $fname (proto-PMS 256 colors)\n";
		echo "  use vsp2pms to convert. SKIPPED\n";
	}
	else
	{
		printf("VSP-0 , %3d , %3d , %3d , %3d , $fname\n",
			$vsp["px"]*8, $vsp["py"], $vsp["pw"]*8, $vsp["ph"]
		);

		$clut = "CLUT";
		$clut .= chrint(16 , 4);
		$clut .= chrint($vsp["pw"]*8 , 4);
		$clut .= chrint($vsp["ph"]   , 4);

		$clut .= clut_vsp0( $file , 0xa );
		$clut .= data_vsp0( $file , $vsp , 0x3a );

		file_put_contents("{$fname}.clut", $clut);
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	vsp2clut( $argv[$i] );
