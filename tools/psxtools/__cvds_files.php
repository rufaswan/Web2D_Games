<?php
require "common.inc";

//////////////////////////////
/*
	rambc($ram, $dir, 0xbf9fc, $ofn, $blk);
function rambc( &$ram, $dir, $oset, $ofns, $blk)
{
	printf("=== rambc( $dir , %x , %x , %x )\n", $oset, $ofns, $blk);
	while(1)
	{
		$off = str2int($ram, $oset, 3);
			$oset += 4;
		if ( $off == 0 )
			break;

		printf("set = %x\n", $off);
		$set = array();
		while(1)
		{
			$id = str2int($ram, $off+0, 3);
				$off += 4;
			if ( $id == BIT24 )
				break;

			$ps = $ofns + ($id * $blk) + 6;
			$fn = substr0($ram, $ps);

			printf("  $fn\n");
			$set[] = $fn;
		}
		//echo implode(' , ', $set) . "\n";
	}
	return;
}
*/

function ramspr( &$ram, $dir, $oset, $ofns, $blk)
{
	printf("=== ramspr( $dir , %x , %x , %x )\n", $oset, $ofns, $blk);
	$sid = 0;
	while(1)
	{
		$bak = $oset;
		$off = str2int($ram, $oset, 3);
			$oset += 4;
		if ( $off == 0 )
			break;

		printf("set_%d @ %x = %x\n", $sid, $bak, $off);
		$set = array();
		while(1)
		{
			$id = str2int($ram, $off+0, 3);
			// 1 = f_*.dat  t_*.dat
			// 2 = p_*.dat  *.nsbmd  *.nsbtx
			$ty = str2int($ram, $off+4, 3);
				$off += 8;
			if ( $id == BIT24 )
				break;

			$ps = $ofns + ($id * $blk) + 6;
			$fn = substr0($ram, $ps);

			printf("  $ty $fn\n");
			if ( strpos($fn, ".nsb") === false )
				$set[$ty][] = $fn;
		}
		$sid++;
		//echo implode(' , ', $set) . "\n";
	}
	echo "\n";
	return;
}
//////////////////////////////
function cvds_ooee( &$ram, $dir ) // us order of ecclesia
{
	echo "DETECT : CV Order of Ecclesia US\n";

	//arm9bin($dir, 0xd8cec, 0xeca0c, 0x20);
	return;
}

function cvds_pore( &$ram, $dir ) // portrait of ruins
{
	echo "DETECT : CV Portrait of Ruins US\n";
	$ofn = 0xcdafc;
	$blk = 0x20;
	ramspr($ram, $dir, 0xcd88c, $ofn, $blk);

	$off = 0xdfa50;
	while(1)
	{
		$ost = str2int($ram, $off, 3);
			$off += 4;
		if ( $ost == 0 )
			break;
		ramspr($ram, $dir, $ost, $ofn, $blk);
	}

	ramspr($ram, $dir, 0xe19dc, $ofn, $blk);

	//   0      1cdf60-21f420
	//   1-2    21f680-22c840  STR
	//   3-4    22c840-29d040
	//   5      2b2a80-2b2ae0
	//   6      2b2ae0-2b7660
	//   7      2b7660-2d0f40
	//   8      2d0f40-2d7500
	//   9-24   2d7500-2d7900
	//  25      2d7900-2e00a0
	//  40-77   2d7900-2e8820
	//  26-39   2e01a0-2e4f60
	// 118      2e04a0-2e3ac0
	//  78-117  2e8820-3076a0  MAP
	overlay($ram, $dir, 0);
	overlay($ram, $dir, 5);
	overlay($ram, $dir, 6);
	overlay($ram, $dir, 7);
	overlay($ram, $dir, 8);
	return;
}

function cvds_dose( &$ram, $dir ) // dawn of sorrow
{
	echo "DETECT : CV Dawn of Sorrow US\n";
	//arm9bin($dir, 0x8cc6c, 0x9a0c4, 0x28);
	return;
}
//////////////////////////////
function overlay( &$ram, $dir, $oid )
{
	$ofn = sprintf("$dir/overlay/overlay_%04d.bin", $oid);
	$bin = file_get_contents($ofn);
	if ( empty($bin) )  return;

	$y9 = file_get_contents("$dir/y9.bin");
	$p = ($oid * 0x20) + 4;
	$off = str2int($y9, $p, 3);

	strupd($ram, $off, $bin);
	file_put_contents("$dir/ram.bin", $ram);
	return;
}

function ndsram( &$head, $dir )
{
	$ram = strpad( 0x400000 );

	$off = str2int($head, 0x28, 3);
	$bin = file_get_contents("$dir/arm9.bin");
	strupd($ram, $off, $bin);

	$off = str2int($head, 0x38, 3);
	$bin = file_get_contents("$dir/arm7.bin");
	strupd($ram, $off, $bin);

	file_put_contents("$dir/ram.bin", $ram);
	return $ram;
}

function cvds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 0, 0x10);
	$func = "";
	if ( $mgc == "CASTLEVANIA1ACVE" )
		$func = "cvds_dose";
	if ( $mgc == "CASTLEVANIA2ACBE" )
		$func = "cvds_pore";
	if ( $mgc == "CASTLEVANIA3YR9E" )
		$func = "cvds_ooee";

	if ( ! function_exists($func) )
		return;

	$ram = ndsram($head, $dir);
	$func($ram, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

// DS RAM = 4 MB ( 0x2000000-0x2400000 )
