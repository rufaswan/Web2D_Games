<?php
require "common.inc";

function rusty_decode( &$file, $st )
{
	$dict = str_repeat(ZERO, 0x1000);
	$dicp = 0xfee;
	$dec = "";

	$ed = strlen($file);
	$bylen = 0;
	$bycod = 0;
	while ( $st < $ed )
	{
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] );
			$bylen = 8;
			printf("%6x BYTECODE %2x\n", $st, $bycod);
			$st++;
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

			$dicp = ($dicp + 1) & 0xfff;
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
				$p = ($pos + $i) & 0xfff;
				$b1 = $dict[$p];

				$dec .= $b1;
				$dict[$dicp] = $b1;

				$dicp = ($dicp + 1) & 0xfff;
			}
		}
	} // while ( $st < $ed )

	return $dec;
}
//////////////////////////////
function rusty( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )   return;

	$mgc = substr($file, 0, 2);
	if ( $mgc != "LZ" )
		return;

	$dec = rusty_decode( $file, 7 );
	file_put_contents("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );
