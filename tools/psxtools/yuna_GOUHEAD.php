<?php
/*
[license]
[/license]
 */
require "common.inc";

function yuna( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 7) != "GOUHEAD" )
		return;
	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 12, 4);
	while ( $cnt > 0 )
	{
		$cnt--;
		$p = 0x20 + ($cnt * 0x20);
		$fn = substr0($file, $p+0);
		$of = str2int($file, $p+0x10, 4);
		$sz = str2int($file, $p+0x18, 4);

		printf("%8x , %8x , %s\n", $of, $sz, $fn);
		save_file("$dir/$fn", substr($file, $of, $sz));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	yuna( $argv[$i] );
