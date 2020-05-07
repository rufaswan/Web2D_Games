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
require "common.inc";
req_ext( "zlib_decode", "zlib" );

define("DEBUG", false);
//////////////////////////////
function qnt_pixel( &$dec, &$qnt )
{
	$pix = array();
	$w = $qnt["pw"];
	$h = $qnt["ph"];
	$j = 0;

	// [BBBB]...[GGGG]...[RRRR]... blocks
	for ( $i=0; $i < 3; $i++ )
	{
		$data = array();
		for ( $y=0; $y < ($h-1); $y += 2 )
		{
			$r1 =  $y    * $w;
			$r2 = ($y+1) * $w;
			for ( $x=0; $x < ($w-1); $x += 2 )
			{
				$data[ $r1+$x   ] = ord( $dec[$j+0] );
				$data[ $r2+$x   ] = ord( $dec[$j+1] );
				$data[ $r1+$x+1 ] = ord( $dec[$j+2] );
				$data[ $r2+$x+1 ] = ord( $dec[$j+3] );
				$j += 4;
			} // for ( $x=0; $x < ($w-1); $x += 2 )

			// odd-size width , right-most side
			if ( $w % 2 )
			{
				$data[ $r1+$x ] = ord( $dec[$j+0] );
				$data[ $r2+$x ] = ord( $dec[$j+1] );
				$j += 4;
			}
		} // for ( $y=0; $y < ($h-1); $y += 2 )

		// odd-size height , bottom side
		if ( $h % 2 )
		{
			$r1 = $y * $w;
			for ( $x=0; $x < ($w-1); $x += 2 )
			{
				$data[ $r1+$x   ] = ord( $dec[$j+0] );
				$data[ $r1+$x+1 ] = ord( $dec[$j+2] );
				$j += 4;
			} // for ( $x=0; $x < ($w-1); $x += 2 )

			// odd-size height+width , bottom-right corner
			if ( $w % 2 )
			{
				$data[ $r1+$x ] = ord( $dec[$j] );
				$j += 4;
			}
		}

		// pixel diff
		if ( $w > 1 )
		{
			for ( $x=1; $x < $w; $x++ )
				$data[$x] = ($data[$x-1] - $data[$x]) & BIT8;
		}

		if ( $h > 1 )
		{
			for ( $y=1; $y < $h; $y++ )
			{
				$r1 =  $y    * $w;
				$r2 = ($y-1) * $w;
				$data[$r1] = ($data[$r2] - $data[$r1]) & BIT8;

				for ( $x=1; $x < $w; $x++ )
				{
					$px = $data[ $r2+$x   ];
					$py = $data[ $r1+$x-1 ];
					$data[$r1+$x] = (( ($px+$py) >> 1 ) - $data[$r1+$x]) & BIT8;
				} // for ( $x=1; $x < $w; $x++ )

			} // for ( $y=1; $y < $h; $y++ )
		}

		$pix[$i] = "";
		for ( $n=0; $n < ($w*$h); $n++ )
			$pix[$i] .= chr( $data[$n] );

	} // for ( $i=0; $i < 3; $i++ )

	return $pix;
}

function qnt_alpha( &$dec, &$qnt )
{
	$data = array();
	$w = $qnt["pw"];
	$h = $qnt["ph"];

	$data[0] = ord( $dec[0] );
	$i = 1;
	if ( $w > 1 )
	{
		for ( $x=1; $x < $w; $x++ )
		{
			$c = ord( $dec[$i] );
				$i++;
			$data[$x] = ($data[$x-1] - $c) & BIT8;
		}
		if ( $w % 2 )
			$i++;
	}

	if ( $h > 1 )
	{
		for ( $y=1; $y < $h; $y++ )
		{
			$r1 =  $y    * $w;
			$r2 = ($y-1) * $w;
			$c = ord( $dec[$i] );
				$i++;
			$data[$r1] = ($data[$r2] - $c) & BIT8;
			for ( $x=1; $x < $w; $x++ )
			{
				$px = $data[ $r1+$x-1 ];
				$py = $data[ $r2+$x   ];
				$c = ord( $dec[$i] );
					$i++;
				$data[ $r1+$x ] = (( ($px+$py) >> 1 ) - $c) & BIT8;
			} // for ( $x=1; $x < $w; $x++ )
			if ( $w % 2 )
				$i++;
		} // for ( $y=1; $y < $h; $y++ )
	}

	$alp = "";
	for ( $n=0; $n < ($w*$h); $n++ )
		$alp .= chr( $data[$n] );
	return $alp;
}

