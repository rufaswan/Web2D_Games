<?php
require 'common.inc';
require 'common-guest.inc';

function kuma( $str )
{
	$str = preg_replace('|[^0-9a-fA-F]|', '', $str);
	if ( strlen($str) !== 8 )
		return;

	$f = float32( hexdec($str) );
	$i = (int)($f * 0x1000) & BIT32;
	printf("%s = (int) %8x , (float) %f\n", $str, $i, $f);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
float32 -> int32
	(float) 1.0 == (int) 0x1000

	0.333 = 3eaaaaab -> 0x0555
	3eaaaaab = sign 0  exp 7d/-2  man 2aaaab

	123.456 = 42f6e979 -> 0x07b74b
	42f6e979 = sign 0  exp 85/+6  man 76e979
 */
