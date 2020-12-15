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

	$len = strlen($file);
	$prev = 0;
	for ( $i=0; $i < $len; $i += 4 )
	{
		// nds ram 2000000-23fffff
		if ( $file[$i+3] == "\x02" )
		{
			$ptr = str2int($file, $i, 3);
			if ( $ptr <= 0x3fffff )
			{
				prevnl( $prev, $i );
				printf("%s + %6x = %6x\n", $fname, $i, $ptr);
			}
		}
	} // for ( $i=0; $i < $len; $i += 4 )
	echo "\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ptr( $argv[$i] );
