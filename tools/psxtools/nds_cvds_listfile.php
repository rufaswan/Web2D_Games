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
		$b3 = substr($file, $st+6, $blk-6);
			$b3 = trim($b3, ZERO);
		$txt .= sprintf("%4x , %8x , %4x , %s\n", $id, $b1, $b2, $b3);

		$st += $blk;
		$id++;
	}
	save_file("{$dir}_files.txt", $txt);
	return;
}

function cvds( $dir )
{
	if ( ! is_dir($dir) )  return;

	$file = file_get_contents("$dir/header.bin");
	$NTR = substr($file, 12, 4);

	$file = file_get_contents("$dir/arm9.bin");
	switch ( $NTR )
	{
		// dawn of sorrow
		case 'ACVJ':  listfile($file, "$dir/$NTR", 0x8cc6c, 0x99fd4, 0x28);  break;
		case 'ACVE':  listfile($file, "$dir/$NTR", 0x8cc6c, 0x9a0c4, 0x28);  break;

		// portrait of ruin
		case 'ACBJ':  listfile($file, "$dir/$NTR", 0xc237c, 0xd399c, 0x20);  break;
		case 'ACBE':  listfile($file, "$dir/$NTR", 0xcdafc, 0xdf15c, 0x20);  break;

		// order of ecclesia
		case 'YR9J':  listfile($file, "$dir/$NTR", 0xda694, 0xee3b4, 0x20);  break;
		case 'YR9E':  listfile($file, "$dir/$NTR", 0xd8cec, 0xeca0c, 0x20);  break;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );
