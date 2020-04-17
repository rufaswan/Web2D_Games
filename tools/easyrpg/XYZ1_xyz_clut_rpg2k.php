<?php
require "common.inc";

function rpg2k( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc != "XYZ1" )
		return;

	$w = str2int($file, 4, 2);
	$h = str2int($file, 6, 2);
	$dec = zlib_decode( substr($file, 8) );

	$pal = "";
	for ( $i=0; $i < 0x300; $i += 3 )
		$pal .= $dec[$i+0] . $dec[$i+1] . $dec[$i+2] . BYTE;

	$clut = "CLUT";
	$clut .= chrint(0x100, 4); // no clut
	$clut .= chrint($w, 4); // width
	$clut .= chrint($h, 4); // heigth
	$clut .= $pal;
	$clut .= substr($dec, 0x300);
	file_put_contents("$fname.clut", $clut);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rpg2k( $argv[$i] );
