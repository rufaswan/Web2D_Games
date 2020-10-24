<?php
require "common.inc";
require "common-guest.inc";

req_ext("zlib_decode", "zlib");

function save_png( $fname, $w, $h, $rgba )
{
	// add filter byte on the beginning of every row
	// 0 = none
	// 1 = Sub(x) + Raw(x-bpp)
	// 2 = Up(x) + Prior(x)
	// 3 = Average(x) + floor((Raw(x-bpp)+Prior(x))/2)
	// 4 = Paeth(x) + PaethPredictor(Raw(x-bpp), Prior(x), Prior(x-bpp))
	$idat = "";
	for ( $y=0; $y < $h; $y++ )
		$idat .= ZERO . substr($rgba, $y*$w*4, $w*4);

	// PNG 8-bit RGBA
	$png = chr(0x89) . "PNG\r\n" . chr(0x1a) . "\n";

		$sect = "IHDR";
		$sect .= chrbig($w, 4); // width
		$sect .= chrbig($h, 4); // height
		$sect .= chr(8); // bit depth
		$sect .= chr(6); // color type , 0=gray , 2=rgb , 3=index , 4=gray+a , 6=rgb+a
		$sect .= ZERO; // compression , 0=zlib
		$sect .= ZERO; // filter , 0=adaptive/5 type
		$sect .= ZERO; // interlace , 0=none , 1=adam7
		$len = strlen($sect) - 4;
		$crc = crc32 ($sect);
	$png .= chrbig($len, 4);
	$png .= $sect;
	$png .= chrbig($crc, 4);

		$sect = "IDAT";
		$sect .= zlib_encode($idat, ZLIB_ENCODING_DEFLATE);
		$len = strlen($sect) - 4;
		$crc = crc32 ($sect);
	$png .= chrbig($len, 4);
	$png .= $sect;
	$png .= chrbig($crc, 4);

		$sect = "IEND";
		$len = strlen($sect) - 4;
		$crc = crc32 ($sect);
	$png .= chrbig($len, 4);
	$png .= $sect;
	$png .= chrbig($crc, 4);

	file_put_contents($fname, $png);
	return;
}
//////////////////////////////
function clut2png( &$file, $fname )
{
	echo "== clut2png( $fname )\n";
	$cc = str2int($file,  4, 4);
	$w  = str2int($file,  8, 4);
	$h  = str2int($file, 12, 4);

	$pal = substr($file, 0x10 , $cc*4);
	$pix = substr($file, 0x10 + $cc*4, $w*$h);
	$rgba = clut2rgba($pal, $pix, true);

	save_png("$fname.png", $w, $h, $rgba);
	return;
}

function rgba2png( &$file, $fname )
{
	echo "== rgba2png( $fname )\n";
	$w = str2int($file, 4, 4);
	$h = str2int($file, 8, 4);

	$rgba = substr($file, 12, $w*$h*4);

	save_png("$fname.png", $w, $h, $rgba);
	return;
}
//////////////////////////////
function img2png( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc == "CLUT" )
		return clut2png($file, $fname);
	if ( $mgc == "RGBA" )
		return rgba2png($file, $fname);

	return;
}

for ( $i=0; $i < $argc; $i++ )
	img2png( $argv[$i] );
