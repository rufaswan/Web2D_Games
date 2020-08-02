<?php
require "common.inc";

$gp_pix = array();

function load_texx( &$pak, $pfx, $off1, $off2 )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	$gray = grayclut(16);

	$pos = 0;
	for ( $i = $off1; $i < $off2; $i += 8 )
	{
		// aligned to 8x8 tile
		while ( ($pos % 0x20) != 0 )
			$pos++;

		// 0  1 2 3  4  5  6  7
		// -  chr    w  h  id
		$id = ordint( $pak[$i+7] . $pak[$i+6] );
		$w = ord( $pak[$i+4] );
		$h = ord( $pak[$i+5] );
		$siz = ($w/2 * $h);
		printf("%4x , %6x , %3d x %3d = %4x\n", $id, $pos, $w, $h, $siz);

		$b1 = substr($chr, $pos, $siz);
		$pix = "";
		for ( $s=0; $s < $siz; $s++ )
		{
			$b2 = ord( $b1[$s] );
			$b3 = $b2 >> 4;
			$b4 = $b2 & 0x0f;
			$pix .= chr($b3) . chr($b4);
		}
		$gp_pix[$id] = array($pix, $w, $h);

		$clut = "CLUT";
		$clut .= chrint(16, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gray;
		$clut .= $pix;
		save_file("$pfx/$id.clut", $clut);

		$pos += $siz;
	} // for ( $i = $off1; $i < $off2; $i += 8 )
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$pak = load_file("$pfx.pak");
	if ( empty($pak) )  return;

	if ( substr($pak,0,4) != "unkn" )
		return;

	str_endian($pak, 0x08, 4);
	str_endian($pak, 0x0c, 4);
	str_endian($pak, 0x10, 4);
	str_endian($pak, 0x14, 4);
	str_endian($pak, 0x18, 4);
	str_endian($pak, 0x20, 2);
	str_endian($pak, 0x22, 2);
	str_endian($pak, 0x2c, 4);
	str_endian($pak, 0x30, 4);

	$off1 = str2int($pak, 0x08, 3); // parts def
	$off2 = str2int($pak, 0x0c, 3);
	$off3 = str2int($pak, 0x10, 3);
	$off4 = str2int($pak, 0x14, 3); // anim data
	$off5 = str2int($pak, 0x18, 3);
	$off6 = str2int($pak, 0x2c, 3);
	$off7 = str2int($pak, 0x30, 3);

	$num1 = str2int($pak, 0x20, 2); // no parts
	$num2 = str2int($pak, 0x22, 2); // no sprites

	load_texx($pak, $pfx, $off1, $off2);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
grad palette
	1  f8f8f0  7bff
	2  f8e0c0  639f
	3  f0c0a0  531e
	4  d89088  465b
	5  a07880  41f4
	6  804050  2910
	7  a898f0  7a75
	8  281860  3065
	9  503068  34ca
	a  d898c0  627b
	b  9070b8  5dd2
	c  9050a0  5152
	d  684098  4d0d
	f  c8d0f8  7f59
	=> RAM 9ca8e = 0.bin + 98a8e
*/
