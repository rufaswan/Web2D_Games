<?php
require "common.inc";

function bmp_header( $cw , $ch )
{
	$data_of = 0x7a;
	$data_sz = $cw * $ch * 4;

	$head  = "BM"; // magic
	$head .= chrint( $data_of + $data_sz , 4 ); // filesize
	$head .= chrint( 0 , 2 ); // unused
	$head .= chrint( 0 , 2 ); // unused
	$head .= chrint( $data_of , 4 ); // data offset

	// 38 = v3 undocumented , add alpha channel
	// 6c = v4 win 95+ , add colorspace + gamma
	// 7c = v5 win 98+ , add icc profile
	$head .= chrint( 0x6c , 4 ); // dib head size
	$head .= chrint(  $cw , 4 ); // width
	$head .= chrint(  $ch , 4 ); // height
	$head .= chrint(    1 , 2 ); // plane
	$head .= chrint(   32 , 2 ); // bit-per-pixel
	$head .= chrint(    3 , 4 ); // compression
	$head .= chrint( $data_sz , 4 ); // data size
	$head .= chrint(   72 , 4 ); // density x
	$head .= chrint(   72 , 4 ); // density y
	$head .= chrint(    0 , 4 ); // palette num
	$head .= chrint(    0 , 4 ); // palette num - important

	// RGBA order
	$head .= BYTE . ZERO . ZERO . ZERO; // bitmask red
	$head .= ZERO . BYTE . ZERO . ZERO; // bitmask green
	$head .= ZERO . ZERO . BYTE . ZERO; // bitmask blue
	$head .= ZERO . ZERO . ZERO . BYTE; // bitmask alpha

	$head .= "RGBs";
	for ($i=0; $i < 0x24; $i++)
		$head .= ZERO; // colorspace - unused

	$head .= chrint( 0 , 4 ); // gamma red
	$head .= chrint( 0 , 4 ); // gamma green
	$head .= chrint( 0 , 4 ); // gamma blue

	return $head;
}

function clut2bmp( &$clut, $fname )
{
	$cn = str2int($clut,  4, 4);
	$cw = str2int($clut,  8, 4);
	$ch = str2int($clut, 12, 4);
	printf("CLUT , $cn , $cw , $ch , %s\n", $fname);

	$head = bmp_header( $cw , $ch );

	$pal = array();
	for ($i=0; $i < $cn; $i++)
	{
		$pos = 0x10 + ($i * 4);
		$pal[] = substr($clut, $pos, 4);
	} // for ($i=0; $i < $cn; $i++)

	$data = "";
	while ( $ch > 0 )
	{
		$ch--;
		$pos = 0x10 + ($cn * 4) + ($cw * $ch);
		$pix = substr($clut, $pos, $cw);
		for ( $i=0; $i < $cw; $i++ )
		{
			$px = ord( $pix[$i] );
			$data .= $pal[$px];
		} // for ( $i=0; $i < $cw; $i++ )
	} // while ( $ch > 0 )

	return $bmp = $head . $data;
}

function rgba2bmp( &$rgba, $fname )
{
	$cw = str2int($rgba, 4, 4);
	$ch = str2int($rgba, 8, 4);
	printf("RGBA , $cw , $ch , %s\n", $fname);

	$head = bmp_header( $cw , $ch );

	$data = "";
	while ( $ch > 0 )
	{
		$ch--;
		$pos = 12 + ($cw * $ch * 4);
		$pix = substr($rgba, $pos, $cw*4);
		$data .= $pix;
	} // while ( $ch > 0 )

	return $head . $data;
}
//////////////////////////////
function img2bmp( $fname )
{
	$file = file_get_contents( $fname );
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	$bmp = "";
	if ( $mgc == "CLUT" )
		$bmp = clut2bmp( $file, $fname );
	else
	if ( $mgc == "RGBA" )
		$bmp = rgba2bmp( $file, $fname );
	else
		return;

	if ( ! empty($bmp) )
		file_put_contents( "$fname.bmp", $bmp );
}

for ( $i=1; $i < $argc; $i++ )
	img2bmp( $argv[$i] );
