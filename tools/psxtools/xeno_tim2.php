<?php
require "common.inc";

function xeno_tim2( &$file, $fname )
{
	$w = str2int($file, 0x10, 2);
	$h = str2int($file, 0x12, 2);

	$rgba = "RGBA";
	$rgba .= chrint($w, 4);;
	$rgba .= chrint($h, 4);;

	$file = substr($file, 0x14);
	$rgba .= pal555($file);

	file_put_contents("$fname.rgba", $rgba);
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = str2int($file, 0, 4);
	$b2 = str2int($file, 4, 4);
	if ( $b1 == 0x10 && $b2 == 0x02 )
		return xeno_tim2($file, $fname);

	$dir = str_replace('.', '_', $fname);
	$cnt = $b1;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$p = str2int($file, $p, 4);

		$b1 = str2int($file, $p+0, 4);
		$b2 = str2int($file, $p+4, 4);
		if ( $b1 != 0x10 )
			continue;

		printf("%8x , TIM-$b2\n", $p);
		if ( $b2 == 8 || $b2 == 9 )
		{
			$bin = substr($file, $p);
			$tim = psxtim($bin);

			foreach ( $tim['clut'] as $ck => $cv )
			{
				if ( trim($cv, ZERO.BYTE) == "" )
					continue;

				$clut = "CLUT";
				$clut .= chrint($tim['cc'], 4);
				$clut .= chrint($tim['w'], 4);
				$clut .= chrint($tim['h'], 4);
				$clut .= $cv;
				$clut .= $tim['pix'];
				save_file("$dir/$i-$ck.clut", $clut);
			}
		}
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
