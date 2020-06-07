<?php
require "common.inc";

function ptr( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// nds ram 2000000-2400000
	$MSB = chr(2);

	$ed = strlen($file);
	$st = 0;
	$prev = 0;
	while ( $st < $ed )
	{
		if ( $file[$st+3] == $MSB )
		{
			$ptr = str2int($file, $st, 3);
			if ( $ptr < 0x400000 )
			{
				if ( ($st - 4) != $prev )
					echo "\n";
				printf("$fname , %6x , %6x\n", $st, $ptr);
				$prev = $st;
			}
		}
		$st += 4;
	}
	echo "\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ptr( $argv[$i] );
