<?php
/*
[license]
[/license]
 */
require "common.inc";

function prefix( $fname )
{
	// for *.ext only
	//if ( stripos($fname, '.ext') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	//if ( substr($file, 0, 4) != "FILE" )
		//return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
	return;
}

for ( $i=1; $i < $argc; $i++ )
	prefix( $argv[$i] );
