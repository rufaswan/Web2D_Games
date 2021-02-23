<?php
require "common.inc";

function nscript( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$num  = str2big($file, 0, 2);
	$base = str2big($file, 2, 4);
	$dir  = str_replace('.', '_', $fname);

	$pos = 6;
	while ( $pos < $base )
	{
		$fn = substr0($file, $pos);
			$pos += strlen($fn) + 2;
		$b1 = str2big($file, $pos+0, 4); // offset
		$b2 = str2big($file, $pos+4, 4); // size compressed
		$b3 = str2big($file, $pos+8, 4); // size uncompressed
			$pos += 12;
		printf("%8x , %8x , %8x , $fn\n",$b1, $b2, $b3);

		$data = substr($file, $base+$b1, $b2);
		if ( $b2 == $b3 )
			save_file("$dir/$fn", $data);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	nscript( $argv[$i] );
