<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";

//define("DRY_RUN", true);

function tmxpal( &$pal )
{
	if ( defined("DRY_RUN") )
		return;
	// swizzled
	//  0- 7  10-17
	//  8- f  18-1f
	// 20-27  30-37
	// 28-2f  38-3f
	// ...
	// e0-e7  f0-f7
	// e8-ef  f8-ff
	$new = '';
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 0x80 )
	{
		$b1 = substr($pal, $i+0x00, 0x20);
		$b2 = substr($pal, $i+0x20, 0x20);
		$b3 = substr($pal, $i+0x40, 0x20);
		$b4 = substr($pal, $i+0x60, 0x20);
		$new .= $b1 . $b3 . $b2 . $b4;
	} // for ( $i=0; $i < 0x400; $i += 4 )

	ps2_alpha2x($new);
	$pal = $new;
	return;
}

function pcrown( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 8, 4) != "TMX0" )
		return;

	$siz = str2int($file, 4, 4);
	$w = str2int($file, 0x12, 2);
	$h = str2int($file, 0x14, 2);

	if ( $w*$h+0x400+0x40 == $siz )
	{
		$cc = 0x100;
		$pal = substr($file, 0x40, 0x400);
		$pix = substr($file, 0x40+0x400, $w*$h);
		tmxpal($pal);
	}
	else
	if ( $w/2*$h+0x40+0x40 == $siz )
	{
		$cc = 0x10;
		$pal = substr($file, 0x40, 0x40);
		$pix = substr($file, 0x40+0x40, $w/2*$h);
		tmxpal ($pal);
		bpp4to8($pix);
	}
	else
		php_error("UNKNOWN bpp %s", $fname);

	printf("TMX0-%d  %4d x %4d  %s\n", $cc, $w, $h, $fname);
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
	pcrown( $argv[$i] );
