<?php
/*
[license]
[/license]
 */
require "common.inc";

function gunvolt( $fname )
{
	$data = array();
	$file = file_get_contents($fname);
	if ( empty($file) )
		return $data;

	$ed = strlen($file);
	$st = 4;
	while ( $st < $ed )
	{
		$id = str2int($file, $st, 4);
		$data[] = $id;
		$st += 0x10;
	} // while ( $st < $ed )

	return $data;
}

$gp_file = array();
for ( $i=1; $i < $argc; $i++ )
{
	$fn = $argv[$i];
	$file = gunvolt($fn);
	if ( ! empty($file) )
		$gp_file[$fn] = $file;
}

foreach ( $gp_file as $k1 => $v1 )
{
	$cnt2 = 0;
	foreach ( $v1 as $id )
	{
		$cnt1 = 0;
		foreach ( $gp_file as $k2 => $v2 )
		{
			if ( $k1 == $k2 )
				continue;
			if ( array_search($id, $v2) === false )
				continue;
			printf("%04x , %s == %s\n", $id, $k1, $k2);
			$cnt1++;
			$cnt2++;
		} // foreach ( $gp_file as $k2 => $v2 )
		printf("= MATCHED %x\n", $cnt1);

	} // foreach ( $v1 as $id )
	printf("== MATCHED %x\n", $cnt2);

} // foreach ( $gp_file as $k1 => $v1 )
