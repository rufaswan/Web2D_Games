<?php
//		'quad' => [], // replaces sx,sy for copyquad
//		'rotate' => [0,0,0],
//		'quad'   => [], // replaces dx,dy for copyquad
//		'alpha'  => '', // callback

function alpha_normal( $src, $dst )
{
	if ( $src[3] == BYTE )  return $src;
	if ( $src[3] == ZERO )  return $dst;
	if ( $dst[3] == ZERO )  return $src;

	// en.wikipedia.org/wiki/Alpha_compositing#Alpha_blending
	// rgb is int 0-255 , a is float 0-1
	//
	// FULL Formula
	//   out_a   =   src_a           + dst_a           * (1-src_a)
	//   out_rgb = ( src_rgb * src_a + dst_rgb * dst_a * (1-src_a) ) / out_a
	//
	// rgba(40,40,40,0.5) + rgba(60,60,60,1)
	//   a   = 0.5 + 1 * (1 - 0.5)
	//       = 0.5 + 0.5
	//       = 1
	//   rgb = ( 40 * 0.5 + 60 * 1 * (1 - 0.5) ) / 1
	//       = ( 20 + 30 ) / 1
	//       = 50
	//   out = rgba(50,50,50,1);
	//
	// IF dst_a == 1 , SIMPLIFIED Formula
	//   out_a   = 1
	//   out_rgb = src_rgb * src_a + dst_rgb * (1-src_a)
	//
	$src_a = ord($src[3]) / BIT8;
	$dst_a = ord($dst[3]) / BIT8;
	$r = ord($src[0]) * $src_a + ord($dst[0]) * $dst_a * (1-$src_a);
	$g = ord($src[1]) * $src_a + ord($dst[1]) * $dst_a * (1-$src_a);
	$b = ord($src[2]) * $src_a + ord($dst[2]) * $dst_a * (1-$src_a);
	$a = $src_a + $dst_a * (1-$src_a);

	$r = int_clamp($r / $a  , 0, BIT8);
	$g = int_clamp($g / $a  , 0, BIT8);
	$b = int_clamp($b / $a  , 0, BIT8);
	$a = int_clamp($a * BIT8, 0, BIT8);
	return chr($r) . chr($g) . chr($b) . chr($a);
}

function alpha_none( $fg, $bg )
{
	if ( $fg[3] === ZERO )  return $bg;
	return $fg;
}

function alpha_add( $fg, $bg )
{
	// FG is RGB from palette
	// if BG is blank , FG RGB -> RGBA
	// if BG is solid , FG += BG
	// if BG is RGBA  , BG RGBA -> RGB, FG += BG, FG RGB -> RGBA
	switch ( $bg[3] )
	{
		// blend w/normal pixels
		case BYTE:
			$pix = '';
			for ( $i=0; $i < 4; $i++ )
			{
				$p1 = ord( $fg[$i] );
				$p2 = ord( $bg[$i] );
				$p = int_clamp($p1 + $p2, 0, BIT8);
				$pix .= chr($p);
			}
			return $pix;

		// blend w/transparent pixel
		// #202020 + #00000000 = #ffffff20
		// #204080 + #00000000 = #4080ff80
		case ZERO:
			$r = ord( $fg[0] );
			$g = ord( $fg[1] );
			$b = ord( $fg[2] );
			$a = var_max($r, $g, $b);
			if ( $a === 0 )
				return $bg;

			$r = int_clamp($r * BIT8 / $a, 0, BIT8);
			$g = int_clamp($g * BIT8 / $a, 0, BIT8);
			$b = int_clamp($b * BIT8 / $a, 0, BIT8);
			return chr($r) . chr($g) . chr($b) . chr($a);

		// blend w/semi-transparent pixel
		// #202020   + #204080   = #4060a0
		// #ffffff20 + #4080ff80 = #6699ffa0 (NOT #ffffffa0 !!!)
		// #202020   + #4080ff80 = #6699ffa0
		default:
			$a2 = ord($bg[3]);
			$r2 = ord($bg[0]) / BIT8 * $a2;
			$g2 = ord($bg[1]) / BIT8 * $a2;
			$b2 = ord($bg[2]) / BIT8 * $a2;

			$r = int_clamp(ord($fg[0]) + $r2, 0, BIT8);
			$g = int_clamp(ord($fg[1]) + $g2, 0, BIT8);
			$b = int_clamp(ord($fg[2]) + $b2, 0, BIT8);
			$nfg = chr($r) . chr($g) . chr($b) . BYTE;

			$func = __FUNCTION__;
			return $func($nfg, PIX_ALPHA);
	}
	return $fg;
}

