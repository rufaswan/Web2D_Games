<?php
require "common.inc";

function psxtimfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// can also be 00
	//$mgc = str2int($file, 0, 4);
	//if ( $mgc != 0x10 )  return;

	$type = str2int($file, 4, 1);
	if ( ($type & 8) == 0 )
		return;
	$tim = psxtim($file);

	// no need folder if only 1 palette
	if ( $tim['cn'] == 1 )
	{
		$clut = "CLUT";
		$clut .= chrint($tim['cc'], 4); // no clut
		$clut .= chrint($tim['w'] , 4); // width
		$clut .= chrint($tim['h'] , 4); // height
		$clut .= $tim['clut'][0];
		$clut .= $tim['pix'];

		save_file("$fname.clut", $clut);
		return;
	}

	// organized into folder
	$dir = str_replace('.', '_', $fname);
	foreach ( $tim['clut'] as $ck => $cv )
	{
		if ( trim($cv, ZERO.BYTE) == "" )
			continue;
		$clut = "CLUT";
		$clut .= chrint($tim['cc'], 4); // no clut
		$clut .= chrint($tim['w'] , 4); // width
		$clut .= chrint($tim['h'] , 4); // height
		$clut .= $cv;
		$clut .= $tim['pix'];

		$fn = sprintf("$dir/%04d.clut", $ck);
		save_file($fn, $clut);
	}
	return;

	/*****************************
	switch ( $type )
	{
		// cont'd CLUT 8-bit
		// from SLPS 021.70 Legend of Mana , /wm/wmap/wm1.tim
		// due to no CLUT , convert into grayscale
		case 2:
			$w = str2int($file, 0x10, 2) * 2;
			$h = str2int($file, 0x12, 2);
			$siz = $w * $h;

			$data = "RGBA";
			$data .= chrint($w, 4); // width
			$data .= chrint($h, 4); // height

			$st = 0x14;
			while ( $siz > 0 )
			{
				$siz--;
				$p = $file[$st];
				$data .= $p . $p . $p . BYTE;
				$st++;
			}
			file_put_contents("$fname.rgba", $data);
			return;

		// RGBA 16-bit
		// from SLPS 021.70 Legend of Mana , /ana/etc_etc/title00.dat/1.tim
		case 2:
			$w = str2int($file, 0x10, 2);
			$h = str2int($file, 0x12, 2);
			$siz = $w * $h;

			$data = "RGBA";
			$data .= chrint($w*2, 4); // width
			$data .= chrint($h  , 4); // height

			$st = 0x14;
			while ( $siz > 0 )
			{
				$siz--;
				$data .= rgb555( $file[$st+0] . $file[$st+1] );
				$st += 2;
			}
			file_put_contents("$fname.rgba", $data);
			return;

		// RGBA 24-bit
		// from SLPS 021.70 Legend of Mana , /ana/zukan_p/z00/zukan1p.dat
		case 3:
			$w = str2int($file, 0x10, 2) / 1.5;
			$h = str2int($file, 0x12, 2);
			$siz = $w * $h;

			$data = "RGBA";
			$data .= chrint($w, 4); // width
			$data .= chrint($h, 4); // height

			$st = 0x14;
			while ( $siz > 0 )
			{
				$siz--;
				$r = $file[$st+0];
				$g = $file[$st+1];
				$b = $file[$st+2];
				$a = BYTE;
				$data .= $r . $g . $b . $a;
				$st += 3;
			}
			file_put_contents("$fname.rgba", $data);
			return;
	}
	*****************************/
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxtimfile( $argv[$i] );
