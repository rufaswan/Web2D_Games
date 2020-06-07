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
function sotn_decode( &$meta, $dir, $off )
{
	// sub_800eb398-800eb614 , SLPM_860.23/DRA.BIN
	// st/top/top.bin , 80183538 -> 801ffe18 -> VRAM
	echo "=== begin sub_800eb398 ===\n";
	$dn = "$dir/$off.dec";

	$a0 = substr($meta, $off, 8);
		$off += 8;
	$v0 = array();
	$dec = "";
	while (1)
	{
		while ( count($v0) < 8 )
		{
			$b = ord( $meta[$off] );
				$off++;
			$v0[] = $b >> 4;
			$v0[] = $b & 0x0f;
		}

		$flg = array_shift($v0);
		switch ( $flg )
		{
			case 0:
				$b1 = array_shift($v0);
				$b2 = array_shift($v0);
				$b = ($b1 << 4) + $b2 + 19;
				$dec .= str_pad('', $b, ZERO);
				break;
			case 2:
				$b1 = array_shift($v0);
				$dec .= chr($b1) . chr($b1);
				break;

			case 4:
				$b1 = array_shift($v0);
				$dec .= chr($b1);
			case 3:
				$b1 = array_shift($v0);
				$dec .= chr($b1);
			case 1:
				$b1 = array_shift($v0);
				$dec .= chr($b1);
				break;

			case 5:
				$b1 = array_shift($v0);
				$b2 = array_shift($v0);
				$dec .= str_pad('', $b2+3, chr($b1));
				break;
			case 6:
				$b1 = array_shift($v0);
				$dec .= str_pad('', $b1+3, ZERO);
				break;

			case 7:
			case 8:
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
			case 14:
				$b = ord( $a0[$flg-7] );
				$b1 = $b >> 4;
				$b2 = $b & 0x0f;
				if ( $b1 == 2 )
					$dec .= chr($b2) . chr($b2);
				else
				if ( $b1 == 1 )
					$dec .= chr($b2);
				else
				if ( $b1 == 6 )
					$dec .= str_pad('', $b2+3, ZERO);
				break;

			case 15:
				break 2;
		}
	} // while (1)

	echo "=== end sub_800eb398 ===\n";
	file_put_contents($dn, $dec);
	return $dec;
}
//////////////////////////////
function sotn( $pfx )
{
	if ( ! is_dir($pfx) )
		return;

	// DO WE NEED f_xxx.bin ???
	$fn1 = "$pfx/$pfx.bin";
	if ( ! file_exists($fn1) )  return;

	$meta = file_get_contents($fn1);
	$dir  = $pfx;

	//$off1  = ramint($meta, 0x00); // func() entity attack?
	//$off2  = ramint($meta, 0x04); // func() respawn entity
	//$off3  = ramint($meta, 0x08); // func() respawn screen check by frames
	//$off4  = ramint($meta, 0x0c); // func() respawn room check
	$off5  = ramint($meta, 0x10); // zone layout
	$off6  = ramint($meta, 0x14); // entity sprite
	//$off7  = ramint($meta, 0x18);
	//$off8  = ramint($meta, 0x1c);
	$off9  = ramint($meta, 0x20); // zone layout def
	$off10 = ramint($meta, 0x24); // entity sprite def
	//$off11 = ramint($meta, 0x28); // func() entity AI?
	//$off12 = ramint($meta, 0x2c); // <- $off6 can refer here = are/cat/dai.bin ...

	$ed = $off10;
	$st = ramint($meta, $off10);
	while ( $st < $ed )
	{
		if ( $meta[$st+3] == chr(0x80) )
		{
			$off = ramint($meta, $st);
			$dec = sotn_decode($meta, $dir, $off);
		}
		$st += 4;
	} // while ( $st < $ed )

}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );

