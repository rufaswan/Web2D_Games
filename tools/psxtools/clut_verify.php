<?php
/*
[license]
[/license]
 */
require "common.inc";

function rgba_verify( &$file, $fname )
{
	$w = str2int($file, 4, 4);
	$h = str2int($file, 8, 4);
	if ( $w == 0 )  echo "RGBA zero width  : $fname\n";
	if ( $h == 0 )  echo "RGBA zero height : $fname\n";
	$s = $w * $h * 4;
	if ( strlen($file) < (12+$s) )
		echo "RGBA not enough data : $fname\n";
	return;
}

function clut_verify( &$file, $fname )
{
	$c = str2int($file,  4, 4);
	$w = str2int($file,  8, 4);
	$h = str2int($file, 12, 4);
	if ( $w == 0 )  echo "CLUT zero width  : $fname\n";
	if ( $h == 0 )  echo "CLUT zero height : $fname\n";
	$cc = $c * 4;
	$sz = $w * $h;
	if ( strlen($file) < (16+$cc+$sz) )
		echo "CLUT not enough data : $fname\n";

	$pix = substr($file, 16+$cc, $sz);
	$chr = count_chars($pix, 1);
	foreach ( $chr as $k => $v )
	{
		if ( $k > $c )
			printf("CLUT over cc : %2x  %x  $fname\n", $k, $v);
	}

	for ( $i=0; $i < $sz; $i++ )
	{
		$b = ord( $pix[$i] );
		if ( $b > $c )
		{
			$y = (int)($i / $w);
			$x = $i % $w;
			printf("CLUT error %2x @ %x (%d,%d)  $fname\n", $b, $i, $x, $y);
			$i += $sz;
		}
	}
	return;
}

function imgverify( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc == "RGBA" )
		return rgba_verify($file, $fname);
	if ( $mgc == "CLUT" )
		return clut_verify($file, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	imgverify( $argv[$i] );
