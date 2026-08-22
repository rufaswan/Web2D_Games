<?php
require 'common.inc';

function cvpor( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b04 = str2int($file, 0x04, 4);
	$b08 = str2int($file, 0x08, 4);
	$b0c = str2int($file, 0x0c, 4);
	$b10 = str2int($file, 0x10, 4);
	$b14 = str2int($file, 0x14, 4);
	$b20 = str2int($file, 0x20, 4);
	if ( $b04 === 0 )  printf("%s  b04 = 0\n", $fname);
	if ( $b08 === 0 )  printf("%s  b08 = 0\n", $fname);
	if ( $b0c === 0 )  printf("%s  b0c = 0\n", $fname);
	if ( $b10 === 0 )  printf("%s  b10 = 0\n", $fname);
	if ( $b14 === 0 )  printf("%s  b14 = 0\n", $fname);
	if ( $b20 === 0 )  printf("%s  b20 = 0\n", $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );
