<?php
/*
[license]
[/license]
 */
require "common.inc";

function pad800( &$file )
{
	while ( strlen($file) % 0x800 )
		$file .= ZERO;
	return;
}

function arc_pack( $dir )
{
	echo "== arc_pack( $dir )\n";
	$dir = rtrim($dir, '/\\');
	$len = strlen($dir);

	$list = array();
	lsfile_r($dir, $list);
	if ( empty($list) )
		return;

	$cnt = count($list);
	$file = chrint($cnt, 4);
	$file .= str_repeat(ZERO, $cnt * 0x18 );
	pad800($file);
	foreach ( $list as $k => $v )
	{
		$lba = strlen($file) / 0x800;
		$fdt = file_get_contents($v);
		$fsz = strlen($fdt);
		printf("%6x , %8x , %s\n", $lba, $fsz, $v);

		$b1 = substr($v, $len+1, 15);
			$b1 = strtoupper($b1);
		$b2 = chrint($fsz, 4);
		$b3 = chrint($lba, 4);

		$p = 4 + ($k * 0x18);
		str_update($file, $p+   0, $b1);
		str_update($file, $p+0x10, $b2);
		str_update($file, $p+0x14, $b3);

		$file .= $fdt;
		pad800($file);
	}
	save_file("$dir.arc", $file);
	return;
}

function arc_unpack( $fname )
{
	echo "== arc_unpack( $fname )\n";
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 0x18);
		$b1 = substr0($file, $p+0);
			$b1 = strtolower($b1);
		$b2 = str2int($file, $p+0x10, 4);
		$b3 = str2int($file, $p+0x14, 4);
		printf("%6x , %8x , %s\n", $b3, $b2, $b1);

		$fdt = substr($file, $b3*0x800, $b2);
		save_file("$dir/$b1", $fdt);
	}
	return;
}
//////////////////////////////
function tm2s( $ent )
{
	if ( is_file($ent) )
		return arc_unpack($ent);
	if ( is_dir($ent) )
		return arc_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tm2s( $argv[$i] );
