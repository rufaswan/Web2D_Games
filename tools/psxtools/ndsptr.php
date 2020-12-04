<?php
/*
[license]
[/license]
 */
require "common.inc";

function prevnl( &$prev, $bak )
{
	if ( ($bak - 4) != $prev )
		echo "\n";
	$prev = $bak;
	return;
}

function ptr( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ed = strlen($file);
	$st = 0;
	$prev = 0;
	while ( $st < $ed )
	{
		$bak = $st;
			$st += 4;
		$op = ord( $file[$bak+3] );

		// nds ram 2000000-23fffff
		if ( $op == 0x02 )
		{
			$ptr = str2int($file, $bak, 3);
			if ( $ptr <= 0x3fffff )
			{
				prevnl( $prev, $bak );
				printf("$fname , %8x , ptr %6x\n", $bak, $ptr);
			}
			continue;
		}
	} // while ( $st < $ed )
	echo "\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ptr( $argv[$i] );
