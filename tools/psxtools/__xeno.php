<?php
require "common.inc";

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "$fname\n";
	$cnt = str2int($file, 0, 2);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 2 + ($i*2);
		$off = str2int($file, $p, 2);
		$b1 = str2int($file, $off+2, 2);
		$b2 = str2int($file, $off+4, 2);
		printf("%4x , %4x , %4x , %4x\n", $p, $off, $b1, $b2);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