function scalepix( &$pix, $fact_x, $fact_y, $byte=1 )
{
	if ( defined('DRY_RUN') )
		return;

	$nw = (int)ceil( $pix['src']['w'] * $fact_x );
	$nh = (int)ceil( $pix['src']['h'] * $fact_y );
	$canv = canvpix($nw, $nh);
	$mx = $pix['src']['w'] / $nw;
	$my = $pix['src']['h'] / $nh;
	for ( $y=0; $y < $nh; $y++ )
	{
		$dyy = $y * $nw;
		$syy = (int)($y * $my) * $pix['src']['w'];
		for ( $x=0; $x < $nw; $x++ )
		{
			$dxx = $dyy + $x;
			$sxx = (int)($x * $mx) + $syy;

			$c1 = substr($pix['src']['pix'], $sxx*$byte, $byte);
			str_update($canv, $dxx*$byte, $c1);
		} // for ( $x=0; $x < $nw; $x++ )
	} // for ( $y=0; $y < $nh; $y++ )

	$pix['src']['w'] = $nw;
	$pix['src']['h'] = $nh;
	$pix['src']['pix'] = $canv;
	return;
}

function updatepix( &$pix, $byte, $sx, $sy, $dx, $dy )
{
	if ( $sx < 0 || $sx >= $pix['src']['w'] )
		return;
	if ( $sy < 0 || $sy >= $pix['src']['h'] )
		return;
	//trace("== updatepix( %d , %d , %d , %d , %d )\n", $byte, $sx, $sy, $dx, $dy);

	$sxx = ($sy * $pix['src']['w']) + $sx;
	$c1 = substr($pix['src']['pix'], $sxx*$byte, $byte);

	// Ignore BG color
	//   byte 1 bgzero 0    = cmp c1, bgzero
	//   byte 1 bgzero RGBA = c1 -> RGBA , cmp c1, bgzero
	//   byte 4 bgzero 0    = *OK*
	//   byte 4 bgzero RGBA = cmp c1, bgzero
	// Xenogears Lucca = 0th and 15th color are both #000
	//                   ignore 0th #000 , but not 15th #000
	if ( $byte === 1 )
	{
		$c1 = ord($c1);
		if ( $c1 === $pix['bgzero'] )
			return;
		$c1 = substr($pix['src']['pal'], $c1*4, 4);
	}

	// c1 is now RGBA
	if ( $c1 === $pix['bgzero'] )
		return;

	$dxx = ($dy * $pix['rgba']['w']) + $dx;
	$c2 = substr($pix['rgba']['pix'], $dxx*4, 4);

	// ALPHA BLENDING
	// using callbacks due to implementation difference
	// Legend of Mana /wm/wmland/*
	//   type 1 is PIX=  FG    + BG
	//   type 3 is PIX= (FG/5) + BG
	$alpha = $pix['alpha'];
	if ( function_exists($alpha) )
		$c1 = $alpha($c1, $c2);

	// COPY / UPDATE PIXEL
	str_update($pix['rgba']['pix'], $dxx*4, $c1);
	return;
}

function copypix( &$pix, $byte=1 )
{
	if ( $byte != 1 && $byte != 4 ) // 1=CLUT  4=RGBA  *=invalid
		return;
	if ( empty( $pix['src']['pix'] ) )
		return;
	if ( copypix_errs($pix) )
		return;
	if ( defined('DRY_RUN') )
		return;

	flippix($pix, $byte);

	// src rotation
	//   0x100 == 360 degree == 2 * pi() radian
	//   0x80  == 180 degree == pi() radian
	//   0x01  == 180/128 degree == pi()/128 radian
	//         == 1.40625 degree == 0.02454369260617... radian
	// Legend of Mana
	//   rotation based on dx,dy , parts at +0,+0
	// Xenogears
	//   rotation based on dx,dy , parts at +rx,+ry
	$rot = ($pix['rotate'][0] & BIT8) * pi() / 0x80;
	$sin = sin($rot);
	$cos = cos($rot);
	$hyp = hypot($pix['src']['w'], $pix['src']['h']) * 0.6;

	// 10x10 square       = ( -5, -5, 5, 5)
	//   rotate 45 degree = (  0, -7, 0, 7) or ~14x14 square
	// hypotenuse 10x10 square         = 14.1421
	//   center-based with hypot       = (-14,-14, 14, 14) or ~28x28 square
	//   center-based with hypot * 0.6 = ( -8, -8,  8,  8) or ~16x16 square
	$tx = $pix['rotate'][1] + ($pix['src']['w'] / 2);
	$ty = $pix['rotate'][2] + ($pix['src']['h'] / 2);
		$cx = $tx * $cos - $ty * $sin;
		$cy = $tx * $sin + $ty * $cos;
	$x1 = (int)($cx - $hyp);
	$y1 = (int)($cy - $hyp);
	$x2 = (int)($cx + $hyp);
	$y2 = (int)($cy + $hyp);
	//trace("x %d y %d w %d h %d hyp %d\n", $x1, $y1, $x2-$x1, $y2-$y1, $hyp);

	// dest rotation + alpha blend + copy
	//   direct  rotate src ->  dest == holes on dest (rounding error)
	//   reverse rotate dest <- src  == no holes
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

			updatepix($pix, $byte, $rx, $ry, $dx, $dy);
		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )

	return;
}

