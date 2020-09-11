<?php
// non-PlayStation One (guest systems) functions

function str2big( &$str, $pos, $byte )
{
	$s = substr($str, $pos, $byte);
	$s = strrev($s);
	return ordint($s);
}

function chrbig( $int, $byte )
{
	$t = chrint($int, $byte);
	return strrev($t);
}

function str_endian( &$str, $pos, $byte )
{
	$s = substr($str, $pos, $byte);
	$s = strrev($s);
	strupd($str, $pos, $s);
	return;
}

function req_ext( $func, $ext )
{
	if ( ! function_exists($func) )
		trigger_error("REQ $ext extension not found!\n", E_USER_ERROR);
	return;
}

function bits8( $bit7 , $bit6 , $bit5 , $bit4 , $bit3 , $bit2 , $bit1 , $bit0 )
{
	$b7 = ($bit7 & 1) << 7;
	$b6 = ($bit6 & 1) << 6;
	$b5 = ($bit5 & 1) << 5;
	$b4 = ($bit4 & 1) << 4;
	$b3 = ($bit3 & 1) << 3;
	$b2 = ($bit2 & 1) << 2;
	$b1 = ($bit1 & 1) << 1;
	$b0 = ($bit0 & 1) << 0;
	return ( $b7|$b6|$b5|$b4|$b3|$b2|$b1|$b0 );
}
//////////////////////////////
function clut2rgba( $pal, $pix, $bgzero = true )
{
	$rgba = "";
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $pix[$i] == ZERO )
			$rgba .= ( $bgzero ) ? substr($pal, 0, 4) : PIX_ALPHA;
		else
		{
			$p = ord( $pix[$i] ) * 4;
			if ( ! isset( $pal[$p+3] ) )
				$rgba .= PIX_ALPHA;
			else
				$rgba .= substr($pal, $p, 4);
		}
	}
	return $rgba;
}

function rgba2clut( $rgba, $bg = PIX_BLACK )
{
	$clut = array();
	if ( ! empty($bg) )
		$clut[] = $bg;
	$pix  = "";
	$siz  = strlen($rgba);
	for ( $i=0; $i < $siz; $i += 4 )
	{
		$c = substr($rgba, $i, 4);
		if ( array_search($c, $clut) === false )
			$clut[] = $c;
		$p = array_search($c, $clut);
		if ( $p > BIT8 )
			trigger_error("rgba2clut() clut over 256 colors\n", E_USER_ERROR);
		$pix .= chr($p);
	}
	$clut = implode('', $clut);
	return array($clut, $pix);
}

function riprgba( &$rgba, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") )
		return;

	$rip = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$sxx = ($sy + $y) * $sw + $sx;
		$rip .= substr($rgba, $sxx*4, $w*4);
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}

function copyrgba( &$pix )
{
	copypix($pix, 4);
	return;
}

function alpha_over( $src, $dst )
{
	if ( $src[3] == BYTE )  return $src;
	if ( $src[3] == ZERO )  return $dst;
	if ( $dst[3] == ZERO )  return $src;
	// en.wikipedia.org/wiki/Alpha_compositing#Alpha_blending
	// a is [0.0-1.0]
	//   out_a = src_a + dst_a (1-src_a)
	//   out_rgb = ( src_rgb*src_a + dst_rgb*dst_a (1-src_a) ) / out_a
	// if dst_a = 1
	//   out_a = 1
	//   out_rgb = src_rgb*src_a + dst_rgb (1-src_a)
	// if rgb is premultiplied
	//   out_a = src_a + dst_a (1-src_a)
	//   out_rgb = src_rgb + dst_rgb (1-src_a)
	$src_a = ord($src[3]) / BIT8;
	$dst_a = ord($dst[3]) / BIT8;
	$r = ord($src[0]) * $src_a + ord($dst[0]) * $dst_a * (1-$src_a);
	$g = ord($src[1]) * $src_a + ord($dst[1]) * $dst_a * (1-$src_a);
	$b = ord($src[2]) * $src_a + ord($dst[2]) * $dst_a * (1-$src_a);
	$a = $src_a + $dst_a * (1-$src_a);

	$r = int_clamp($r / $a, 0, BIT8);
	$g = int_clamp($g / $a, 0, BIT8);
	$b = int_clamp($b / $a, 0, BIT8);
	$a = int_clamp($a * BIT8, 0, BIT8);
	return chr($r) . chr($g) . chr($b) . chr($a);
}

function scalergba( &$pix, $fact_x, $fact_y )
{
	scalepix( $pix, $fact_x, $fact_y, 4 );
	return;
}
//////////////////////////////
function nds_ram( $dir )
{
	$head = load_file("$dir/header.bin");
	if ( empty($head) )
		return "";
	$ram = str_repeat(ZERO, 0x400000);

	$bin = load_file("$dir/arm9.bin");
	$off = str2int($head, 0x28, 3);
		strupd($ram, $off, $bin);
	$bin = load_file("$dir/arm7.bin");
	$off = str2int($head, 0x38, 3);
		strupd($ram, $off, $bin);
	return $ram;
}

function nds_patch( $dir, $pfx )
{
	$head = load_file("$dir/header.bin");
	if ( empty($head) )
		return array();
	$NTR = substr($head, 12, 4);

	return patchfile("{$pfx}_{$NTR}.txt");
}

function nds_overlay( &$ndsram, $dir, $id )
{
	if ( strpos($id, 'ov-') !== false )
		$id = (int)substr($id, 3);

	$y9   = load_file("$dir/y9.bin");
	if ( ! isset( $y9[$id*0x20] ) )
		return array();
	$off   = str2int($y9, ($id * 0x20) + 0x04, 3);
	$start = str2int($y9, ($id * 0x20) + 0x10, 3);

	$over = sprintf("$dir/overlay/overlay_%04d.bin", $id);
	$bin  = load_file($over);
	printf("load OVERLAY %d[0x%x] @ %x => %x\n", $id, $id, $off, $start);
		strupd($ndsram, $off, $bin);
	return array($off, $start);
}
//////////////////////////////

