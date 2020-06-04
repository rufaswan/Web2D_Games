<?php
// toolset for PS One games
// - little-endian byte order
// - 32-bit negative int
// - load/store registers
// - RGB555 color format
// - 0x200000 RAM
//   - 80000000-8000ffff BIOS
//   - 80010000-801fffef WRAM
//   - 801ffff0-801fffff stack
// - fileseek in sectors
//   - n * sector_size
//     - absolute position within the CD
//     - relative position within the file
//   - minute:second:frame (absolute position)
// - Sprites are PsuedoColor
// - Backgrounds are PsuedoColor, very rarely DirectColor
define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

function str2int( &$str, $pos, $byte )
{
	$sub = substr($str, $pos, $byte);
	return ordint($sub);
}

function substr0( &$str, $pos )
{
	$len = 0;
	while ( $str[$pos+$len] != ZERO )
		$len++;
	return substr($str, $pos, $len);
}

function ordint( $str )
{
	if ( (int)$str === $str ) // already $int
		return $str;
	$len = strlen($str);
	$int = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$int += ($b << ($i*8));
	}
	return $int;
}

function chrint( $int, $byte = 0 )
{
	if ( (int)$int !== $int ) // already $str
		return $int;
	$str = "";
	for ( $i=0; $i < $byte; $i++ )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
	}
	while ( strlen($str) < $byte )
		$str .= ZERO;
	return $str;
}

function sint8( $s )
{
	$int = ordint($s);
	if ( $int >> 7 )
		return $int - BIT8 - 1;
	return $int;
}

function sint16( $s )
{
	$int = ordint($s);
	if ( $int >> 15 )
		return $int - BIT16 - 1;
	return $int;
}

function strupd( &$file, $pos, $str )
{
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
		$file[$pos+$i] = $str[$i];
	return;
}

function var_swap( &$var1, &$var2 )
{
	$bak = $var1;
	$var1 = $var2;
	$var2 = $bak;
	return;
}

function int_min( $int, $min )
{
	if ( $int < $min )
		return $min;
	return (int)round($int);
}

function int_max( $int, $max )
{
	if ( $int > $max )
		return $max;
	return (int)round($int);
}

function save_file( $fname, $str )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;
	if ( empty($str) )
		return;
	if ( strpos($fname, '\\') )
		$fname = str_replace('\\', '/', $fname);

	if ( strpos($fname, '/') )
	{
		$dir = substr($fname, 0, strrpos($fname, '/'));
		@mkdir( $dir, 0755, true );
	}
	file_put_contents($fname, $str);
	return;
}

function debug( $str, $sep = 4 )
{
	echo "DEBUG : ";
	$len = strlen($str);
	$i = 0;
	while ( $i < $len )
	{
		printf("%2x ", ord( $str[$i] ));
			$i++;
		if ( $i != 0 && ($i % $sep) == 0 )
			echo "  ";
	}
	echo "\n";
	return;
}

// E_USER_ERROR
// E_USER_WARNING
// E_USER_NOTICE
// E_USER_DEPRECATED
function zero_watch( $name, $chr )
{
	$err = sprintf("ERROR $name not zero [%2x]\n", ord($chr));
	if ( $chr != ZERO )
		trigger_error($err, E_USER_WARNING);
	return;
}

function byte_watch( $name, $chr )
{
	$err = sprintf("ERROR $name not 0xFF [%2x]\n", ord($chr));
	if ( $chr != BYTE )
		trigger_error($err, E_USER_WARNING);
	return;
}

function neg_warn( $name, $int )
{
	$err = sprintf("ERROR $name is negative [%d]\n", $int);
	if ( $int < 0 )
		trigger_error($err, E_USER_WARNING);
	return;
}

function flag_warn( $name, $int )
{
	$err = sprintf("ERROR $name some flags is set [%x]\n", $int);
	if ( $int != 0 )
		trigger_error($err, E_USER_WARNING);
	return;
}
//////////////////////////////
define("PIX_BLACK", ZERO . ZERO . ZERO . BYTE);
define("PIX_ALPHA", ZERO . ZERO . ZERO . ZERO);