function trimpix( &$pix )
{
	$x1 = 0;
	$x2 = $pix['rgba']['w'];
	$y1 = 0;
	$y2 = $pix['rgba']['h'];
	$TRIM_SZ = 4;

	// keep it square
	while (1)
	{
		$row = $pix['rgba']['w'] * 4;
		$b = '';

		// trim height
		$p = $y1 * $row;
		$b .= substr($pix['rgba']['pix'], $p, $row*$TRIM_SZ);

		$p = ($y2 - $TRIM_SZ) * $row;
		$b .= substr($pix['rgba']['pix'], $p, $row*$TRIM_SZ);

		// trim width
		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + ($x1 * 4);
			$b .= substr($pix['rgba']['pix'], $p, 4*$TRIM_SZ);
		}
		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + (($x2-$TRIM_SZ) * 4);
			$b .= substr($pix['rgba']['pix'], $p, 4*$TRIM_SZ);
		}

		if ( trim($b, ZERO) !== '' )
			break;

		$y1 += $TRIM_SZ;
		$y2 -= $TRIM_SZ;
		$x1 += $TRIM_SZ;
		$x2 -= $TRIM_SZ;
	} // while (1)

	$w = $x2 - $x1;
	$h = $y2 - $y1;
	$canv = '';
	for ( $y=$y1; $y < $y2; $y++ )
	{
		$p = $y * $pix['rgba']['w'] + $x1;
		$canv .= substr($pix['rgba']['pix'], $p*4, $w*4);
	}

	trace("trimpix() %d x %d => %d x %d\n", $pix['rgba']['w'], $pix['rgba']['h'], $w, $h);
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = $canv;
	return;
}

//	if ( $trim )
//		trimpix($pix);
//////////////////////////////
function psxpal( $pal, $cfactor, $afactor=1 )
{
	// for psx blending = FG.rgb * 0.25 + BG.rgb
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$r = ord( $pal[$i+0] );
		$g = ord( $pal[$i+1] );
		$b = ord( $pal[$i+2] );
		$a = ord( $pal[$i+3] );

		$r = int_clamp($r * $cfactor, 0 , 255);
		$g = int_clamp($g * $cfactor, 0 , 255);
		$b = int_clamp($b * $cfactor, 0 , 255);
		$a = int_clamp($a * $afactor, 0 , 255);

		$pal[$i+0] = chr($r);
		$pal[$i+1] = chr($g);
		$pal[$i+2] = chr($b);
		$pal[$i+3] = chr($a);
	} // for ( $i=0; $i < $len; $i += 4 )
	return $pal;
}

