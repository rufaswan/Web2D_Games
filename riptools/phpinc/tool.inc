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
declare( strict_types=1 );

if ( ! defined('PHP_VERSION_ID') )
	exit('PHP_VERSION_ID is undefined');
if ( PHP_VERSION_ID < 70100 ) // under v7.1.0
	exit('require PHP v7.1+');
if ( ! defined('TOOL_BASE') )
	exit('TOOL_BASE is undefined');
if ( ! function_exists('hash_algos') )
	exit('require HASH extension [core v7.4+]');
if ( ! function_exists('json_decode') )
	exit('require JSON extension [core v8.0+]');

define('ZERO', "\x00");
define('BYTE', "\xff");
define('BIT4',  (1 <<  4)-1);
define('BIT8',  (1 <<  8)-1);
define('BIT16', (1 << 16)-1);
define('BIT24', (1 << 24)-1);
define('BIT32', (1 << 32)-1);
//define('NO_TRACE', 1); // to turn off tool::trace()

class tool
{
	//////////////////////////////
	// fatal error series
	public static function require( string $name ) : void
	{
		// 1. require $(dirname $0)/$name.inc;
		// var_dump( 'get_included_files()', get_included_files() );
		// var_dump( 'get_required_files()', get_required_files() );
		//   phpbash/script.php
		//   phpbash/tool.inc
		//   phpinc/tool.inc
		// var_dump( 'get_include_path()', get_include_path() );
		//   /opt/php-cli
		$list = get_required_files();
		$req = sprintf('%s/%s.inc', dirname($list[0]), $name);
		if ( is_file($req) )
			goto req_ok;

		// 2. require TOOL_BASE/phpinc/$name.inc;
		$req = sprintf('%s/phpinc/%s.inc', TOOL_BASE, $name);
		if ( is_file($req) )
			goto req_ok;

		self::error('require %s.inc not found', $name);

req_ok:
		$var1 = get_defined_vars();
		require $req;

		// $var from INC file exists on this func
		$var2 = get_defined_vars();
		foreach ( $var2 as $k => $v )
		{
			if ( substr($k,0,3) !== 'gp_' )
				continue;
			if ( isset($var1[$k]) ) // not from this INC file
				continue;
			$GLOBALS[$k] = $v;
		} // foreach ( $var2 as $k => $v )
	}

	public static function extension( string $ext ) : void
	{
		$t = [
			'zlib' => 'zlib_decode' ,
		];
		if ( ! isset($t[$ext]) || ! function_exists($t[$ext]) )
			self::error('php extension %s.dll or %s.so is not loaded', $ext, $ext);
	}

	public static function class( string $class ) : void
	{
		if ( ! class_exists($class) )
			self::error('class %s not found [or missing require class-%s.inc;]', $class, $class);
	}

	private static function console( string $pfx, array $arg ) : string
	{
		$str = [];
		while ( ! empty($arg) )
		{
			$av = array_shift($arg);

			if ( is_array($av) )
				$str[] = sprintf('(array=%x)', count($av));
			else
			if ( is_object($av) )
				$str[] = '(object)';
			else
			if ( is_string($av) )
			{
				if ( preg_match('|[\x00\x80-\xff]+|', $av) )
					$str[] = sprintf('(binary=%x)', strlen($av));
				else
					$str[] = $av;
			}
			else  //if ( is_int($av) || is_float($av) )
			{
				// %f == 9999.990000
				$num = number_format(0.0 + $av, 6, '.', '');
				$p   = strpos($num, '.000000');
				if ( $p !== false )
				{
					// int = hex by default
					if ( $av < 0 )
						$str[] = sprintf('-%x', -$av);
					else
						$str[] = sprintf( '%x',  $av);
				}
				else
				{
					// float = trim unnecessary trailing 0s
					// ERROR
					//   rtrim('2000.000000','0.') === '2'
					$str[] = rtrim($num, '0');
				}
			}
		} // while ( ! empty($arg) )
		return sprintf('%s = %s'."\n", $pfx, implode(' , ', $str));
	}

