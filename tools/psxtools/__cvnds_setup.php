<?php
require "common.inc";

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
