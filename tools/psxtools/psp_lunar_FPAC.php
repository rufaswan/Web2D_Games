<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-zlib.inc";

function unFCHN($dir, &$sub)
{
	if ( substr($sub, 0, 4) !== 'FCHN' )
		return save_file("$dir.bin", $sub);

	$hds = str2int($sub, 4, 2);
	$hdn = str2int($sub, 6, 2);
	for ( $i=0; $i < $hdn; $i++ )
	{
		$p = 8 + ($i * 8);
		$siz = str2int($sub, $p+0, 4);
		$off = str2int($sub, $p+4, 4);

		$fn = sprintf("%s/%04d.bin", $dir, $i);
		$s = substr($sub, $hds+$off, $siz);
		save_file($fn, $s);
	}
	return;
}

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FPAC" )
		return;

	$dir = str_replace('.', '_', $fname);
	$hdz = str2int($file, 4, 4);

	$pos = 8;
	$id  = 0;
	while (1)
	{
		$siz = str2int($file, $pos+0, 4);
		$zip = str2int($file, $pos+4, 2);
		$lba = str2int($file, $pos+6, 2);
			$pos += 8;
		if ( ($siz|$lba) == 0 )
			break;

		$fn = sprintf("%s/%04d", $dir, $id);
			$id++;
		$lba += $hdz;
		printf("%4x , %8x , %8x , %s\n", $lba, $lba*0x800, $siz, $fn);

		$sub = substr($file, $lba*0x800, $siz);
		if ( $zip )
		{
			$str = substr0($sub, 10);
			$fn  = "$dir/$str";
			$sub = zlib_decode($sub);
		}
		unFCHN($fn, $sub);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );
