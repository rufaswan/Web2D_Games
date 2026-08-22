<?php
/*
[license]
[/license]
 */
require "common.inc";

$gp_depth = [
	"0",
	"16-bpp",
	"24-bpp",
	"32-bpp",
	" 4-bpp",
	" 8-bpp",
];

//////////////////////////////
function bpp4to8( &$pix )
{
	$len = strlen($pix);
	$buf = '';
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $pix[$i] );
		$b1 = ($b >> 0) & BIT4;
		$b2 = ($b >> 4) & BIT4;
		$buf .= chr($b1) . chr($b2);
	}
	$pix = $buf;
	return;
}

function unswizzle( &$pix, $ow, $oh)
{
	// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
	// ezSwizzle.exe
	//   Convert on loading -> Convert to 8bit
	//   png size 64x64 or less == crashed
	//
	// SIMPLIFIED
	// original @ size 32x32 (* 4 lines)
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
	//  0 44  8 4c   1 45  9 4d   2 46  a 4e   3 47  b 4f
	//  4 40  c 48   5 41  d 49   6 42  e 4a   7 43  f 4b
	// 10 54 18 5c  11 55 19 5d  12 56 1a 5e  13 57 1b 5f
	// 14 50 1c 58  15 51 1d 59  16 52 1e 5a  17 53 1f 5b
	// 20 64 28 6c  21 65 29 6d  22 66 2a 6e  23 67 2b 6f
	// 24 60 2c 68  25 61 2d 69  26 62 2e 6a  27 63 2f 6b
	// 30 74 38 7c  31 75 39 7d  32 76 3a 7e  33 77 3b 7f
	// 34 70 3c 78  35 71 3d 79  36 72 3e 7a  37 73 3f 7b [repeat]
	//                                                   -
	$new = '';
	for ( $y=0; $y < $oh; $y += 4 )
	{
		$pos = $y * $ow;
		$len = $ow * 4;
		$sub = substr($pix, $pos, $len);

		$upper = '';
		$lower = '';
		for ( $i=0; $i < $len; $i += 0x20 )
		{
			$b1a = $sub[$i+0x00] . $sub[$i+0x04] . $sub[$i+0x08] . $sub[$i+0x0c]; // 0-3
			$b2a = $sub[$i+0x01] . $sub[$i+0x05] . $sub[$i+0x09] . $sub[$i+0x0d]; // 14-17
			$b3a = $sub[$i+0x02] . $sub[$i+0x06] . $sub[$i+0x0a] . $sub[$i+0x0e]; // 8-b
			$b4a = $sub[$i+0x03] . $sub[$i+0x07] . $sub[$i+0x0b] . $sub[$i+0x0f]; // 1c-1f

			$b1b = $sub[$i+0x10] . $sub[$i+0x14] . $sub[$i+0x18] . $sub[$i+0x1c]; // 4-7
			$b2b = $sub[$i+0x11] . $sub[$i+0x15] . $sub[$i+0x19] . $sub[$i+0x1d]; // 10-13
			$b3b = $sub[$i+0x12] . $sub[$i+0x16] . $sub[$i+0x1a] . $sub[$i+0x1e]; // c-f
			$b4b = $sub[$i+0x13] . $sub[$i+0x17] . $sub[$i+0x1b] . $sub[$i+0x1f]; // 18-1b

			$upper .= $b1a . $b1b . $b3a . $b3b;
			$lower .= $b2b . $b2a . $b4b . $b4a;
		} // for ( $i=0; $i < $len; $i += 0x20 )

		$new .= $upper . $lower;
	} // for ( $y=0; $y < $oh; $y += 4 )

	$pix = $new;
	return;
}

function tm2file( &$file, $pos, $dir, $id )
{
	printf("== tm2file( %x , %s, %d )\n", $pos, $dir, $id);
	$palsz = str2int($file, $pos+0x04, 4);
	$pixsz = str2int($file, $pos+0x08, 4);
	$hdsz  = str2int($file, $pos+0x0c, 2);
	$cc    = str2int($file, $pos+0x0e, 2);
	$ifmt  = str2int($file, $pos+0x10, 1);
	$mip   = str2int($file, $pos+0x11, 1);
	$cfmt  = str2int($file, $pos+0x12, 1);
	$dep   = str2int($file, $pos+0x13, 1);
	$zw    = str2int($file, $pos+0x14, 2);
	$zh    = str2int($file, $pos+0x16, 2);

	$lin  = ( $cfmt & 0x80 );
	$cfmt &= 0x7f;

	global $gp_depth;
	$fn = "$dir/$id.clut";
	printf("%8x , %xx%x , %x , %x , %s , %s\n", $pos, $zw, $zh, $ifmt, $cfmt, $gp_depth[$dep], $fn);

	$pal = substr($file, $pos+$hdsz+$pixsz, $palsz);
	$pix = substr($file, $pos+$hdsz, $pixsz);

	// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
	// TextureSwizzling.pdf
	//   256x128 texture
	//   - 8-bit = swizzle 128x64
	//   - 4-bit = swizzle 128x32
	//   texture size (in bytes) remain the same
	$bpp = -1;
	if ( $zw*$zh == $pixsz*2 ) // 4-bit original
	{
		$ow = $zw;
		$oh = $zh;
		$bpp = 4;
		printf("original 4-bit %xx%x\n", $ow, $oh);
		bpp4to8($pix);
	}
	else
	if ( $zw*$zh == $pixsz ) // 8-bit original
	{
		$ow = $zw;
		$oh = $zh;
		$bpp = 8;
		printf("original 8-bit %xx%x\n", $ow, $oh);
	}
	else
	if ( $zw*$zh*4 == $pixsz*2 ) // 4-bit swizzled
	{
		$ow = $zw*2;
		$oh = $zh*4;
		$bpp = 4;
		printf("swizzed  4-bit %xx%x\n", $ow, $oh);
		bpp4to8($pix);
		unswizzle($pix, $ow, $oh);
	}
	else
	if ( $zw*$zh*4 == $pixsz ) // 8-bit swizzled
	{
		$ow = $zw*2;
		$oh = $zh*2;
		$bpp = 8;
		printf("swizzed  8-bit %xx%x\n", $ow, $oh);
		unswizzle($pix, $ow, $oh);
	}
	if ( $bpp == -1 )
		return;
	save_file("$dir/$id.pix", $pix);

	$clut = [
		'cc' => $cc,
		'w' => $ow,
		'h' => $oh,
		'pal' => $pal,
		'pix' => $pix,
	];
	save_clutfile("$dir/$id.clut", $clut);
	return;
}

function ps2tm2( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "TIM2" )
		return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$ver = str2int($file, 4, 2);
	$cnt = str2int($file, 6, 2);
	$pos = 0x10;

	for ( $i=0; $i < $cnt; $i++ )
	{
		$tmsz = str2int($file, $pos+ 0, 4);
		tm2file($file, $pos, $dir, $i);
			$pos += $tmsz;
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ps2tm2( $argv[$i] );
