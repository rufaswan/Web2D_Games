<?php
require 'common.inc';

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "== $fname\n";
	$len = str2int($file, 0, 2);
	for ( $i = 0x7e; $i < $len; $i += 0x170 )
	{
		$hp  = str2int($file, $i+0, 2);
		$mhp = str2int($file, $i+2, 2);

		$gear = ( ($hp|$mhp) == 0 );
		if ( $gear )
			$data = array(0xb8,0xbc,0x100,0x10a);
		else
			$data = array(0,2,0x100,0x10a);

		$exp = str2int($file, $i+$data[2], 4);
		$gil = str2int($file, $i+$data[3], 2);

		if ( $gear )
		{
			$hp  = str2int($file, $i+$data[0], 4);
			$mhp = str2int($file, $i+$data[1], 4);
			printf("%4x GEAR = HP %6x/%6x  EXP %8x GIL %4x\n", $i, $hp, $mhp, $exp, $gil);
		}
		else
		{
			printf("%4x CHAR = HP %4x/%4x  EXP %8x GIL %4x\n", $i, $hp, $mhp, $exp, $gil);
		}

		$exp *= 2;
		$gil *= 2;

		$sexp = chrint( (int)$exp, 4);
		$sgil = chrint( (int)$gil, 2);
		str_update($file, $i+$data[2], $sexp);
		str_update($file, $i+$data[3], $sgil);
	} // for ( $i = 0x7e; $i < $len; $i += 0x170 )

	file_put_contents($fname, $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
