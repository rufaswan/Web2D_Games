<?php
require 'common.inc';

$gp_list = array();

function gunvolt( $fname )
{
	// for *.irlst only
	if ( stripos($fname, '.irlst') === false )
		return;
	if ( is_link($fname) )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_list;
	$len = strlen($file);
	for ( $i=4; $i < $len; $i += 0x10 )
	{
		$id = str2int($file, $i, 4);
		if ( isset( $gp_list[$id] ) )
			printf("DUP %x = %s -> %s\n", $id, $gp_list[$id], $fname);

		$gp_list[$id] = $fname;
	} // for ( $i=4; $i < $len; $i += 0x10 )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );
