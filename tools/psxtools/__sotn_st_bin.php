<?php
/*
 * prefixes
 *  NO3 RNO3  Castle Entrance
 *  NZ0 RNZ0  Alchemy Laboratory
 *  NO0 RNO0  Marble Gallery
 *  NO1 RNO1  Outer Wall
 *  LIB RLIB  Long Library
 *  NZ1 RNZ1  Clock Tower
 *  DAI RDAI  Royal Chapel
 *  TOP RTOP  Castle Keep
 *  ARE RARE  Colosseum
 *  NO2 RNO2  Olrox Quarters
 *  NO4 RNO4  Underground Caverns
 *  CHI RCHI  Abandoned Mine
 *  CAT RCAT  Catacombs
 *  CEN RCEN  Castle Center
 *  WRP RWRP  warp room
 *
 * Special Thanks to:
 *   Zone File Technical Documentation by Nyxojaele (Dec 26, 2010)
 *   romhacking.net/documents/528/
 */
require "common.inc";

// map files are loaded to RAM 80180000
// offsets here are RAM pointers
function ramint( &$file, $pos )
{
	$int = str2int($file, $pos, 3);
	if ( $int )
		$int -= 0x180000;
	else
		printf("ERROR ramint zero @ %x\n", $pos);
	return $int;
}
//////////////////////////////
function sect1()
{
	return;
}
//////////////////////////////
function sotn( $fname )
{
	// for /st/xxx/xxx.bin   and /st/xxx/f_xxx.bin   pair
	// for /boss/xxx/xxx.bin and /boss/xxx/f_xxx.bin pair
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	if ( ! file_exists("$pfx.bin"  ) )  return;
	if ( ! file_exists("f_$pfx.bin") )  return;

	$meta = file_get_contents("$pfx.bin");
	$file = file_get_contents("f_$pfx.bin");

	$off1 = ramint($meta, 0x10);
	$off2 = ramint($meta, 0x20);
	$off3 = ramint($meta, $off2);

	$ed = $off2;
	$st = $off3;
	while ( $st < $ed )
	{
		$tpos = ramint($meta, $st+0);
		$tmet = ramint($meta, $st+4);
		$t1 = str2int($meta, $st+8, 4);
		$t2 = str2int($meta, $st+12, 4);

		printf("%6x , %6x , %6x , %8x , %8x\n", $st, $tpos, $tmet, $t1, $t2);
		$st += 0x10;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );

/*
 * dra.bin       is loaded @ 800a0000
 * st/xxx.bin    is loaded @ 80180000 -> 8003c77c
 * bin/arc_f.bin is loaded @
 * bin/ric.bin   is loaded @
 *
 * bin/weapon.bin , 0x7000/block , 256x128 4-bit , +0x4000 = meta
 * lbaini.php -lba  dra.bin 3cac-4a6c cb3c-cd1c  st/sel/sel.bin b0dc-b0fc
 *
 */
