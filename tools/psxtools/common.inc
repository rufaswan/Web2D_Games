<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
// toolset for PlayStation One games
// - little-endian byte order
// - 31th bit is negative int flag
// - One delayed slot on jump and branch
// - All values in registers , no hidden CF/ZF/SF/PF etc
// - All values in int , no float/double
// - 0x200000 RAM
//   - 80000000-8000ffff BIOS
//   - 80010000-801effff WRAM
//   - 801f0000-801fffff stack
//   - JUMP   are absolute RAM offset * 4
//   - BRANCH are relative RAM offset * 4
//   - SWITCH is  absolute RAM offset + (c << 2)
//     CASE   are absolute RAM offsets
// - fileseek in sectors
//   - n * 0x800
//     - absolute position within the CD
//     - relative position within the file
//   - Minute:Second:Frame (absolute position)
// - Sprites are PsuedoColor
// - Backgrounds are PsuedoColor, very rarely DirectColor
// - RGB555 color format , in 15-ABBBBBGGGGGRRRRR-0 order
// - Alpha is by Additive Blending , as in FG + BG
// - Layers are Top-to-Bottom order
// - 0,0 is at Top-Left corner
// - Points are Left-Hand Winding Order , or in Clockwise (CW)
//
// CODING STANDARD
// - use PHP core functions only
//   if compile PHP from source, use option '--disable-all' on configure
// - *.php files
//   - *.php are independant scripts
//   - *.php MUST NOT include each other
//   - *.php CAN include multiple *.inc , and in correct order
// - *.inc files
//   - *.inc are shared scripts
//   - *.inc MUST NOT include each other
//   - *.inc MUST NOT include *.php
// - global variables are prefixed "gp_" (global pointer)
define("ZERO", "\x00");
define("BYTE", "\xff");
define("BIT4",  (1 <<  4)-1);
define("BIT8",  (1 <<  8)-1);
define("BIT16", (1 << 16)-1);
define("BIT24", (1 << 24)-1);
define("BIT32", (1 << 32)-1);
//define("DRY_RUN", true);  // per script
//define("NO_TRACE", true); // per script
//if ( defined("DRY_RUN") )
//if ( defined("NO_TRACE") )

//////////////////////////////
// E_USER_ERROR
// E_USER_WARNING
// E_USER_NOTICE
// E_USER_DEPRECATED
function php_error()
{
	$args = func_get_args();
	$fmt = array_shift($args);
	$err = vsprintf("PHP : $fmt\n", $args);
	return trigger_error($err, E_USER_ERROR);
}

function php_warning()
{
	$args = func_get_args();
	$fmt = array_shift($args);
	$err = vsprintf("PHP : $fmt\n", $args);
	return trigger_error($err, E_USER_WARNING);
}

function php_notice()
{
	$args = func_get_args();
	$fmt = array_shift($args);
	$err = vsprintf("PHP : $fmt\n", $args);
	return trigger_error($err, E_USER_NOTICE);
}
//////////////////////////////
function php_req_define($define)
{
	if ( ! defined($define) )
		php_error("required global constant %s not defined", $define);
	return;
}
function php_req_extension($func, $ext)
{
	if ( ! function_exists($func) )
		php_error("required %s() [%s] not found", $func, $ext);
	return;
}

// https://www.php.net/manual/en/reserved.constants.php
php_req_define("PHP_INT_SIZE");   // since 5.0.5
php_req_define("PHP_VERSION_ID"); // since 5.2.7

// PHP 7.0+ for native 64-bit int on windows
if ( PHP_VERSION_ID < 50600 )
	php_warning("version older than 5.6 is not supported");
//////////////////////////////
function str2int( &$str, $pos, $byte, $sign=false )
{
	if ( PHP_INT_SIZE < $byte )
		return php_error("required %d-bit support", $byte*8);
	$sub = substr($str, $pos, $byte);
	$int = ordint($sub);
	if ( $sign )
	{
		$sra = (PHP_INT_SIZE - $byte) * 8; // shift right arithmetic
		return ( ($int << $sra) >> $sra );
	}
	return $int;
}

