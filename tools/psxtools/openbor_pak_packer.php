<?php
require "common.inc";

function pak_pack( $dir )
{
	echo "== pak_pack( $dir )\n";
	$dir = rtrim($dir, '/\\');
	$len = strlen($dir);

	$list = array();
	lsfile_r($dir, $list);
	if ( empty($list) )
		return;

	$file = "PACK" . ZERO . ZERO . ZERO . ZERO;
	$toc  = "";
	foreach ( $list as $k => $v )
	{
		$fdt = file_get_contents($v);
		$fsz = strlen($fdt);
		$fnm = substr($v, $len+1);
			$b1 = strlen($fnm);
			$b2 = strlen($file);
			$fnm = str_replace('/', '\\', $fnm);
		printf("%8x , %8x , %s\n", $b2, $fsz, $fnm);

		$toc .= chrint($b1+13, 4); // header 3*4 + str NULL
		$toc .= chrint($b2   , 4);
		$toc .= chrint($fsz  , 4);
		$toc .= $fnm . ZERO;

		$file .= $fdt;
	}

	$b2 = strlen($file);
	$file .= $toc . chrint($b2, 4);

	save_file("$dir.pak", $file);
	return;
}

function pak_unpack( $fname )
{
	echo "== pak_unpack( $fname )\n";
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "PACK" )
		return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$ed = $len - 8;
	$st = str2int($file, $len-4, 4);
	while ( $st < $ed )
	{
		$enz = str2int($file, $st+0, 4);
		$off = str2int($file, $st+4, 4);
		$siz = str2int($file, $st+8, 4);
		$nam = substr0($file, $st+12);
		printf("%8x , %8x , %s\n", $off, $siz, $nam);

		$sub = substr($file, $off, $siz);
		save_file("$dir/$nam", $sub);
		$st += $enz;
	}
	return;
}

function openbor( $ent )
{
	if ( is_file($ent) )
		return pak_unpack($ent);
	if ( is_dir($ent) )
		return pak_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	openbor( $argv[$i] );
