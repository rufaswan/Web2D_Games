<?php
require "common.inc";

define("TRACE", true);

function xeno_decode( &$file, $st, $ed )
{
	echo "== begin sub_80032cac\n";

	$lw = str2int($file, $st, 3);
		$st += 4;
	$bycod = 0;
	$bylen = 0;
	$dec = '';
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] ); // t8
				$st++;
			trace("BYTECODE %2x\n", $bycod);
			$bylen = 8; // t9
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = ord( $file[$st+0] ); // t0
			$b2 = ord( $file[$st+1] ); // t4
				$st += 2;
			$pos = ($b2 & 0xf) << 8;
				$pos |= $b1;
			$len = ($b2 >> 4) + 3;
			trace("REF  POS -%d LEN %d\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = strlen($dec) - $pos;
				$dec .= $dec[$p];
			}
		}
		else
		{
			$b1 = $file[$st]; // t0
				$st++;
			trace("COPY %2x\n", ord($b1));
			$dec .= $b1;
		}
	} // while ( $st < $ed )
	echo "== end sub_80032cac\n";

	return $dec;
}

function xeno( $fname )
{
	$bak = file_exists("$fname.bak");
	if ( $bak )
		$file = file_get_contents("$fname.bak");
	else
		$file = file_get_contents($fname);

	if ( empty($file) )
		return;

	if ( ! $bak )
		file_put_contents("$fname.bak", $file);

	$dec = xeno_decode($file, 0, strlen($file));
	file_put_contents($fname, $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
