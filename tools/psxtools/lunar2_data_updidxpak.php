<?php
require "common.inc";

function lunar2( $dir )
{
	$upd = load_file("$dir/data.upd");
	$idx = load_file("$dir/data.idx");
	$pak = load_file("$dir/data.pak");
	if ( empty($upd) || empty($idx) || empty($pak) )
		return;

	$ed = strlen($upd);
	$st = str2int($upd, 0x10, 4);
	$fn = array();
	while ( $st < $ed )
	{
		$s = substr0($upd, $st);
		$fn[] = strtolower($s);
		$st += strlen($s) + 1;
	}

	$ed = strlen($idx);
	$cnt = $ed / 5;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $i * 5;
		$lba = ordint( $idx[$p+2] . $idx[$p+1] . $idx[$p+0] );
		$siz = ordint( $idx[$p+4] . $idx[$p+3] );
		printf("%6x , %8x , %s\n", $lba, $siz, $fn[$i]);
		save_file($fn[$i], substr($pak, $lba*0x800, $siz*0x800));
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
