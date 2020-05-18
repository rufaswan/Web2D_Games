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
function cvds_dos_mon( $fp, $pos )
{
	printf("= cvds_dos_mon( %x )\n", $pos);
	$siz = 118 * 0x24; // 0x1098 = 116+2 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sr = chr(0x40); // in-game max = 0x40 , boss = 0
	for ( $i=0; $i < $siz; $i += 0x24 )
	{
		// 00  4  func
		// 04  4  func
		// 08  2  drop 1
		// 0a  2  drop 2
		// 0c
		// 0d
		// 0e  2  hp
		// 10  2  mp
		// 12  2  exp
		// 14  1  soul rarity (get++) , zero = boss/100%
		// 15
		// 16
		// 17
		// 18
		// 19
		// 1a
		// 1b
		// 1c
		// 1d
		// 1e
		// 1f
		// 20
		// 21
		// 22
		// 23
		$mon[$i+0x0e] = chr(1);
		$mon[$i+0x0f] = ZERO;
		$mon[$i+0x14] = ZERO;
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}
function cvds_ooe_mon( $fp, $pos )
{
	printf("= cvds_ooe_mon( %x )\n", $pos);
	$siz = 121 * 0x24; // 0x1104 = 121 mon * 36 bytes
	$mon = fp2str($fp, $pos, $siz);

	$gr = chr(0x64); // in-game max = 0x64
	$dr = chr(0x5f); // in-game max = 0x0f
	for ( $i=0; $i < $siz; $i += 0x24 )
	{
		// 00  4  func
		// 04  4  func
		// 08  2  drop 1
		// 0a  2  drop 2
		// 0c
		// 0d
		// 0e  2  hp
		// 10  2  exp
		// 12
		// 14  2  glyph
		// 16  1  glyph rate (get++) , stars (rare--)
		// 17
		// 18
		// 19
		// 1a  1  drop rate 1 (get++) , stars (rare--)
		// 1b  1  drop rate 2 (get++) , stars (rare--)
		// 1c
		// 1d
		// 1e
		// 1f
		// 20
		// 21
		// 22
		// 23
		$mon[$i+0x0e] = chr(1);
		$mon[$i+0x0f] = ZERO;
		$mon[$i+0x16] = $gr;
		$mon[$i+0x1a] = $dr;
		$mon[$i+0x1b] = $dr;
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}
function cvds_por_mon( $fp, $pos )
{
	printf("= cvds_por_mon( %x )\n", $pos);
	$siz = 155 * 0x20; // 0x1360 = 155 mon * 32 bytes
	$mon = fp2str($fp, $pos, $siz);

	$sp = chr(0x63); // in-game max = 0x63
	$dr = chr(0x32); // in-game max = 0x32
	for ( $i=0; $i < $siz; $i += 0x20 )
	{
		// 00  4  func
		// 04  4  func
		// 08  2  drop 1
		// 0a  2  drop 2
		// 0c  1
		// 0d  1  sp
		// 0e  2  hp
		// 10  3  exp
		// 13  1  atk
		// 14  1  phy.def
		// 15  1  mgc.def
		// 16  1  drop 1 rate (get++) , stars (rare--)
		// 17  1  drop 2 rate (get++) , stars (rare--)
		// 18  2  weakness (all=FF 07)
		// 1a  2
		// 1c  2  strong   (all=FF 07)
		// 1e  2
		$mon[$i+0x0d] = $sp;
		$mon[$i+0x0e] = chr(1);
		$mon[$i+0x0f] = ZERO;
		$mon[$i+0x16] = $dr;
		$mon[$i+0x17] = $dr;
	}
	fseek($fp, $pos, SEEK_SET);
	fwrite($fp, $mon);
	return;
}
//////////////////////////////
function cvds( $fname )
{
	$fp = fopen($fname, "rb+");
	if ( ! $fp )  return;

	$mgc = fp2str($fp, 12, 4);
	switch ( $mgc )
	{
		case "ACVJ":  cvds_dos_mon($fp, 0x7cca8); return;
		case "ACVE":  cvds_dos_mon($fp, 0x7ccac); return;
		case "YR9J":  cvds_ooe_mon($fp, 0xba3d4); return;
		case "YR9E":  cvds_ooe_mon($fp, 0xba364); return;
		case "ACBJ":  cvds_por_mon($fp, 0xb67ac); return;
		case "ACBE":  cvds_por_mon($fp, 0xc2568); return;

		default:  echo "UNKNOWN $fname\n"; return;
	}

	fclose($fp);
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );
