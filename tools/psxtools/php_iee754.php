<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "common-64bit.inc";

function hexfloat( $str )
{
	$str = preg_replace('|[^0-9a-fA-F]|', '', $str);
	$bit = strlen($str) * 4;

	$func = "float{$bit}";
	if ( ! function_exists($func) )
		return printf("NOT float %s\n", $str);

	$fl = $func( hexdec($str) );
	printf("%s( %s ) = %f\n", $func, $str, $fl);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	hexfloat( $argv[$i] );
