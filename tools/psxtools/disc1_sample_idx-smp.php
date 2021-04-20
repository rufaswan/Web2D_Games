<?php
/*
[license]
[/license]
 */
require "common.inc";

function disc1( $fname )
{
	// for *.idx only
	if ( stripos($fname, '.idx') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$fp = fopen("$pfx.smp", "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$ids = array();
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = str2int($file, $i, 4);
		if ( $b === 0 )
			continue;
		$ids[ $i/4 ] = $b;
	}

	foreach ( $ids as $k => $v )
	{
		$b = fp2str($fp, $v, 0x10);
		$size = str2int($b, 0, 4);
		$skip = 4;

		if ( $size > 0xfffff )
		{
			$size = str2int($b, 4, 4);
			$skip = 8;
		}

		if ( $size > 0xfffff )
			continue;

		$fn = sprintf("%s/%06d.vb", $dir, $k);
		printf("%8x , %8x , %s\n", $v, $size, $fn);

		$b = fp2str($fp, $v+$skip, $size);
		save_file($fn, $b);
	} // foreach ( $ids as $k => $v )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc1( $argv[$i] );
