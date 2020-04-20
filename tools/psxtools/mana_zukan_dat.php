<?php
require "common.inc";

define("CANV_S", 0x200);

function mana( $fname )
{
	// JT????.DAT  = indexed 256 colors , 1 palette
	// ZUKAN1P.DAT = RGB
	if ( stripos($fname, ".dat") == false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;
	echo "$fname\n";

	$dat = substr($file, str2int($file, 12, 4));
	$tim = psxtim($dat);

	if ( ($tim['t'] & 8) == 0 )
	{
		file_put_contents("$fname.tim", $dat);
		return;
	}

	$pix = COPYPIX_DEF;
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);
	$pix['bgzero'] = true;

	$cnt = ord( $file[0x14] );
	$st = 0x18;
	while ( $cnt )
	{
		$pix['dx'] = str2int($file, $st+ 0, 2);
		$pix['dy'] = str2int($file, $st+ 2, 2);

		$sx = str2int($file, $st+ 4, 2);
		$sy = str2int($file, $st+ 6, 2);
		$w  = str2int($file, $st+ 8, 2);
		$h  = str2int($file, $st+10, 2);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($tim['pix'], $sx, $sy, $w, $h, $tim['w'], $tim['h']);
		$pix['src']['pal'] = $tim['clut'][0];

		printf("%4d , %4d , %4d , %4d , %4d , %4d\n",
			$pix['dx'], $pix['dy'], $sx, $sy, $w, $h);
		copypix($pix);

		$cnt--;
		$st += 0x10;
	} // while ( $cnt )

	savpix($fname, $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
rect over
  0429.dat
 */
