<?php
require 'common.inc';
require 'quad_vanillaware.inc';

function odin_CMNR( &$file, $fmbp_off )
{
	$bin  = chrint($fmbp_off, 4);
	$cmnr = strpos_all($file, $bin);
	foreach ( $cmnr as $cmnr_off )
	{
		if ( substr($file, $cmnr_off - 0x3c, 4) === 'CMNR' )
		{
			printf("  %8x  CMNR + 3c -> %8x  FMBP\n", $cmnr_off, $fmbp_off);
			$bin  = chrint($cmnr_off - 0x3c, 4);
			$work = strpos_all($file, $bin);
			foreach( $work as $work_off )
				printf("    %8x  -> %8x  CMNR\n", $work_off, $cmnr_off - 0x3c);
		}
		else
			printf("  %8x  -> %8x  FMBP\n", $cmnr_off, $fmbp_off);
	} // foreach ( $cmnr as $cmnr_off )
	return;
}

function odin_FMBP( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return '';

	global $gp_data;
	$sect = $gp_data['ps2_odin']['sect'];

	$fmbp = strpos_all($file, 'FMBP');
	$ret  = array();
	foreach ( $fmbp as $fmbp_off )
	{
		// not FMBP file, but for strcmp()
		$b1 = str2int($file, $fmbp_off + 8, 4);
		if ( $b1 !== 0xa0 )
			continue;

		// original file from ISO , invalid offset
		$b1 = str2int($file, $fmbp_off + 0x54, 4);
		if ( $b1 < $fmbp_off )
			continue;

		$fn = substr0($file, $fmbp_off + 0x80);
		foreach ( $sect as $sk => $sv )
		{
			$p = str2int($file, $fmbp_off + $sv['p'], 4);
			$c = str2int($file, $fmbp_off + $sv['c'][0], $sv['c'][1]);
			$data = array(
				'pos' => $p,
				'siz' => $c * $sv['k'],
				'mbp' => $fn,
				'sec' => $sk,
				'blk' => $sv['k'],
			);

			printf("%s [ s%x ] = %8x + %8x\n", $data['mbp'], $data['sec'], $data['pos'], $data['siz']);
			$ret[] = $data;
		} // foreach ( $sect as $sk => $sv )

		odin_CMNR($file, $fmbp_off);
	} // foreach ( $fmbp as $fmbp_off )

	return $ret;
}

function odinoff( $fmbp, $hex )
{
	if ( empty($fmbp) )
		return;
	$hex = hexdec($hex);

	foreach ( $fmbp as $fv )
	{
		$off = $hex - $fv['pos'];
		if ( $off < 0 )
			continue;
		if ( $off >= $fv['siz'] )
			continue;

		$id = 0;
		while ( $off > $fv['blk'] )
		{
			$id++;
			$off -= $fv['blk'];
		}
		printf("%8x = %s [ s%x ][ %x ] + %x\n", $hex, $fv['mbp'], $fv['sec'], $id, $off);
		return;
	} // foreach ( $fmbp as $fv )
	return;
}

printf("%s  eeMemory.bin  HEX...\n", $argv[0]);
if ( $argc < 2 )  exit();

$fmbp = odin_FMBP( $argv[1] );

for ( $i=2; $i < $argc; $i++ )
	odinoff( $fmbp, $argv[$i] );
