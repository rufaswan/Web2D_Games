<?php
require "common.inc";

// extract RAM section from uncompressed save states
function subram( &$file )
{
	// ePSXe Windows + Linux
	if ( substr($file, 0, 5) == "ePSXe" )
	{
		echo "DETECT emulator = ePSXe\n";
		return substr($file, 0x1ba, 0x200000);
	}

	// pSXfin Windows + Linux
	if ( substr($file, 0, 7) == "ARS2CPU" || substr($file, 0, 6) == "ARSCPU" )
	{
		echo "DETECT emulator = pSXfin\n";
		$st = 0;
		$ed = 0x800;
		while ( $st < $ed )
		{
			if ( substr($file, $st, 3) == "RAM" )
				return substr($file, $st + 0xc, 0x200000);
			$st += 4;
		}
		return "";
	}

	// no$psx Windows
	if ( substr($file, 0, 15) == 'NO$PSX SNAPSHOT' )
	{
		echo "DETECT emulator = nocash PSX\n";
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$mgc = substr($file, $st+0, 4);
			switch ( $mgc )
			{
				case "CORE":
				case "BIOS":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					$st += ($len + 12);
					break;
				case "MRAM":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					return substr($file, $st + 12, $len);
				default:
					printf("%8x , $mgc\n", $st);
					return "";
			} // switch ( $mgc )
		} // while ( $st < $ed )
		return "";
	}

	// no$gba Windows
	if ( substr($file, 0, 15) == 'NO$GBA SNAPSHOT' )
	{
		echo "DETECT emulator = nocash GBA\n";
		$ed = strlen($file);
		$st = 0x40;
		while ( $st < $ed )
		{
			$mgc = substr($file, $st+0, 4);
			switch ( $mgc )
			{
				case "VAL2":
				case "IOP7":
				case "DTCM":
				case "ITCM":
				case "RAMS":
				case "RAM7":
				case "WIFI":
				case "VALS":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					$st += ($len + 12);
					break;
				case "RAM2":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					return substr($file, $st + 12, $len);
				default:
					printf("%8x , $mgc\n", $st);
					return "";
			} // switch ( $mgc )
		} // while ( $st < $ed )
		return "";
	}

	return "";
}

function psxram( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ram = subram($file);
	if ( empty($ram) )  return;

	$base = str_replace('.', '_', $fname);
	save_file("$base.ram", $ram);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxram( $argv[$i] );
