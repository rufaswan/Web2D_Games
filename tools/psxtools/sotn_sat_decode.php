<?php
/*
 * stages
 *  00  *rondo of blood*
 *  0X  *entrance*
 *  01,R01  *entrance*
 *  02,R02  Alchemy Laboratory
 *  03,R03  Marble Gallery
 *  04,R04  Outer Wall
 *  05,R05  Long Library
 *  06AB,R06AB  Royal Chapel
 *  07AB,R07AB  Castle Keep
 *  08,R08  Clock Tower
 *  09ABC,R09ABC  Ground Water Veil
 *  10,R10  Olrox Room
 *  11,R11  Colosseum
 *  12,R12  Abandoned Pit to the Catacomb
 *  13AB,R13  Catacomb
 *  14,R14  *center*
 *  15,R15  Underground Garden
 *  16,R16  Cursed Prison
 *  EX  Nightmare
 *  MA  *maria*
 */
require "common.inc";

function sotn_decode( &$file, $st )
{
	$dicz = 0x3ff;
	$dicp = 0x3de;
	$dict = str_pad("", $dicz+1, ZERO);
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
			$len =  ($b2 & 0x1f) + 3;
			$pos = (($b2 & 0xe0) << 3) | $b1;
			printf("%6x DICT %3x LEN %2x\n", $st-2, $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = ($pos + $i) & $dicz;
				$b1 = $dict[$p];
				//$b1 = ZERO;

				$dec .= $b1;
				$dict[$dicp] = $b1;

				$dicp = ($dicp + 1) & $dicz;
			}
		}
	} // while ( $st < $ed )

	return $dec;
}

// for sega saturn symphony of the night
function sotn( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )
		return;
	if ( $file[0] == ZERO || $file[1] != ZERO ) // decompressed
		return;

	$dec = sotn_decode($file, 0);
	file_put_contents("$fname.dec", $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
