<?php
/*
[license]
[/license]
 */
require "common.inc";

function disc2( $fname )
{
	// for *.lfi only
	if ( stripos($fname, '.lfi') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$fp = fopen("$pfx.lfd", "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$fn = substr ($file, $st+0 , 12);
		$sz = str2int($file, $st+12,  4);
		$ps = str2int($file, $st+16,  4);
			$st += 20;
		$fn = strtolower( rtrim($fn, ZERO) );
		printf("%8x , %8x , %s\n", $ps, $sz, $fn);

		fseek($fp, $ps, SEEK_SET);
		save_file("$dir/$fn", fread($fp, $sz));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc2( $argv[$i] );
