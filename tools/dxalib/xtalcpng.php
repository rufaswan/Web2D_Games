<?php
function xtalc( $fname )
{
	$file = file_get_contents( $fname );
	if ( file_exists("$fname.bak") )
		$file = file_get_contents( "$fname.bak" );
	if ( empty($file) )
		return;

	if ( strpos($file, ".bmp") )
	{
		echo "BMP $fname\n";
		file_put_contents("$fname.bak", $file);
		$file = str_replace(".bmp", ".png", $file);
		file_put_contents($fname, $file);
	}
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	xtalc( $argv[$i] );
