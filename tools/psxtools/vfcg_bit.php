<?php
/*
[license]
[/license]
 */
require "common.inc";

function vfcg( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen($file);
	if ( $len < 0x10000 )
		return;

	$w = 512;
	$h = (int)($len / ($w*2));

	$rgba = "RGBA";
	$rgba .= chrint($w, 4);
	$rgba .= chrint($h, 4);
	for ( $i=0; $i < $len; $i += 2 )
		$rgba .= rgb555( $file[$i+1] . $file[$i+0] );

	save_file("$fname.rgba", $rgba);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	vfcg( $argv[$i] );
