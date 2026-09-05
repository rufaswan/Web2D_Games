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

/*
// php.ini memory_limit = 128M
define('WRITE_S', 0x80 << 20);

function expiso( $fp, $fname, $bksz, $bkhd, $skip )
{
	if ( $bksz == 0x800 )
		return;
	printf("== expiso( $fname , %x , %x , %x )\n", $bksz, $bkhd, $skip);

	$size = filesize($fname);
	$isop = fopen("$fname.iso", 'wb');
	$cache = '';
	for ( $i=$skip; $i < $size; $i += $bksz )
	{
		fseek($fp, $i + $bkhd, SEEK_SET);
		$cache .= fread($fp, 0x800);
		if ( strlen($cache) >= WRITE_S )
		{
			fwrite($isop, $cache);
			$cache = '';
		}
	}
	if ( strlen($cache) > 0 )
		fwrite($isop, $cache);
	fclose($isop);
	return;
}

function bin2iso( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$detect = [
		//    type               s-size  s-head  cd-head
		["iso/800+ 0"      , 0x800 ,    0  , 0      ],
		["sat/930+10"      , 0x930 , 0x10  , 0      ], // mode 1/930 , saturn bin
		["psx/930+18"      , 0x930 , 0x18  , 0      ], // mode 2/930 , psx bin
		["psx/920+ 8"      , 0x920 , 0x08  , 0      ], // mode 2/920
		["bin/990+10"      , 0x990 , 0x10  , 0      ], // mode 1/930 + sub/60 , mds + mdf
		["bin/990+18"      , 0x990 , 0x18  , 0      ], // mode 2/930 + sub/60

		["bin/930+10+  930", 0x930 , 0x10  , 0x930  ],
		["cvm/800+ 0+ 1800", 0x800 ,    0  , 0x1800 ],
		["cdi/800+ 0+4b000", 0x800 ,    0  , 0x4b000],
	];

	foreach ( $detect as $det )
	{
		list($type,$ssize,$shead,$cdhead) = $det;
		$p = $cdhead + ($ssize * 0x10) + $shead;

		fseek($fp, $p, SEEK_SET);
		$head = fread($fp, 0x800);
		if ( substr($head, 1, 5) == 'CD001' )
		{
			printf("DETECT %s , %x , %x , %x , %s\n", $type, $ssize, $shead, $cdhead, $fname);
			return expiso($fp, $fname, $ssize, $shead, $cdhead);
		}
	} // foreach ( $detect as $det )
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	bin2iso( $argv[$i] );

<?php

//////////////////////////////
//////////////////////////////
function mode1_ecc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$edc1 = substr  ($file, 0x810, 4);
	$edc2 = edc_calc($file, 0    , 0x810);
		printf("  edc = %s\n", bin2hex($edc1));
		printf("  edc = %s\n", bin2hex($edc2));

	$eccp1 = substr($file, 0x81c, 0xac);
	$eccq1 = substr($file, 0x8c8, 0x68);
	$eccpq = ecc_calc($file, 0xc, 0x10); // 81c
		printf("  ecc p = %s\n", bin2hex($eccp1));
		printf("  ecc p = %s\n", bin2hex($eccpq[0]));
		printf("  ecc q = %s\n", bin2hex($eccq1));
		printf("  ecc q = %s\n", bin2hex($eccpq[1]));
	return;
}
function mode2_ecc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$edc1 = substr  ($file, 0x818, 4);
	$edc2 = edc_calc($file, 0x10 , 0x818);
		printf("  edc = %s\n", bin2hex($edc1));
		printf("  edc = %s\n", bin2hex($edc2));

	$eccp1 = substr($file, 0x81c, 0xac);
	$eccq1 = substr($file, 0x8c8, 0x68);
	$eccpq = ecc_calc($file, -1, 0x10); // 81c
		printf("  ecc p = %s\n", bin2hex($eccp1));
		printf("  ecc p = %s\n", bin2hex($eccpq[0]));
		printf("  ecc q = %s\n", bin2hex($eccq1));
		printf("  ecc q = %s\n", bin2hex($eccpq[1]));
	return;
}

mode1_ecc('xeno-jp-lahan-trim-m1-2352.bin.9300');
mode2_ecc('xeno-jp-lahan-trim-m2-2352.bin.9300');

// cdmage subhead = all -- -- 08 --
// for mode 2 form 2 data / STR video or XA audio
// - edc data    = 0-91c (m2-920)  or 10-92c (m2-930)
// - no ecc data

function mode1_ecc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$edc1 = substr  ($file, 0x810, 4);
	$edc2 = edc_calc($file, 0    , 0x810);
		printf("  edc = %s\n", bin2hex($edc1));
		printf("  edc = %s\n", bin2hex($edc2));

	$eccp1 = substr($file, 0x81c, 0xac);
	$eccq1 = substr($file, 0x8c8, 0x68);
	$eccpq = ecc_calc($file, 0xc, 0x10); // 81c
		printf("  ecc p = %s\n", bin2hex($eccp1));
		printf("  ecc p = %s\n", bin2hex($eccpq[0]));
		printf("  ecc q = %s\n", bin2hex($eccq1));
		printf("  ecc q = %s\n", bin2hex($eccpq[1]));
	return;
}
function mode2_ecc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$edc1 = substr  ($file, 0x818, 4);
	$edc2 = edc_calc($file, 0x10 , 0x818);
		printf("  edc = %s\n", bin2hex($edc1));
		printf("  edc = %s\n", bin2hex($edc2));

	$eccp1 = substr($file, 0x81c, 0xac);
	$eccq1 = substr($file, 0x8c8, 0x68);
	$eccpq = ecc_calc($file, -1, 0x10); // 81c
		printf("  ecc p = %s\n", bin2hex($eccp1));
		printf("  ecc p = %s\n", bin2hex($eccpq[0]));
		printf("  ecc q = %s\n", bin2hex($eccq1));
		printf("  ecc q = %s\n", bin2hex($eccpq[1]));
	return;
}

*/

declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ecc');

$gp_fp = 0;

function read_iso( array &$iso, int $lba ) : string
{
	list($head,$sect,$cd001) = $iso;
	$is_m2 = ( $cd001 & 0x0f );

	global $gp_fp;
	$pos = $head + ($lba * $sect);
	fseek($gp_fp, $pos, SEEK_SET);
	$bin = fread($gp_fp, 0x100 * $sect);

	$ecc = new ecc;
	$dec = '';

	$len = strlen($bin);
	$pos = 0;
	$lb2 = 0;
	while ( $pos < $len )
	{
		if ( $is_m2 === 0 )
		{
			$p = $pos + $cd001 - 0x10;
			$sub = tool::substr($bin, $p, 0x930);
			$s = $ecc->mode1($sub);
		}
		else
		{
			$p = $pos + $cd001 - 8;
			$sub = tool::substr($bin, $p, 0x920);
			$s = $ecc->mode2($sub);
		}

		if ( empty($s) )
		{
			tool::trace('dummy lba', $lba+$lb2);
			$s = str_repeat(ZERO, 0x800);
		}
		$dec .= $s;

		$pos += $sect;
		$lb2++;
	} // while ( $pos < $len )

	return $dec;
}

function detect_iso() : array
{
	global $gp_fp;
	$head = [
		0       , // iso
		0x930   , // bin
		0x1800  , // cvm ps2
		0x4b000 , // cdi
	];
	foreach ( $head as $headv )
	{
		$sect = [
			0x800 , // iso
			0x920 , // mode2 without header
			0x930 , // mode1 + mode2
			0x990 , // mode1 + mode2
		];
		foreach ( $sect as $sectv )
		{
			$cd001 = [
				0    , // iso
				8    , // mode2 without header
				0x10 , // mode1
				0x18 , // mode2
			];
			foreach ( $cd001 as $cdv )
			{
				$pos = $headv + (0x10 * $sectv);
				fseek($gp_fp, $pos, SEEK_SET);
				$bin = fread($gp_fp, 2 * $sectv);
				if ( strlen($bin) !== (2*$sectv) )
					continue;

				if ( substr($bin,$cdv,6) !== "\x01CD001" )
					continue;
				$p = $sectv + $cdv;
				if ( substr($bin,$p+1,5) !== 'CD001' )
					continue;
				return [$headv , $sectv, $cdv];
			}
		} // foreach ( $sect as $sectv )
	} // foreach ( $head as $headv )

	return [];
}

function bin2iso( string $fname ) : int
{
	global $gp_fp;
	$gp_fp = fopen($fname, 'rb');
	if ( ! $gp_fp )
		return -1;

	$iso = detect_iso();
	if ( empty($iso) )
		return -1;

	list($head,$sect,$cd001) = $iso;
	tool::trace('detect', $head, $sect, $cd001);

	if ( $sect === 0x800 )
		return tool::trace('normal iso', $head, $fname);
	if ( $head > 0 )
	{
		fseek($gp_fp, 0, SEEK_SET);
		$bin = fread($gp_fp, $head);
		tool::save($fname.'.head', $bin);
	}

	$isop = fopen($fname.'.iso', 'wb');
	if ( ! $isop )
		return -1;

	$pos = 0;
	while (1)
	{
		$bin = read_iso($iso, $pos);
			$pos += 0x100;

		if ( empty($bin) )
			break;
		fseek($isop, 0, SEEK_END);
		fwrite($isop, $bin);
	} // while (1)

	return 0;
}

tool::argv_callback($argv, 'bin2iso');
