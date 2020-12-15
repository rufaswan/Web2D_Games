<?php
/*
[license]
[/license]
 */
require "common.inc";

function rgba_optimize( &$file, $fname )
{
	$w = str2int($file, 4, 4);
	$h = str2int($file, 8, 4);
	if ( $w == 0 )  php_error("RGBA zero width  : %s", $fname);
	if ( $h == 0 )  php_error("RGBA zero height : %s", $fname);
	$s = $w * $h * 4;
	if ( strlen($file) < (12+$s) )
		php_error("RGBA not enough data : %s", $fname);

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
		return printf("RGBA [%x]  %d x %d  %s\n", $cc, $w, $h, $fname);
	else
	{
		printf("RGBA->CLUT [%x]  %d x %d  %s\n", $cc, $w, $h, $fname);

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
	$c = str2int($file,  4, 4);
	$w = str2int($file,  8, 4);
	$h = str2int($file, 12, 4);
	if ( $c == 0 )  php_error("CLUT zero color  : %s", $fname);
	if ( $w == 0 )  php_error("CLUT zero width  : %s", $fname);
	if ( $h == 0 )  php_error("CLUT zero height : %s", $fname);
	$cc = $c * 4;
	$sz = $w * $h;
	if ( strlen($file) < (16+$cc+$sz) )
		php_error("CLUT not enough data : %s", $fname);

	// optimize by removing duplicate and unused palettes
	$clpal = array();
	$clpix = "";
	$pos = 16 + $cc;
	for ( $y=0; $y < $h; $y++ )
	{
		for ( $x=0; $x < $w; $x++ )
		{
			$b1 = ord( $file[$pos] );
				$pos++;
			if ( $b1 > $c )
				php_error("CLUT %d,%d @ over cc %2x  %s", $x, $y, $b1, $fname);

			$b2 = substr($file, 16+$b1*4, 4);
			if ( array_search($b2, $clpal) === false )
				$clpal[] = $b2;
			$b3 = array_search($b2, $clpal);

			$clpix .= chr($b3);
		} // for ( $x=0; $x < $w; $x++ )
	} // for ( $y=0; $y < $h; $y++ )

	if ( count($clpal) == $c )
		return printf("CLUT [%x]  %d x %d  %s\n", $c, $w, $h, $fname);
	else
	{
		$cc = count($clpal);
		printf("CLUT [%x->%x]  %d x %d  %s\n", $c, $cc, $w, $h, $fname);

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