function substr0( &$str, $pos, $end="" )
{
	if ( empty($end) )
		$end = ZERO;
	$len = 0;
	while (1)
	{
		if ( ! isset( $str[$pos+$len] ) )
			break;
		if ( strpos($end, $str[$pos+$len]) !== false )
			break;
		$len++;
	}
	return substr($str, $pos, $len);
}

function ordint( $str )
{
	if ( (int)$str === $str ) // already int
		return $str;
	$str = rtrim("$str", ZERO);
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
	if ( "$int" === $int ) // already str
		return $int;
	$int = (int)$int;
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
	$int = ( (int)$s === $s ) ? $s : ordint($s);
	$sra = (PHP_INT_SIZE - 1) * 8; // shift right arithmetic
	return ( ($int << $sra) >> $sra );
}

function sint16( $s )
{
	$int = ( (int)$s === $s ) ? $s : ordint($s);
	$sra = (PHP_INT_SIZE - 2) * 8; // shift right arithmetic
	return ( ($int << $sra) >> $sra );
}

function sint24( $s )
{
	$int = ( (int)$s === $s ) ? $s : ordint($s);
	$sra = (PHP_INT_SIZE - 3) * 8; // shift right arithmetic
	return ( ($int << $sra) >> $sra );
}

function sint32( $s )
{
	$int = ( (int)$s === $s ) ? $s : ordint($s);
	$sra = (PHP_INT_SIZE - 4) * 8; // shift right arithmetic
	return ( ($int << $sra) >> $sra );
}

function str_update( &$file, $pos, $str )
{
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
		$file[$pos+$i] = $str[$i];
	return;
}

function int_clamp( $int, $min, $max )
{
	if ( $int < $min )  $int = $min;
	if ( $int > $max )  $int = $max;
	return (int)$int;
}

//        (int)    ceil()     floor()
//  4.5 = int( 4)  float( 5)  float( 4)
// -4.5 = int(-4)  float(-4)  float(-5)
function int_ceil( $int, $ceil )
{
	// round int away from zero
	//          4   -4  4.5  -4.5
	//  8   |   8    8   10     8 |
	// -8   |  -8   -8  -10    -8 |
	//  8.5 |  12   12   10    12 |
	// -8.5 | -12  -12  -10   -12 |
	$sign = ( 0 > $int );
	if ( $sign )
		$int = -$int;
	$int  = (int)ceil($int );
	$ceil = (int)ceil($ceil);
	while ( $int % $ceil )
		$int++;
	return ( $sign ) ? -$int : $int;
}

function int_floor( $int, $floor )
{
	// round int toward to zero
	//         4  -4  4.5  -4.5
	//  8   |  8   8    8    10 |
	// -8   | -8  -8   -8   -10 |
	//  8.5 |  8   8    8    10 |
	// -8.5 | -8  -8   -8   -10 |
	$sign = ( 0 > $int );
	if ( $sign )
		$int = -$int;
	$int   = (int)floor($int  );
	$floor = (int)floor($floor);
	while ( $int % $floor )
		$int--;
	return ( $sign ) ? -$int : $int;
}

function int_ceil_pow2( $int )
{
	// round int to nearest 2^n
	// away from zero
	$sign = ( 0 > $int );
	if ( $sign )
		$int = -$int;
	$int  = (int)ceil($int);
	$ceil = 1;
	while ( $ceil < $int )
		$ceil <<= 1;
	return ( $sign ) ? -$ceil : $ceil;
}
//////////////////////////////
function debug( $str, $name="DEBUG" )
{
	$buf = "$name : ";
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $i > 0 && ($i % 4) == 0 )
			$buf .= '  ';
		if ( $str[$i] == ZERO )
			$buf .= '-- ';
		else
			$buf .= sprintf("%02x ", ord($str[$i]) );
	}
	$buf .= "\n";
	//echo $buf;
	return $buf;
}

function debug_block( &$str, $blksz )
{
	$len = int_floor( strlen($str), $blksz);
	$buf = '';
	for ( $i=0; $i < $len; $i += $blksz )
	{
		$n = sprintf("%4x", $i/$blksz);
		$buf .= debug( substr($str, $i, $blksz), $n );
	}
	return $buf;
}

function trace()
{
	if ( defined("NO_TRACE") )
		return;
	$args = func_get_args();
	$fmt = array_shift($args);
	vprintf($fmt, $args);
	return;
}

