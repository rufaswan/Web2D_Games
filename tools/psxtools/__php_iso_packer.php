<?php

function iso_unpack( $file )
{
	return;
}
//////////////////////////////
function iso_read_meta( $dir )
{
	// cvmh = CVMH header
	// boot = lead-in sector , size must be within 0x10*0x800 byte
	// root = root sector
	//   10  01-CD001
	//   11  ff-CD001
	//   12
	//   13  path table          - little endian
	//   14  optional path table - little endian
	//   15  path table          - big endian
	//   16  optional path table - big endian
	// first = default first file on the ISO , at sector 0x17
	// end   = lead-out sector , size is 2 seconds = 2 * 75 * 0x800 = 0x4b000
	$meta = array(
		'cvmh'  => '',
		'boot'  => str_repeat(ZERO,   16*0x800),
		'root'  => str_repeat(ZERO,    7*0x800),
		'first' => '',
		'end'   => str_repeat(ZERO, 2*75*0x800),
	);
	if ( ! is_dir($dir) )
		return $meta;

	// if ( is_file("$dir/boot.bin") )
	// if ( is_file("$dir/patch.txt") )
	return $meta;
}

function iso_pack( $dir )
{
	$dir  = rtrim($dir, '/\\');
	$meta = iso_read_meta("$dir/__CDXA__");

/*
	$fname   = ( $meta['mode2'] ) ? "$dir.bin" : "$dir.bin";
	$lbasize = ( $meta['mode2'] ) ? 0x930 : 0x800;
	$fp = fopen($fname, 'wb');

	fclose($fp);
*/
	return;
}
//////////////////////////////
function isofile( $ent )
{
	if ( is_file($ent) )
		return iso_unpack($ent);
	if ( is_dir($ent) )
		return iso_pack($ent);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isofile( $argv[$i] );
