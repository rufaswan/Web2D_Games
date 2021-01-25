<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("NO_TRACE", true);

function mura_decode( &$file, $st )
{
	$dicz = 0xfff;
	$dicp = 0xfee;
	$dict = str_repeat(ZERO, $dicz+1);
	$dec = "";

	$ed = strlen($file);
	$bylen = 0;
	$bycod = 0;
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
				$st++;
			trace("BYTECODE %2x\n", $bycod);
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = $file[$st];
				$st++;
			trace("COPY %2x\n", ord($b1));

			$dec .= $b1;
			$dict[$dicp] = $b1;

			$dicp = ($dicp + 1) & $dicz;
		}
		else
		{
			$b1 = ord( $file[$st+0] );
			$b2 = ord( $file[$st+1] );
				$st += 2;
			$len =  ($b2 & 0x0f) + 3;
			$pos = (($b2 & 0xf0) << 4) | $b1;
			trace("DICT %3x LEN %2x\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = ($pos + $i) & $dicz;
				$b1 = $dict[$p];

				$dec .= $b1;
				$dict[$dicp] = $b1;

				$dicp = ($dicp + 1) & $dicz;
			}
		}
	} // while ( $st < $ed )

	return $dec;
}
//////////////////////////////
function mura( $fname )
{
	$bak = file_exists("$fname.bak");
	if ( $bak )
		$file = file_get_contents("$fname.bak");
	else
		$file = file_get_contents($fname);

	if ( empty($file) )
		return;
	if ( substr($file, 0, 4) != "FCMP" )
		return;

	if ( ! $bak )
		file_put_contents("$fname.bak", $file);

	$dec = mura_decode( $file, 12 );
	file_put_contents($fname, $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
