<?php
require "common.inc";

function cvds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 12, 4);
	$func = "cvds_" . strtolower($mgc);

	if ( ! function_exists($func) )
		return;

	$ram = ndsram($dir);
	$func($ram, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

/*
overlays

	dos alru une
		19e460 = 0
		22faa0 = 1
		2999c0 = 2
		29d6a0 = 3
		2b89a0 = 4
		2d5ec0 = 5
		2d94e0 = 17
		2fea00 = -
 */
