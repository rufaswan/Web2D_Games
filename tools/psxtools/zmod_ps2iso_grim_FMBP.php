<?php
require 'common.inc';
require 'class-bakfile.inc';
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

		$file[$p+6] = "\x01";
		$file[$p+7] = "\x00";
	} // for ( $i=0; $i < $sc; $i++ )

	$id = 9;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+0x2a] = "\x01";
	} // for ( $i=0; $i < $sc; $i++ )
	//////////////////////////////

	$pos += $size;
	return;
}

function ps2grim( $fname )
{
	$bak = new BakFile;
	$bak->load($fname);
	if ( $bak->is_empty() )
		return;

	$t  = substr($bak->file, 0x8001, 5);
	$t .= substr($bak->file, 0x8801, 5);
	if ( $t !== 'CD001CD001' )
		return;

	$pos = 0;
	while (1)
	{
		$pos = strpos($bak->file, 'FMBP', $pos);
		if ( $pos === false )
			break;

		if ( $pos & 0x7ff )
		{
			$pos += 4;
			continue;
		}

		loop_FMBP($bak->file, $pos);
	} // while (1)

	$bak->save();
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2grim( $argv[$i] );