/*
, $solid=true
	// STP = semi-transparent FLAG
	//   STP  RGB
	//   0    0    transparent
	//   0    any  opaque
	//   1    any  semi-transparent
	if ( $solid )
		$a = BIT8;
	else
	{
		$stp = ($pal & 0x8000);
		if ( $stp )
			$a = BIT8 >> 1;
		else
			$a = ( $pal === 0 ) ? 0 : BIT8;
	}

	// Semi-Transparent Rates
	// (FG.rgb *  0.5 ) + (BG.rgb * 0.5)  average
	// (FG.rgb *  1   ) + (BG.rgb * 1  )  add
	// (FG.rgb * -1   ) + (BG.rgb * 1  )  subtract
	// (FG.rgb *  0.25) + (BG.rgb * 1  )  partial add
	$str = chr($r) . chr($g) . chr($b) . chr($a);

, $trim=false, $clut=true
	if ( ! $clut )
		goto rgba;

	$clpal = [];
	$clpix = '';
	$pos = 0;
	for ( $y=0; $y < $pix['rgba']['h']; $y++ )
	{
		for ( $x=0; $x < $pix['rgba']['w']; $x++ )
		{
			$c1 = substr($pix['rgba']['pix'], $pos, 4);
				$pos += 4;

			if ( array_search($c1, $clpal) === false )
				$clpal[] = $c1;
			$c2 = array_search($c1, $clpal);
			if ( count($clpal) > 0x100 )
				goto rgba;

			$clpix .= chr($c2);
		} // for ( $x=0; $x < $pix['rgba']['w']; $x++ )
	} // for ( $y=0; $y < $pix['rgba']['h']; $y++ )

clut:
	$pix['rgba']['cc']  = count($clpal);
	$pix['rgba']['pal'] = implode('', $clpal);
	$pix['rgba']['pix'] = $clpix;
	trace("savepix() CLUT [%x]  %d x %d = %s\n", $pix['rgba']['cc'], $pix['rgba']['w'], $pix['rgba']['h'], $fn);
	return save_clutfile("$fn.clut", $pix['rgba']);

rgba:

*/

function psx_patch( $dir, $pfx )
{
	foreach( file("$dir/system.cnf") as $sys )
	{
		if ( stripos($sys, 'boot') === false )
			continue;
		preg_match('|[a-z]{4}_[0-9]{3}\.[0-9]{2}|i', $sys, $m);
		if ( $m[0] === '' )
			return [];
		return patchfile("{$pfx}_{$m[0]}.txt");
	} // foreach( file("$dir/system.cnf") as $sys )
	return [];
}

function psx_exe( &$psxram, $psxexe )
{
	if ( empty($psxram) )
		return '';

	$bin = load_file($psxexe);
	if ( empty($bin) || substr($bin, 0, 8) != 'PS-X EXE' )
		return;
	$off1 = str2int($bin, 0x10, 3); // mips start()
	$off2 = str2int($bin, 0x18, 3); // mips ram
	$off3 = str2int($bin, 0x1c, 3); // mips size
	$off4 = str2int($bin, 0x30, 3); // init stack

	// Tokimemo 2 EVS series has garbage data at the end
	$bin = substr($bin, 0x800, $off3);
	str_update($psxram, $off2, $bin);
	return;
}

function rippix4( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined('DRY_RUN') )
		return '';
	$sx >>= 1;
	$w  >>= 1;
	$sw >>= 1;
	$rip = rippix8($pix, $sx, $sy, $w, $h, $sw, $sh);
	bpp4to8($rip);
	return $rip;
}

function rgba_array( $rgba )
{
	if ( "$rgba" === $rgba )
		return [ ord($rgba[0]) , ord($rgba[1]) , ord($rgba[2]) , ord($rgba[3]) ];

	$pix = '';
	for ( $i=0; $i < 4; $i++ )
	{
		$c = int_clamp($rgba[$i], 0, BIT8);
		$pix .= chr($c);
	}
	return $pix;
}

function rgba_multiply1( $rgba, $factor, $in_array=false )
{
	$rgba = rgba_array($rgba);
	for ( $i=0; $i < 4; $i++ )
		$rgba[$i] *= $factor;
	return ( $in_array ) ? $rgba : rgba_array($rgba);
}

function rgba_multiply2( $rgba1, $fact1, $rgba2, $fact2 )
{
	$rgba1 = rgba_multiply1($rgba1, $fact1, true);
	$rgba2 = rgba_multiply1($rgba2, $fact2, true);
	for ( $i=0; $i < 4; $i++ )
		$rgba1[$i] += $rgba2[$i];
	return rgba_array($rgba1);
}
//////////////////////////////
function sect_sum( &$sect, $name, $off )
{
	if ( defined('NO_TRACE') )
		return;

	$pos = 0;
	$sum = [];
	while (1)
	{
		if ( ! isset( $sect['d'][$pos+$off] ) )
			break;

		$b = ord( $sect['d'][$pos+$off] );
		if ( ! isset( $sum[$b] ) )
			$sum[$b] = 0;

		$sum[$b]++;
		$pos += $sect['k'];
	}
	ksort($sum);

	trace("== %s( %s , %x )\n", __FUNCTION__, $name, $off);
	foreach ( $sum as $k => $v )
		trace("  %2x = %8x\n", $k, $v);
	return;
}

