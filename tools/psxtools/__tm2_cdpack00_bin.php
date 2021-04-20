<?php
require "common.inc";

function tm2decrypt($fp, $off, $end)
{
	$siz = $end - $off;
	if ( $siz < 1 )  return '';

	fseek($fp, $off, SEEK_SET);
	$sub = fread($fp, $siz);
	for ( $i=0; $i < $siz; $i++ )
	{
		$b = ord( $sub[$i] );
		$b = ($b + 0x9c) & BIT8;
		//$b = ($b - 0x64) & BIT8;
		$sub[$i] = chr($b);
	}
	return $sub;
}
//////////////////////////////
function tm2d1( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x15a5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2d2( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x15a5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2d3( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x15a5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2d4( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x15a5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2d5( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x15a5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2evs1( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x1da5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2evs2( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x1da5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}

function tm2evs3( $dir, $binp )
{
	$ov1 = tm2decrypt($binp, 0x800+1, 0x1da5);
	save_file("$dir/meta/ov1.psx", $ov1);
	return;
}
//////////////////////////////
function tm2( $dir )
{
	if ( ! is_dir($dir) )
		return;
	$dir = rtrim($dir, '/\\');

	$binp = fopen_file("$dir/cdpack00.bin");
	if ( ! $cdp )  return;

	if ( fopen_file("$dir/slpm_863.55") )  return tm2d1  ($dir, $binp);
	if ( fopen_file("$dir/slpm_863.56") )  return tm2d2  ($dir, $binp);
	if ( fopen_file("$dir/slpm_863.57") )  return tm2d3  ($dir, $binp);
	if ( fopen_file("$dir/slpm_863.58") )  return tm2d4  ($dir, $binp);
	if ( fopen_file("$dir/slpm_863.59") )  return tm2d5  ($dir, $binp);
	if ( fopen_file("$dir/slpm_805.27") )  return tm2evs1($dir, $binp);
	if ( fopen_file("$dir/slpm_805.44") )  return tm2evs2($dir, $binp);
	if ( fopen_file("$dir/slpm_805.50") )  return tm2evs3($dir, $binp);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	tm2( $argv[$i] );

/*
	// event data
	//
	//  fedc  ba98  7654321  0 fedcba98 7  65432  10
	//  fid   cid   sid      bgid          day    -
	//
	//                title         sprite        yyyy  bg            mm dd   cid   fid
	// 2c c2 02 1d => 98 73 0c 80   01 -- -- --   cf 07 84 01   01 02 04 0b   0d -- 01 --   -- -- 0d --
	// 18 c2 02 1d => 98 73 0c 80   01 -- -- --   cf 07 84 01   01 02 06 06   0d -- 01 --   -- -- 0d --
	// 28 c2 04 1d => 98 73 0c 80   02 -- -- --   cf 07 84 01   01 02 0a 0a   0d -- 01 --   -- -- 0d --
	// 24 c2 04 ad => 98 73 0c 80   02 -- -- --   d0 07 84 01   01 02 01 09   0d -- 0a --   -- -- 0d --
	// 08 c2 02 1d => 98 73 0c 80   01 -- -- --   d0 07 84 01   01 02 04 02   0d -- 01 --   -- -- 0d --
	// 08 c2 02 1d => 98 73 0c 80   01 -- -- --   d0 07 84 01   01 02 07 02   0d -- 01 --   -- -- 0d --
	// 04 c2 04 2d => 98 73 0c 80   02 -- -- --   d0 07 84 01   01 02 0a 01   0d -- 02 --   -- -- 0d --
	// 38 c2 04 2d => 98 73 0c 80   02 -- -- --   d1 07 84 01   01 02 01 0e   0d -- 02 --   -- -- 0d --
	// 20 c2 02 1d => 98 73 0c 80   01 -- -- --   d1 07 84 01   01 02 04 08   0d -- 01 --   -- -- 0d --
	// 1c c2 04 2d => 98 73 0c 80   02 -- -- --   d1 07 84 01   01 02 0a 07   0d -- 02 --   -- -- 0d --
	// 34 c2 04 2d => 98 73 0c 80   02 -- -- --   d2 07 84 01   01 02 01 0d   0d -- 02 --   -- -- 0d --
	//
 */
