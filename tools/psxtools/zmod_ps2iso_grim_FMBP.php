<?php
require 'common.inc';
require 'quad_vanillaware.inc';

function loop_FMBP( &$c_mod, &$file, &$pos )
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
	printf("%8x  %8x  %8x  %s\n", $c_mod, $pos, $size, $fn);

	global $gp_data;
	$sect = $gp_data['ps2_grim']['sect'];

	//////////////////////////////
	$id = 4;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+0] = "\x07";
		$file[$p+2] = "\x00";
		$c_mod++;
	} // for ( $i=0; $i < $sc; $i++ )
	//////////////////////////////

	$pos += $size;
	return;
}

function ps2grim( $fname )
{
	$file = load_bakfile($fname);
	if ( empty($file) )  return;

	$t  = substr($file, 0x8001, 5);
	$t .= substr($file, 0x8801, 5);
	if ( $t !== 'CD001CD001' )
		return;

	$c_mod = 0;
	$pos   = 0;
	while (1)
	{
		$pos = strpos($file, 'FMBP', $pos);
		if ( $pos === false )
			break;

		if ( $pos & 0x7ff )
		{
			$pos += 4;
			continue;
		}

		loop_FMBP($c_mod, $file, $pos);
	} // while (1)

	if ( $c_mod > 0 )
		save_file($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2grim( $argv[$i] );
