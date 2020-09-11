<?php
require "common.inc";

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
		$st = 0;
		$ed = 0x800;
		while ( $st < $ed )
		{
			if ( substr($file, $st, 3) == "RAM" )
				return substr($file, $st + 12, 0x200000);
			$st += 4;
		}
		return "";
	}

	// no$psx PlayStation emulator (Windows)
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
				case "MRAM":
				case "VRAM":
				case "SRAM":
				case "HC05":
				case "SECT":
				case "CDDA":
				case "STOP":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					save_file("$base/$mgc", substr($file, $st+12, $len));
					$st += ($len + 12);
					break;
				default:
					printf("%8x , $mgc\n", $st);
					return "";
			} // switch ( $mgc )
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
				case "RAM2":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					save_file("$base/$mgc", substr($file, $st+12, $len));
					$st += ($len + 12);
					break;
				default:
					printf("%8x , $mgc\n", $st);
					return "";
			} // switch ( $mgc )
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
			$mgc = substr($file, $st+0, 4);
			switch ( $mgc )
			{
				case "CART":
				case "CS2 ":
				case "MSH2":
				case "SSH2":
				case "SCSP":
				case "SCU ":
				case "VDP1":
				case "SMPC":
				case "VDP2":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					save_file("$base/$mgc", substr($file, $st+12, $len));
					$st += ($len + 12);
					break;
				case "OTHR":
					$len = str2int($file, $st+8, 4);
					printf("%8x , %8x , $mgc\n", $st, $len);
					$st += (12 + 0x10000);
					$sz = $len - 0x10000;
					$ram = "";
					for ( $i=0; $i < $sz; $i += 2 )
						$ram .= $file[$st+$i+1] . $file[$st+$i+0];
					return $ram;
				default:
					printf("%8x , $mgc\n", $st);
					return "";
			} // switch ( $mgc )
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
			$mgc = substr0($file, $st);
			$len = str2int($file, $st+12, 4);
			printf("%8x , %8x , $mgc\n", $st, $len);
			save_file("$base/$mgc", substr($file, $st+16, $len));
			$st = int_ceil($st + $len + 16, 16);
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
