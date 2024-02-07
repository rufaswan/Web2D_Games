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
//   - *.inc MUST NOT use echo/printf , only trace()
// - global variables are prefixed "gp_" (global pointer)
define('ZERO', "\x00");
define('BYTE', "\xff");
define('BIT4',  (1 <<  4)-1);
define('BIT8',  (1 <<  8)-1);
define('BIT16', (1 << 16)-1);
define('BIT24', (1 << 24)-1);
define('BIT32', (1 << 32)-1);
//define('DRY_RUN', true);  // per script
//define('NO_TRACE', true); // per script
//if ( defined('DRY_RUN') )
//if ( defined('NO_TRACE') )

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

function php_exec()
{
	$args = func_get_args();
	$fmt = array_shift($args);

	$is_ret = false;
	if ( (int)$fmt === $fmt )
	{
		$is_ret = $fmt;
		$fmt = array_shift($args);
	}

	$cmd = escapeshellcmd( vsprintf($fmt, $args) );
	$out = array();
	$ret = 0;
	exec($cmd, $out, $ret);

	$out = implode("\n", $out);
	if ( $is_ret === false )
		return $out;
	if ( $is_ret === $ret )
		return $out;
	return '';
}
//////////////////////////////
function php_req_bytes( $byte )
{
	if ( PHP_INT_SIZE < $byte )
		return php_error('required %d-bit support', $byte*8);
}
function php_req_define( $define )
{
	if ( ! defined($define) )
		return php_error('required global constant %s not defined', $define);
}
function php_req_extension( $func, $ext )
{
	if ( ! function_exists($func) )
		return php_error('required %s() [%s] not found', $func, $ext);
}

// https://www.php.net/manual/en/reserved.constants.php
php_req_define('PHP_INT_SIZE');   // since 5.0.5
php_req_define('PHP_VERSION_ID'); // since 5.2.7

// PHP 7.0+ for native 64-bit int on windows
php_req_bytes(4);
if ( PHP_VERSION_ID < 50600 )
	php_warning('version older than 5.6 is not supported');
//////////////////////////////
function str2int( &$str, $pos, $byte, $sign=false )
{
	php_req_bytes($byte);
	if ( ! isset($str[$pos+$byte-1]) )
		return php_error('[%x] substr %x > strlen %x', $pos, $byte, strlen($str));

	$sub = substr($str, $pos, $byte);
	$int = ordint($sub);
	if ( $sign )
	{
		$sra = (PHP_INT_SIZE - $byte) * 8; // shift right arithmetic
		return ( ($int << $sra) >> $sra );
	}
	return $int;
}

function substr0( &$str, $pos, $end='' )
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
	// accept str , return int
	if ( "$str" !== $str )
		return (int)$str;
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
	// accept int , return str
	if ( (int)$int !== $int )
		return "$int";
	$str = '';
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

function sint_bit( $s, $bit )
{
	$int = ( (int)$s === $s ) ? $s : ordint($s);
	$bit = (int)abs($bit);

	$sra = (PHP_INT_SIZE * 8) - $bit; // shift right arithmetic
	if ( $sra < 0 )
		return php_error('required at least %d-bit support', $bit);

	return ( ($int << $sra) >> $sra );
}

function sint8 ( $s )  { return sint_bit($s,  8); }
function sint16( $s )  { return sint_bit($s, 16); }
function sint24( $s )  { return sint_bit($s, 24); }
function sint32( $s )  { return sint_bit($s, 32); }

function is_hexstr( $str )
{
	if ( "$str" !== $str )
		return false;
	$m = '0123456789abcdefABCDEF';
	$l = strlen($str);
	for ( $i=0; $i < $l; $i++ )
	{
		if ( strpos($m, $str[$i]) === false )
			return false;
	}
	return hexdec($str);
}

function neghex( $int )
{
	if ( $int < 0 )
		return sprintf('-%x', -$int);
	else
		return sprintf('%x', $int);
}

function splitline( $t, $line )
{
	if ( strpos($line,$t) === false )
		return $line;
	$arr = explode($t, $line);
	$cnt = count($arr);
	for ( $i=0; $i < $cnt; $i++ )
		$arr[$i] = trim( $arr[$i] );
	return $arr;
}

