<?php
require "common.inc";

function newext( $fname )
{
	$file = file_get_contents( $fname );
	if ( empty($file) )   return;

	echo "BMP $fname\n";
	$file = str_replace(".bmp\x00", ".png\x00", $file);
	file_put_contents("$fname-png", $file);
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	newext( $argv[$i] );
