<?php
require "common.inc";

// map files are loaded to RAM 80180000
// offsets here are RAM pointers
function ramint( &$file, $pos )
{
	$int = str2int($file, $pos, 3);
	if ( $int )
		$int -= 0x180000;
	else
		printf("ERROR ramint zero @ %x\n", $pos);
	return $int;
}
//////////////////////////////
function mana( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 8) != "SKmapDat" )
		return;

	$b1 = ramint($file, 0x14);
	$b2 = str2int($file, 0x0c, 4);
	$pix = substr($file, $b1, $b2);
	save_file("$fname.pix", $pix);

	$w = 0x20;
	$h = $b2 / $w;

	$b1 = ramint($file, 0x20);  // 118
	$b1 = ramint($file, $b1);   // 120
	$b1 = ramint($file, $b1+4); // 154
	while (1)
	{
		if ( $file[$b1+0x20] == ZERO )
		{
			$b1 += 0x200;
			continue;
		}

		$pal = strpal555($file, $b1, 0x100);
		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $pal;
		$clut .= $pix;
		return save_file("$fname.clut", $clut);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );
