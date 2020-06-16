<?php
require "common.inc";

function ramfile( &$ram, $off, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen($file);
	while ( strlen($ram) < ($off+$len) )
		$ram .= ZERO;

	for ( $i=0; $i < $len; $i++ )
		$ram[$off+$i] = $file[$i];
	return;
}
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

function y9ram( $dir )
{
	$ram = "";
	ramfile($ram, 0, "$dir/arm9.bin");

	$y9 = file_get_contents("$dir/y9.bin");
	$ed = strlen($y9);
	$st = 0;
	while ( $st < $ed )
	{
		$oid = str2int($y9, $st + 0x00, 4);
		$off = str2int($y9, $st + 0x04, 3);
		$siz = str2int($y9, $st + 0x08, 4);
		$fn = sprintf("$dir/overlay/overlay_%04d.bin", $oid);
		ramfile($ram, $off, $fn);

		$st += 0x20;
	}
	file_put_contents("$dir/ram.bin", $ram);
	return;
}

function overlaystr( $dir, $id, $base, $padd = false )
{
	$fname = sprintf("$dir/overlay/overlay_%04d.bin", $id);
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	printf("=== $fname\n");
	$term = chr(0xea);

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$bak = $st;
		if ( $file[$st] == ZERO )
			break;
		$st += 2;
		$str = "";
		while ( $file[$st] != $term )
		{
			if ( $file[$st] == chr(0xe6) )
				$str .= '\n';
			else
			{
				$b = ord( $file[$st] );
				$b += 0x20;
				$str .= chr($b);
			}
			$st++;
		}
		$st++;
		if ( $padd )
		{
			while ( $st % 4 )
				$st++;
		}
		printf("%6x , %4x , %s\n", $base+$bak, $bak, $str);
	}
	return;
}
//////////////////////////////
function cvnds_ooee( $dir ) // us order of ecclesia
{
	echo "DETECT : CV Order of Ecclesia US\n";
	y9bin($dir);
	y9ram($dir);
	overlaystr($dir, 0, 0x1dd280);
	overlaystr($dir, 1, 0x1dd280);
	arm9bin($dir, 0xd8cec, 0xeca0c, 0x20);
	return;
}

function cvnds_pore( $dir ) // portrait of ruins
{
	echo "DETECT : CV Portrait of Ruins US\n";
	y9bin($dir);
	y9ram($dir);
	overlaystr($dir, 1, 0x21f680, true);
	overlaystr($dir, 2, 0x21f680, true);
	arm9bin($dir, 0xcdafc, 0xdf15c, 0x20);
	return;
}

function cvnds_dose( $dir ) // dawn of sorrow
{
	echo "DETECT : CV Dawn of Sorrow US\n";
	y9bin($dir);
	y9ram($dir);
	arm9bin($dir, 0x8cc6c, 0x9a0c4, 0x28);
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 0, 0x10);
	if ( $mgc == "CASTLEVANIA1ACVE" )
		return cvnds_dose( $dir );
	if ( $mgc == "CASTLEVANIA2ACBE" )
		return cvnds_pore( $dir );
	if ( $mgc == "CASTLEVANIA3YR9E" )
		return cvnds_ooee( $dir );

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

// DS RAM = 4 MB ( 0x2000000-0x2400000 )

/*
	//   0      1cdf60-21f420
	//   1-2    21f680-22c840  STR
	//   3-4    22c840-29d040
	//   5      2b2a80-2b2ae0
	//   6      2b2ae0-2b7660
	//   7      2b7660-2d0f40  SPR CLUT
	//   8      2d0f40-2d7500  CLUT
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

POR
  1182-1189  49e-4a5  f_imo1-8.dat
  1972       7b4      p_imo.dat
  221fee0    Loretta (overlay_1)
  2226bdc    Stella's twin sister. She's a powerful\nwitch. (overlay_1)
  e5 e9
  e7 06 e3 24
  e2 01 e7 06 e3 25
  e5 e9 e3 29 33 0d

  e7 06 e3 28
  e7 05 e3 21

  28e306e7
   AA TTA

Start
jonathan
	tpm.f  364000 -> 132074,13229c,2d0c14(ov7)
	tpm.p  353200 -> 1282b8,132294,37fb2f
charlotte
	tpf.f  392000 -> 132084,1322a0,2d0d28(ov7)
	tpf.p  35b900 -> 132298

132260 -> 11f14,12330,12434
	308ec0  HPXE@
	340e00  font/ld_font_u8.dat
	344600  sc2/wp75.f
	346600  -
	348600  so/wp91/a1/a4.p
	34a600  so/wp99.p
	34c600  -
	34e600  -
	350600  -
	351c00  -
	353200  so/tpm.p
	35b900  so/tpf.p
	364000  sc2/tpm.f
	392000  sc2/tpf.f

132070 -> 109ac,10ab8,10b3c

*/