function zero_watch( $name, $chr )
{
	if ( $chr == ZERO )
		return;
	php_notice("$name not zero [%2x]", ord($chr));
	return;
}

function byte_watch( $name, $chr )
{
	if ( $chr == BYTE )
		return;
	php_notice("$name not 0xFF [%2x]", ord($chr));
	return;
}

function flag_watch( $name, $int )
{
	if ( $int == 0 )
		return;
	php_notice("$name some flags is set [%x]", $int);
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
//////////////////////////////
function fp2str( $fp, $pos, $byte )
{
	if ( $byte == 0 )
		return "";
	fseek($fp, $pos, SEEK_SET);
	return fread($fp, $byte);
}

function fp_update( $fp, $pos, $str )
{
	if ( empty($str) )
		return;
	fseek($fp, $pos, SEEK_SET);
	return fwrite($fp, $str);
}

function fopen_file( $fname, $scan=true )
{
	$fp = fopen($fname, 'rb');
	if ( $fp )  return $fp;
	if ( ! $scan )
		return false;

	// case-insensitive loading
	// files/data/enemydata.bin == FILES/Data/EnemyData.bin
	$func = __FUNCTION__;
	$list = array();
	lsfile_r('.', $list);
	foreach ( $list as $f )
	{
		if ( stripos($f, $fname) === false )
			continue;
		$fp = $func($f, false);
		if ( $fp )
			return $fp;
	} // foreach ( $list as $f )

	return false;
}

function load_file( $fname, $scan=true )
{
	if ( is_file($fname) )
		return file_get_contents($fname);
	if ( ! $scan )
		return '';

	// case-insensitive loading
	// files/data/enemydata.bin == FILES/Data/EnemyData.bin
	$func = __FUNCTION__;
	$list = array();
	lsfile_r('.', $list);
	foreach ( $list as $f )
	{
		if ( stripos($f, $fname) === false )
			continue;
		$data = $func($f, false);
		if ( $data !== '' )
			return $data;
	} // foreach ( $list as $f )

	// nothing found
	return '';
}

function save_file( $fname, $str )
{
	if ( defined("DRY_RUN") )
		return;
	if ( empty($str) )
		return;
	if ( strpos($fname, '\\') )
		$fname = str_replace('\\', '/', $fname);

	if ( strpos($fname, '/') )
		@mkdir( dirname($fname), 0755, true );
	file_put_contents($fname, $str);
	return;
}

function load_clutfile( $fname )
{
	if ( is_file($fname) )
	{
		$clut = file_get_contents($fname);
		$mgc = substr($clut, 0, 4);
		$data = array();
		if ( $mgc == "CLUT" )
		{
			$data['cc'] = str2int($clut,  4, 4);
			$data['w']  = str2int($clut,  8, 4);
			$data['h']  = str2int($clut, 12, 4);
			$cc = $data['cc'] * 4;
			$sz = $data['w'] * $data['h'];

			$data['pal'] = substr($clut, 16, $cc);
			$data['pix'] = substr($clut, 16+$cc, $sz);

			// pad to enough data
			while ( strlen($data['pal']) < $cc )
				$data['pal'] .= ZERO;
			while ( strlen($data['pix']) < $sz )
				$data['pix'] .= ZERO;
			return $data;
		}

		if ( $mgc == "RGBA" )
		{
			$data['w'] = str2int($clut, 4, 4);
			$data['h'] = str2int($clut, 8, 4);
			$sz = $data['w'] * $data['h'] * 4;

			$data['pix'] = substr($clut, 12, $sz);

			// pad to enough data
			while ( strlen($data['pix']) < $sz )
				$data['pix'] .= ZERO;
			return $data;
		}

		// not CLUT/RGBA
		return 0;
	} // if ( is_file($fname) )

	// case-insensitive + extensionless loading
	// files/data/enemydata == FILES/Data/EnemyData.rgba
	$func = __FUNCTION__;
	$list = array();
	lsfile_r('.', $list);
	foreach ( $list as $f )
	{
		if ( stripos($f, $fname) === false )
			continue;
		$data = $func($f);
		if ( $data !== 0 )
			return $data;
	} // foreach ( $list as $f )

	return 0;
}

function save_clutfile( $fname, &$data )
{
	if ( ! isset( $data['w'] ) || ! isset( $data['h'] ) )
		return;
	if ( ! isset( $data['pix'] ) )
		return;
	if ( isset( $data['cc'] ) )
	{
		// pad to enough data
		$cc = $data['cc'] * 4;
		while ( strlen($data['pal']) < $cc )
			$data['pal'] .= ZERO;

		$sz = $data['w'] * $data['h'];
		while ( strlen($data['pix']) < $sz )
			$data['pix'] .= ZERO;

		$clut = "CLUT";
		$clut .= chrint($data['cc'], 4);
		$clut .= chrint($data['w'], 4);
		$clut .= chrint($data['h'], 4);
		$clut .= $data['pal'];
		$clut .= $data['pix'];
		save_file($fname, $clut);
	}
	else
	{
		// pad to enough data
		$sz = $data['w'] * $data['h'] * 4;
		while ( strlen($data['pix']) < $sz )
			$data['pix'] .= ZERO;

		$rgba = "RGBA";
		$rgba .= chrint($data['w'], 4);
		$rgba .= chrint($data['h'], 4);
		$rgba .= $data['pix'];
		save_file($fname, $rgba);
	}
	return;
}

function load_text( $fname )
{
	$txt = load_file($fname);
	if ( empty($txt) )
		return "";
	$txt = str_replace( array("\r\n","\r"), "\n", $txt);
	return explode("\n", $txt);
}

function file2sect( &$file, $sect, $pfx, $parse, $end=0, $save=true )
{
	if ( $end <= 0 )
		$end = strlen($file);

	list($parse,$int) = $parse;
	if ( ! function_exists($parse) )
		return;

	// sect = [
	//   { 'p' : 0 , 'k' : 0 }, // p = position , k = block size/row
	// ]
	// added 'o' = offset
	// added 'd' = data
	$offs = array();
	$offs[] = $end;
	foreach ( $sect as $k => $v )
	{
		$b1 = $parse($file, $v['p'], $int);
		if ( $b1 == 0 )
			continue;
		$offs[] = $b1;
		$sect[$k]['o'] = $b1;
	} // foreach ( $sect as $k => $v )
	sort($offs);

	foreach ( $sect as $k => $v )
	{
		if ( ! isset( $v['o'] ) )
			continue;
		$id = array_search($v['o'], $offs);
		$sz = int_floor($offs[$id+1] - $v['o'], $v['k']);
		$dat = substr($file, $v['o'], $sz);

		if ( $save )
		{
			$name = "$pfx/meta/$k";
			trace("== %s( %s , %x )\n", __FUNCTION__, $name, $v['k']);

			$buf = debug_block( $dat, $v['k'] );
			//trace("%s\n", $buf);
			save_file("$name.txt", $buf);
		}

		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$file = $sect;
	return;
}

function sect_sum( &$sect, $name, $off )
{
	if ( defined("NO_TRACE") )
		return;

	$pos = 0;
	$sum = array();
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

function req_file( $fname )
{
	if ( ! file_exists($fname) )
		trigger_error("req_file( $fname ) : not found\n", E_USER_ERROR);
	return file_get_contents($fname);
}

function arrayint( &$array )
{
	foreach ( $array as $k => $v )
		$array[$k] = (int)$v;
	return;
}

function arrayhex( &$array )
{
	foreach ( $array as $k => $v )
		$array[$k] = hexdec($v);
	return;
}

function patchfile( $fname, $val_callback="" )
{
	$fn = __DIR__ . "/patch/$fname";
	if ( ! file_exists($fn) )
		return array();

	$data = array();
	$sect = "";
	foreach ( file($fn) as $v )
	{
		$v = preg_replace("|[\s]+|", '', $v);
		if ( empty($v) )
			continue;
		if ( $v[0] == '#' ) // comments
			continue;
		if ( $v[0] == '[' ) // section header
		{
			$sect = trim($v, '[]');
			continue;
		}

		list($vk,$vv) = explode('=', $v);
		if ( strpos($vv, ',') !== false )
		{
			$vv = explode(',', $vv);
			if ( function_exists($val_callback) )
				$val_callback($vv);
		}
		$data[$sect][$vk] = $vv;
	} // foreach ( file($fn) as $v )

	return $data;
}

function lsfile_r( $dir, &$list )
{
	if ( empty($dir) || ! is_dir($dir) )
		return;
	if ( is_link($dir) ) // DO NOT follow symlinks
		return;

	$func = __FUNCTION__;
	foreach ( scandir($dir) as $en )
	{
		if ( $en[0] == '.' )
			continue;
		$fn = "$dir/$en";

		if ( is_file($fn) ) // allow FILE Symlinks
			$list[] = $fn;
		else
		if ( is_dir ($fn) )
			$func($fn, $list);
	} // foreach ( scandir($dir) as $en )
	return;
}

function rmfile_r( $dir )
{
	if ( is_link($dir) ) // skip ALL Symlinks
		return;
	if ( is_file($dir) )
		return unlink($dir);
	if ( is_dir($dir) )
	{
		$list = array();
		lsfile_r( $dir, $list );
		if ( empty($list) )
			return;
		foreach ( $list as $en )
			unlink($en);
	}
	return;
}
//////////////////////////////
define("PIX_BLACK", ZERO . ZERO . ZERO . BYTE);
define("PIX_WHITE", BYTE . BYTE . BYTE . BYTE);
define("PIX_ALPHA", ZERO . ZERO . ZERO . ZERO);

function canvpix( $w, $h, $c = PIX_ALPHA )
{
	$s = $w * $h;
	return str_repeat($c, $s);
}

function rgb555( $str )
{
	// 15-bit ABGR
	// RGB = c6         52         52
	//     = 1100 0110  0101 0010  0101 0010
	//     = 11000      01010      01010
	// 15-bit = 0(A) 01010(B) 01010(G) 11000(R)
	//        = 0010 1001 0101 1000 = 2958
	//        = 58 29
	$pal = ordint($str);

	$b = ($pal >> 7) & 0xf8; // <<  1 >> 8 == >> 7
	$g = ($pal >> 2) & 0xf8; // <<  6 >> 8 == >> 2
	$r = ($pal << 3) & 0xf8; // << 11 >> 8 == << 3
	$a = BYTE;

	$str = chr($r) . chr($g) . chr($b) . $a;
	return $str;
}

function pal555( $str )
{
	$clut = "";
	$siz = strlen($str);
	for ( $i=0; $i < $siz; $i += 2 )
		$clut .= rgb555( $str[$i+0] . $str[$i+1] );
	return $clut;
}

function strpal555( &$file, $pos, $cc )
{
	$str = substr($file, $pos, $cc*2);
	return pal555($str);
}

function mstrpal555( &$file, $pos, $cc, $cn )
{
	$ret = array();
	while ( $cn > 0 )
	{
		$ret[] = strpal555($file, $pos, $cc);
		$pos += ($cc*2);
		$cn--;
	}
	return $ret;
}

function is_blank( &$pix )
{
	// TOFIX : GVA 0022.17
	//         frame 0,3,4 are shadow sprite , have only alpha and black pixels
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 4 )
	{
		if ( $pix[$i+0] != ZERO )  return false;
		if ( $pix[$i+1] != ZERO )  return false;
		if ( $pix[$i+2] != ZERO )  return false;
		if ( $pix[$i+3] != ZERO && $pix[$i+3] != BYTE )
			return false;
	}
	return true;
}

function is_gray( &$pix )
{
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 4 )
	{
		if ( $pix[$i+0] != $pix[$i+1] || $pix[$i+0] != $pix[$i+2] )
			return false;
	}
	return true;
}

function grayclut( $cc )
{
	$grad = gradient($cc, PIX_BLACK, PIX_WHITE);
	return implode('', $grad);
}

function gradient( $cc, $src, $dst )
{
	if ( $cc <= 1 )
		return php_error("gradient() cc [%d]", $cc);
	$r1 = ord( $src[0] );
	$g1 = ord( $src[1] );
	$b1 = ord( $src[2] );
	$a1 = ord( $src[3] );
	$r2 = ord( $dst[0] );
	$g2 = ord( $dst[1] );
	$b2 = ord( $dst[2] );
	$a2 = ord( $dst[3] );

	$sr = ($r2 - $r1) / ($cc - 1);
	$sg = ($g2 - $g1) / ($cc - 1);
	$sb = ($b2 - $b1) / ($cc - 1);
	$sa = ($a2 - $a1) / ($cc - 1);

	$clut = array();
	for ( $i=0; $i < $cc; $i++ )
	{
		$clut[] = chr($r1) . chr($g1) . chr($b1) . chr($a1);
		$r1 = int_clamp($r1 + $sr, 0, BIT8);
		$g1 = int_clamp($g1 + $sg, 0, BIT8);
		$b1 = int_clamp($b1 + $sb, 0, BIT8);
		$a1 = int_clamp($a1 + $sa, 0, BIT8);
	}
	return $clut;
}

function bpp4to8( &$pix )
{
	$new = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $pix[$i] );
		$b1 = ($b >> 0) & BIT4;
		$b2 = ($b >> 4) & BIT4;
		$new .= chr($b1) . chr($b2);
	}
	$pix = $new;
	return;
}
//////////////////////////////
function rippix_errs( $sx, $sy, $w, $h, $sw, $sh )
{
	$err = "";
	if ( $sx < 0 )
		$err .= sprintf("rippix() sx = %d\n", $sx);
	if ( $sy < 0 )
		$err .= sprintf("rippix() sy = %d\n", $sy);
	if ( ($sx + $w) > $sw )
		$err .= sprintf("rippix() sx + w = %d + %d [%d]\n", $sx, $w, $sw);
	if ( ($sy + $h) > $sh )
		$err .= sprintf("rippix() sy + h = %d + %d [%d]\n", $sy, $h, $sh);

	if ( ! empty($err) )
	{
		php_error($err);
		return true;
	}
	return false;
}

