<?php
/*
[license]
[/license]
 *
 * Special Thanks
 *   http://playstationdev.wiki/psvitadevwiki/index.php?title=GXT
 *   http://forum.xentax.com/viewtopic.php?f=18&t=16171&sid=00e26d4f119d2985bbc8137c42e3a10d
 */
require "common.inc";
require "common-guest.inc";

//define("DRY_RUN", true);

$gp_fmt = array(
	"\x00\x10\x00\x0c" => 'im_argb8888',
	"\x00\x00\x00\x85" => 'im_dxt1',
	"\x00\x00\x00\x86" => 'im_dxt3',
	"\x00\x00\x00\x87" => 'im_dxt5',
);
$gp_type = array(
	"\x00\x00\x00\x00" => 'gxt_swizzled',
	"\x00\x00\x00\xa0" => 'gxt_a0',
);
//////////////////////////////
function unmorton_square( &$pix, &$buf, $base, &$pos, $min, $row )
{
	if ( $min == 2 )
	{
		$s1 = substr($buf, $pos+ 0, 4);
		$s2 = substr($buf, $pos+ 4, 4);
		$s3 = substr($buf, $pos+ 8, 4);
		$s4 = substr($buf, $pos+12, 4);
			$pos += 16;

		str_update($pix, ($base           ) * 4, $s1);
		str_update($pix, ($base + $row    ) * 4, $s2);
		str_update($pix, ($base + 1       ) * 4, $s3);
		str_update($pix, ($base + $row + 1) * 4, $s4);
		return;
	}
	else
	{
		$func = __FUNCTION__;
		$hm = $min >> 1;
		$rh = $row * $hm;
		$func($pix, $buf, $base            , $pos, $hm, $row);
		$func($pix, $buf, $base + $rh      , $pos, $hm, $row);
		$func($pix, $buf, $base + $hm      , $pos, $hm, $row);
		$func($pix, $buf, $base + $rh + $hm, $pos, $hm, $row);
		return;
	}
	return;
}

function gxt_swizzled( &$pix, $ow, $oh )
{
	printf("== gxt_swizzled( %x , %x )\n", $ow, $oh);
	//return;
	$buf = $pix;
	$row = $ow;
	$pos = 0;

	// square image = 128x128
	$min = $ow;
	if ( $ow == $oh )
		return unmorton_square($pix, $buf, 0, $pos, $min, $row);

	// landscape image = 512x128
	// split it into 4 parts of 128x128 square
	if ( $ow > $oh )
	{
		$min = $oh;
		for ( $i=0; $i < $ow; $i += $min )
			unmorton_square($pix, $buf, $i, $pos, $min, $row);
		return;
	}

	// portrait image = 128x512
	// split it into 4 parts of 128x128 square
	if ( $oh > $ow )
	{
		$min = $ow;
		for ( $i=0; $i < $oh; $i += $min )
			unmorton_square($pix, $buf, $i*$min, $pos, $min, $row);
		return;
	}
	return;
}

// Unswizzle logic by @FireyFly
//   http://xen.firefly.nu/up/rearrange.c.html
// REMOVED = wrong unswizzle
//           image is flipped and rotated 270 degree

function gxt_a0( &$pix, $w, $h )
{
	printf("== gxt_a0( %x , %x )\n", $w, $h);
	return gxt_swizzled($pix, $w, $h);
	//return;
}
//////////////////////////////
// https://en.m.wikipedia.org/wiki/S3_Texture_Compression
function dxt_block( &$pix, &$dxt )
{
	$pix .= $dxt[ 0] . $dxt[ 4] . $dxt[ 1] . $dxt[ 5];
	$pix .= $dxt[ 8] . $dxt[12] . $dxt[ 9] . $dxt[13];
	$pix .= $dxt[ 2] . $dxt[ 6] . $dxt[ 3] . $dxt[ 7];
	$pix .= $dxt[10] . $dxt[14] . $dxt[11] . $dxt[15];
	return;
}

function dxt_mix( &$color, $alpha )
{
	// in 4x4 block
	//echo debug( $alpha );
	for ( $i=0; $i < 16; $i++ )
	{
		$color[$i][3] = $alpha[$i];
		//echo debug( $color[$i] );
	}
	return;
}