function str_update( &$file, $pos, $str )
{
	if ( "$str" !== $str )
		return;
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( ! isset($file[$pos]) )
			return php_error('file[%x] not init', $pos);
		$file[$pos] = $str[$i];
			$pos++;
	}
	return;
}

function strpos_all( &$str, $term, $pos=0, $igCASE=false )
{
	if ( empty($term) || empty($str) )
		return '';
	if ( $pos < 0 )
		$pos = 0;
	$strpos = ( $igCASE ) ? 'stripos' : 'strpos';

	$tlen = strlen($term);
	$ret  = array();
	while (1)
	{
		$p = $strpos($str, $term, $pos);
		if ( $p === false )
			return $ret;
		$ret[] = $p;
		$pos   = $p + $tlen;
	}
	return $ret;
}

function stripos_all( &$str, $term, $pos=0 )
{
	return strpos_all($str, $term, $pos, true);
}

function int_clamp( $int, $min, $max )
{
	$int = (int)$int;
	if ( $int < $min )  return $min;
	if ( $int > $max )  return $max;
	return $int;
}

//  int near
//  123, 100 =  200 / ceil
//  123,-100 =  100 / floor
// -123, 100 = -100 / ceil
// -123,-100 = -200 / floor
//  123,   0 =    0 / invalid
// -123,   0 =    0 / invalid
function int_ceil( $int, $near )
{
	$int  = (int)$int;
	$near = (int)$near;
	if ( $near > 0 )
	{
		while ( $int % $near )
			$int++;
		return $int;
	}
	if ( $near < 0 )
	{
		while ( $int % $near )
			$int--;
		return $int;
	}
	return 0;
}

// int based rounding
//   base 16
//    0 -  7 =>  0 ,  8 - 15 => 16
//   16 - 23 => 16 , 24 - 31 => 32
function int_round( $int, $near )
{
	$rnd = (int)round($int / $near);
	$res = (int)($rnd * $near);
	return $res;
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

function var_swap( &$var1, &$var2 )
{
	$t = $var2;
	$var2 = $var1;
	$var1 = $t;
	return;
}

function var_min()
{
	$arg = func_get_args();
	$min = array_shift($arg);
	foreach ( $arg as $a );
	{
		if ( $a < $min )
			$min = $a;
	}
	return $min;
}

function var_max()
{
	$arg = func_get_args();
	$max = array_shift($arg);
	foreach ( $arg as $a );
	{
		if ( $a > $max )
			$max = $a;
	}
	return $max;
}

function var_div( $num, $fact )
{
	$mod = $num % $fact;
		$num -= $mod;
	$div = $num / $fact;
	return array($div, $mod);
}

function count_bits( $int )
{
	$bits = array(0,0);
	$int  = (int)$int;
	while ( $int > 0 )
	{
		$b = $int & 1;
		$int >>= 1;
		$bits[$b]++;
	}
	return $bits;
}
//////////////////////////////
function printhex( &$str, $sep=-1 )
{
	$sep = (int)$sep;
	$txt = '';
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $sep > 0 && $i > 1 && ($i % $sep) === 0 )
			$txt .= '  ';
		if ( $str[$i] === ZERO )
			$txt .= '-- ';
		else
			$txt .= sprintf('%2x ', ord($str[$i]) );
	}
	return $txt;
}

function debug( $str, $name='DEBUG' )
{
	$txt = sprintf("%s : %s\n", $name, printhex($str));
	return $txt;
}

function debug_block( &$str, $blksz )
{
	$len = int_ceil( strlen($str) , -$blksz);
	$txt = '';
	$id  = 0;
	for ( $i=0; $i < $len; $i += $blksz )
	{
		$sub  = substr($str, $i, $blksz);
		$txt .= sprintf("%4x : %s\n", $id, printhex($sub));
			$id++;
	}
	return $txt;
}

function trace()
{
	if ( defined('NO_TRACE') )
		return;
	$args = func_get_args();
	$fmt = array_shift($args);
	vprintf($fmt, $args);
	return;
}

function zero_watch( $name, $chr )
{
	if ( $chr === ZERO )
		return;
	php_notice('%s not zero [%2x]', $name, ord($chr));
	return;
}

function byte_watch( $name, $chr )
{
	if ( $chr === BYTE )
		return;
	php_notice('%s not 0xFF [%2x]', $name, ord($chr));
	return;
}

