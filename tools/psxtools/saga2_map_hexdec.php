<?php
/*
[license]
[/license]
 */
function saga2( $fname )
{
	$sep = strrpos($fname, '/');
	if ( $sep === false )
		$dir = '.';
	else
	{
		$dir = substr($fname, 0, $sep);
		$fname = substr($fname, $sep+1);
	}

	if ( substr($fname,0,3) !== 'map' )
		return;

	// map086_map002.png
	// map0134_map002.png
	if ( $fname[6] !== '_' )
		return;

	$suf = substr($fname, 6);
	$hex = substr($fname, 3, 3);
		$hex = hexdec($hex);

	$new = sprintf("map%04d%s", $hex, $suf);
	echo "$fname -> $new\n";
	rename("$dir/$fname", "$dir/$new");

	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );
