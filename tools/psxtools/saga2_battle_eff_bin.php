<?php
/*
 * has AKAO sound data
 */
require "common.inc";

define("CANV_S", 0x200);

function sectparts( &$file, &$canv, $off, $dir )
{
	printf("== sectparts( %x , $dir )\n", $off);
	$len = strlen($file);
	while ( $off < $len )
	{
		$dx = str2int($file, $off+0, 2) * 2;
		$dy = str2int($file, $off+2, 2);
		$w  = str2int($file, $off+4, 2) * 2;
		$h  = str2int($file, $off+6, 2);
		$siz = $w * $h;
			$off += 8;
		printf("%6x , %3d , %3d , %3d , %3d\n", $off-8, $dx, $dy, $w, $h);
		if ( ($dx + $w*2) > CANV_S )
			trigger_error("OVER dx\n", E_USER_ERROR);
		if ( ($dy + $h  ) > CANV_S )
			trigger_error("OVER dy\n", E_USER_ERROR);

		$pix = "";
		for ( $i=0; $i < $siz; $i++ )
		{
			$b = ord( $file[$off+$i] );
			$b1 = ($b >> 0) & 0x0f;
			$b2 = ($b >> 4) & 0x0f;
			$pix .= chr($b1) . chr($b2);
		}
		$off += $siz;

		for ( $y=0; $y < $h; $y++ )
		{
			$syy = $y * $w * 2;
			$dyy = ($y + $dy) * CANV_S + ($dx * 2);
			$b = substr($pix, $syy, $w*2);
			strupd($canv, $dyy, $b);
		}
	} // while ( $off < $siz )
	return;
}

function saga2( $fname )
{
	// only EFF????.BIN files
	if ( ! preg_match("|EFF[0-9]+\.BIN|i", $fname) )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$clut = mstrpal555($file, 0x54, 0x10, 0x10);

	$canv = canvpix(CANV_S,CANV_S);
	$off  = str2int($file, 0x04, 4);
	sectparts( $file, $canv, $off, $dir );

	foreach ( $clut as $k => $c )
	{
		if ( trim($c, ZERO.BYTE) == "" )
			continue;

		$data = "CLUT";
		$data .= chrint(0x10, 4); // no clut
		$data .= chrint(CANV_S, 4); // width
		$data .= chrint(CANV_S, 4); // height
		$data .= $c;
		$data .= $canv;

		$fn = sprintf("$dir/%04d.clut", $k);
		save_file($fn, $data);
	} // foreach ( $clut as $k => $c )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
