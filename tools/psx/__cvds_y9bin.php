<?php
require "common.inc";

//////////////////////////////
function arm9bin( $dir, $st, $ed, $blk )
{
	$arm9 = file_get_contents("$dir/arm9.bin");
	if ( empty($arm9) )  return;

	$id = 0;
	while ( $st < $ed )
	{
		$off  = str2int($arm9, $st+0, 4);
		$path = substr0($arm9, $st+6);
		printf("%4x , %8x , %s\n", $id, $off, $path);

		$id++;
		$st += $blk;
	}
	return;
}

function y9bin( $dir )
{
	$y9 = file_get_contents("$dir/y9.bin");
	if ( empty($y9) )  return;

	$ed = strlen($y9);
	$st = 0;
	while ( $st < $ed )
	{
		$oid = str2int($y9, $st + 0x00, 4);
		$off = str2int($y9, $st + 0x04, 4);
		$siz = str2int($y9, $st + 0x08, 4);
		$bss = str2int($y9, $st + 0x0c, 4);
		$pst = str2int($y9, $st + 0x10, 4) - $off;
		$ped = str2int($y9, $st + 0x14, 4) - $off;
		$fid = str2int($y9, $st + 0x18, 4);
		printf("%2x , %4d , %8x , %8x\n", $oid, $fid , $off, $off+$siz);

		$st += 0x20;
	}
	return;
}
//////////////////////////////
function cvds_ooee( $dir ) // us order of ecclesia
{
	echo "DETECT : CV Order of Ecclesia US\n";
	y9bin($dir);
	arm9bin($dir, 0xd8cec, 0xeca0c, 0x20);
	return;
}

function cvds_pore( $dir ) // portrait of ruins
{
	echo "DETECT : CV Portrait of Ruins US\n";
	y9bin($dir);
	arm9bin($dir, 0xcdafc, 0xdf15c, 0x20);
	return;
}

function cvds_dose( $dir ) // dawn of sorrow
{
	echo "DETECT : CV Dawn of Sorrow US\n";
	y9bin($dir);
	arm9bin($dir, 0x8cc6c, 0x9a0c4, 0x28);
	return;
}

function cvds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 0, 0x10);
	if ( $mgc == "CASTLEVANIA1ACVE" )
		return cvds_dose( $dir );
	if ( $mgc == "CASTLEVANIA2ACBE" )
		return cvds_pore( $dir );
	if ( $mgc == "CASTLEVANIA3YR9E" )
		return cvds_ooee( $dir );

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

// DS RAM = 4 MB ( 0x2000000-0x2400000 )