function dxt5_alpha( $str )
{
	$a = array();
	$a[0] = ord( $str[0] );
	$a[1] = ord( $str[1] );
	if ( $a[0] > $a[1] )
	{
		$a[2] = int_clamp((6*$a[0] + 1*$a[1])/7, 0, BIT8);
		$a[3] = int_clamp((5*$a[0] + 2*$a[1])/7, 0, BIT8);
		$a[4] = int_clamp((4*$a[0] + 3*$a[1])/7, 0, BIT8);
		$a[5] = int_clamp((3*$a[0] + 4*$a[1])/7, 0, BIT8);
		$a[6] = int_clamp((2*$a[0] + 5*$a[1])/7, 0, BIT8);
		$a[7] = int_clamp((1*$a[0] + 6*$a[1])/7, 0, BIT8);
	}
	else
	{
		$a[2] = int_clamp((4*$a[0] + 1*$a[1])/5, 0, BIT8);
		$a[3] = int_clamp((3*$a[0] + 2*$a[1])/5, 0, BIT8);
		$a[4] = int_clamp((2*$a[0] + 3*$a[1])/5, 0, BIT8);
		$a[5] = int_clamp((1*$a[0] + 4*$a[1])/5, 0, BIT8);
		$a[6] = 0;
		$a[7] = BIT8;
	}

	// 4x4 3-bit lookup
	$data = '';
	for ( $i=2; $i < 8; $i += 3 )
	{
		$b = ordint( $str[$i+0] . $str[$i+1] . $str[$i+2] );
		for ( $j=0; $j < 24; $j += 3 )
		{
			$b1 = ($b >> $j) & 7;
			$data .= chr( $a[$b1] );
		}
	}
	return $data;
}

function dxt3_alpha( $str )
{
	$alp = '';
	for ( $i=0; $i < 8; $i++ )
	{
		$b = ord( $str[$i] );
		$b1 = ($b >> 0) & BIT4;
		$b2 = ($b >> 4) & BIT4;

		$b1 |= ($b1 << 4);
		$b2 |= ($b2 << 4);
		$alp .= chr($b1) . chr($b2);
	}
	return $alp;
}

function dxt_rgb565( $str )
{
	// fedc ba98  7654 3210
	// rrrr rggg  gggb bbbb
	$pal = ordint($str);
	$b = ($pal << 3) & 0xf8; // << 11 >> 8
	$g = ($pal >> 3) & 0xfc; // <<  5 >> 8
	$r = ($pal >> 8) & 0xf8; // <<  0 >> 8
	return chr($r) . chr($g) . chr($b) . BYTE;
}

function dxt_color_math( $c0, $f0, $c1, $f1 )
{
	$c0r = ord( $c0[0] ) * $f0;
	$c0g = ord( $c0[1] ) * $f0;
	$c0b = ord( $c0[2] ) * $f0;
	$c1r = ord( $c1[0] ) * $f1;
	$c1g = ord( $c1[1] ) * $f1;
	$c1b = ord( $c1[2] ) * $f1;
	$cr = int_clamp($c0r + $c1r, 0, BIT8);
	$cg = int_clamp($c0g + $c1g, 0, BIT8);
	$cb = int_clamp($c0b + $c1b, 0, BIT8);
	return chr($cr) . chr($cg) . chr($cb) . BYTE;
}

