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

	$size = $b1 + 0xa0 + 0x10; // b1 + FMBS header + FEOC
	$fn = substr0($file, $pos + 0x80);
	printf("%8x  %8x  %s\n", $pos, $size, $fn);

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
	} // for ( $i=0; $i < $sc; $i++ )
	//////////////////////////////

	$pos += $size;
	return;
}

function ndskuma( $fname )
{
	$bak = new BakFile;
	$bak->load($fname);
	if ( $bak->is_empty() )
		return;

	$t = substr($bak->file, 0, 18);
		$t = str_replace(ZERO, '', $t);
	if ( $t !== 'KUMATANCHICKUJMP' )
		return;

	$pos = 0;
	while (1)
	{
		$pos = strpos($bak->file, 'FMBS', $pos);
		if ( $pos === false )
			break;

		if ( $pos & 0xff )
		{
			$pos += 4;
			continue;
		}

		loop_FMBS($bak->file, $pos);
	} // while (1)

	$bak->save();
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndskuma( $argv[$i] );
