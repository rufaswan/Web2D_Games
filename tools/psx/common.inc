<?php
define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

function str2int( &$str, $pos, $byte )
{
	$int = substr($str, $pos, $byte);
	return ordint($int);
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

function strpad( $len, $c = ZERO )
{
	$str = "";
	for ( $i=0; $i < $len; $i++ )
		$str .= $c;
	return $str;
}

function strupd( &$file, $pos, $str )
{
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
		$file[$pos+$i] = $str[$i];
}

function int_min( $int, $min )
{
	if ( $int < $min )
		return $min;
	return (int)$int;
}

function int_max( $int, $max )
{
	if ( $int > $max )
		return $max;
	return (int)$int;
}

function save_file( $fname, $str )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;
	if ( empty($str) )
		return;

	if ( strpos($fname, '/') )
	{
		$dir = substr($fname, 0, strrpos($fname, '/'));
		@mkdir( $dir, 0755, true );
	}
	file_put_contents($fname, $str);
}

function zero_watch( $name, $chr )
{
	if ( $chr != ZERO )
		printf("$name not zero [%2x]\n", ord($chr));
}
function byte_watch( $name, $chr )
{
	if ( $chr != BYTE )
		printf("$name not 0xFF [%2x]\n", ord($chr));
}

function neg_warn( $name, $int )
{
	if ( $int < 0 )
		printf("$name is negative [%d]\n", $int);
}
function flag_warn( $name, $int )
{
	if ( $int != 0 )
		printf("$name some flags is set [%d]\n", $int);
}
//////////////////////////////
define("PIX_BLACK", ZERO . ZERO . ZERO . BYTE);
define("PIX_ALPHA", ZERO . ZERO . ZERO . ZERO);

function canvpix( $w, $h )
{
	$c = PIX_ALPHA;
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
define("COPYPIX_DEF", array(
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
	'black'  => false,
	'vflip'  => false,
	'hflip'  => false,
	'rotate' => 0,
	'alpha'  => "",
));
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
			$sxx = $syy + $sx + $x;
			$p = ord( $pix[$sxx] );

			$p1 = $p & 0x0f;
			$p2 = $p >> 4;

			$rip .= chr($p1);
			$rip .= chr($p2);
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
		$syy = ($sy + $y) * $sw;
		for ( $x=0; $x < $w; $x++ )
		{
			$sxx = $syy + $sx + $x;
			$rip .= $pix[$sxx];
		} // for ( $x=0; $x < $w; $x++ )
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}

function copypix( &$pix )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	// horizontal flip + vertical flip
	$flip = strpad( $pix['src']['w'] * $pix['src']['h'] );
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

	// rotation + alpha blend + copy
		// rotation
		//   0x100 == 360 degree == 2 * pi() radian
		//   0x80  == 180 degree == pi() radian
		//   0x01  == 180/128 degree == pi()/128 radian
		//         == 1.40625 degree == 0.02454369260617... radian
	$rot = ($pix['rotate'] & BIT8) * pi() / 0x80;
	$sin = sin($rot);
	$cos = cos($rot);

	$hyp = (int)hypot($pix['src']['w'], $pix['src']['h']);
	$x1 = int_min($pix['dx'] - $hyp, 0);
	$y1 = int_min($pix['dy'] - $hyp, 0);
	$x2 = int_max($pix['dx'] + $hyp, $pix['rgba']['w']);
	$y2 = int_max($pix['dy'] + $hyp, $pix['rgba']['h']);

	for ( $y=$y1; $y < $y2; $y++ )
	{
		$dyy = $y * $pix['rgba']['w'] * 4;
		for ( $x=$x1; $x < $x2; $x++ )
		{
			$tx = $x - $pix['dx'];
			$ty = $y - $pix['dy'];

			$rx = (int)round($tx * $cos - $ty * $sin);
			$ry = (int)round($tx * $sin + $ty * $cos);
			if ( $rx < 0 )  continue;
			if ( $ry < 0 )  continue;
			if ( $rx >= $pix['src']['w'] )  continue;
			if ( $ry >= $pix['src']['h'] )  continue;

			$sxx = ($ry * $pix['src']['w']) + $rx;
			$p1 = $flip[$sxx];
			$c1 = substr($pix['src']['pal'], ord($p1)*4, 4);
			if ( $c1 == PIX_BLACK && ! $pix['black'] )
				continue;

			$dxx = ($x * 4) + $dyy;
			$c2 = substr($pix['rgba']['pix'], $dxx, 4);

			// implementation difference
			// Legend of Mana /wm/wmland/*.dat @ 1=M+I , 3=(M/5)+I
			$alpha = $pix['alpha'];
			if ( function_exists($alpha) )
				$c1 = $alpha($c1, $c2);

			strupd($pix['rgba']['pix'], $dxx, $c1);

		} // for ( $x=$x1; $x < $x2; $x++ )
	} // for ( $y=$y1; $y < $y2; $y++ )

	return;
}

function savpix( $fn, &$pix )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	if ( trim($pix['rgba']['pix'], ZERO.BYTE) == "" )
		return printf("ERROR $fn [pix] blank\n");

	$rgba = "RGBA";
	$rgba .= chrint( $pix['rgba']['w'], 4 );
	$rgba .= chrint( $pix['rgba']['h'], 4 );
	$rgba .= $pix['rgba']['pix'];
	save_file("$fn.rgba", $rgba);
}

function alpha_add( $fg, $bg )
{
	// blend w/transparent pixels
	if ( $bg == PIX_ALPHA )
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

	// blend w/normal pixels
	$ret = "";
	for ( $i=0; $i < 4; $i++ )
	{
		$p1 = ord( $fg[$i] );
		$p2 = ord( $bg[$i] );
		$p = int_max($p1 + $p2, BIT8);
		$ret .= chr($p);
	}
	return $ret;
}
//////////////////////////////
function psxtim( &$file )
{
	$tim = array();
	$hdz  = str2int($file, 0x08, 4);

	$tim['t']  = ord( $file[4] );
	$tim['cc'] = str2int($file, 0x10, 2);
	$tim['cn'] = str2int($file, 0x12, 2);
	$tim['w'] = str2int($file, 8 + $hdz +  8, 2);
	$tim['h'] = str2int($file, 8 + $hdz + 10, 2);
	$sz = $tim['w'] * 2 * $tim['h'];

	$tim['clut'] = mclut2str($file, 0x14, $tim['cc'], $tim['cn']);

	$off = 8 + $hdz + 12;
	if ( $tim['cc'] > 0x10 )
	{
		$tim['w'] *= 2;
		$tim['pix'] = substr($file, $off, $sz);
		return $tim;
	}
	else
	{
		$tim['w'] *= 4;
		$tim['pix'] = "";

		while ( $sz )
		{
			$b = ord( $file[$off] );
			$b1 = $b & 0xf;
			$b2 = $b >> 4;
			$tim['pix'] .= chr($b1) . chr($b2);

			$sz--;
			$off++;
		}
		return $tim;
	}
	return $tim;
}
//////////////////////////////
