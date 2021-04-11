<?php
/*
[license]
[/license]
 */
require "common.inc";

function disc3( $fname )
{
	// for cdimage.elf only
	if ( stripos($fname, 'cdimage.elf') === false )
		return;

	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);

	fseek($fp, 0, SEEK_SET);
	$head = fread($fp, 0x10);

	$cnt = str2int($head, 0, 4);
	$siz = str2int($head, 8, 4);

	fseek($fp, 0, SEEK_SET);
	$head = fread($fp, $siz);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 0x10 + ($i * 0x10);
		$lba = str2int($head, $pos+4, 3);
		$siz = str2int($head, $pos+8, 4);
		$fn = sprintf("%s/%04d.bin", $dir, $i);
		printf("%6x , %8x , %8x , %s\n", $lba, $lba*0x800, $siz, $fn);

		fseek($fp, $lba*0x800, SEEK_SET);
		save_file($fn, fread($fp, $siz));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc3( $argv[$i] );
