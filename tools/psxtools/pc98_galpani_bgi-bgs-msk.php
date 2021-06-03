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
require "common.inc";

//define("NO_TRACE", true);

function galpani_decode( &$file, $pos, $end, $bits )
{
	trace("== galpani_decode( %x , %x )\n", $pos, $end);
	trace("=== begin sub_2436c() ===\n");

	$pix = str_repeat(ZERO, 0x1000);
	$pix .= $file[$pos];
	$pos++;

	while ( $pos < $end )
	{
		trace("%6x  %6x  ", $pos, strlen($pix)-0x1000);
		$b1 = ord( $file[$pos+0] );

		// 80-ff
		if ( $b1 & 0x80 )
		{
			$b2 = ord( $file[$pos+1] );
				$pos += 2;
			$ref = ($b1 << 8) | $b2; // eax
				$ref &= $bits[0];

			if ( $ref > 3 )
			{
				// fedcba98 76543210
				// -ppppprr rrrrrrrr
				// -pppprrr rrrrrrrr
				// -ppprrrr rrrrrrrr
				$op = $b1 >> $bits[1]; // ecx
					$op &= $bits[2];

				// $op switch cases
				// 0 = 2, 4 = 3, 8 = 4 ... 7c = 33
				$len = ($op / 4) + 2;
				$p = strlen($pix) - 1 - $ref;
				trace("80>3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$pix .= $pix[$p+$i];
			}
			else
			{
				// 76543210
				// -lllll--
				// -llll---
				// -lll----
				$len = $b1 >> $bits[7]; // ecx
					$len &= $bits[8];
					$len += 2;
				$p = strlen($pix) - 1 - $ref;
				trace("80-3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$pix .= $pix[$p+$i];
			}
			continue;
		} // if ( $b1 & 0x80 )

		// 40-7f
		if ( $b1 & 0x40 )
		{
			// 76543210 fedcba98 76543210
			// --llllll llllllrr rrrrrrrr
			// --llllll lllllrrr rrrrrrrr
			// --llllll llllrrrr rrrrrrrr
			$b2 = ord( $file[$pos+1] );
			$b3 = ord( $file[$pos+2] );
				$pos += 3;
			$ref = ($b2 << 8) | $b3; // edx
			$len = ($b1 << 8) | $b2; // ecx
				$ref &=  $bits[3];
				$len >>= $bits[4];
				$len &=  $bits[5];
				$len +=  $bits[6];

			$p = strlen($pix) - 1 - $ref;
			trace("40  REF -%d LEN %d\n", $ref, $len);

			for ( $i=0; $i < $len; $i++ )
				$pix .= $pix[$p+$i];

			continue;
		} // if ( $b1 & 0x40 )

		// 3f
		if ( $b1 == 0x3f )
		{
			$b1 = ord( $file[$pos+1] );

			if ( $b1 == 0 )
			{
				trace("3f 00  end\n");
				$pos = $end + 1;
			}
			else
			{
				$ed = strlen($pix) - 1;
				$b2 = ord( $pix[$ed] );
				$by = ($b1 + $b2) & BIT8;

				trace("3f  DIFF %2x + %2x = %2x\n", $b1, $b2, $by);
				$pix .= chr($by);
				$pos += 2;
			}
			continue;
		} // if ( $b1 == 0x3f )

		// 00-3e
		$b1 = $b1 - 0x1f;

		$ed = strlen($pix) - 1;
		$b2 = ord( $pix[$ed] );
		$by = ($b1 + $b2) & BIT8;

		trace("1f  DIFF %2x + %2x = %2x\n", $b1&BIT8, $b2, $by);
		$pix .= chr($by);
		$pos++;
	} // while ( $pos < $end )

	trace("=== end sub_2436c() ===\n");
	return substr($pix, 0x1000);
}
//////////////////////////////
$gp_bits = array(
	//                   0       1   2      3       4   5       6      7   8
	//                   80>3               40                         80-3
	"sub_2436c" => array(0x3ff , 0 , 0x7c , 0x3ff , 2 , 0xfff , 0x22 , 2 , 0x1f), // sub_2436c
	"sub_24910" => array(0x7ff , 1 , 0x3c , 0x7ff , 3 , 0x7ff , 0x12 , 3 , 0x0f), // sub_24910
	"sub_24b78" => array(0xfff , 2 , 0x1c , 0xfff , 4 , 0x3ff , 0x0a , 4 , 0x07), // sub_24b78
);
$gp_sub = array("" , "sub_2436c" , "sub_24910" , "sub_24b78");

function sectsc( &$file, $fname )
{
	echo "== sectsc( $fname )\n";
	$w  = str2int($file, 0x14, 2);
	$h  = str2int($file, 0x16, 2);
	$cc = str2int($file, 0x2a, 2);
		$cc += ord( $file[0x28] );
	printf("sc : %d x %d , %d color\n", $w, $h, $cc);

	$b1 = str2int($file, 0xb4, 4); // palette
	$b2 = str2int($file, 0xb8, 4); // data
	$b3 = str2int($file, 0xbc, 4); // size

	$pal = "";
	if ( $b1 == 0 )
	{
		$cc = 0x100;
		$pal = grayclut($cc);
	}
	else
	{
		$by = ord( $file[0x28] );
		$pal = str_repeat(ZERO, $by*4);
		for ( $i=$b1; $i < $b2; $i += 3 )
			$pal .= substr($file, $i, 3) . BYTE;
	}

	global $gp_bits, $gp_sub;
	$file .= ZERO . ZERO;

	$ty = ord( $file[0xb0] );
	echo "TYPE : $ty , {$gp_sub[$ty]}\n";
	$pix = galpani_decode($file, $b2, $b2+$b3, $gp_bits[ $gp_sub[$ty] ]);
	//file_put_contents("$fname.pix", $pix);

	$clut = "CLUT";
	$clut .= chrint($cc, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= $pal;
	$clut .= $pix;
	file_put_contents("$fname.clut", $clut);
	return;
}

function sectmsk( &$file, $fname )
{
	echo "== sectmsk( $fname )\n";
	$w  = str2int($file, 0x14, 2);
	$h  = str2int($file, 0x16, 2);
	$cc = 2;
	printf("msk : %d x %d , %d color\n", $w, $h, $cc);

	$b2 = str2int($file, 0xb8, 4); // data
	$b3 = str2int($file, 0xbc, 4); // size

	$pal = grayclut($cc);

	global $gp_bits, $gp_sub;
	$file .= ZERO . ZERO;

	$ty = ord( $file[0xb0] );
	$pix = galpani_decode($file, $b2, $b2+$b3, $gp_bits[ $gp_sub[$ty] ]);
	echo "TYPE : $ty , {$gp_sub[$ty]}\n";
	//file_put_contents("$fname.pix", $pix);

	$len = strlen($pix);
	$dat = "";
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $pix[$i] );
		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$b1 = ($b >> $j) & 1;
			$dat .= chr($b1);
		}
	}

	$clut = "CLUT";
	$clut .= chrint($cc, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= $pal;
	$clut .= $dat;
	file_put_contents("$fname.clut", $clut);
	return;
}
function sectsprt( &$file, $fname )
{
	echo "== sectsprt( $fname )\n";

	$dir = str_replace('.', '_', $fname);

	$b1 = str2int($file, 0x18, 4); // palette
	$b2 = str2int($file, 0x1c, 4); // size
	$b3 = str2int($file, 0x20, 4); // offset (for decoded data)
	$b4 = str2int($file, 0x24, 4); // size
	$b5 = str2int($file, 0x28, 4); // data
	$b6 = str2int($file, 0x2c, 4); // size

	$pal = "";
	for ( $i=0; $i < $b2; $i += 3 )
		$pal .= substr($file, $b1+$i, 3) . BYTE;

	global $gp_bits, $gp_sub;
	$file .= ZERO . ZERO;

	// auto detect decompression type
	$pxsz = str2int($file, 0x10, 4);
	for ( $i=3; $i > 0; $i-- )
	{
		$pix = galpani_decode($file, $b5, $b5+$b6, $gp_bits[ $gp_sub[$i] ]);
		if ( strlen($pix) >= $pxsz )
		{
			$ty = $i;
			echo "TYPE : $ty , {$gp_sub[$ty]}\n";
			//file_put_contents("$fname.pix", $pix);
			break;
		}
	} // for ( $i=3; $i > 0; $i-- )

	$cnt = $b4 / 4;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $b3 + ($i*4);
		$p = str2int($file, $p, 4);

		$w  = str2int($pix, $p+0x38, 4);
		$sz = str2int($pix, $p+0x3c, 4);
		$h  = $sz / $w;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= grayclut(0x100);
		$clut .= substr($pix, $p+0x40, $sz);

		$fn = sprintf("$dir/%04d.clut", $i);
		save_file($fn, $clut);
	}
	return;
}
//////////////////////////////
function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 2) == "sc" )
	{
		if ( stripos($fname, '.msk') !== false )
			return sectmsk($file, $fname);
		else
			return sectsc($file, $fname);
	}
	if ( substr($file, 0, 4) == "sprt" )
		return sectsprt($file, $fname);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );
