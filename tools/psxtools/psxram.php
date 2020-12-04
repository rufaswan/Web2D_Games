<?php
/*
[license]
[/license]
 */
require "common.inc";

function psxvram2clut( &$vram, $base )
{
	// 8-bpp
	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint(0x800, 4);
	$clut .= chrint(0x200, 4);
	$clut .= grayclut(0x100);
	$clut .= $vram;
	save_file("$base/vram-8.clut", $clut);

	// 4-bpp
	$clut = "CLUT";
	$clut .= chrint(0x10,   4);
	$clut .= chrint(0x1000, 4);
	$clut .= chrint(0x200,  4);
	$clut .= grayclut(0x10);
	for ( $i=0; $i < 0x100000; $i++ )
	{
		$b = ord( $vram[$i] );
		$b1 = ($b >> 0) & BIT4;
		$b2 = ($b >> 4) & BIT4;
		$clut .= chr($b1) . chr($b2);
	}
	save_file("$base/vram-4.clut", $clut);

	// 16-bpp
	$clut = "RGBA";
	$clut .= chrint(0x400, 4);
	$clut .= chrint(0x200, 4);
	$clut .= pal555($vram);
	save_file("$base/vram-16.clut", $clut);
	return;
}

// extract RAM section from uncompressed save states
function subram( &$file, $base )
{
	// ePSXe PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 5) == 'ePSXe' )
	{
		echo "DETECT emulator = ePSXe\n";
		return substr($file, 0x1ba, 0x200000);
	}

	// pSXfin PlayStation emulator (Windows + Linux)
	if ( substr($file, 0, 7) == "ARS2CPU" || substr($file, 0, 6) == "ARSCPU" )
	{
		echo "DETECT emulator = pSXfin\n";
		$pos = strpos($file, "RAM".ZERO);
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
	if ( substr($file, 0, 15) == "Neko Project II" )
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
