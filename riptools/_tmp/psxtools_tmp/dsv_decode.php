<?php
function ain_dec( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 14);
	if ( $mgc != "DeSmuME SState" )
		return;

	//$hd = substr($file, 0, 0x20);
	$dec = zlib_decode( substr($file, 0x20) );
	file_put_contents("$fname.dec", $dec);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain_dec( $argv[$i] );
