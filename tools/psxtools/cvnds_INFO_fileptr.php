<?php
/*
[license]
[/license]
 */
require "common.inc";

define("TWO",    chr(2));
define("ZERO32", ZERO.ZERO.ZERO);

$gp_files = array();

function findptr( &$file, $name, $ram, $fst, $fed )
{
	global $gp_files;
	echo "== $name ==\n";
	$ed = strlen($file);
	for ( $i=0; $i < $ed; $i += 4 )
	{
		if ( $file[$i+3] != TWO )
			continue;
		$p = $ram + $i;
		if ( $p >= $fst && $p < $fed )
			continue;
		$b1 = substr($file, $i, 3);
		if ( isset( $gp_files[$b1] ) )
			printf("$name + %6x [%6x] = %s\n", $i, $p, $gp_files[$b1]);
	}
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$patch = nds_patch($dir, "cvnds");
	if ( empty($patch) )
		return;

	$y9 = load_file("$dir/y9.bin");
	$file = load_file("$dir/arm9.bin");
	arrayhex( $patch['ndsram']['files'] );

	global $gp_files;
	$gp_files = array();
	list($fst,$fed,$fbk) = $patch['ndsram']['files'];
	for ( $i=$fst; $i < $fed; $i += $fbk )
	{
		$b1 = substr($file, $i+0, 3);
		if ( $b1 == ZERO24 )
			continue;
		$b2 = substr0($file, $i+6);

		if ( isset( $gp_files[$b1] ) )
			printf("SAME fp = %s [%s]\n", $b1, $b2, $gp_files[$b1]);
		else
			$gp_files[$b1] = $b2;
	}

	findptr($file, "ARM9_BIN", 0, $fst, $fed);
	$id = 0;
	while (1)
	{
		$ovfn = sprintf("$dir/overlay/overlay_%04d.bin", $id);
		if ( ! is_file($ovfn) )
			break;
		$ovps = str2int($y9, ($id*0x20)+4, 3);
		$file = load_file($ovfn);

		findptr($file, "OVERLAY_{$id}", $ovps, $fst, $fed);
		$id++;
	}

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
