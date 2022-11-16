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

	$size = $b1 + 0xa0 + 0x10; // b1 + FMBS header + FEOC
	$fn = substr0($file, $pos + 0x80);
	printf("%8x  %8x  %8x  %s\n", $c_mod, $pos, $size, $fn);

	global $gp_data;
	$sect = $gp_data['nds_kuma']['sect'];

	//////////////////////////////
	$id = 4;
	$sp = str2int($file, $pos + $sect[$id]['p']   , 4);
	$sc = str2int($file, $pos + $sect[$id]['c'][0], $sect[$id]['c'][1]);
	$sk = $sect[$id]['k'];
	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $pos + $sp + ($i * $sk);

		$file[$p+0] = ZERO;
		$c_mod++;
	} // for ( $i=0; $i < $sc; $i++ )
	//////////////////////////////

	$pos += $size;
	return;
}

function ndskuma( $fname )
{
	$file = load_bakfile($fname);
	if ( empty($file) )  return;

	$t = substr($file, 0, 18);
		$t = str_replace(ZERO, '', $t);
	if ( $t !== 'KUMATANCHICKUJMP' )
		return;

	$c_mod = 0;
	$pos   = 0;
	while (1)
	{
		$pos = strpos($file, 'FMBS', $pos);
		if ( $pos === false )
			break;

		if ( $pos & 0xff )
		{
			$pos += 4;
			continue;
		}

		loop_FMBS($c_mod, $file, $pos);
	} // while (1)

	if ( $c_mod > 0 )
		save_file($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndskuma( $argv[$i] );
