<?php
require "common.inc";

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
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
				$st++;
			$bylen = 8;
			printf("%6x BYTECODE %2x\n", $st-1, $bycod);
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = $file[$st];
				$st++;
			printf("%6x COPY %2x\n", $st-1, ord($b1));

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
			printf("%6x DICT %3x LEN %2x\n", $st-2, $pos, $len);

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
	$file = file_get_contents($fname);
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 4);
	if ( $mgc != "FCMP" )
		return;

	$dec = mura_decode( $file, 12 );
	file_put_contents("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
