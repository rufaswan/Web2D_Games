<?php
/*
[license]
[/license]
 */
require "common.inc";

function unswizz( &$sub, $inv )
{
	$upper = '';
	$lower = '';
	$len = strlen($sub);
	for ( $i=0; $i < $len; $i += 0x20 )
	{
		$b1a = $sub[$i+0x00] . $sub[$i+0x04] . $sub[$i+0x08] . $sub[$i+0x0c]; // 0-3
		$b1b = $sub[$i+0x01] . $sub[$i+0x05] . $sub[$i+0x09] . $sub[$i+0x0d]; // 14-17
		$b1c = $sub[$i+0x02] . $sub[$i+0x06] . $sub[$i+0x0a] . $sub[$i+0x0e]; // 8-b
		$b1d = $sub[$i+0x03] . $sub[$i+0x07] . $sub[$i+0x0b] . $sub[$i+0x0f]; // 1c-1f

		$b2a = $sub[$i+0x10] . $sub[$i+0x14] . $sub[$i+0x18] . $sub[$i+0x1c]; // 4-7
		$b2b = $sub[$i+0x11] . $sub[$i+0x15] . $sub[$i+0x19] . $sub[$i+0x1d]; // 10-13
		$b2c = $sub[$i+0x12] . $sub[$i+0x16] . $sub[$i+0x1a] . $sub[$i+0x1e]; // c-f
		$b2d = $sub[$i+0x13] . $sub[$i+0x17] . $sub[$i+0x1b] . $sub[$i+0x1f]; // 18-1b

		if ( ! $inv )
		{
			$upper .= $b1a . $b2a . $b1c . $b2c;
			$lower .= $b2b . $b1b . $b2d . $b1d;
		}
		else
		{
			$upper .= $b2a . $b1a . $b2c . $b1c;
			$lower .= $b1b . $b2b . $b1d . $b2d;
		}
	} // for ( $i=0; $i < $len; $i += 0x20 )

	$sub = $upper . $lower;
	return;
}

function tm2pix( &$pix, $ow, $oh )
{
	// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
	// TextureSwizzling.pdf
	//   256x128 texture
	//   - 8-bit = swizzle 128x64
	//   - 4-bit = swizzle 128x32
	//   texture size (in bytes) remain the same
	//
	// SIMPLIFIED
	// original @ size 32x32
	//  0  1  2  3   4  5  6  7   8  9  a  b   c  d  e  f
	// 10 11 12 13  14 15 16 17  18 19 1a 1b  1c 1d 1e 1f [row]
	// 20 21 22 23  24 25 26 27  28 29 2a 2b  2c 2d 2e 2f
	// 30 31 32 33  34 35 36 37  38 39 3a 3b  3c 3d 3e 3f [row]
	// 40 41 42 43  44 45 46 47  48 49 4a 4b  4c 4d 4e 4f
	// 50 51 52 53  54 55 56 57  58 59 5a 5b  5c 5d 5e 5f [row]
	// 60 61 62 63  64 65 66 67  68 69 6a 6b  6c 6d 6e 6f
	// 70 71 72 73  74 75 76 77  78 79 7a 7b  7c 7d 7e 7f [row] [repeat]
	//
	// swizzle @ size 32x32 => 16x16
	//  0 24  8 2c   1 25  9 2d   2 26  a 2e   3 27  b 2f-
	//  4 20  c 28   5 21  d 29   6 22  e 2a   7 23  f 2b- [zrow 0]
	// 10 34 18 3c  11 35 19 3d  12 36 1a 3e  13 37 1b 3f-
	// 14 30 1c 38  15 31 1d 39  16 32 1e 3a  17 33 1f 3b- [zrow 1]
	// 44 60 4c 68  45 61 4d 69  46 62 4e 6a  47 63 4f 6b-
	// 40 64 48 6c  41 65 49 6d  42 66 4a 6e  43 67 4b 6f- [zrow 2]
	// 54 70 5c 78  55 71 5d 79  56 72 5e 7a  57 73 5f 7b-
	// 50 74 58 7c  51 75 59 7d  52 76 5a 7e  53 77 5b 7f- [zrow 3] [repeat]
	//
	$new = '';
	$inv = false;
	for ( $y=0; $y < $oh; $y += 4 )
	{
		$sub = substr($pix, $y*$ow, $ow*4);
		unswizz($sub, $inv);

		$new .= $sub;
		$inv = ! $inv;
	} // for ( $y=0; $y < $oh; $y += 2 )

	$pix = $new;
	return;
}

function tm2pal( &$pal )
{
	// also swizzled
	//  0- 7  10-17
	//  8- f  18-1f
	// 20-27  30-37
	// 28-2f  38-3f
	// ...
	// e0-e7  f0-f7
	// e8-ef  f8-ff
	$new = '';
	for ( $i=0; $i < 0x400; $i += 0x80 )
	{
		$b1 = substr($pal, $i+0x00, 0x20);
		$b2 = substr($pal, $i+0x20, 0x20);
		$b3 = substr($pal, $i+0x40, 0x20);
		$b4 = substr($pal, $i+0x60, 0x20);
		$new .= $b1 . $b3 . $b2 . $b4;
	} // for ( $i=0; $i < 0x400; $i += 4 )

	for ( $i=0; $i < 0x400; $i += 4 )
	{
		$a = ord( $new[$i+3] );
		$a = int_clamp($a*2, 0, BIT8);
		$new[$i+3] = chr($a);
	}

	$pal = $new;
	return;
}

function sect_fgst( &$file, $pos, $pfx, $id )
{
	printf("== sect_fgst( %x , %s , %d )\n", $pos, $pfx, $id);
	$fgsz = str2int($file, $pos+4, 4);
	$hdsz = str2int($file, $pos+8, 4);

	$ow = str2int($file, $pos+0x14, 2);
	$oh = str2int($file, $pos+0x16, 2);
	$dp = str2int($file, $pos+0x18, 1);
	$zw = str2int($file, $pos+0x2e, 2);
	$zh = str2int($file, $pos+0x30, 2);
	$tm = substr0($file, $pos+0x44);
		$pos += $hdsz;
	printf("%8x , %xx%x , %xx%x , %s\n", $pos, $ow, $oh, $zw, $zh, $tm);
	if ( $dp != 8 )
		return php_error("%s/%s in not 8-bpp", $pfx, $id);

	$pal = substr($file, $pos, 0x400);
	tm2pal($pal);
	$pos += 0x400;

	$pix = substr($file, $pos, $ow*$oh);
	tm2pix($pix, $ow, $oh);
	//save_file("$pfx.$id.pix", $pix);

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($ow, 4);
	$clut .= chrint($oh, 4);
	$clut .= $pal;
	$clut .= $pix;
	save_file("$pfx.$id.tm2", $clut);
	return;
}

function odin( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$cnt = str2int($file, 12, 4);

	$ed = strlen($file);
	$st = str2int($file, 8, 4);
	$id = 0;
	//$FGST = "";
	while ( $st < $ed )
	{
		$mgc = substr($file, $st, 4);
		switch ( $mgc )
		{
			case "FGST":
				$siz = str2int($file, $st+0x04, 4);
				sect_fgst($file, $st, $pfx, $id);
				//$FGST .= substr($file, $st, 0x80);

				$st = int_ceil($st+0x80+$siz, 0x10);
				$id++;
				break;
			case "FEOC":
				//save_file("$fname.FGST", $FGST);
				return;
			default:
				php_error("UNKNOWN mgc @ %x", $st);
				return;
		} // switch ( $mgc )
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	odin( $argv[$i] );
