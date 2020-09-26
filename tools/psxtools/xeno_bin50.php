<?php
require "common.inc";

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	for ( $i=0; $i < $len; $i += 0x1000 )
	{
		$clut = "CLUT";
		$clut .= chrint(0x80, 4);
		$clut .= chrint(60, 4);
		$clut .= chrint(64, 4);
		$clut .= strpal555($file, $i+0, 0x80);
		$clut .= substr($file, $i+0x100, 60*64);

		$fn = sprintf("$dir/%04d.clut", $i/0x1000);
		save_file($fn, $clut);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