function load_sect( &$file, $sect )
{
	// sect = [
	//   [ 'p' => 0 , 'k' => 0, 'o' => 0, 'd' => '' ],
	// ];
	// p = position
	// k = block
	// o = offset
	// d = data
	$offs = [];
	foreach ( $sect as $v )
	{
		if ( ! isset($v['o']) )
			continue;
		$offs[] = $v['o'];
	} // foreach ( $sect as $v )
	sort($offs);

	foreach ( $sect as $k => $v )
	{
		if ( ! isset( $v['o'] ) )
			continue;
		if ( ! isset( $v['k'] ) )
			continue;
		$id = array_search($v['o'], $offs);
		$sz = int_ceil($offs[$id+1] - $v['o'], -$v['k']);
		if ( $sz === 0 )
			continue;

		$dat = substr($file, $v['o'], $sz);
		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$file = $sect;
	return;
}

function save_sect( &$sect, $pfx )
{
	foreach ( $sect as $k => $v )
	{
		if ( ! isset($v['d']) || $v['k'] < 1 )
			continue;
		$fn  = sprintf('%s/%04d.txt', $pfx, $k);
		$buf = debug_block( $v['d'], $v['k'] );
		save_file($fn, $buf);
	} // foreach ( $sect as $k => $v )
	return;
}

function performance( $file_func='', $dir_func='', $op_func='' )
{
	global $argc, $argv;

	$st = microtime(true);
	for ( $i=1; $i < $argc; $i++ )
	{
		if ( is_file($argv[$i]) && function_exists($file_func) )
			$file_func( $argv[$i] );
		else
		if ( is_dir ($argv[$i]) && function_exists($dir_func ) )
			$dir_func ( $argv[$i] );
		else
		if ( function_exists($op_func) )
			$op_func( $argv[$i] );
	}
	$ed = microtime(true);

	$mem = memory_get_peak_usage();
	trace("%s()  %.2f msec  %x RAM\n", $func, $ed-$st, $mem);
	return;
}

function load_text( $fname )
{
	$txt = load_file($fname);
	if ( empty($txt) )
		return '';
	$txt = str_replace( ["\r\n","\r"], "\n", $txt);
	return explode("\n", $txt);
}

/*
function nds_ramload( &$ram, $fname, $off )
{
	$bin = load_file($fname);
	if ( empty($bin) )
		return;
	$bin = substr($bin, $off & BIT16);
	str_update($ram, $off, $bin);
	return;
}

function nds_ram( $dir, $dsi=false )
{
	$head = load_file("$dir/head.bin");
	if ( empty($head) )
		return php_error('NDS_RAM %s/head.bin not found', $dir);

	$siz = ( $dsi ) ? NDSI_RAM : NDS_RAM;
	$ram = str_repeat(ZERO, $siz);
	$off9 = str2int($head, 0x28, 3);
	$off7 = str2int($head, 0x38, 3);

	nds_ramload($ram, "$dir/arm9/main.bin", $off9);
	nds_ramload($ram, "$dir/arm7/main.bin", $off7);
	return $ram;
}
//////////////////////////////
function nds_patch( $dir, $pfx )
{
	$head = load_file("$dir/head.bin");
	if ( empty($head) )
		return [];
	$NTR = substr($head, 12, 4);
	$ver = ord( $head[0x1e] );

	$txt = sprintf('%s_%s_%d.txt', $pfx, $NTR, $ver);
	return patchfile($txt);
}

function nds_overlay( &$ram, $dir, $id )
{
	if ( strpos($id, 'ov-') !== false )
		$id = (int)substr($id, 3);

	$bin = load_file("$dir/arm9/overlay.bin");
	$bin = substr($bin, $id*0x20, 0x20);
	if ( empty($bin) )
		return -1;
	$ov_ram = str2int($bin, 4, 3);

	$over = sprintf('%s/arm9/%06x/%04d_%x.overlay', $dir, $ov_ram, $id, $id);
	nds_ramload($ram, $over, $ov_ram);
	trace("load OVERLAY %x @ %s\n", $ov_ram, $over);
	return $ov_ram;
}

function nds_game( &$ram, $dir, $game )
{
	foreach ( $game as $g )
	{
		if ( strpos($g, 'ov-') === false )
			continue;
		nds_overlay( $ram, $dir, $g );
	}
	return;
}
*/
