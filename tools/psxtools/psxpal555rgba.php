<?php
/*
[license]
[/license]
 */
require "common.inc";

define("TILE_S", 16);

$gp_cc = 0; // 16 or 256

function paltile( $str )
{
	$len = strlen($str);
	$pix = "";
	for ( $i=0; $i < $len; $i += 4 )
	{
		$c = substr($str, $i, 4);
		$pix .= str_repeat($c, TILE_S);
	}
	return str_repeat($pix, TILE_S);
}

function palette( $fname )
{
	global $gp_cc;
	if ( $gp_cc < 1 )
		return printf("ERROR gp_cc is zero\n");

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$cc = $gp_cc * 2;
	while ( strlen($file) % $cc )
		$file .= ZERO;

	$cn = strlen($file) / $cc;
	$clut = mstrpal555($file, 0, $gp_cc, $cn);

	$rgba = "RGBA";
	$rgba .= chrint($gp_cc * TILE_S, 4);
	$rgba .= chrint($cn    * TILE_S, 4);

	foreach ( $clut as $cv )
		$rgba .= paltile($cv);

	save_file("$fname.rgba", $rgba);
	return;
}

echo "{$argv[0]}  [-16/-256]  PALETTE_FILE...\n";
for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt[0] == '-' )
		$gp_cc = $opt * -1;
	else
		palette( $opt );
}
