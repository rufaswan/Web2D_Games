<?php
require "common.inc";

//////////////////////////////
function sect1( &$file, $dir )
{
	$p1 = str2int($file, 0x04, 4);
	$p2 = str2int($file, 0x08, 4);
	$p3 = str2int($file, 0x0c, 4); // palette
	$p4 = str2int($file, 0x10, 4); // 3,end  4,seds  5,seds+wds

	save_file("$dir/0.meta", substr($file, $p1, $p2-$p1));
	save_file("$dir/1.meta", substr($file, $p2, $p3-$p2));
	save_file("$dir/2.meta", substr($file, $p3, $p4-$p3));
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$mgc = str2int($file, 0, 4);
	$end = str2int($file, 4 + ($mgc*4), 4);
	if ( $end != $len )
		return;
	sect1($file, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
xeno jp1 / slps 011.60
  2619-2770  spr1 monsters bosses
  2989-3018  spr2 party
xeno jp2 / slps 011.61
  2610-2761  spr1 monsters bosses
  2980-3009  spr2 party
*/
