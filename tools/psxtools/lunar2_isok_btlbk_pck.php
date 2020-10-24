<?php
require "common.inc";

function lunar2( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( strlen($file) != 0x14800 )
		return;
	if ( str2int($file, 0, 4) != 2 )
		return;
	if ( str2int($file, 4, 4) != 0x14000 )
		return;

	$w = 320;
	$h = 256;

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= strpal555($file, 0x1400c, 0x100);
	$clut .= substr($file, 8, $w*$h);

	file_put_contents("$fname.clut", $clut);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
