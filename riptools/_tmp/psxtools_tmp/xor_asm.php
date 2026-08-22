<?php

function xorasm( $fname )
{
	echo "=== $fname\n";
	foreach ( file($fname) as $lk => $lv )
	{
		$lv = preg_split("|[\s,]+|", trim($lv));
		if ( stripos($lv[0], 'xor') === false )
			continue;
		if ( $lv[1] == $lv[2] )
			continue;
		printf("%9d => %s\n", $lk+1, implode(' ', $lv));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xorasm( $argv[$i] );
