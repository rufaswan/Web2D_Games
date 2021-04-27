<?php
/*
[license]
[/license]
 */
require "common.inc";

function ralph( $fname )
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
	ralph( $argv[$i] );

/*
kid.tbl
	    c-  75c    ea*8
	  844-  abc    4f*8   [+ 0]  3aa+15
	  abc- 28b4   3bf*8
	 28b4- 4118    df*1c  [+ 0]  467+1
	 4118- 52b8   468*4
	 52b8- 7518   1b8*14  [+ 0] 1559+11
	 7518-17bec  15e7*c
	17bec-180fc    51*10
	180fc-19e6c   274*c
 */