function dxt_color( $str, $bc )
{
	$c0 = $str[0] . $str[1];
	$c1 = $str[2] . $str[3];

	$pal = array();
	$pal[0] = dxt_rgb565($c0);
	$pal[1] = dxt_rgb565($c1);

	$c01 = ordint($c0);
	$c11 = ordint($c1);
	// always use 4-colors version for non-BC1 / DXT1
	if ( $bc != 1 || $c01 > $c11 )
	{
		$pal[2] = dxt_color_math( $pal[0], 2/3, $pal[1], 1/3 );
		$pal[3] = dxt_color_math( $pal[0], 1/3, $pal[1], 2/3 );
	}
	else
	{
		$pal[2] = dxt_color_math( $pal[0], 1/2, $pal[1], 1/2 );
		$pal[3] = PIX_ALPHA;
	}

	// 4x4 2-bit lookup
	$data = array();
	for ( $i=4; $i < 8; $i++ )
	{
		$b1 = ord( $str[$i] );
		for ( $j=0; $j < 8; $j += 2 )
		{
			$b2 = ($b1 >> $j) & 3;
			$data[] = $pal[$b2];
		}
	} // for ( $i=4; $i < 8; $i++ )
	return $data;
}
//////////////////////////////
function im_dxt5( &$file, $pos, $fn, $w, $h )
{
	printf("== im_dxt5( %x , %s , %x , %x )\n", $pos, $fn, $w, $h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	// store 16 pixel = to 16 bytes
	// alpha = 2x  8-bit alpha  , then 4x4 3-bit lookup
	// color = 2x 16-bit RGB565 , then 4x4 2-bit lookup
	$pix = '';
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$alp = substr($file, $pos+0, 8);
			$clr = substr($file, $pos+8, 8);
				$pos += 16;
			$alp = dxt5_alpha($alp);
			$clr =  dxt_color($clr, 3);
				dxt_mix($clr, $alp);

			dxt_block($pix, $clr);
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	return $pix;
}

function im_dxt3( &$file, $pos, $fn, $w, $h )
{
	printf("== im_dxt3( %x , %s , %x , %x )\n", $pos, $fn, $w, $h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	// store 16 pixel = to 16 bytes
	// alpha = 16x  4-bit alpha
	// color =  2x 16-bit RGB565 , then 4x4 2-bit lookup
	$pix = '';
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$alp = substr($file, $pos+0, 8);
			$clr = substr($file, $pos+8, 8);
				$pos += 16;
			$alp = dxt3_alpha($alp);
			$clr =  dxt_color($clr, 2);
				dxt_mix($clr, $alp);

			dxt_block($pix, $clr);
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	return $pix;
}

function im_dxt1( &$file, $pos, $fn, $w, $h )
{
	printf("== im_dxt1( %x , %s , %x , %x )\n", $pos, $fn, $w, $h);
	$w = int_ceil_pow2($w);
	$h = int_ceil_pow2($h);

	// store 16 pixel to 8 bytes
	// alpha = none
	// color = 2x 16-bit RGB565 , then 4x4 2-bit lookup
	$pix = '';
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$clr = substr($file, $pos+8, 8);
				$pos += 8;
			$clr = dxt_color($clr, 1);

			dxt_block($pix, $clr);
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	return $pix;
}
//////////////////////////////
function im_argb8888( &$file, $pos, $fn, $w, $h )
{
	printf("== im_argb8888( %x , %s , %x , %x )\n", $pos, $fn, $w, $h);

	$pix = '';
	$siz = $w * $h;
	for ( $i=0; $i < $siz; $i++ )
	{
		$pix .= $file[$pos+2]; // r
		$pix .= $file[$pos+1]; // g
		$pix .= $file[$pos+0]; // b
		$pix .= $file[$pos+3]; // a
			$pos += 4;
	}
	return $pix;
}
//////////////////////////////
function vitagxt( &$file, $base, $pfx, $id )
{
	printf("== vitagxt( %x , $pfx , $id )\n", $base);
	if ( substr($file, $base+0, 4) != "GXT\x00" )
		return;

	$cnt = str2int($file, $base+8, 4);
	if ( $cnt != 1 )
		return php_error("%s/%04d is multi-GXT [%d]", $pfx, $id, $cnt);

	$typ = substr ($file, $base+0x30, 4);
	$fmt = substr ($file, $base+0x34, 4);
	$w   = str2int($file, $base+0x38, 2);
	$h   = str2int($file, $base+0x3a, 2);
	printf("SIZE %x x %x = %x\n", $w, $h, $w*$h);

	global $gp_type, $gp_fmt;
	if ( ! isset( $gp_fmt [$fmt] ) )
		return php_error("UNKNOWN im fmt  %s", debug($fmt));
	if ( ! isset( $gp_type[$typ] ) )
		return php_error("UNKNOWN im type %s", debug($typ));
	printf("DETECT  fmt %s  type %s\n", $gp_fmt[$fmt], $gp_type[$typ]);
	//return;

	$pos = str2int($file, $base+0x20, 4);
	$siz = str2int($file, $base+0x24, 4);
	$fn  = sprintf("%s.%d.gtx", $pfx, $id);

	$func = $gp_fmt[$fmt];
	$pix = $func($file, $base+$pos, $fn, $w, $h);

	$func = $gp_type[$typ];
	if ( function_exists($func) )
		$func($pix, $w, $h);

	$img = "RGBA";
	$img .= chrint($w, 4);
	$img .= chrint($h, 4);
	$img .= $pix;
	save_file($fn, $img);
	return;
}
//////////////////////////////
function mura( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = str2int($file,  8, 4);
	$cnt  = str2int($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) != "FTX0" )
			return php_error("%s 0x%x not FTX0\n", $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("GXT  %x , %x , %s\n", $st, $sz1, $fn);

		vitagxt($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );

/*
mura
	app
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	patch
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 1
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 2
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 3
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 4
		-- -- -- --  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
dcrown
	app
		-- -- -- --  gxt_swizzled
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
	patch
		-- -- -- --  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
odin
	bt
		-- -- -- --  gxt_swizzled
		-- -- -- 87  im_dxt5
	or
		-- -- -- --  gxt_swizzled
		-- -- -- a0  gxt_
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
		-- 10 -- 0c  im_argb8888
	re
		-- -- -- --  gxt_swizzled
		-- -- -- a0  gxt_
		-- -- -- 85  im_dxt1
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
		-- 10 -- 0c  im_argb8888

NinPriPack1_cpk
	ftx.7z               = 16 MB
	ftx -> gtx.7z        = 28 MB
	ftx -> gtx -> png.7z = 38 MB
 */
