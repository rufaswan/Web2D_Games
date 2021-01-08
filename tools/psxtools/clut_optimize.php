<?php
/*
[license]
[/license]
 */
require "common.inc";

function rgba_optimize( &$file, $fname )
{
	$log = "$fname [RGBA]";
	$w = str2int($file, 4, 4);
	$h = str2int($file, 8, 4);
	if ( $w == 0 )  php_error("$log zero width");
	if ( $h == 0 )  php_error("$log zero height");
	$s = $w * $h * 4;
	if ( strlen($file) < (12+$s) )
		php_error("$log not enough data");

	// optimize by converting into CLUT
	$clpal = array();
	$clpix = "";
	$pos = 12;
	for ( $y=0; $y < $h; $y++ )
	{
		for ( $x=0; $x < $w; $x++ )
		{
			$b2 = substr($file, $pos, 4);
				$pos += 4;
			if ( array_search($b2, $clpal) === false )
				$clpal[] = $b2;
			$b3 = array_search($b2, $clpal);

			$clpix .= chr($b3);
		} // for ( $x=0; $x < $w; $x++ )
	} // for ( $y=0; $y < $h; $y++ )

	$cc = count($clpal);
	if ( $cc > 0x100 )
		return printf("$log [%x] %d x %d\n", $cc, $w, $h);
	else
	{
		printf("%s [RGBA->CLUT] [%x] %d x %d\n", $fname, $cc, $w, $h);

		$clut = "CLUT";
		$clut .= chrint($cc, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= implode('', $clpal);
		$clut .= $clpix;
		return file_put_contents($fname, $clut);
	}
	return;
}

function clut_optimize( &$file, $fname )
{
	$log = "$fname [CLUT]";
	$cc = str2int($file,  4, 4);
	$w  = str2int($file,  8, 4);
	$h  = str2int($file, 12, 4);
	if ( $cc == 0 )  php_error("$log zero color");
	if ( $w  == 0 )  php_error("$log zero width");
	if ( $h  == 0 )  php_error("$log zero height");
	$cz = $cc * 4;
	$sz = $w * $h;
	if ( strlen($file) < (16+$cz+$sz) )
		php_error("$log not enough data");

	// optimize by removing duplicate and unused palettes
	$clpal = array();
	$clpix = "";
	$pos = 16 + $cz;
	for ( $y=0; $y < $h; $y++ )
	{
		for ( $x=0; $x < $w; $x++ )
		{
			$b1 = ord( $file[$pos] );
				$pos++;
			if ( $b1 > $cc )
				php_error("$log %x [%d,%d] @ over cc %2x", $pos-1, $x, $y, $b1);

			$b2 = substr($file, 16+$b1*4, 4);
			if ( array_search($b2, $clpal) === false )
				$clpal[] = $b2;
			$b3 = array_search($b2, $clpal);

			$clpix .= chr($b3);
		} // for ( $x=0; $x < $w; $x++ )
	} // for ( $y=0; $y < $h; $y++ )

	if ( count($clpal) == $cc )
		return printf("$log [%x] %d x %d\n", $cc, $w, $h);
	else
	{
		$cc = count($clpal);
		printf("$log [%x->%x] %d x %d\n", $c, $cc, $w, $h);

		$clut = "CLUT";
		$clut .= chrint($cc, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= implode('', $clpal);
		$clut .= $clpix;
		return file_put_contents($fname, $clut);
	}
	return;
}

function img_optimize( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc == "RGBA" )
		return rgba_optimize($file, $fname);
	if ( $mgc == "CLUT" )
		return clut_optimize($file, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	img_optimize( $argv[$i] );
