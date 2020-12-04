<?php
/*
[license]
[/license]
 */
require "common.inc";

function gihren_decode( &$str )
{
	trace("== begin sub_80016f28\n");
	$dec = "";

	$ed = strlen($str);
	$st = 12;
	while ( $st < $ed )
	{
	} // while ( $st < $ed )

	trace("== end sub_80016f28\n");
	return $dec;
}

function gihren_files( &$tocs, &$dats, $pos, $dir )
{
	printf("== gihren_files( %x , $dir )\n", $pos);
	$id = 0;
	while (1)
	{
		$p = str2int($tocs, $pos+0, 2) * 0x800;
		//b1 = ord( $tocs[$pos+2] );
		//b2 = ord( $tocs[$pos+3] );
			$pos += 4;

		if ( $p == 0 )
			return;
		if ( substr($dats, $p+0, 3) != "SD0" )
			return;
		$fn = sprintf("$dir/%04d.bin", $id);
			$id++;
		$sz = str2int($dats, $p+4, 4);
		printf("%8x , SD0 , %8x , %s\n", $p, $sz, $fn);

		$sub = substr($dats, $p, $sz);
		$sub = gihren_decode($sub);
		save_file($fn, $sub);
	} // while (1)
	return;
}

function gihren( $dir )
{
	$mkd0 = load_file("$dir/zzzzzzz0.mkd");
	$mkd1 = load_file("$dir/zzzzzzz1.mkd");
	if ( empty($mkd0) || empty($mkd1) )
		return;

	$st0 = str2int($mkd0, 0x08, 4);
	$st1 = str2int($mkd0, 0x14, 4);
	gihren_files($mkd0, $mkd0, $st0+4, "$dir/mkd/0");
	gihren_files($mkd0, $mkd1, $st1+4, "$dir/mkd/1");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gihren( $argv[$i] );
