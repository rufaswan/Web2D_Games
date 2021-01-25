<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("DRY_RUN", true);

// https://playstationdev.wiki/psvitadevwiki/index.php?title=GXT
$gp_type = array(
	0x00 => 'gxt_swizzled',
);
$gp_fmt = array(
	"\x00\x00\x00\x86" => 'im_dxt3',
	"\x00\x00\x00\x87" => 'im_dxt5',
);

//////////////////////////////
// https://en.m.wikipedia.org/wiki/S3_Texture_Compression
function dxt_mix( &$color, $alpha )
{
	// in 4x4 block
	for ( $i=0; $i < 16; $i++ )
	{
		$p = $i * 4 + 3;
		$color[$p] = $alpha[$i];
	}
	return;
}

function dxt5_alpha( $str )
{
	$a0 = ord( $str[0] );
	$a1 = ord( $str[1] );
	if ( $a0 > $a1 )
	{
		$a2 = int_clamp((6*$a0 + 1*$a1)/7, 0, BIT8);
		$a3 = int_clamp((5*$a0 + 2*$a1)/7, 0, BIT8);
		$a4 = int_clamp((4*$a0 + 3*$a1)/7, 0, BIT8);
		$a5 = int_clamp((3*$a0 + 4*$a1)/7, 0, BIT8);
		$a6 = int_clamp((2*$a0 + 5*$a1)/7, 0, BIT8);
		$a7 = int_clamp((1*$a0 + 6*$a1)/7, 0, BIT8);
	}
	else
	{
		$a2 = int_clamp((4*$a0 + 1*$a1)/5, 0, BIT8);
		$a3 = int_clamp((3*$a0 + 2*$a1)/5, 0, BIT8);
		$a4 = int_clamp((2*$a0 + 3*$a1)/5, 0, BIT8);
		$a5 = int_clamp((1*$a0 + 4*$a1)/5, 0, BIT8);
		$a6 = 0;
		$a7 = 255;
	}
	$pal = chr($a0) . chr($a1) . chr($a2) . chr($a3) . chr($a4) . chr($a5) . chr($a6) . chr($a7);

	$data = '';
	for ( $i=2; $i < 8; $i += 3 )
	{
		$b = ordint( $str[$i+0] . $str[$i+1] . $str[$i+2] );
		for ( $j=0; $j < 24; $j += 3 )
		{
			$b1 = ($b >> $j) & 7;
			$data .= $pal[$b1];
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
	$c1r = ord( $c0[0] ) * $f0;
	$c1g = ord( $c0[1] ) * $f0;
	$c1b = ord( $c0[2] ) * $f0;
	$c2r = ord( $c1[0] ) * $f1;
	$c2g = ord( $c1[1] ) * $f1;
	$c2b = ord( $c1[2] ) * $f1;
	$cr = int_clamp($c0r + $c1r, 0, BIT8);
	$cg = int_clamp($c0g + $c1g, 0, BIT8);
	$cb = int_clamp($c0b + $c1b, 0, BIT8);
	return chr($cr) . chr($cg) . chr($cb) . BYTE;
}

function dxt_color( $str )
{
	$c0 = $str[0] . $str[1];
	$c1 = $str[2] . $str[3];

	$pal = array();
	$pal[] = dxt_rgb565($c0);
	$pal[] = dxt_rgb565($c1);

	$c01 = ordint($c0);
	$c11 = ordint($c1);
	if ( $c01 > $c11 )
	{
		$pal[] = cmpr_inter( $pal[0], 2/3, $pal[1], 1/3 );
		$pal[] = cmpr_inter( $pal[0], 1/3, $pal[1], 2/3 );
	}
	else
	{
		$pal[] = cmpr_inter( $pal[0], 1/2, $pal[1], 1/2 );
		$pal[] = PIX_ALPHA;
	}

	$data = '';
	for ( $i=4; $i < 8; $i++ )
	{
		$b1 = ord( $str[$i] );
		for ( $j=0; $j < 8; $j += 2 )
		{
			$b2 = ($b1 >> $j) & 3;
			$data .= $pal[$b2];
		}
	} // for ( $i=4; $i < 8; $i++ )
	return $data;
}

function dxt_rgba( &$data, $w, $h )
{
	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => canvpix($w, $h),
	);

	// copy 4x4 blocks to canvas
	$col = 0;
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$dxx = (($y * $w) + $x) * 4;
			for ( $r=0; $r < 4; $r++ )
			{
				$s = substr($data[$col], $r*4*4, 4*4);
				str_update($img['pix'], $dxx+$r*$w*4, $s);
			}
			$col++;
		}
	}

	$data = $img;
	return;
}