function canvpix( $w, $h, $c = PIX_ALPHA )
{
	$s = $w * $h;
	$p = "";
	for ( $i=0; $i < $s; $i++ )
		$p .= $c;
	return $p;
}

function clutpix( &$file, $pos )
{
	// 15-bit ABGR
	// RGB = c6         52         52
	//     = 1100 0110  0101 0010  0101 0010
	//     = 11000      01010      01010
	// 15-bit = 0(A) 01010(B) 01010(G) 11000(R)
	//        = 0010 1001 0101 1000 = 2958
	//        = 58 29
	$b1 = ord( $file[$pos+0] );
	$b2 = ord( $file[$pos+1] );
	$pal = $b1 + ($b2 << 8);

	$b = ($pal >> 7) & 0xf8; // <<  1 >> 8 == >> 7
	$g = ($pal >> 2) & 0xf8; // <<  6 >> 8 == >> 2
	$r = ($pal << 3) & 0xf8; // << 11 >> 8 == << 3
	//$a = ( $pal >> 15 ) ? ZERO : BYTE;
	$a = BYTE;

	$str = chr($r) . chr($g) . chr($b) . $a;
	return $str;
}

function clut2str( &$file, $pos, $no )
{
	$str = "";
	for ( $i=0; $i < $no; $i++ )
	{
		$str .= clutpix( $file, $pos );
		$pos += 2;
	}
	return $str;
}

function mclut2str( &$file, $pos, $no, $m )
{
	$ret = array();
	while ( $m > 0 )
	{
		$ret[] = clut2str($file, $pos, $no);
		$pos += ($no*2);
		$m--;
	}
	return $ret;
}

function grayclut( $no )
{
	$add = (int)(255 / ($no-1));
	$clut = "";
	$c = 0;
	for ( $i=0; $i < $no; $i++ )
	{
		$clut .= chr($c) . chr($c) . chr($c) . BYTE;
		$c = int_max($c + $add, BIT8);
	}
	return $clut;
}
//////////////////////////////
function COPYPIX_DEF()
{
	// PHP 5.6 fix
	// Warning: Constants may only evalute to scalar values
	$php56 = array(
		'rgba' => array(
			'w' => 0,
			'h' => 0,
			'pix' => "",
		),
		'src' => array(
			'w' => 0,
			'h' => 0,
			'pix' => "",
			'pal' => "",
		),
		'dx' => 0,
		'dy' => 0,
		'bgzero' => false,
		'vflip'  => false,
		'hflip'  => false,
		'rotate' => array(0,0,0),
		'alpha'  => "",
	);
	return $php56;
}
//////////////////////////////
function rippix4( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	$rip = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$syy = ($sy + $y) * $sw;
		for ( $x=0; $x < ($w/2); $x++ )
		{
			$sxx = $syy + ($sx/2) + $x;
			$p = ord( $pix[$sxx] );

			$p1 = $p & 0x0f;
			$p2 = $p >> 4;

			$rip .= chr($p1) . chr($p2);
		} // for ( $x=0; $x < ($w/2); $x++ )
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}

function rippix8( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	$rip = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$sxx = ($sy + $y) * $sw + $sx;
		$rip .= substr($pix, $sxx, $w);
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}

function copypix_errs( &$pix )
{
	$err = "";
	if ( $pix['dx'] < 0 )
		$err .= sprintf("copypix() dx = %d\n", $pix['dx']);
	if ( $pix['dy'] < 0 )
		$err .= sprintf("copypix() dy = %d\n", $pix['dy']);
	if ( ($pix['dx'] + $pix['src']['w']) > $pix['rgba']['w'] )
		$err .= sprintf("copypix() dx + sw = %d + %d\n", $pix['dx'], $pix['src']['w']);
	if ( ($pix['dy'] + $pix['src']['h']) > $pix['rgba']['h'] )
		$err .= sprintf("copypix() dy + sh = %d + %d\n", $pix['dy'], $pix['src']['h']);

	if ( ! empty($err) )
	{
		trigger_error($err, E_USER_ERROR);
		return true;
	}
	return false;
}

