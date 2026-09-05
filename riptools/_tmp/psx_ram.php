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
tool::require('class-clutfile');
tool::require('func-console');

function psxvram2clut( string &$vram, string $pfx ) : void
{
	$img = new clutdata;

	// 16-bpp
	$img->w = 0x400;
	$img->h = 0x200;
	$img->pal = '';
	$img->pix = psx::pal555($vram);
	clutfile::save($pfx.'/vram-16', $img);

	// 8-bpp
	$img->w = 0x800;
	$img->h = 0x200;
	$img->pal = clutfile::graypal(0x100);
	$img->pix = $vram;
	clutfile::save($pfx.'/vram-8', $img);

	// 4-bpp
	$img->w = 0x1000;
	$img->h = 0x200;
	$img->pal = clutfile::graypal(0x10);
	$img->pix = psx::bpp4to8($vram);
	clutfile::save($pfx.'/vram-4', $img);
}

// extract RAM section from uncompressed save states
function subram( string &$file, string $pfx ) : string
{
	// ePSXe PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 5) === 'ePSXe' )
	{
		tool::trace('DETECT emulator = ePSXe');
		$sub = tool::substr($file, 0x2733df, 0x100000);
		psxvram2clut($sub, $pfx);

		return tool::substr($file, 0x1ba, 0x200000);
	}

	// pSXfin PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 7) === 'ARS2CPU' || substr($file, 0, 6) === 'ARSCPU' )
	{
		tool::trace('DETECT emulator = pSXfin');
		$pos = strpos($file, "\xff\x00UPG\xbb\x00\x10");
		$sub = tool::substr($file, $pos+0xc8, 0x100000);
		psxvram2clut($sub, $pfx);

		$pos = strpos($file, "RAM\x00");
		return tool::substr($file, $pos + 12, 0x200000);
	}

	// no$psx PlayStation emulator (Windows)
	if ( substr($file, 0, 15) === 'NO$PSX SNAPSHOT' )
	{
		tool::trace('DETECT emulator = nocash PSX');
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr($file, $st+0, 4);
			$len = tool::ordstr($file, $st+8, 4);
				$st += ($len + 12);
			tool::trace($bak, $len, $mgc);

			$sub = tool::substr($file, $bak+12, $len);
			tool::save("$pfx/$mgc", $sub);

			if ( $mgc == 'VRAM' )
				psxvram2clut($sub, $pfx);
		} // while ( $st < $ed )
		return '';
	}

	// no$gba Gameboy Advance + Nintendo DS emulator (Windows)
	if ( substr($file, 0, 15) === 'NO$GBA SNAPSHOT' )
	{
		tool::trace('DETECT emulator = nocash GBA');
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr($file, $st+0, 4);
			$len = tool::ordstr($file, $st+8, 4);
				$st += ($len + 12);
			tool::trace($bak, $len, $mgc);

			$sub = tool::substr($file, $bak+12, $len);
			tool::save("$pfx/$mgc", $sub);
		} // while ( $st < $ed )
		return '';
	}

	// Yabause Saturn emulator (Linux)
	if ( substr($file, 0, 3) === 'YSS' )
	{
		tool::trace('DETECT emulator = Yabause');
		$ed = strlen($file);
		$st = 0x14;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = substr($file, $st+0, 4);
			$len = tool::ordstr($file, $st+8, 4);
				$st += ($len + 12);
			tool::trace($bak, $len, $mgc);

			$sub = tool::substr($file, $bak+12, $len);
			tool::save("$pfx/$mgc", $sub);

			if ( $mgc == 'OTHR' )
			{
				$sub = substr($sub, 0x10000);
				$len = strlen($sub);
				$ram = '';
				for ( $i=0; $i < $len; $i += 2 )
					$ram .= $sub[$i+1] . $sub[$i+0];
				return $ram;
			}
		} // while ( $st < $ed )
		return '';
	}

	// Neko Project II PC98 emulator (Linux)
	if ( substr($file, 0, 15) === 'Neko Project II' )
	{
		tool::trace('DETECT emulator = Neko Project II');
		$ed = strlen($file);
		$st = 0x30;
		while ( $st < $ed )
		{
			$bak = $st;
			$mgc = tool::substr0($file, $st);
			$len = tool::ordstr($file, $st+12, 4);
				$st = tool::int_ceil($st + $len + 16, 16);
			tool::trace($bak, $len, $mgc);

			$sub = tool::substr($file, $bak+16, $len);
			tool::save("$pfx/$mgc", $sub);
		} // while ( $st < $ed )
		return '';
	}

	return '';
}

function psxram( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = preg_replace('|[^a-zA-Z0-9]|', '_', $fname);
	$ram = subram($file, $pfx);

	if ( ! empty($ram) )
		tool::save("$pfx.ram", $ram);
}

for ( $i=1; $i < $argc; $i++ )
	psxram( $argv[$i] );