function rippix4( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") )
		return "";
	$sx >>= 1;
	$w  >>= 1;
	$sw >>= 1;
	if ( rippix_errs($sx, $sy, $w, $h, $sw, $sh) )
		return "";

	$rip = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$syy = ($sy + $y) * $sw;
		for ( $x=0; $x < $w; $x++ )
		{
			$sxx = $syy + $sx + $x;
			$p = ord( $pix[$sxx] );

			$buf = "";
			$buf .= chr( ($p >> 0) & BIT4 );
			$buf .= chr( ($p >> 4) & BIT4 );

			$rip .= $buf;
		} // for ( $x=0; $x < ($w/2); $x++ )
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}

function rippix8( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined("DRY_RUN") )
		return "";
	if ( rippix_errs($sx, $sy, $w, $h, $sw, $sh) )
		return "";

	$rip = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$sxx = ($sy + $y) * $sw + $sx;
		$rip .= substr($pix, $sxx, $w);
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}
//////////////////////////////
function COPYPIX_DEF()
{
	// PHP 5.6 fix
	// Warning: Constants may only evaluate to scalar values
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
			'vector' => array(), // replaces sx,sy for copyquad
		),
		'dx' => 0,
		'dy' => 0,
		'bgzero' => -1, // 1-byte (CLUT,int) or 4-byte (RGBA,string) to ignore
		'vflip'  => false,
		'hflip'  => false,
		'rotate' => array(0,0,0),
		'vector' => array(), // replaces dx,dy for copyquad
		'alpha'  => "", // callback
	);
	return $php56;
}
//////////////////////////////
function alpha_none( $fg, $bg )
{
	if ( $fg[3] == ZERO )  return $bg;
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
			$pix = "";
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
			$r = ord($fg[0]);
			$g = ord($fg[1]);
			$b = ord($fg[2]);

			$a = ( $r > $g ) ? $r : $g;
			$a = ( $b > $a ) ? $b : $a;
			if ( $a == 0 )
				return $bg;

			$r = int_clamp($r / $a * BIT8, 0, BIT8);
			$g = int_clamp($g / $a * BIT8, 0, BIT8);
			$b = int_clamp($b / $a * BIT8, 0, BIT8);
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
	if ( defined("DRY_RUN") )
		return;
	// ~1.00 == no scaling needed
	if ( $fact_x == 1.0 && $fact_y == 1.0 )
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

function flippix( &$pix, $byte=1 )
{
	// horizontal flip + vertical flip
	$flip = str_repeat(ZERO, $pix['src']['w'] * $pix['src']['h'] * $byte);
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

			$c1 = substr($pix['src']['pix'], $sxx*$byte, $byte);
			str_update($flip, $dxx*$byte, $c1);

		} // for ( $x=0; $x < $pix['src']['w']; $x++ )
	} // for ( $y=0; $y < $pix['src']['h']; $y++ )

	$pix['src']['pix'] = $flip;
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
	if ( $byte == 1 )
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

