<?php

function sect_joint( &$list )
{
	$data = [];
	foreach ( $list as $fn )
	{
		$file = file_get_contents($fn);
		$cjnt = ord( $file[0x26] );

		$pos = 0x30;
		for ( $i=0; $i < $cjnt; $i++ )
		{
			$b2 = $file[$pos+2];
				$pos += 4;

			if ( ! isset($data[$b2]) )
				$data[$b2] = [];
			if ( array_search($fn, $data[$b2]) === false )
				$data[$b2][] = $fn;
		} // for ( $i=0; $i < $cjnt; $i++ )
	} // foreach ( $list as $fn )

	foreach ( $data as $dk => $dv )
	{
		printf("%2x\n", ord($dk));
		foreach ( $dv as $fn )
			echo "  $fn\n";
		echo "\n";
	} // foreach ( $data as $dk => $dv )
	return;
}

$list = [];
for ( $i=1; $i < $argc; $i++ )
	$list[] = $argv[$i];
if ( empty($list) )
	exit();

sect_joint($list);