function im_dxt5( &$str, $fn, $w, $h, $swizz )
{
	printf("== im_dxt5( %s , %x , %x , %s )\n", $fn, $w, $h, $swizz);
	// store 16 pixel = to 16 bytes
	// alpha = 2x 8-bit , then 4x4 3-bit lookup
	// color = 2x RGB565 pixel , then 4x4 2-bit lookup
	$len = strlen($str);
	$img = array();
	for ( $i=0; $i < $len; $i += 16 )
	{
		$alp = substr($str, $i+0, 8);
		$alp = dxt5_alpha($alp);

		$clr = substr($str, $i+8, 8);
		$clr = dxt_color($sub);

		dxt_mix($clr, $alp);
		$img[] = $clr;
	} // for ( $i=0; $i < $len; $i += 16 )

	dxt_rgba($img, $w, $h);
	if ( function_exists($swizz) )
		$swizz();

	save_clutfile($fn, $img);
	return;
}

function im_dxt3( &$str, $fn, $w, $h, $swizz )
{
	printf("== im_dxt3( %s , %x , %x , %s )\n", $fn, $w, $h, $swizz);
	// store 16 pixel = to 16 bytes
	// alpha = 16x 4-bit for 4x4
	// color = 2x RGB565 pixel , then 4x4 2-bit lookup
	$len = strlen($str);
	$img = array();
	for ( $i=0; $i < $len; $i += 16 )
	{
		$alp = substr($str, $i+0, 8);
		$alp = dxt3_alpha($alp);

		$clr = substr($str, $i+8, 8);
		$clr = dxt_color($sub);

		dxt_mix($clr, $alp);
		$img[] = $clr;
	} // for ( $i=0; $i < $len; $i += 16 )

	dxt_rgba($img, $w, $h);
	if ( function_exists($swizz) )
		$swizz();

	save_clutfile($fn, $img);
	return;
}

function im_dxt1( &$str, $fn, $w, $h, $swizz )
{
	printf("== im_dxt1( %s , %x , %x , %s )\n", $fn, $w, $h, $swizz);
	// store 16 pixel to 8 bytes
	// alpha = none
	// color = 2x 16-bit RGB565 , then 4x4 2-bit lookup
	$len = strlen($str);
	$img = array();
	for ( $i=0; $i < $len; $i += 8 )
	{
		$clr = substr($str, $i, 8);
		$clr = dxt_color($sub);
		$img[] = $clr;
	} // for ( $i=0; $i < $len; $i += 8 )

	dxt_rgba($img, $w, $h);
	if ( function_exists($swizz) )
		$swizz();

	save_clutfile($fn, $img);
	return;
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

	$typ = str2int($file, $base+0x30, 4);
	$fmt = substr ($file, $base+0x34, 4);
	$w = str2int($file, $base+0x38, 2);
	$h = str2int($file, $base+0x3a, 2);
	printf("SIZE %x x %x = %x\n", $w, $h, $w*$h);

	global $gp_type, $gp_fmt;
	if ( ! isset( $gp_type[$typ] ) )
		return php_error("UNKNOWN im type %x", $typ);
	if ( ! isset( $gp_fmt [$fmt] ) )
		return php_error("UNKNOWN im fmt %s", debug($fmt));
	printf("DETECT type %s  fmt %s\n", $gp_type[$typ], $gp_fmt[$fmt]);
	return;

	$pos = str2int($file, $base+0x20, 4);
	$siz = str2int($file, $base+0x24, 4);
	$fn  = sprintf("%s.%d.gtx", $pfx, $id);

	$sub = substr($file, $base+$pos, $siz);
	$func = $gp_fmt[$fmt];
	$func($sub, $fn, $w, $h, $gp_type[$fmt]);
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
	patch
		0  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 1
		0  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 2
		0  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 3
		0  gxt_swizzled
		-- -- -- 87  im_dxt5
	dlc 4
		0  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
dcrown
	app
	patch
		0  gxt_swizzled
		-- -- -- 86  im_dxt3
		-- -- -- 87  im_dxt5
gkh
odin

 */
