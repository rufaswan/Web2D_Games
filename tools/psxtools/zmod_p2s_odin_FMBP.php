<?php
require 'common.inc';
require 'quad_vanillaware.inc';

function loop_FMBP( &$file, &$pos )
{
	$b1 = str2int($file, $pos+4, 4);
	$b2 = str2int($file, $pos+8, 4);
	if ( $b2 !== 0xa0 )
	{
		$pos += 4;
		return;
	}

	$size = $b1 + 0xa0 + 0x10; // b1 + FMBP header + FEOC
	$fn = substr0($file, $pos + 0x80);
	printf("%8x  %8x  %s\n", $pos, $size, $fn);

	global $gp_data;
	$sect = $gp_data['ps2_grim']['sect'];

	//////////////////////////////
	$id = 8;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+ 8] = "\x00";
		$file[$p+ 9] = "\x00";
		$file[$p+10] = "\x00";
		$file[$p+11] = "\x00";
	} // for ( $i=0; $i < $sc; $i++ )

	$id = 6;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+0x16] = "\x00";
		$file[$p+0x17] = "\x00";
	} // for ( $i=0; $i < $sc; $i++ )

	$id = 4;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+0] = "\x00";
		$file[$p+1] = "\x00";
	} // for ( $i=0; $i < $sc; $i++ )
	//////////////////////////////

	$pos += $size;
	return;
}

function ps2grim( $fname )
{
	// for pcsx2 save state.p2s
	if ( stripos($fname, 'eememory.bin') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pos = 0;
	while (1)
	{
		$pos = strpos($file, 'FMBP', $pos);
		if ( $pos === false )
			break;

		loop_FMBP($file, $pos);
	} // while (1)

	file_put_contents($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2grim( $argv[$i] );
