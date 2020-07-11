<?php
require "common.inc";

function fp2str( $fp, $pos, $byte )
{
	if ( $byte == 0 )
		return "";
	fseek($fp, $pos, SEEK_SET);
	return fread($fp, $byte);
}
//////////////////////////////
function smt_sj_demon( $fp, $pos )
{
	printf("= smt_sj_demon( %x )\n", $pos);
	// BMTE 23b5410-23c4990
	// $siz = 491 * 0x80; // 0xf580 = + demon * 128 bytes
	// if +0x6c == '0' SKIP dummy
	return;
}
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
		$p = $i * 0x24;
		// 0 1 2 3  4 5 6 7  8 9   a b   c d  e f
		// func     func     drop  drop  - -  hp
		// 10 11  12 13  14      15   16   17    18 19  1a    1b    1c 1d 1e 1f
		// mp     exp    rarity  atk  def  rate  -  -   soul  cost  weak
		// 20 21 22 23
		// half
		//// soul rarity (get++) , stars (rare--) , zero = boss/100%
		//// drop 1 = (rate * 3) / (1024 - LUCK)
		//// drop 2 = 256 / (1024 - LUCK)
		$mon[$p+0x0e] = ($i >= 101) ? chr(10) : chr(1);
		$mon[$p+0x0f] = ZERO;
		$mon[$p+0x14] = $sr;
		$mon[$p+0x17] = $dr;
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

	$gr = chr(0x64); // in-game max = 0x64
	$dr = chr(0xff); // in-game max = 0x0f
	for ( $i=0; $i < 121; $i++ )
	{
		$p = $i * 0x24;
		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  ap  hp
		// 10 11  12 13  14 15  16      17   18   19   1a    1b    1c 1d 1e 1f
		// exp    -  -   glyph  rarity  atk  def  int  rate  rate  weak
		// 20 21 22 23
		// half
		//// glyph rarity (get++) , stars (rare--)
		//// drop  rate   (get++) , stars (rare--)
		$mon[$p+0x0e] = ($i >= 108) ? chr(10) : chr(1);
		$mon[$p+0x0f] = ZERO;
		$mon[$p+0x16] = $gr;
		$mon[$p+0x1a] = $dr;
		$mon[$p+0x1b] = $dr;
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

	$sp = chr(0x63); // in-game max = 0x63
	$dr = chr(0x80); // in-game max = 0x32
	for ( $i=0; $i < 155; $i++ )
	{
		$p = $i * 0x20;
		// 0 1 2 3  4 5 6 7  8 9   a b   c  d   e f
		// func     func     drop  drop  -  sp  hp
		// 10 11 12  13   14   15   16    17    18 19 1a 1b  1c 1d 1e 1f
		// exp       atk  def  int  rate  rate  weak         half
		//// drop rate (get++) , stars (rare--)
		//// weak/half (all=FF 07)
		$mon[$p+0x0d] = $sp;
		$mon[$p+0x0e] = ($i >= 129) ? chr(10) : chr(1);
		$mon[$p+0x0f] = ZERO;
		$mon[$p+0x16] = $dr;
		$mon[$p+0x17] = $dr;
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

	$mgc = fp2str($fp, 12, 4);
	switch ( $mgc )
	{
		case "ACVJ":  cvnds_dos_mon($fp, 0x7cca8); return;
		case "ACVE":  cvnds_dos_mon($fp, 0x7ccac); return;
		case "YR9J":  cvnds_ooe_mon($fp, 0xba3d4); return;
		case "YR9E":  cvnds_ooe_mon($fp, 0xba364); return;
		case "ACBJ":  cvnds_por_mon($fp, 0xb67ac); return;
		case "ACBE":  cvnds_por_mon($fp, 0xc2568); return;

		default:  echo "UNKNOWN $fname\n"; return;
	}

	fclose($fp);
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
