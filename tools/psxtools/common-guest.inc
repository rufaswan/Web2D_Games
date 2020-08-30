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
			$rgba .= substr($pal, ord($pix[$i])*4, 4);
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
	if ( copypix_errs($pix) )
		return;
	if ( defined("DRY_RUN") )
		return;

	flippix($pix, 4);

	$rot = ($pix['rotate'][0] & BIT8) * pi() / 0x80;
	$sin = sin($rot);
	$cos = cos($rot);
	$hyp = hypot($pix['src']['w'], $pix['src']['h']) * 0.6;

	$tx = $pix['rotate'][1] + ($pix['src']['w'] / 2);
	$ty = $pix['rotate'][2] + ($pix['src']['h'] / 2);
		$cx = $tx * $cos - $ty * $sin;
		$cy = $tx * $sin + $ty * $cos;
	$x1 = (int)($cx - $hyp);
	$y1 = (int)($cy - $hyp);
	$x2 = (int)($cx + $hyp);
	$y2 = (int)($cy + $hyp);

	$rot = ((0x100 - $pix['rotate'][0]) & BIT8) * pi() / 0x80;
	$sin = sin($rot);
	$cos = cos($rot);
	for ( $y=$y1; $y <= $y2; $y++ )
	{
		$dy = $y + $pix['dy'];
		if ( $dy < 0 || $dy >= $pix['rgba']['h'] )
			continue;

		for ( $x=$x1; $x <= $x2; $x++ )
		{
			$dx = $x + $pix['dx'];
			if ( $dx < 0 || $dx >= $pix['rgba']['w'] )
				continue;

			$rx = (int)round($x * $cos - $y * $sin) - $pix['rotate'][1];
			$ry = (int)round($x * $sin + $y * $cos) - $pix['rotate'][2];
			if ( $rx < 0 || $rx >= $pix['src']['w'] )
				continue;
			if ( $ry < 0 || $ry >= $pix['src']['h'] )
				continue;

			$sxx = ($ry * $pix['src']['w']) + $rx;
			$c1 = substr($pix['src']['pix'], $sxx*4, 4);

			$dxx = ($dy * $pix['rgba']['w']) + $dx;
			$c2 = substr($pix['rgba']['pix'], $dxx*4, 4);

			$alpha = $pix['alpha'];
			if ( function_exists($alpha) )
				$c1 = $alpha($c1, $c2);

			strupd($pix['rgba']['pix'], $dxx*4, $c1);

		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )

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
	return scalepix( $pix, $fact_x, $fact_y, 4 );
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

