<?php
require "common.inc";

function sotn( $fname )
{
	// for /bin/weaponx.bin
	if ( stripos($fname, "weapon") === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$ed = strlen($file);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$b1 = substr($file, $st+0,      0x4000);
		$b2 = substr($file, $st+0x4000, 0x3000);

		$tfn = sprintf("$dir/tw_%03d.bin", $id);
		$ffn = sprintf("$dir/fw_%03d.bin", $id);
		save_file($tfn, $b2);
		save_file($ffn, $b1);

		$st += 0x7000;
		$id++;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
