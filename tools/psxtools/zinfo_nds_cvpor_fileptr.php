<?php
require 'common.inc';
require 'common-guest.inc';
require 'nds.inc';

define('ZERO24', "\x00\x00\x00");

$gp_files = array();

function findptr( &$file, $name, $ram, $fst, $fed )
{
	global $gp_files;
	echo "== $name ==\n";
	$ed = strlen($file);
	for ( $i=0; $i < $ed; $i += 4 )
	{
		// NDS ram is 02xxxxxx range
		if ( $file[$i+3] !== "\x02" )
			continue;
		$p = $ram + $i;
		if ( $p >= $fst && $p < $fed )
			continue;
		$b1 = substr($file, $i, 3);
		if ( isset( $gp_files[$b1] ) )
			printf("$name + %6x [%6x] = [%4x] %s\n", $i, $p, $gp_files[$b1][0], $gp_files[$b1][1]);
	}
	return;
}

function cvpor( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$patch = nds_patch($dir, "cvpor");
	if ( empty($patch) )
		return;
	arrayhex( $patch['ndsram']['files'] );

	global $gp_files;
	$gp_files = array();

	$file = load_file("$dir/arm9/main.bin");
	list($fst,$fed,$fbk) = $patch['ndsram']['files'];

	echo "== FILE IDS ==\n";
	$id = 0;
	for ( $i=$fst; $i < $fed; $i += $fbk )
	{
		$id++;
		$b1 = substr ($file, $i+0, 3);
		$b2 = substr0($file, $i+6);
		if ( $b1 == ZERO24 )
			continue;
		printf("%4x = %s\n", $id-1, $b2);

		if ( isset( $gp_files[$b1] ) )
			printf("SAME fp = %s [%s]\n", $b1, $b2, $gp_files[$b1]);
		else
			$gp_files[$b1] = array($id-1, $b2);
	} // for ( $i=$fst; $i < $fed; $i += $fbk )

	// analyze main.bin
	findptr($file, "ARM9_BIN", 0, $fst, $fed);

	// analyze overlay dll
	$bin = load_file("$dir/arm9/overlay.bin");
	$len = strlen($bin);
	for ( $i=0; $i < $len; $i += 0x20 )
	{
		$ov_id  = str2int($bin, $i+0, 4);
		$ov_ram = str2int($bin, $i+4, 3);
		$ov_fn  = sprintf('%s/arm9/%06x/%04d_%x.overlay', $dir, $ov_ram, $ov_id, $ov_id);

		$file = load_file($ov_fn);
		findptr($file, "OVERLAY_{$ov_id}", $ov_ram, $fst, $fed);
	} // for ( $i=0; $i < $len; $i += 0x20 )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );
