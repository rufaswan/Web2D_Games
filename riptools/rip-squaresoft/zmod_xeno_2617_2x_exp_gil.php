<?php
declare( strict_types=1 );

require 'tool.inc';

function xeno( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	tool::trace(__FUNCTION__, $fname);

	$len = tool::ordstr($file, 0, 2);
	for ( $i = 0x7e; $i < $len; $i += 0x170 )
	{
		$hp  = tool::ordstr($file, $i+0, 2);
		$mhp = tool::ordstr($file, $i+2, 2);

		// hp,mhp,exp,gil
		$gear = ( ($hp|$mhp) == 0 );
		if ( $gear )
			$data = [0xb8,0xbc,0x100,0x10a];
		else
			$data = [   0,   2,0x100,0x10a];

		$exp = tool::ordstr($file, $i+$data[2], 4);
		$gil = tool::ordstr($file, $i+$data[3], 2);

		if ( $gear )
		{
			$hp  = tool::ordstr($file, $i+$data[0], 4);
			$mhp = tool::ordstr($file, $i+$data[1], 4);
			tool::trace($i,'GEAR' , 'HP',$hp,$mhp , 'EXP',$exp , 'GIL',$gil);
		}
		else
			tool::trace($i,'CHAR' , 'HP',$hp,$mhp , 'EXP',$exp , 'GIL',$gil);

		$sexp = tool::chr($exp * 2, 4);
		$sgil = tool::chr($gil * 2, 2);
		tool::str_update($file, $i+$data[2], $sexp);
		tool::str_update($file, $i+$data[3], $sgil);
	} // for ( $i = 0x7e; $i < $len; $i += 0x170 )

	file_put_contents($fname, $file);
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
