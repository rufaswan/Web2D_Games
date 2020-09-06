<?php
require "common.inc";

function galpani_decode( &$file, $pos, $end, $bits )
{
	printf("== galpani_decode( %x , %x )\n", $pos, $end);
	echo "=== begin sub_2436c() ===\n";

	$pix = str_repeat(ZERO, 0x1000);
	while ( $pos < $end )
	{
		$b1 = ord( $file[$pos+0] );
		$test = $b1 & 0xc0;
		if ( $test & 0x80 )
		{
			$b2 = ord( $file[$pos+1] );
				$pos += 2;
			$ref = ( ($b1 << 8) | $b2 ) & $bits[0];

			if ( $ref <= 3 )
			{
				$len = ( ($b1 >> $bits[7]) & $bits[8] ) + $bits[9];
				$p = strlen($pix) - 1 - $ref;
				printf("80-3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$pix .= $pix[$p+$i];
			}
			else
			{
				$op = ($b1 >> $bits[1]) & $bits[2];
				$p = strlen($pix) - 1 - $ref;
				// $op switch cases
				// 0 = 2, 4 = 3, 8 = 4 ... 7c = 33
				$len = ($op / 4) + 2;
				printf("80>3  REF -%d LEN %d\n", $ref, $len);

				for ( $i=0; $i < $len; $i++ )
					$pix .= $pix[$p+$i];
			}
		}
		else
		if ( $test & 0x40 )
		{
			$b2 = ord( $file[$pos+1] );
			$b3 = ord( $file[$pos+2] );
				$pos += 3;
			$len = (( ($b1 << 8) | $b2 ) >> $bits[4]) & $bits[5];
			$ref =  ( ($b2 << 8) | $b3 ) & $bits[3];

			$len += $bits[6];
			$p = strlen($pix) - 1 - $ref;
			printf("40  REF -%d LEN %d\n", $ref, $len);

			for ( $i=0; $i < $len; $i++ )
				$pix .= $pix[$p+$i];
		}
		else
		{
			if ( $test == 0x3f )
			{
				$b1 = ord( $file[$pos+1] );

				if ( $b1 == 0 )
					$pos = $end + 1;
				else
				{
					$ed = strlen($pix) - 1;
					$b2 = ord( $pix[$ed] );
					$by = ($b1 + $b2) & BIT8;

					printf("3f  DIFF %x\n", $by);
					$pix .= chr($by);
					$pos += 2;
				}
			}
			else
			{
				$b1 -= 0x1f;

				$ed = strlen($pix) - 1;
				$b2 = ord( $pix[$ed] );
				$by = ($b1 + $b2) & BIT8;

				printf("1f  DIFF %x\n", $by);
				$pix .= chr($by);
				$pos++;
			}
		}
	} // while ( $pos < $end )

	echo "=== end sub_2436c() ===\n";
	return substr($pix, 0x1000);
}

function galpani( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 2) != "sc" )
		return;

	$w  = str2int($file, 0x20, 2);
	$h  = str2int($file, 0x22, 2);
	$cc = str2int($file, 0x2a, 2);
	printf("img : %d x %d , %d color\n", $w, $h, $cc);

	$b1 = str2int($file, 0xb4, 4);
	$b2 = str2int($file, 0xb8, 4);
	$b3 = str2int($file, 0xbc, 4);

	$pal = "";
	for ( $i=$b1; $i < $b2; $i += 3 )
		$pal .= substr($file, $i, 3) . BYTE;

	$bits = array(
		array(),
		//    0       1   2      3       4   5       6      7   8      9
		//    80>3               40                         80-3
		array(0x3ff , 0 , 0x7c , 0x3ff , 2 , 0xfff , 0x22 , 2 , 0x1f , 2), // sub_2436c
		array(0x7ff , 1 , 0x3c , 0x7ff , 3 , 0x7ff , 0x12 , 3 , 0x0f , 2), // sub_24910
		array(0xfff , 2 , 0x1c , 0xfff , 4 , 0x3ff , 0x0a , 4 , 0x07 , 2), // sub_24b78
	);
	$sub = array("" , "sub_2436c" , "sub_24910" , "sub_24b78");

	$ty = ord($file[0xb0]);
	echo "TYPE : $ty , {$sub[$ty]}\n";

	$file .= ZERO . ZERO;
	$pix = galpani_decode($file, $b2, $b2+$b3, $bits[$ty]);

	$s = $w * $h;
	while ( strlen($pix) < $s )
		$pix .= ZERO;

	$clut = "CLUT";
	$clut .= chrint($cc, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= $pal;
	$clut .= $pix;
	file_put_contents("$fname.clut", $clut);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	galpani( $argv[$i] );
