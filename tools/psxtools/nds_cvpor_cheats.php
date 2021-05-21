<?php
/*
[license]
[/license]
 */
require "common.inc";

//////////////////////////////
function cvnds_dos_mon( $fp, $pos )
{
	printf("= cvnds_dos_mon( %x )\n", $pos);
	$siz = 118 * 0x24; // 0x1098 = 116+2 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sr = ZERO; // in-game max = 0x40 , boss = 0
	$dr = BYTE; // in-game max = 0x08
	for ( $i=0; $i < 118; $i++ )
	{
		// $i >= 101 == bosses
		$p = $i * 0x24;

		// 0 1 2 3  4 5 6 7  8 9   a b   c d  e f
		// func     func     drop  drop  - -  hp
		// 10 11  12 13  14      15   16   17    18 19  1a    1b    1c 1d 1e 1f
		// mp     exp    rarity  atk  def  rate  -  -   soul  cost  weak
		// 20 21 22 23
		// half
		//
		// soul rarity (get++) , stars (rare--) , zero = boss/100%
		// drop 1 = (rate * 3) / (1024 - LUCK)
		// drop 2 = 256 / (1024 - LUCK)
		//
		$mon[$p+0x12] = BYTE; // experience points
		$mon[$p+0x14] = $sr;  // soul drop rate
		$mon[$p+0x17] = $dr;  // item 1 drop rate
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}

function cvnds_por_mon( $fp, $pos )
{
	printf("= cvnds_por_mon( %x )\n", $pos);
	$siz = 155 * 0x20; // 0x1360 = 155 mon * 32 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sp = "\x63"; // in-game max = 0x63
	$dr = "\x80"; // in-game max = 0x32
	for ( $i=0; $i < 155; $i++ )
	{
		// $i >= 129 == bosses
		$p = $i * 0x20;

		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  sp  hp
		// 10 11 12  13   14   15   16    17    18 19 1a 1b  1c 1d 1e 1f
		// exp       atk  def  int  rate  rate  weak         half
		//
		// drop rate (get++) , stars (rare--)
		// weak/half (all=FF 07)
		//
		$mon[$p+0x0d] = $sp;  // skill points/jonathan
		$mon[$p+0x10] = BYTE; // experience points
		$mon[$p+0x16] = $dr;  // item 1 drop rate
		$mon[$p+0x17] = $dr;  // item 2 drop rate
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}

function cvnds_ooe_mon( $fp, $pos )
{
	printf("= cvnds_ooe_mon( %x )\n", $pos);
	$siz = 121 * 0x24; // 0x1104 = 121 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$gr = "\x64"; // in-game max = 0x64
	$dr = "\xff"; // in-game max = 0x0f
	for ( $i=0; $i < 121; $i++ )
	{
		// $i >= 108 == bosses
		$p = $i * 0x24;

		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  ap  hp
		// 10 11  12 13  14 15  16      17   18   19   1a    1b    1c 1d 1e 1f
		// exp    -  -   glyph  rarity  atk  def  int  rate  rate  weak
		// 20 21 22 23
		// half
		//
		// glyph rarity (get++) , stars (rare--)
		// drop  rate   (get++) , stars (rare--)
		//
		$mon[$p+0x10] = BYTE; // experience points
		$mon[$p+0x16] = $gr;  // gryph drop rate
		$mon[$p+0x1a] = $dr;  // item 1 drop rate
		$mon[$p+0x1b] = $dr;  // item 2 drop rate
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}
//////////////////////////////
function cvnds( $fname )
{
	$fp = fopen($fname, "rb+");
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0x160);
	$mgc = substr($head, 0x0c, 4);
	$ver = ord( $head[0x1e] );

	$mgc = sprintf("%s_%d", $mgc, $ver);
	switch ( $mgc )
	{
		case 'ACVJ_1':  return cvnds_dos_mon($fp, 0x7cca8);
		case 'ACVE_0':  return cvnds_dos_mon($fp, 0x7ccac);
		case 'ACVP_0':  return cvnds_dos_mon($fp, 0x7d984);

		case 'ACBJ_0':  return cvnds_por_mon($fp, 0xc25f0);
		case 'ACBJ_1':  return cvnds_por_mon($fp, 0xb67ac);
		case 'ACBE_0':  return cvnds_por_mon($fp, 0xc2568);
		case 'ACBP_0':  return cvnds_por_mon($fp, 0xc32f8);

		case 'YR9J_0':  return cvnds_ooe_mon($fp, 0xba3d4);
		case 'YR9E_0':  return cvnds_ooe_mon($fp, 0xba364);
		case 'YR9P_0':  return cvnds_ooe_mon($fp, 0xdad20);

		default:
			printf("UNKNOWN %s = %s\n", $mgc, $fname);
			return;
	}

	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
function smt_sj_demon( $fp, $pos )
{
	printf("= smt_sj_demon( %x )\n", $pos);
	// BMTE 23b5410-23c4990
	// $siz = 491 * 0x80; // 0xf580 = + demon * 128 bytes
	// if +0x6c == '0' SKIP dummy
	return;
}
 */
