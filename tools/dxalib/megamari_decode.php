<?php
require 'common.inc';

function mari_decode( &$file, $pos, $siz )
{
	printf("== mari_decode( %x )\n", $pos);
	$dec = '';

	$key  = 100;
	$step = 100;
	for ( $i=0; $i < $siz; $i++ )
	{
		$b  = ord( $file[$pos+$i] );
		$b ^= $key;
		$dec .= chr($b);

		$key  = ($key  + $step) & BIT8;
		$step = ($step + 77   ) & BIT8;
	}
	return $dec;
}

function marisa( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 2);
	$siz = $cnt * 0x10c;

	$head = mari_decode($file, 2, $siz);
	save_file("$dir/head.dat", $head);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $i * 0x10c;
		$fn = substr0($head, $p+0);
		$sz = str2int($head, $p+0x104, 4);
		$of = str2int($head, $p+0x108, 4);
		$key = ( ($of >> 1) | 8 ) & BIT8;

		printf("%8x , %8x , %2x , %s\n", $of, $sz, $key, $fn);
		$dec = '';
		for ( $j=0; $j < $sz; $j++ )
		{
			$b  = ord( $file[$of+$j] );
			$b ^= $key;
			$dec .= chr($b);
		}
		save_file("$dir/$fn", $dec);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	marisa( $argv[$i] );