function copypix( &$pix )
{
	if ( copypix_errs($pix) )
		return;
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	// horizontal flip + vertical flip
	$flip = str_pad( "", $pix['src']['w'] * $pix['src']['h'], ZERO );
	for ( $y=0; $y < $pix['src']['h']; $y++ )
	{
		$syy = $y * $pix['src']['w'];
		$dyy = $y;
		if ( $pix['vflip'] )
			$dyy = $pix['src']['h'] - 1 - $y;
		$dyy *= $pix['src']['w'];

		for ( $x=0; $x < $pix['src']['w']; $x++ )
		{
			$sxx = $syy + $x;
			$dxx = $x;
			if ( $pix['hflip'] )
				$dxx = $pix['src']['w'] - 1 - $x;
			$dxx += $dyy;

			$flip[$dxx] = $pix['src']['pix'][$sxx];

		} // for ( $x=0; $x < $pix['src']['w']; $x++ )
	} // for ( $y=0; $y < $pix['src']['h']; $y++ )

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
	$hyp = hypot($pix['src']['w'], $pix['src']['h']);

	// 10x10 square (-5,-5,5,5) rotate 45 degree = (  0, -7, 0, 7) or ~14x14 square
	// hypotenuse 10x10 square = 14.1421        += (-14,-21,14,21)
	//   center-based with hypot                 = (-14,-14,14,14)
	$tx = $pix['rotate'][1] + ($pix['src']['w'] / 2);
	$ty = $pix['rotate'][2] + ($pix['src']['h'] / 2);
		$cx = $tx * $cos - $ty * $sin;
		$cy = $tx * $sin + $ty * $cos;
	$x1 = (int)($cx - $hyp);
	$y1 = (int)($cy - $hyp);
	$x2 = (int)($cx + $hyp);
	$y2 = (int)($cy + $hyp);
	//printf("x %d y %d w %d h %d hyp %d\n", $x1, $y1, $x2-$x1, $y2-$y1, $hyp);

	// dest rotation + alpha blend + copy
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
			if ( $flip[$sxx] == ZERO && ! $pix['bgzero'] )
				continue;
			$c1 = substr($pix['src']['pal'], ord( $flip[$sxx] )*4, 4);

			$dxx = ($dy * $pix['rgba']['w'] + $dx) * 4;
			$c2 = substr($pix['rgba']['pix'], $dxx, 4);

			// ALPHA BLENDING
			// using callbacks due to implementation difference
			// Legend of Mana @ E=M+I
			// Legend of Mana /wm/wmland/* @ 1 is E=M+I @ 3 is E=(M/5)+I
			$alpha = $pix['alpha'];
			if ( function_exists($alpha) )
				$c1 = $alpha($c1, $c2);

			// COPY / UPDATE PIXEL
			strupd($pix['rgba']['pix'], $dxx, $c1);

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
	$TRIM_SZ = 16;

	// keep it square
	while (1)
	{
		$row = $pix['rgba']['w'] * 4;
		$b = "";

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

		if ( trim($b, ZERO) != "" )
			break;

		$y1 += $TRIM_SZ;
		$y2 -= $TRIM_SZ;
		$x1 += $TRIM_SZ;
		$x2 -= $TRIM_SZ;
	}
	printf("TRIM %4d , %4d , %4d , %4d\n", $x1, $y1, $x2, $y2);

	$w = $x2 - $x1;
	$h = $y2 - $y1;
	$canv = "";
	for ( $y=$y1; $y < $y2; $y++ )
	{
		$p = ($y * $pix['rgba']['w'] + $x1) * 4;
		$canv .= substr($pix['rgba']['pix'], $p, $w*4);
	}
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = $canv;
	return;
}

function savpix( $fn, &$pix, $trim = false )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	if ( trim($pix['rgba']['pix'], ZERO) == "" )
		return trigger_error("ERROR $fn [pix] blank\n", E_USER_WARNING);
	if ( $trim )
		trimpix($pix);

	$rgba = "RGBA";
	$rgba .= chrint( $pix['rgba']['w'], 4 );
	$rgba .= chrint( $pix['rgba']['h'], 4 );
	$rgba .= $pix['rgba']['pix'];
	save_file("$fn.rgba", $rgba);
}

