<?php
require 'common.inc';

function xorkey( &$key, $pos, $enc, $dec, &$cnt )
{
	$b1 = ord($enc);
	$b2 = ord($dec);
	$b = $b1 ^ $b2;
	$key[$pos] = chr($b);
	$cnt++;
	return;
}

function cmpkey( &$key, $pos, &$head, $p1, $p2, &$cnt )
{
	if ( $head[$p1+$pos] == $head[$p2+$pos] )
		xorkey($key, $pos, $head[$p1+$pos], ZERO, $cnt);
	return;
}

// generate 12-byte XOR key based on pattern
function dxkey( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$fsz  = filesize($fname);
	$head = fread($fp, 0x300);

	$key = str_repeat("\x00", 12);
	$cnt = 0;
	xorkey($key, 0, $head[0], 'D', $cnt);
	xorkey($key, 1, $head[1], 'X', $cnt);

	cmpkey($key,  2, $head, 0x3c, 0x48, $cnt);
	cmpkey($key,  3, $head, 0x3c, 0x48, $cnt);

	// 4
	// 5
	cmpkey($key,  6, $head, 0x0 , 0xc , $cnt);
	cmpkey($key,  7, $head, 0x0 , 0xc , $cnt);

	// 8
	cmpkey($key,  9, $head, 0x0 , 0x18, $cnt);
	cmpkey($key, 10, $head, 0x0 , 0xc , $cnt);
	cmpkey($key, 11, $head, 0x0 , 0xc , $cnt);



	printf("[%3d%%] decoded %d / 12 key\n", $cnt*100/12, $cnt);
	save_file("$fname.key", $key);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dxkey( $argv[$i] );
