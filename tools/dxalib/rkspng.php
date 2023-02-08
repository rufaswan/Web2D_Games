<?php
require 'common.inc';

function replext( &$file, &$st, $src, $new )
{
	$len = strlen($src);
	if ( $file[ $st + $len + 1 ] != ZERO )
		goto err;
	$str = substr($file, $st+1, $len);
	if ( $str != $src )
		goto err;

	printf("REPL_EXT %8x $src -> $new\n", $st);
	for ( $i=0; $i < $len; $i++ )
		$file[$st+1+$i] = $new[$i];
	$st += ($len + 2); // skip period & ZERO
	return;

err:
	$st++;
	return;
}

function newext( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		if ( $file[$st] == '.' )
		{
			replext( $file, $st, 'bmp', 'png' );
			replext( $file, $st, 'BMP', 'png' );
		}
		else
			$st++;
	} // while ( $st < $ed )

	file_put_contents( $fname, $file );
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	newext( $argv[$i] );