function alpha_add( $fg, $bg )
{
	// FG is RGB from palette
	// if BG is blank , FG RGB -> RGBA
	// if BG is solid , FG += BG
	// if BG is RGBA  , BG RGBA -> RGB, FG += BG, FG RGB -> RGBA

	// blend w/normal pixels
	if ( $bg[3] == BYTE )
	{
		$pix = "";
		for ( $i=0; $i < 4; $i++ )
		{
			$p1 = ord( $fg[$i] );
			$p2 = ord( $bg[$i] );
			$p = int_max($p1 + $p2, BIT8);
			$pix .= chr($p);
		}
		return $pix;
	}

	// blend w/transparent pixel
	// #202020 + #00000000 = #ffffff20
	// #204080 + #00000000 = #4080ff80
	if ( $bg[3] == ZERO )
	{
		$r = ord($fg[0]);
		$g = ord($fg[1]);
		$b = ord($fg[2]);

		$a = ( $r > $g ) ? $r : $g;
		$a = ( $b > $a ) ? $b : $a;

		$r = int_max($r / $a * BIT8, BIT8);
		$g = int_max($g / $a * BIT8, BIT8);
		$b = int_max($b / $a * BIT8, BIT8);
		return chr($r) . chr($g) . chr($b) . chr($a);
	}

	// blend w/semi-transparent pixel
	// #202020   + #204080   = #4060a0
	// #ffffff20 + #4080ff80 = #6699ffa0 (NOT #ffffffa0)
	// #202020   + #4080ff80 = #6699ffa0
	$a2 = ord($bg[3]);
	$r2 = ord($bg[0]) / BIT8 * $a2;
	$g2 = ord($bg[1]) / BIT8 * $a2;
	$b2 = ord($bg[2]) / BIT8 * $a2;

	$r = int_max(ord($fg[0]) + $r2, BIT8);
	$g = int_max(ord($fg[1]) + $g2, BIT8);
	$b = int_max(ord($fg[2]) + $b2, BIT8);
	$nfg = chr($r) . chr($g) . chr($b) . BYTE;

	$func = __FUNCTION__;
	return $func($nfg, PIX_ALPHA);
}
//////////////////////////////
function psxtim( &$file )
{
	$tim = array();
	$pos = 0;

	// TIM header
	$tim['t'] = str2int( $file, $pos+4, 1 );
	$tim['cc'] = 0;
	if ( $tim['t'] == 9 )  $tim['cc'] = 0x100;
	if ( $tim['t'] == 8 )  $tim['cc'] = 0x10;
	if ( $tim['cc'] == 0 )
		return $tim;
	$pos += 8;

	// CLUT + header
	$siz = str2int( $file, $pos+0, 4 ) - 12;
	$tim['cn'] = (int)($siz / ($tim['cc'] * 2));
	$tim['clut'] = mclut2str($file, $pos+12, $tim['cc'], $tim['cn']);
	$pos += $siz + 12;

	// PIX + header
	$siz = str2int( $file, $pos+0, 4 ) - 12;
	$tim['w'] = str2int($file, $pos+ 8, 2);
	$tim['h'] = str2int($file, $pos+10, 2);
	$pos += 12;

	if ( $tim['t'] == 9 )
	{
		$tim['w'] *= 2;
		$tim['pix'] = substr($file, $pos, $siz);

		printf("psxtim() = %d , %d x %d , %d x %d\n",
			$tim['t'], $tim['w'], $tim['h'], $tim['cc'], $tim['cn']
		);
		return $tim;
	}
	if ( $tim['t'] == 8 )
	{
		$tim['w'] *= 4;
		$tim['pix'] = "";

		while ( $siz > 0 )
		{
			$b = ord( $file[$pos] );
			$b1 = $b & 0x0f;
			$b2 = $b >> 4;
			$tim['pix'] .= chr($b1) . chr($b2);

			$siz--;
			$pos++;
		}

		printf("psxtim() = %d , %d x %d , %d x %d\n",
			$tim['t'], $tim['w'], $tim['h'], $tim['cc'], $tim['cn']
		);
		return $tim;
	}
	return $tim;
}
//////////////////////////////