function data_qnt( &$file, &$qnt, $fname )
{
	if ( $qnt['t'] > 2 )  return "";

	$w = $qnt["pw"];
	$h = $qnt["ph"];
	$siz = $w * $h;

	$pix = "";
	if ( $qnt["pix"] != 0 )
	{
		$dec = substr($file, $qnt["hdr"]);
		$dec = zlib_decode($dec);
		if ( DEBUG )
			file_put_contents("$fname.1", $dec);
		$pix = qnt_pixel($dec, $qnt);
	}

	$alp = "";
	if ( $qnt["alp"] != 0 )
	{
		$dec = substr($file, $qnt["hdr"] + $qnt["pix"]);
		$dec = zlib_decode($dec);
		if ( DEBUG )
			file_put_contents("$fname.2", $dec);
		$alp = qnt_alpha($dec, $qnt);
	}

	//file_put_contents("pix0", $pix[0]); // Blue
	//file_put_contents("pix1", $pix[1]); // Green
	//file_put_contents("pix2", $pix[2]); // Red
	//file_put_contents("alp",  $alp); // Alpha

	$data = "";
	for ( $n=0; $n < $siz; $n++ )
	{
			$r = ( empty($pix) ) ? ZERO : $pix[2][$n];
			$g = ( empty($pix) ) ? ZERO : $pix[1][$n];
			$b = ( empty($pix) ) ? ZERO : $pix[0][$n];
			$data .= $r;
			$data .= $g;
			$data .= $b;
			if ( empty($alp) )
				$data .= BYTE; // A
			else
				$data .= $alp[$n]; // A
	}

	return $data;
}
//////////////////////////////////////////////////
function qnt_header( &$file, $type )
{
	$qnt = array();
	switch ( $type )
	{
		case 0:
			$qnt["t"]   = $type;
			$qnt["hdr"] = 0x30;
			$qnt["px"]  = str2int($file, 0x08, 4);
			$qnt["py"]  = str2int($file, 0x0c, 4);
			$qnt["pw"]  = str2int($file, 0x10, 4);
			$qnt["ph"]  = str2int($file, 0x14, 4);
			$qnt["bpp"] = str2int($file, 0x18, 4);
			$qnt["rsv"] = str2int($file, 0x1c, 4);
			$qnt["pix"] = str2int($file, 0x20, 4);
			$qnt["alp"] = str2int($file, 0x24, 4);
			return $qnt;
		case 1:
		case 2:
			$qnt["t"]   = $type;
			$qnt["hdr"] = str2int($file, 0x08, 4);
			$qnt["px"]  = str2int($file, 0x0c, 4);
			$qnt["py"]  = str2int($file, 0x10, 4);
			$qnt["pw"]  = str2int($file, 0x14, 4);
			$qnt["ph"]  = str2int($file, 0x18, 4);
			$qnt["bpp"] = str2int($file, 0x1c, 4);
			$qnt["rsv"] = str2int($file, 0x20, 4);
			$qnt["pix"] = str2int($file, 0x24, 4);
			$qnt["alp"] = str2int($file, 0x28, 4);
			return $qnt;
		default:
			return $qnt;
	}
}
//////////////////////////////////////////////////
function qnt2rgba( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 3);
		if ( $mgc != "QNT" )  return;

	$type = str2int($file, 4, 4);
	$qnt = qnt_header($file, $type);
		if ( empty($qnt) )  return;

	if ( $qnt["pix"] != 0 )  $type .= 'p';
	if ( $qnt["alp"] != 0 )  $type .= 'a';
	printf("QNT-$type , %4d , %4d , %4d , %4d , $fname\n",
		$qnt["px"], $qnt["py"], $qnt["pw"], $qnt["ph"]
	);

	$head  = "RGBA";
	$head .= chrint($qnt["pw"], 4);
	$head .= chrint($qnt["ph"], 4);

	$data  = data_qnt($file, $qnt, $fname);

	$file  = $head . $data;
	file_put_contents("{$fname}.rgba", $file);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	qnt2rgba( $argv[$i] );
