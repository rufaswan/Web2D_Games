<?php
/*
[license]
[/license]
 */
require "common.inc";

$gp_clut = [];

function repal( $fname )
{
	$clut = load_clutfile($fname);
	if ( $clut === 0 )
		return;
	if ( ! isset( $clut['pal'] ) )
		return;

	global $gp_clut;
	$len = strlen( $clut['pix'] );
	$new = '';
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $clut['pix'][$i] );
		$c = substr($clut['pal'], $b*4, 4);
		$n = array_search($c, $gp_clut);
		if ( $n === false )
			return php_error("%s missing color from palette");
		$new .= chr($n);
	}
	$pal = implode('', $gp_clut);
	$clut['pal'] = substr($pal, 0, $clut['cc']*4);
	$clut['pix'] = $new;


	save_clutfile($fname, $clut);
	return;
}

function loadclut( $fname )
{
	global $gp_clut;
	$gp_clut = [];

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = int_floor(strlen($file), 4);
	for ( $i=0; $i < $len; $i += 4 )
		$gp_clut[] = substr($file, $i, 4);
	printf("add CLUT @ %x\n", count($gp_clut));
	return;
}

if ( $argc < 3 )  exit();
loadclut( $argv[1] );
for ( $i=2; $i < $argc; $i++ )
	repal( $argv[$i] );
