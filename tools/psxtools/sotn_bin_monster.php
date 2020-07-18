<?php
require "common.inc";

//define("DRY_RUN", true);

function sotn( $fname )
{
	// for /bin/monster.bin
	if ( stripos($fname, "monster.bin") === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$ed = strlen($file);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$pix = strpal555($file, $st, 96*112);
		$rgba = "RGBA";
		$rgba .= chrint( 96, 4);
		$rgba .= chrint(112, 4);
		$rgba .= $pix;

		$fn = sprintf("$dir/%04d.rgba", $id);
		save_file($fn, $rgba);
		printf("%4x , %8x\n", $id, $st);
		$st += 0x5800;
		$id++;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
