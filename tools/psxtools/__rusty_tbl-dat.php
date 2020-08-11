<?php
require "common.inc";

function rusty( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$dat = load_file("$pfx.dat");
	$tbl = load_file("$pfx.tbl");
	if ( empty($dat) || empty($tbl) )
		return;

	$cnt = str2int($tbl, 0, 2);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 8 + ($i * 12);
		$v4 = str2int($tbl, $p+4, 2); // ?width?
		$v6 = str2int($tbl, $p+6, 2);
		$v8 = str2int($tbl, $p+8, 2); // ?total?
		$v10 = str2int($tbl, $p+10, 2);
		debug( substr($tbl, $p, 12) );
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