function copypix_errs( &$pix )
{
	$err = "";
	if ( $pix['dx'] < 0 )
		$err .= sprintf("copypix() dx = %d\n", $pix['dx']);
	if ( $pix['dy'] < 0 )
		$err .= sprintf("copypix() dy = %d\n", $pix['dy']);
	if ( ($pix['dx'] + $pix['src']['w']) > $pix['rgba']['w'] )
		$err .= sprintf("copypix() dx + sw = %d + %d [%d]\n", $pix['dx'], $pix['src']['w'], $pix['rgba']['w']);
	if ( ($pix['dy'] + $pix['src']['h']) > $pix['rgba']['h'] )
		$err .= sprintf("copypix() dy + sh = %d + %d [%d]\n", $pix['dy'], $pix['src']['h'], $pix['rgba']['h']);

	if ( ! empty($err) )
	{
		php_error($err);
		return true;
	}
	return false;
}

function copypix( &$pix, $byte=1 )
{
	if ( $byte != 1 && $byte != 4 ) // 1=CLUT  4=RGBA  *=invalid
		return;
	if ( empty( $pix['src']['pix'] ) )
		return;
	if ( copypix_errs($pix) )
		return;
	if ( defined("DRY_RUN") )
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

function copypix_fast( &$pix, $byte=1 )
{
	if ( $byte != 1 && $byte != 4 ) // 1=CLUT  4=RGBA  *=invalid
		return;
	if ( empty( $pix['src']['pix'] ) )
		return;
	if ( copypix_errs($pix) )
		return;
	if ( defined("DRY_RUN") )
		return;

	flippix($pix, $byte);

	// copypix() with
	//   IGNORED [rotate]
	//   IGNORED [alpha ]
	//   IGNORED [bgzero]
	//   = blendFunc( fg=ONE , bg=ZERO )
	for ( $y=0; $y < $pix['src']['h']; $y++ )
	{
		$row = '';
		if ( $byte == 1 )
		{
			$syy = $y * $pix['src']['w'];
			for ( $x=0; $x < $pix['src']['w']; $x++ )
			{
				$b = ord( $pix['src']['pix'][$syy+$x] );
				$row .= substr($pix['src']['pal'], $b*4, 4);
			}
		}
		else
		if ( $byte == 4 )
		{
			$syy = $y * $pix['src']['w'] * 4;
			$row = substr($pix['src']['pix'], $syy, $pix['src']['w']*4);
		}

		$dyy = ($pix['dy'] + $y) * $pix['rgba']['w'];
		$dxx = ($pix['dx'] + $dyy) * 4;

		str_update($pix['rgba']['pix'], $dxx, $row);
	} // for ( $y=0; $y < $pix['src']['h']; $y++ )

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

		if ( trim($b, ZERO) != '' )
			break;

		$y1 += $TRIM_SZ;
		$y2 -= $TRIM_SZ;
		$x1 += $TRIM_SZ;
		$x2 -= $TRIM_SZ;
	} // while (1)

	$w = $x2 - $x1;
	$h = $y2 - $y1;
	$canv = "";
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

function savepix( $fn, &$pix, $trim = false )
{
	if ( defined("DRY_RUN") )
		return;

	if ( is_blank( $pix['rgba']['pix'] ) )
		return php_warning("%s [pix] blank", $fn);
	if ( $trim )
		trimpix($pix);

	$clpal = array();
	$clpix = "";
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
	trace("savepix() CLUT [%x]  %d x %d\n", $pix['rgba']['cc'], $pix['rgba']['w'], $pix['rgba']['h']);
	return save_clutfile("$fn.clut", $pix['rgba']);

rgba:
	trace("savepix() RGBA  %d x %d\n", $pix['rgba']['w'], $pix['rgba']['h']);
	return save_clutfile("$fn.rgba", $pix['rgba']);
}
//////////////////////////////
function psx_patch( $dir, $pfx )
{
	foreach( file("$dir/system.cnf") as $sys )
	{
		if ( stripos($sys, 'boot') === false )
			continue;
		preg_match('|[a-z]{4}_[0-9]{3}\.[0-9]{2}|i', $sys, $m);
		if ( $m[0] == '' )
			return array();
		return patchfile("{$pfx}_{$m[0]}.txt");
	} // foreach( file("$dir/system.cnf") as $sys )
	return array();
}

function psx_exe( &$psxram, $psxexe )
{
	if ( empty($psxram) )
		return "";

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

function psxtim( &$file )
{
	$tim = array();
	$pos = 0;

	// TIM header
	//   0 = 4-bpp grayscale
	//   1 = 8-bpp grayscale
	//   2 = rgb555 true color
	//   3 = rgb888 true color
	//   8 = 4-bpp pseudo-color
	//   9 = 8-bpp pseudo-color
	$type = str2int( $file, $pos+4, 1 );
		$pos += 8;

	$tim['pix'] = '';
	switch ( $type )
	{
		//////////////////////////////
		case 8:
			// VERY COMMON
			// palette header
			$siz = str2int($file, $pos, 4);
			$tim['cn'] = (int)($siz / 0x20);
			$tim['pal'] = mstrpal555($file, $pos+12, 0x10, $tim['cn']);
				$pos += $siz;
		case 0:
			// pixel header
			$siz = str2int($file, $pos, 4);
			$tim['w'] = str2int($file, $pos+ 8, 2) * 4;
			$tim['h'] = str2int($file, $pos+10, 2);

			$psz = $siz - 12;
			$tim['pix'] = substr($file, $pos+12, $psz);
			bpp4to8( $tim['pix'] );
			$pos += $siz;

			$tim['t'] = "CLUT";
			$tim['cc'] = 0x10;
			break;
		//////////////////////////////
		case 9:
			// VERY COMMON
			// palette header
			$siz = str2int($file, $pos, 4);
			$tim['cn'] = (int)($siz / 0x200);
			$tim['pal'] = mstrpal555($file, $pos+12, 0x100, $tim['cn']);
				$pos += $siz;
		case 1:
			// TokiMemo 2 substories *.tml
			// pixel header
			$siz = str2int($file, $pos, 4);
			$tim['w'] = str2int($file, $pos+ 8, 2) * 2;
			$tim['h'] = str2int($file, $pos+10, 2);
			$tim['pix'] = substr($file, $pos+12, $siz-12);
				$pos += $siz;

			$tim['t'] = "CLUT";
			$tim['cc'] = 0x100;
			break;
		//////////////////////////////
		case 2:
			// Legend of Mana /ana/etc_etc/title00.dat/1.tim
			// Xenogears 2464/*.tim
			// Lunar 2 Eternal Blue data/isok/*.tim
			// pixel header
			$siz = str2int($file, $pos, 4);
			$tim['w'] = str2int($file, $pos+ 8, 2);
			$tim['h'] = str2int($file, $pos+10, 2);

			$sub = substr($file, $pos+12, $siz-12);
			$tim['pix'] = pal555($sub);
				$pos += $siz;

			$tim['t'] = "RGBA";
			break;
		case 3:
			// Legend of Mana /ana/zukan_p/z00/zukan1p.dat
			// pixel header
			$siz = str2int($file, $pos, 4);
			$tim['w'] = str2int($file, $pos+ 8, 2);
			$tim['h'] = str2int($file, $pos+10, 2);

			$psz = $siz - 12;
			$sub = substr($file, $pos+12, $psz);
			for ( $i=0; $i < $psz; $i += 3 )
				$tim['pix'] .= $sub[$i+0] . $sub[$i+1] . $sub[$i+2] . BYTE;
			$pos += $siz;

			$tim['t'] = "RGBA";
			break;
		//////////////////////////////
		default:
			return $tim;
	} // switch ( $tim['t'] )

	trace("psxtim() %s @ %d x %d\n", $tim['t'], $tim['w'], $tim['h']);
	return $tim;
}
//////////////////////////////
