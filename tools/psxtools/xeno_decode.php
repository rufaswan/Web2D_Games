<?php
require "common.inc";
require "xeno.inc";

define("TRACE", true);

function xeno( $fname )
{
	$bak = file_exists("$fname.bak");
	if ( $bak )
		$file = file_get_contents("$fname.bak");
	else
		$file = file_get_contents($fname);

	if ( empty($file) )
		return;

	if ( ! $bak )
		file_put_contents("$fname.bak", $file);

	$dec = xeno_decode($file, 0, strlen($file));
	file_put_contents($fname, $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
