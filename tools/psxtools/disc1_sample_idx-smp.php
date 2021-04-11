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
	$off = array($len);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = str2int($file, $i, 4);
		if ( array_search($b, $off) === false )
			$off[] = $b;
	}
	sort($off);

	$len = count($off) - 1;
	for ( $i=0; $i < $len; $i++ )
	{
		$p1 = $off[$i+0];
		$p2 = $off[$i+1];
		$sz = $p2 - $p1;
		$fn = sprintf("%s/%04d.vb", $dir, $i);
		printf("%8x , %8x , %s\n", $p1, $sz, $fn);

		fseek($fp, $p1, SEEK_SET);
		$b = fread($fp, $sz);
		$sz = str2int($b, 0, 4);
		save_file($fn, substr($b, 4, $sz));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc1( $argv[$i] );
