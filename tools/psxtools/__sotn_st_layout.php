<?php
/*
 * prefixes
 *  no0 rno0  Marble Gallery
 *  no1 rno1  Outer Wall
 *  no2 rno2  Olrox Quarters
 *  no3 rno3  Castle Entrance
 *  no4 rno4  Underground Caverns
 *  nz0 rnz0  Alchemy Laboratory
 *  nz1 rnz1  Clock Tower
 *  are rare  Colosseum
 *  cat rcat  Catacombs
 *  cen rcen  Castle Center
 *  chi rchi  Abandoned Mine
 *  dai rdai  Royal Chapel
 *  lib rlib  Long Library
 *  top rtop  Castle Keep
 *  wrp rwrp  warp room
 *
 * Special Thanks to:
 *   Zone File Technical Documentation by Nyxojaele (Dec 26, 2010)
 *   romhacking.net/documents/528/
 */
require "common.inc";

//define("DRY_RUN", true);

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
function layout_spr( &$meta, $roomfunc )
{
	printf("=== layout_spr( %x )\n", $roomfunc);
	// 18 80 01 3c  lui  $at, 0x8018
	// 21 08 24 00  addu $at, $a0
	// 40 07 25 8c  lw   $a1, 0x740($at)
	$off = -0x180000;
	$op3c = false;
	$op8c = false;
	$st = $roomfunc;
	while ( ! $op3c || ! $op8c )
	{
		if ( $meta[$st+3] == chr(0x3c) )
		{
			$op3c = true;
			$off += ( ord($meta[$st+0]) << 16 );
		}

		if ( $meta[$st+3] == chr(0x8c) )
		{
			$op8c = true;
			$off += str2int($meta, $st+0, 2);
		}
		$st += 4;
	}


}
//////////////////////////////
function sotn( $pfx )
{
	if ( ! is_dir($pfx) )
		return;

	// for /st/xxx/xxx.bin
	// for /boss/xxx/xxx.bin
	$fn1 = "$pfx/$pfx.bin";
	if ( ! file_exists($fn1) )  return;

	$meta = file_get_contents($fn1);
	$dir  = $pfx;

	//$off1  = ramint($meta, 0x00); // func() entity attack?
	//$off2  = ramint($meta, 0x04); // func() respawn entity
	//$off3  = ramint($meta, 0x08); // func() respawn screen check by frames
	$off4  = ramint($meta, 0x0c); // func() respawn room check
	$off5  = ramint($meta, 0x10); // zone layout
	$off6  = ramint($meta, 0x14); // entity sprite
	//$off7  = ramint($meta, 0x18);
	//$off8  = ramint($meta, 0x1c);
	$off9  = ramint($meta, 0x20); // zone layout def
	$off10 = ramint($meta, 0x24); // entity sprite def
	//$off11 = ramint($meta, 0x28); // func() entity AI?
	//$off12 = ramint($meta, 0x2c); // <- $off6 can refer here = are/cat/dai.bin ...

	layout_spr($meta, $off4);

/*
CODE HERE
*/
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );

