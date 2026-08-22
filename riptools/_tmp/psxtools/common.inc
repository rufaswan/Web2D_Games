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
//define('DRY_RUN', true);  // per script
//define('NO_TRACE', true); // per script
//if ( defined('DRY_RUN') )
//if ( defined('NO_TRACE') )

//////////////////////////////

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

// https://www.php.net/manual/en/reserved.constants.php
php_req_define('PHP_INT_SIZE');   // since 5.0.5
php_req_define('PHP_VERSION_ID'); // since 5.2.7

// PHP 7.0+ for native 64-bit int on windows
php_req_bytes(4);
if ( PHP_VERSION_ID < 50600 )
	php_warning('version older than 5.6 is not supported');
//////////////////////////////

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

function strpos_all( &$str, $term, $pos=0, $igCASE=false )
{
	if ( empty($term) || empty($str) )
		return '';
	if ( $pos < 0 )
		$pos = 0;
	$strpos = ( $igCASE ) ? 'stripos' : 'strpos';

	$tlen = strlen($term);
	$ret  = [];
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

function int_ceil_bit( $int, $bit=0 )
{
	$int  = (int)$int;
	$bit  = (int)$bit;
	$mask = (1 << abs($bit)) - 1;

	// + = ceil
	// - = floor
	$low = $int - ($int & $mask);
	if ( $bit > 0 )
		$int = $low + $mask + 1;
	else
		$int = $low;
	return $int;
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
	return [$div, $mod];
}

function count_bits( $int )
{
	$bits = [0,0];
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
	if ( is_file($fname) )
		return fopen($fname, 'rb');
	if ( ! $scan )
		return false;

	// relative path only
	$fname = str_replace('\\', '/', $fname);
	$pos = strpos($fname, '/');
	if ( $pos === false )
	{
		$path  = '.';
		$fname = './' . $fname;
	}
	else
		$path = substr($fname, 0, $pos);
	$lower = strtolower($fname);

	// case-insensitive
	//   files/data/enemydata.bin == FILES/Data/EnemyData.bin
	$list = [];
	lsfile_r($path, $list);
	foreach ( $list as $f )
	{
		if ( strtolower($f) !== $lower )
			continue;
		return fopen($fname, 'rb');
	} // foreach ( $list as $f )

	return false;
}

function save_palfile( $fname, &$pal, $col=16 )
{
	if ( empty($fname) || empty($pal) || $col < 1 )
		return;
	$len = strlen($pal);
	if ( $len & 3 ) // in RGBA = 4 bytes
		return;
	$len >>= 2;
	$row = (int)ceil($len / $col);
	$img = [
		'w'   => $col,
		'h'   => $row,
		'pix' => $pal,
	];
	return save_clutfile($fname, $img);
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

	$data = [];
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


	public static function is_hexstr( string $str ) : bool
	{
		$m = '0123456789abcdefABCDEF';
		$l = strlen($str);
		for ( $i=0; $i < $l; $i++ )
		{
			if ( strpos($m, $str[$i]) === false )
				return false;
		}
		return true;
	}

function lsfile_bysize_r( $dir )
{
	$list = [];
	lsfile_r($dir, $list);
	if ( empty($list) )
		return '';

	$list2 = [];
	foreach ( $list as $fn )
		$list2[] = [filesize($fn), $fn];

	//usort($list2, function($a,$b){ return ($a[0] > $b[0]); });
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
		$list = [];
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

//////////////////////////////

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

//////////////////////////////
//////////////////////////////
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

/*
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
*/
//////////////////////////////
