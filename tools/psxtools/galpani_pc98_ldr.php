<?php
/*
[license]
[/license]
 */
require "common.inc";

function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "LBRG" )
		return;
	$dir = str_replace('.', '_', $fname);

	$pos = str2int($file,  8, 4);
	$cnt = str2int($file, 12, 4);
	$list = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $pos + ($i * 0x10);
		$tit = substr($file, $p+0, 8);
			$tit = rtrim($tit, ' ');
		$ext = substr($file, $p+8, 3);
		$b1 = str2int($file, $p+11, 2);
		$b2 = str2int($file, $p+13, 2);
		$b3 = ord( $file[$p+15] );
			$b2 |= ($b3 << 13);

		$list[$b1] = array($tit, $ext, $b2);
	}
	ksort($list);
	foreach ( $list as $k => $v )
	{
		list($tit,$ext,$len) = $v;
		printf("%8x , %6x , %s.%s\n", $k*0x800, $len, $tit, $ext);
		save_file("$dir/$ext/$tit.$ext", substr($file, $k*0x800, $len));
	}
	printf("total %d == %d\n", $cnt, count($list));

	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );
