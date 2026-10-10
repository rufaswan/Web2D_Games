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
tool::require('class-isoecc');

$gp_fp = 0;

function read_iso( array &$iso, int $lba ) : string
{
	list($head,$sect,$cd001) = $iso;
	$is_m2 = ( $cd001 & 0x0f );

	global $gp_fp;
	$pos = $head + ($lba * $sect);
	fseek($gp_fp, $pos, SEEK_SET);
	$bin = fread($gp_fp, 0x100 * $sect);

	$ecc = new isoecc;
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
