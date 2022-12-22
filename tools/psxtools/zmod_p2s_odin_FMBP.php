<?php
require 'common.inc';
require 'class-bakfile.inc';
require 'quad_vanillaware.inc';

function loop_FMBP( &$file, &$pos )
{
	$b1 = str2int($file, $pos+4, 4);
	$b2 = str2int($file, $pos+8, 4);
	$b3 = str2int($file, $pos+0x54, 4);
	if ( $b2 !== 0xa0 || $b3 === 0xa0 )
	{
		$pos += 4;
		return;
	}

	$size = $b1 + 0xa0 + 0x10; // b1 + FMBP header + FEOC
	$fn = substr0($file, $pos + 0x80);
	printf("%8x - %8x  %s\n", $pos, $pos+$size, $fn);

	global $gp_data;
	$sect = $gp_data['ps2_grim']['sect'];

	//////////////////////////////
	// set RGBA for debugging
	$id = 0;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);

		// cen c1 c2 c3 c4 c1
		$b = str_repeat("\x80\x80\x80\x80", 6);
		str_update($file, $p+4, $b);
	} // for ( $i=0; $i < $sc; $i++ )

	$id = 7;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);

		$b  = "\x00\x00\x80\x3f"; // red
		$b .= "\x00\x00\x80\x3f"; // green
		$b .= "\x00\x00\x80\x3f"; // blue
		$b .= "\x00\x00\x80\x3f"; // alpha
		str_update($file, $p+0, $b);
	} // for ( $i=0; $i < $sc; $i++ )
	// set RGBA for debugging
	//////////////////////////////
/*
	$id = 0;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		printf("  %8x = %8x[s%x][%x]\n", $p, $sp, $id, $i);

		$b = ord( $file[$p+8] );
			$b &= ~0x80;
			$b |= 0x80;
		$file[$p+8] = chr($b);

		$file[$p+0] = "\x00";
	} // for ( $i=0; $i < $sc; $i++ )
*/
	//////////////////////////////

	$pos += $size;
	return;
}

function ps2grim( $fname )
{
	// for pcsx2 save state.p2s
	if ( stripos($fname, 'eememory.bin') === false )
		return;

	$bak = new BakFile;
	$bak->load($fname);
	if ( $bak->is_empty() )
		return;

	$pos = 0;
	while (1)
	{
		$pos = strpos($bak->file, 'FMBP', $pos);
		if ( $pos === false )
			break;

		loop_FMBP($bak->file, $pos);
	} // while (1)

	$bak->save();
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2grim( $argv[$i] );
