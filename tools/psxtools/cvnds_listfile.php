<?php
require "common.inc";

function listfile( &$file, $dir, $st, $ed, $blk )
{
	$txt = "";
	$id = 0;
	while ( $st < $ed )
	{
		$b1 = str2int($file, $st+0, 4);
		$b2 = str2int($file, $st+4, 2);
		$b3 = substr0($file, $st+6);
		$txt .= sprintf("%4x , %8x , %4x , %s\n", $id, $b1, $b2, $b3);

		$st += $blk;
		$id++;
	}
	save_file("$dir/files.txt", $txt);
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;
	$ram = nds_ram($dir);

	$st = hexdec( $pat['arm9.bin']['files'][0] );
	$ed = hexdec( $pat['arm9.bin']['files'][1] );
	$bk = hexdec( $pat['arm9.bin']['files'][2] );
	listfile($ram, $dir, $st, $ed, $bk);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