function flag_watch( $name, $int )
{
	if ( $int === 0 )
		return;
	php_notice('%s some flags is set [%x]', $name, $int);
	return;
}
//////////////////////////////
function fp2str( $fp, $pos, $byte )
{
	if ( $byte === 0 )
		return '';
	fseek($fp, 0, SEEK_END);
	$fsz = ftell($fp);
	if ( ($pos+$byte) > $fsz )
	{
		php_warning('fp2str (%x + %x) >= %x', $pos, $byte, $fsz);
		return ZERO;
	}

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
	if ( is_dir($fname) )
		return false;
	$fp = fopen($fname, 'rb');
	if ( $fp )
		return $fp;
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
	if ( defined('DRY_RUN') )
		return;
	if ( empty($str) )
		return;
	if ( strpos($fname, '\\') !== false )
		$fname = str_replace('\\', '/', $fname);

	if ( strpos($fname, '/') !== false )
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
		if ( $mgc === 'CLUT' )
		{
			$data['t']  = 'CLUT';
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

		if ( $mgc === 'RGBA' )
		{
			$data['t'] = 'RGBA';
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
		while ( strlen($data['pal']) & 3 )
			$data['pal'] .= ZERO;
		$cc = strlen($data['pal']) >> 2;

		$sz = $data['w'] * $data['h'];
		while ( strlen($data['pix']) < $sz )
			$data['pix'] .= ZERO;
		$data['pix'] = substr($data['pix'], 0, $sz);

		$clut = 'CLUT';
		$clut .= chrint($cc, 4);
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
		$data['pix'] = substr($data['pix'], 0, $sz);

		$rgba = 'RGBA';
		$rgba .= chrint($data['w'], 4);
		$rgba .= chrint($data['h'], 4);
		$rgba .= $data['pix'];
		save_file($fname, $rgba);
	}
	return;
}

function load_patchfile( $game_id )
{
	$patch_dir = __DIR__ . '/patch';
	$fname = '';
	foreach ( scandir($patch_dir) as $f )
	{
		if ( $f[0] === '.' )
			continue;
		if ( stripos($f, $game_id) !== false )
		{
			$fname = $patch_dir . '/' . $f;
			goto loadpatch;
		}
	} // foreach ( scandir($patch_dir) as $f )

	return php_error('load_patchfile( %s ) not found', $game_id);

loadpatch:
	//trace("load_patchfile( %s )\n", $fname);
	$file = file_get_contents($fname);
	while (1)
	{
		$p1 = strpos($file, '/*');
		if ( $p1 === false )
			break;
		$p2 = strpos($file, '*/', $p1);
		if ( $p2 === false )
			$p2 = strlen($file);
		else
			$p2 += 2;
		$s = substr($file, $p1, $p2-$p1);
		$file = str_replace($s, '', $file);
	} // while(1)

	$file = str_replace("\r", "\n", $file);

	$data = array();
	$sect = '';
	foreach ( explode("\n", $file) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		if ( $line[0] === '#' ) // one line comments
			continue;

		if ( $line[0] === '[' ) // section header
		{
			$sect = trim($line, '][');
			continue;
		}

		$val = preg_split('|[\s]+|', $line);
		$key = array_shift($val);

		foreach ( $val as $vk => $vv )
		{
			$vvh = is_hexstr($vv);
			if ( $vvh !== false )
				$val[$vk] = $vvh;
		}

		$data[$sect][$key] = $val;
	} // foreach ( explode("\n", $file) as $line )
	return $data;
}

function imgfile_size( $fname )
{
	$ext = substr($fname, strrpos($fname, '.')+1);
		$ext = strtolower($ext);
	switch ( $ext )
	{
		case 'png':
			$file = file_get_contents($fname);
			$w = str2big($file, 0x10, 4);
			$h = str2big($file, 0x14, 4);
			return array($w,$h);

		case 'bmp':
			$file = file_get_contents($fname);
			$w = str2int($file, 0x12, 4);
			$h = str2int($file, 0x16, 4);
			return array($w,$h);

		case 'clut':
			$file = file_get_contents($fname);
			$w = str2int($file,  8, 4);
			$h = str2int($file, 12, 4);
			return array($w,$h);

		case 'rgba':
			$file = file_get_contents($fname);
			$w = str2int($file, 4, 4);
			$h = str2int($file, 8, 4);
			return array($w,$h);
	} // switch ( $ext )
	return -1;
}

function center_clutfile( &$data, $x1, $y1 )
{
	if ( ! isset($data['pix']) )
		return;
	if ( $x1 === 0 && $y1 === 0 )
		return;
	$by = ( isset($data['pal']) ) ? 1 : 4;

	$x2 = $x1 + $data['w'];
	$y2 = $y1 + $data['h'];
	$hw = ( abs($x1) > abs($x2) ) ? abs($x1) : abs($x2);
	$hh = ( abs($y1) > abs($y2) ) ? abs($y1) : abs($y2);

	$nw = $hw * 2;
	$nh = $hh * 2;
	$npix = str_repeat(ZERO, $nw*$nh*$by);

	$row = $data['w'] * $by;
	for ( $y=0; $y < $data['h']; $y++ )
	{
		$sub = substr($data['pix'], $y*$row, $row);

		$dyy = ($y1 + $hh) + $y;
		$dxx = ($dyy * $nw) + ($x1 + $hw);
		str_update($npix, $dxx*$by, $sub);
	} // for ( $y=0; $y < $data['h']; $y++ )

	$data['w'] = $nw;
	$data['h'] = $nh;
	$data['pix'] = $npix;
	return;
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
		if ( $en[0] === '.' )
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

function lsfile_bysize_r( $dir )
{
	$list = array();
	lsfile_r($dir, $list);
	if ( empty($list) )
		return '';

	$list2 = array();
	foreach ( $list as $fn )
		$list2[] = array(filesize($fn), $fn);

	usort($list2, function($a,$b){
		return ($a[0] > $b[0]);
	});
	return $list2;
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

function dir_file_exists( &$list, $dir, &$check )
{
	if ( empty($list) )
	{
		foreach ( scandir($dir) as $f )
		{
			if ( $f[0] === '.' )
				continue;
			$list[] = "$dir/$f";
		}
	}

	foreach ( $check as $cf )
	{
		$m = false;
		foreach ( $list as $lf )
		{
			if ( stripos($lf, $cf) !== false )
				$m = true;
		} // foreach ( $list as $lf )

		if ( ! $m )
			return false;
	} // foreach ( $check as $cf )

	return true;
}

function argv_loopfile( &$argv, $func )
{
	if ( ! function_exists($func) )
		return;
	trace("usage : %s  FILE/DIR...\n", $argv[0]);
	$argc = count($argv);
	for ( $i=1; $i < $argc; $i++ )
	{
		$ent = rtrim($argv[$i], '/\\');
		if ( is_file($ent) )
		{
			trace("[%x] %s( %s )\n", $argc-$i, $func, $ent);
			$func($ent);
			continue;
		}
		if ( is_link($ent) ) // DO NOT follow non-file symlinks
			continue;
		if ( is_dir($ent) )
		{
			$list = array();
			lsfile_r($ent, $list);

			$cnt = count($list);
			for( $j=0; $j < $cnt; $j++ )
			{
				trace("[%x][%x] %s( %s )\n", $argc-$i, $cnt-$j, $func, $list[$j]);
				$func($list[$j]);
			}
			continue;
		}
		// skip vars
	} // for ( $i=1; $i < $argc; $i++ )
	return;
}
//////////////////////////////
define('PIX_BLACK', ZERO . ZERO . ZERO . BYTE);
define('PIX_WHITE', BYTE . BYTE . BYTE . BYTE);
define('PIX_ALPHA', ZERO . ZERO . ZERO . ZERO);

function canvpix( $w, $h, $c = PIX_ALPHA )
{
	$s = $w * $h;
	return str_repeat($c, $s);
}

function webpal16()
{
	// https://en.wikipedia.org/wiki/Web_color
	// Basic colors
	$clr  = '---f  --8f  -8-f  -88f'; // K   8B  8G  8C
	$clr .= '8--f  8-8f  88-f  cccf'; // 8R  8M  8Y  cW
	$clr .= '888f  --ff  -f-f  -fff'; // 8W  B   G   C
	$clr .= 'f--f  f-ff  ff-f  ffff'; // R   M   Y   W

	$len = strlen($clr);
	$pal = '';
	for ( $i=0; $i < $len; $i++ )
	{
		switch ( $clr[$i] )
		{
			case '-':  $pal .= "\x00"; break;
			case '8':  $pal .= "\x80"; break;
			case 'c':  $pal .= "\xc0"; break;
			case 'f':  $pal .= "\xff"; break;
		} // switch ( $clr[$i] )
	} // for ( $i=0; $i < 16; $i++ )

	return $pal;
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

	$str = chr($r) . chr($g) . chr($b) . BYTE;
	return $str;
}

function pal555( $str )
{
	$clut = '';
	$siz = strlen($str);
	for ( $i=0; $i < $siz; $i += 2 )
		$clut .= rgb555($str[$i+0] . $str[$i+1]);
	return $clut;
}

function is_blank( &$rgba )
{
	// TOFIX : GVA 0022.17
	//         frame 0,3,4 are shadow sprite , have only alpha and black pixels
	$len = strlen($rgba);
	for ( $i=0; $i < $len; $i += 4 )
	{
		if ( $rgba[$i+0] !== ZERO )  return false;
		if ( $rgba[$i+1] !== ZERO )  return false;
		if ( $rgba[$i+2] !== ZERO )  return false;
		if ( $rgba[$i+3] !== ZERO && $rgba[$i+3] !== BYTE )
			return false;
	}
	return true;
}

function is_gray( &$rgba )
{
	$len = strlen($rgba);
	for ( $i=0; $i < $len; $i += 4 )
	{
		if ( $rgba[$i+0] !== $rgba[$i+1] || $rgba[$i+0] !== $rgba[$i+2] )
			return false;
	}
	return true;
}

function grayclut( $cc )
{
	return gradient_clut($cc, PIX_BLACK, PIX_WHITE);
}

function gradient_clut( $cc, $src, $dst )
{
	if ( $cc <= 1 )
		return php_error('gradient_clut() cc [%d]', $cc);
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

	$clut = '';
	for ( $i=0; $i < $cc; $i++ )
	{
		$clut .= chr($r1) . chr($g1) . chr($b1) . chr($a1);
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
	$err = '';
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

function rippix8( &$pix, $sx, $sy, $w, $h, $sw, $sh )
{
	if ( defined('DRY_RUN') )
		return '';
	if ( rippix_errs($sx, $sy, $w, $h, $sw, $sh) )
		return '';

	$rip = '';
	for ( $y=0; $y < $h; $y++ )
	{
		$sxx = ($sy + $y) * $sw + $sx;
		$rip .= substr($pix, $sxx, $w);
	} // for ( $y=0; $y < $h; $y++ )
	return $rip;
}
//////////////////////////////
function copypix_def( $w, $h, $c = PIX_ALPHA )
{
	// PHP 5.6 fix
	// Warning: Constants may only evaluate to scalar values
	$php56 = array(
		'rgba' => array(
			'w' => $w,
			'h' => $h,
			'pix' => canvpix($w,$h,$c),
		),
		'src' => array(
			'w'    => 0,
			'h'    => 0,
			'pix'  => '',
			'pal'  => '',
		),
		'dx' => 0,
		'dy' => 0,
		'bgzero' => -1, // 1-byte (CLUT,int) or 4-byte (RGBA,string) to ignore
		'vflip'  => false,
		'hflip'  => false,
	);
	return $php56;
}
//////////////////////////////
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

function copypix_errs( &$pix )
{
	$err = '';
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

function copypix_fast( &$pix, $byte=1 )
{
	if ( $byte !== 1 && $byte !== 4 ) // 1=CLUT  4=RGBA  *=invalid
		return;
	if ( empty( $pix['src']['pix'] ) )
		return;
	if ( copypix_errs($pix) )
		return;
	if ( defined('DRY_RUN') )
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
		if ( $byte === 1 )
		{
			$syy = $y * $pix['src']['w'];
			for ( $x=0; $x < $pix['src']['w']; $x++ )
			{
				$b = ord( $pix['src']['pix'][$syy+$x] );
				$row .= substr($pix['src']['pal'], $b*4, 4);
			}
		}
		else
		if ( $byte === 4 )
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

function savepix( $fn, &$pix )
{
	if ( defined('DRY_RUN') )
		return;

	if ( is_blank( $pix['rgba']['pix'] ) )
		return php_warning('%s [pix] blank', $fn);

	trace("savepix() RGBA  %d x %d = %s\n", $pix['rgba']['w'], $pix['rgba']['h'], $fn);
	return save_clutfile("$fn.rgba", $pix['rgba']);
}
//////////////////////////////
function psxtim( &$file, $pos=0, $check=true )
{
	$bak = $pos;

	// TIM header
	//   0 = 4-bpp grayscale
	//   1 = 8-bpp grayscale
	//   2 = rgb555 true color
	//   3 = rgb888 true color
	//   8 = 4-bpp pseudo-color
	//   9 = 8-bpp pseudo-color
	$type = str2int($file, $pos+4, 1);
		$pos += 8;

	$pix = '';
	$pal = '';
	$cc  = 0;
	$w   = 0;
	$h   = 0;
	switch ( $type )
	{
		//////////////////////////////
		case 8:
			// VERY COMMON
			// palette header
			$siz = str2int($file, $pos+ 0, 4);
			$cc  = str2int($file, $pos+ 8, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;
			$pal = pal555($sub);
			if ( $cc > 16 )
				$cc = 16;
		case 0:
			// pixel header
			$siz = str2int($file, $pos+ 0, 4);
			$w   = str2int($file, $pos+ 8, 2) * 4;
			$h   = str2int($file, $pos+10, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;

			bpp4to8($sub);
			$pix = $sub;
			if ( $cc === 0 )
				$cc = 0x10;
			$type = 'CLUT';
			break;
		//////////////////////////////
		case 9:
			// VERY COMMON
			// palette header
			$siz = str2int($file, $pos+ 0, 4);
			$cc  = str2int($file, $pos+ 8, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;
			$pal = pal555($sub);
			if ( $cc > 256 )
				$cc = 256;
		case 1:
			// TokiMemo 2 substories *.tml
			// pixel header
			$siz = str2int($file, $pos+ 0, 4);
			$w   = str2int($file, $pos+ 8, 2) * 2;
			$h   = str2int($file, $pos+10, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;

			$pix = $sub;
			if ( $cc === 0 )
				$cc = 0x100;
			$type = 'CLUT';
			break;
		//////////////////////////////
		case 2:
			// Legend of Mana /ana/etc_etc/title00.dat/1.tim
			// Xenogears 2464/*.tim
			// Lunar 2 Eternal Blue data/isok/*.tim
			// pixel header
			$siz = str2int($file, $pos+ 0, 4);
			$w   = str2int($file, $pos+ 8, 2);
			$h   = str2int($file, $pos+10, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;

			$pix = pal555($sub);
			$type = 'RGBA';
			break;
		case 3:
			// Legend of Mana /ana/zukan_p/z00/zukan1p.dat
			// pixel header
			$siz = str2int($file, $pos+ 0, 4);
			$w   = str2int($file, $pos+ 8, 2);
			$h   = str2int($file, $pos+10, 2);
			$sub = substr ($file, $pos+12, $siz-12);
				$pos += $siz;

			$siz -= 12;
			for ( $i=0; $i < $siz; $i += 3 )
				$pix .= $sub[$i+0] . $sub[$i+1] . $sub[$i+2] . BYTE;
			$type = 'RGBA';
			break;
		//////////////////////////////
		default:
			return -1;
	} // switch ( $type )

	// VRAM size check = 400 x 200
	//   for false positive
	if ( $check )
	{
		if ( $w > 0x400 || $w < 1 )  return -1;
		if ( $h > 0x200 || $h < 1 )  return -1;
	}

	$tim = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $pix,
		'siz' => $pos - $bak,
		't'   => $type,
	);

	if ( $cc > 0 )
	{
		$tim['pal'] = $pal;
		$tim['cc' ] = $cc;
	}

	trace("psxtim() %s @ %d x %d\n", $tim['t'], $tim['w'], $tim['h']);
	return $tim;
}
//////////////////////////////
function array_nextid( &$array )
{
	if ( ! is_array($array) )
		return -1;
	$id = 0;
	foreach ( $array as $k => $v )
	{
		if ( $id <= (int)$k )
			$id = (int)$k + 1;
	}
	return $id;
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

function req_file( $fname )
{
	if ( ! file_exists($fname) )
		trigger_error("req_file( $fname ) : not found\n", E_USER_ERROR);
	return file_get_contents($fname);
}
//////////////////////////////
