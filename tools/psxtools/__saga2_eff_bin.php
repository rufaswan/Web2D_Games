<?php
/*
 * has AKAO sound data
 */
require "common.inc";

function saga2( $fname )
{
	// only EFF????.BIN files
	if ( ! preg_match("|EFF[0-9]+\.BIN|i", $fname) )
		return;

	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$clut = mstrpal555($file, 0x54, 0x10, 0x10);

	$st = str2int($file, 0x04, 4);
	$ed = strlen ($file) - 8;
	$no = 1;
	while ( $st < $ed )
	{
		$w = str2int($file, $st+4, 2) * 2;
		$h = str2int($file, $st+6, 2);
		$siz = $w * $h;

		$pix = "";
		for ( $i=0; $i < $siz; $i++ )
		{
			$b = ord( $file[$st+8+$i] );
			$pix .= chr( $b & 0xf );
			$pix .= chr( $b >> 4 );
		}

		foreach ( $clut as $k => $c )
		{
			if ( trim($c, ZERO.BYTE) == "" )
				continue;
			$fn = sprintf("$dir/%04d-%d.clut", $no, $k);

			$data = "CLUT";
			$data .= chrint(0x10, 4); // no clut
			$data .= chrint($w*2, 4); // width
			$data .= chrint($h  , 4); // height
			$data .= $c;
			$data .= $pix;

			printf("%8x , %8x , $fn\n", $st, $siz);
			save_file($fn, $data);
		}

		$no++;
		$st += ($siz + 8);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
