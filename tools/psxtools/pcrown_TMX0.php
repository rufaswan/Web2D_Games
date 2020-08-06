<?php
require "common.inc";

function pcrown( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 8, 4) != "TMX0" )
		return;

	$w = str2int($file, 0x12, 2);
	$h = str2int($file, 0x14, 2);
	$cc = 0x100;
	printf("TMX0-%d  %4d x %4d  %s\n", $cc, $w, $h, $fname);

	$pal = substr($file, 0x40, $cc*4);
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 4 )
		$pal[$i+3] = BYTE;

	$clut = "CLUT";
	$clut .= chrint($cc, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= $pal;
	$clut .= substr($file, 0x40+($cc*4), $w*$h);

	file_put_contents("$fname.clut", $clut);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );
