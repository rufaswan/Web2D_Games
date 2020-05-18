<?php
require "common.inc";

// summaries overlay table file y9.bin and y7.bin
function ndsovt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$addr = array();
	$len = strlen($file);
	for ( $i=0; $i < $len; $i += 0x20 )
	{
		// 00  4  overlay id
		// 04  4  target ram address
		// 08  4  overlay size
		// 0c  4  bss size
		// 10  4  static init start addrress
		// 14  4  static init end addrress
		// 18  4  file id
		// 1c  4  *zer*
		$id1 = str2int($file, $i + 0x00, 3);
		$id2 = str2int($file, $i + 0x18, 3);
		if ( $id1 != $id2 )
			printf("ERROR id $id1 != $id2\n");

		$ram = str2int($file, $i + 0x04, 3);
		if ( ! isset($addr[$ram]) )
			$addr[$ram] = array($id1);
		else
			$addr[$ram][] = $id1;
	}

	ksort($addr);
	foreach ( $addr as $ak => $av )
	{
		$aav = implode(' , ', $av);
		printf("[%2d] %6x  %s\n", count($av), $ak, $aav);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndsovt( $argv[$i] );