	// backtrace + error + full stop
	public static function error() : void
	{
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,10);
		$arg = func_get_args();
		$msg = self::console('ERROR', $arg);
		fwrite(STDERR, $msg);
		exit(-1);
	}

	// backtrace + error + return -1 for loop to return early
	public static function warning() : int
	{
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,10);
		$arg = func_get_args();
		$msg = self::console('WARNING', $arg);
		fwrite(STDERR, $msg);
		return -1;
	}

	// backtrace + normal printf
	public static function notice() : int
	{
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,10);
		$arg = func_get_args();
		$msg = self::console('NOTICE', $arg);
		fwrite(STDOUT, $msg);
		return 1;
	}

	// normal printf + can be disabled with NO_TRACE
	public static function trace() : int
	{
		if ( defined('NO_TRACE') )
			return 1;
		$arg = func_get_args();
		$msg = self::console('TRACE', $arg);
		fwrite(STDOUT, $msg);
		return 1;
	}
	//////////////////////////////
	// file save/load series
	public static function safepath( string $path, int $max_word=16, int $max_name=255 ) : string
	{
		$orig = basename($path);
		//$base = preg_replace('|[^A-Za-z0-9]+|', ' ', $orig);

		// \p{L}  Matches any letter from any language (ASCII, accents, Cyrillic, Asian scripts, etc.).
		// \p{N}  Matches any numeric digit in any script.
		// /u     Turns on PCRE UTF-8 mode.
		$base = preg_replace('|[^\p{L}\p{N}]+|u', ' ', $orig);

		$wd = explode(' ', trim($base));
		foreach ( $wd as $k=>$v )
			$wd[$k] = substr($v, 0, $max_word);

		if ( count($wd) < 2 ) // no ext
			return '';
		$ext = strtolower( array_pop($wd) );

		$max = $max_name - strlen($ext) - 3; // max safepath string
		$fn  = array_shift($wd);
		while ( ! empty($wd) )
		{
			$tmp = $fn .' '. array_shift($wd);
			if ( strlen($tmp) > $max )
				break;
			$fn = $tmp;
		} // while ( ! empty($wd) )

		// if origin remain unchanged
		$new = "$fn.$ext";
		if ( $orig === $new )
			return $orig;

		self::do_not_overwrite($new, 1);
		return $new;
	}

	public static function do_not_overwrite( string &$path, int $d=-1 ) : void
	{
		if ( $d < 1 )
			return;

		if ( ! file_exists($path) )
			return;
		$pos = strrpos($path, '.');
		if ( $pos === false )
		{
			$fname = '';
			return;
		}

		$tit = substr($path, 0, $pos);
		$ext = substr($path, $pos+1);
		$fmt = $tit . '.%0'.$d.'d.' . $ext;
		$i = 0;
		while (1)
		{
			$new = sprintf($fmt, $i);
			if ( ! file_exists($new) )
			{
				$path = $new;
				return;
			}
			$i++;
		}
	}

	public static function loadbak( string $fname ) : string
	{
		$bak = $fname . '.bak';
		if ( is_file($bak) )
			return file_get_contents($bak);

		if ( ! is_file($fname) )
			return '';
		self::copy($fname, $bak);

		return file_get_contents($bak);
	}

	public static function dos2unix( string &$path ) : void
	{
		if ( strpos($path, '\\') !== false )
			$path = str_replace('\\', '/', $path);
		$path = rtrim($path, '/');
	}

	public static function mkdir( string $path, bool $is_file=true ) : void
	{
		self::dos2unix($path);
		$pos = strrpos($path, '/');
		if ( $pos === false ) // no need mkdir
			return;

		// auto dirname()
		if ( $is_file )
			$dir = substr($path, 0, $pos);
		else
			$dir = $path;

		@mkdir($dir, 0755, true);
	}

	public static function save( string $dst, string &$bin, int $d=-1 ) : bool
	{
		if ( empty($bin) )
			return false;

		self::do_not_overwrite($dst, $d);
		self::mkdir($dst);
		file_put_contents($dst, $bin);

		// in case stopped halfway , or run out of disk space
		// delete the incomplete/corrupted file
		if ( filesize($dst) !== strlen($bin) )
		{
			unlink($dst);
			return false;
		}

		return true;
	}

	public static function copy( string $src, string $dst ) : bool
	{
		// is same file
		if ( $src === $dst )
			return false;

		self::mkdir($dst);
		$srcp = @fopen($src, 'rb');
		$dstp = @fopen($dst, 'wb');
		if ( ! $srcp || ! $dstp )
			return false;

		// copy chunk-by-chunk in case of large file
		// 0x100000 == (1 << 20) == 1 MB
		$size = filesize($src);
		while ( $size > 0 )
		{
			$bin = fread($srcp, 0x100000);
			fwrite($dstp, $bin);
			$size -= 0x100000;
		}
		fclose($srcp);
		fclose($dstp);

		// in case stopped halfway , or run out of disk space
		// delete the incomplete/corrupted file
		$srcsz = filesize($src);
		$dstsz = filesize($dst);
		if ( $srcsz !== $dstsz )
		{
			unlink($dst);
			return false;
		}

		return true;
	}

	public static function move( string $src, string $dst ) : bool
	{
		// in case SRC_FILE already moved
		// DO NOT overwrite DST_FILE
		if ( is_file($dst) )
			return false;

		// DO NOT delete SRC_FILE if copy failed
		if ( ! self::copy($src,$dst) )
			return false;
		unlink($src);
		return true;
	}

	public static function load( string $fname ) : string
	{
		// file_get_contents()
		// + case-insensitive
		//     files/data/enemydata.bin == FILES/Data/EnemyData.bin
		if ( is_file($fname) )
			return file_get_contents($fname);

		$list = [];
		self::scan($list, '.');
		foreach ( $list as $v )
		{
			if ( stripos($v,$fname) !== false )
				return file_get_contents($v);
		}

		return '';
	}
	//////////////////////////////
	// string series
	public static function substr0( string &$file, int $pos, string $end="\x00" ) : string
	{
		$str = '';
		while (1)
		{
			// if $file ended
			if ( ! isset($file[$pos]) )
				break;

			// get an char and check if it is an $end char
			$c = $file[$pos];
				$pos++;
			if ( strpos($end,$c) !== false )
				break;

			$str .= $c;
		} // while (1)
		return $str;
	}

	public static function substr( string &$file, int $pos, int $len ) : string
	{
		$str = substr($file, $pos, $len);
		if ( strlen($str) !== $len )
			self::error('substr not enough data', $pos, $len, strlen($str), strlen($file));
		return $str;
	}

	public static function str_update( string &$file, int $pos, string $sub ) : void
	{
		$len = strlen($sub);
		for ( $i=0; $i < $len; $i++ )
		{
			if ( ! isset($file[$pos+$i]) )
				self::error('str_update[%x + %x] not enough data', $pos, $i);
			$file[$pos+$i] = $sub[$i];
		} // for ( $i=0; $i < $len; $i++ )
	}
	//////////////////////////////
	// binary pack/unpack series
	public static function ord( string $bin, bool $sign=false ) : int
	{
		// little-endian ord
		// 33 22 11 00 = 112233
		$bin = rtrim ($bin, ZERO); // skip ord zero
		$len = strlen($bin);
		$int = 0;
		while ( $len > 0 )
		{
			$len--;
			$int <<= 8;
			$int |= ord( $bin[$len] );
		}

		$len = strlen($bin);
		if ( $sign )
		{
			$sra = (PHP_INT_SIZE - $len) * 8; // shift right arithmetic
			return ( ($int << $sra) >> $sra );
		}
		return $int;
	}

	public static function ordstr( string &$bin, int $pos, int $byte=4, bool $sign=false ) : int
	{
		if ( PHP_INT_SIZE < $byte )
			return 0;
		$sub = substr($bin, $pos, $byte);
		if ( strlen($sub) !== $byte )
			return 0;
		return self::ord($sub, $sign);
	}

	public static function sign( int &$int, int $bit ) : void
	{
		if ( $bit < 0 )
			return;
		$sra = (PHP_INT_SIZE * 8) - $bit;
		if ( $sra < 0 )
			return;
		$int = ($int << $sra) >> $sra;
	}

	public static function chr( float $int, int $byte=4 ) : string
	{
		// little-endian chr
		// 112233 = 33 22 11 00
		$bin = '';
		for ( $i=0; $i < $byte; $i++ )
		{
			$c = $int & 0xff;
			$bin .= chr($c);
			$int >>= 8;
		}
		return $bin;
	}
	//////////////////////////////
	// php-cli args series
	public static function real2rel_path( array &$list ) : string
	{
		$cnt = count($list);
		if ( $cnt < 2 )
			return '';

		// has trailing /
		// ./dir/sub/
		// ./directory/subfolder/
		$base = '';
		while (1)
		{
			$pos = strlen($base);
			$p = strpos($list[0], '/', $pos);
			if ( $p === false )
				break;

			// check if filelist are same $base
			$p++;
			$dir1 = substr($list[0], 0, $p);
			for ( $i=1; $i < $cnt; $i++ )
			{
				$dir2 = substr($list[$i], 0, $p);
				if ( $dir1 !== $dir2 )
					break 2;
			} // for ( $i=1; $i < $cnt; $i++ )

			// all same $base
			$base = $dir1;
		} // while (1)

		if ( empty($base) )
			return '';

		// convert filelist to relative to $base
		$pos = strlen($base);
		for ( $i=0; $i < $cnt; $i++ )
		{
			$rel = substr($list[$i], $pos);
			$list[$i] = $rel;
		} // for ( $i=0; $i < $cnt; $i++ )

		// remove trailing /
		// ./dir/sub/ -> ./dir/sub
		return substr($base, 0, $pos-1);
	}

	public static function find( string $dir, string $preg ) : string
	{
		if ( empty($dir) || ! is_dir($dir) )
			return '';
		if ( is_link($dir) ) // DO NOT follow symlinks
			return '';
		$dir = rtrim($dir, '\\/');

		$func = [__CLASS__, __FUNCTION__];
		foreach ( scandir($dir) as $ent )
		{
			if ( $ent[0] === '.' )
				continue;
			$path = "$dir/$ent";

			if ( is_file($path) )
			{
				if ( preg_match($preg,$ent) )
				{
					self::trace('find', $path);
					return $path;
				}
			}
			else
			if ( is_dir($path) )
			{
				$res = $func($list, $path);
				if ( ! empty($res) )
					return $res;
			}
		} // foreach ( scandir($dir) as $ent )
		return '';
	}

	public static function scan( array &$list, string $dir ) : void
	{
		if ( empty($dir) || ! is_dir($dir) )
			return;
		if ( is_link($dir) ) // DO NOT follow symlinks
			return;
		$dir = rtrim($dir, '\\/');

		$func = [__CLASS__, __FUNCTION__];
		foreach ( scandir($dir) as $ent )
		{
			if ( $ent[0] === '.' )
				continue;
			$path = "$dir/$ent";

			if ( is_file($path) ) // allow FILE Symlinks
				$list[] = $path;
			else
			if ( is_dir($path) )
				$func($list, $path);
		} // foreach ( scandir($dir) as $ent )
	}

	public static function argv_callback( array &$argv, string $callback ) : void
	{
		if ( ! function_exists($callback) )
			return;
		printf('@usage : %s  FILE/DIR...'."\n", $argv[0]);
		$argc = count($argv);
		for ( $i=1; $i < $argc; $i++ )
		{
			$rem_i = $argc - $i;

			// DIR/ or DIR\\ -> DIR
			$ent = rtrim($argv[$i], '/\\');
			if ( is_file($ent) )
			{
				printf('[%x] %s( %s )'."\n", $rem_i, $callback, $ent);
				$callback($ent);
				continue;
			}

			// DO NOT follow non-file symlinks
			//   dir can be recursive (ln -s . this)
			if ( is_link($ent) )
				continue;

			if ( is_dir($ent) )
			{
				$list = [];
				self::scan($list, $ent);

				$cnt = count($list);
				foreach ( $list as $lk => $lv )
				{
					$rem_j = $cnt - $lk;
					printf('[%x][%x] %s( %s )'."\n", $rem_i, $rem_j, $callback, $lv);
					$callback($lv);
				}
				continue;
			}
		} // for ( $i=1; $i < $argc; $i++ )
	}

	public static function docblock_decode( string &$file ) : array
	{
		// get last DocBlock
		$p1 = strrpos($file, '/**');
		$p2 = strrpos($file, '*/' );
		if ( $p1 === false || $p2 === false || ($p2-$p1) < 0 )
			return [];
		$p1 += 3;
		$doc = substr($file, $p1, $p2-$p1);
		$doc = preg_replace('|[\s\*]+|', ' ', $doc);
		$file = explode(' ', trim($doc));

		$doc = [];
		$cur = [];
		while ( ! empty($file) )
		{
			$s = array_shift($file);
			if ( $s[0] === '@' )
			{
				if ( ! empty($cur) )
					$doc[] = $cur;
				$cur = [];
			}
			$cur[] = $s;
		} // while ( ! empty($arr) )

		if ( ! empty($cur) )
			$doc[] = $cur;
		return $doc;
	}

	public static function usage( array $argv, string $callback ) : void
	{
		// get the DocBlock from main.php
		$list = get_required_files();
		$file = file_get_contents($list[0]);

		// already have @usage
		if ( strrpos($file, '::argv_callback(') !== false )
			return;

		$doc = self::docblock_decode($file);
		if ( empty($doc) )
			self::error('docblock_decode failed');
		print_r($doc);

/*
		$option = [];
		$argc = count($argv);
		for ( $i=1; $i < $argc; $i++ )
		{
			// verify user arg with docblock

		} // for ( $i=1; $i < $argc; $i++ )
		//$callback($option);
*/
	}
	//////////////////////////////
	// int series
	public static function int_ceil( float $int, int $near ) : int
	{
		if ( $near === 0 )
			return 0;
		$div = (int)($int / $near);
		$adj = $div * $near;

		// if no remainder , return as is
		if ( $adj === (int)$int )
			return (int)$int;

		// if has remainder , ceil/floor it
		if ( $adj > 0 && $near > 0 )
			$adj += $near;
		if ( $adj < 0 && $near < 0 )
			$adj += $near;
		return $adj;
	}

	public static function int_ceil_pow2( float $int ) : int
	{
		$pow = 1;
		while ( $pow < $int )
			$pow <<= 1;
		return $pow;
	}

	public static function int_clamp( float $int, int $min, int $max ) : int
	{
		if ( $int < $min )  return $min;
		if ( $int > $max )  return $max;
		return (int)$int;
	}

	//////////////////////////////
} // class load
