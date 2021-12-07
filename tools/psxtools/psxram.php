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

function psxvram2clut( &$vram, $base )
{
	// 16-bpp
	$img = array(
		'w'   => 0x400,
		'h'   => 0x200,
		'pix' => pal555($vram),
	);
	save_clutfile("$base/vram-16.clut", $img);

	// 8-bpp
	$img = array(
		'cc'  => 0x100,
		'w'   => 0x800,
		'h'   => 0x200,
		'pal' => grayclut(0x100),
		'pix' => $vram,
	);
	save_clutfile("$base/vram-8.clut", $img);

	// 4-bpp
	bpp4to8($vram);
	$img = array(
		'cc'  => 0x10,
		'w'   => 0x1000,
		'h'   => 0x200,
		'pal' => grayclut(0x10),
		'pix' => $vram,
	);
	save_clutfile("$base/vram-4.clut", $img);
	return;
}

// extract RAM section from uncompressed save states
function subram( &$file, $base )
{
	// ePSXe PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 5) == 'ePSXe' )
	{
		echo "DETECT emulator = ePSXe\n";
		$sub = substr($file, 0x2733df, 0x100000);
		psxvram2clut($sub, $base);

		return substr($file, 0x1ba, 0x200000);
	}

	// pSXfin PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 7) == 'ARS2CPU' || substr($file, 0, 6) == 'ARSCPU' )
	{
		echo "DETECT emulator = pSXfin\n";
		$pos = strpos($file, "\xff\x00UPG\xbb\x00\x10");
		$sub = substr($file, $pos+0xc8, 0x100000);
		psxvram2clut($sub, $base);

		$pos = strpos($file, "RAM\x00");
		return substr($file, $pos + 12, 0x200000);
	}

	// no$psx PlayStation emulator (Windows)
	if ( substr($file, 0, 15) == 'NO$PSX SNAPSHOT' )
	{
		echo "DETECT emulator = nocash PSX\n";
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr ($file, $st+0, 4);
			$len = str2int($file, $st+8, 4);
				$st += ($len + 12);
			printf("%8x , %8x , $mgc\n", $bak, $len);

			$sub = substr($file, $bak+12, $len);
			save_file("$base/$mgc", $sub);

			if ( $mgc == 'VRAM' )
				psxvram2clut($sub, $base);
		} // while ( $st < $ed )
		return "";
	}

	// no$gba Gameboy Advance + Nintendo DS emulator (Windows)
	if ( substr($file, 0, 15) == 'NO$GBA SNAPSHOT' )
	{
		echo "DETECT emulator = nocash GBA\n";
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr ($file, $st+0, 4);
			$len = str2int($file, $st+8, 4);
				$st += ($len + 12);
			printf("%8x , %8x , $mgc\n", $bak, $len);

			$sub = substr($file, $bak+12, $len);
			save_file("$base/$mgc", $sub);
		} // while ( $st < $ed )
		return "";
	}

	// Yabause Saturn emulator (Linux)
	if ( substr($file, 0, 3) == 'YSS' )
	{
		echo "DETECT emulator = Yabause\n";
		$ed = strlen($file);
		$st = 0x14;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr ($file, $st+0, 4);
			$len = str2int($file, $st+8, 4);
				$st += ($len + 12);
			printf("%8x , %8x , $mgc\n", $bak, $len);

			$sub = substr($file, $bak+12, $len);
			save_file("$base/$mgc", $sub);

			if ( $mgc == 'OTHR' )
			{
				$sub = substr($sub, 0x10000);
				$len = strlen($sub);
				$ram = "";
				for ( $i=0; $i < $len; $i += 2 )
					$ram .= $sub[$i+1] . $sub[$i+0];
				return $ram;
			}
		} // while ( $st < $ed )
		return "";
	}

	// Neko Project II PC98 emulator (Linux)
	if ( substr($file, 0, 15) == 'Neko Project II' )
	{
		echo "DETECT emulator = Neko Project II\n";
		$ed = strlen($file);
		$st = 0x30;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr0($file, $st);
			$len = str2int($file, $st+12, 4);
				$st = int_ceil($st + $len + 16, 16);
			printf("%8x , %8x , $mgc\n", $bak, $len);

			$sub = substr($file, $bak+16, $len);
			save_file("$base/$mgc", $sub);
		} // while ( $st < $ed )
		return "";
	}

	return "";
}

function psxram( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$base = preg_replace("|[^a-zA-Z0-9]|", '_', $fname);
	$ram = subram($file, $base);

	if ( ! empty($ram) )
		save_file("$base.ram", $ram);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxram( $argv[$i] );
