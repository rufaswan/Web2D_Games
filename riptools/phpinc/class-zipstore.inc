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

define('PNG_MAGIC' , "\x89PNG\x0d\x0a\x1a\x0a");
define('GZIP_MAGIC', "\x1f\x8b\x08");

class zipstore
{
	private function chr( int $i ) : string
	{
		$b0 = ($i >> 0) & BIT8;
		return chr($b0);
	}

	private function chr2( int $i ) : string
	{
		$b0 = ($i >> 0) & BIT8;
		$b1 = ($i >> 8) & BIT8;
		return chr($b0) . chr($b1);
	}

	private function chr4( int $i ) : string
	{
		$b0 = ($i >>  0) & BIT8;
		$b1 = ($i >>  8) & BIT8;
		$b2 = ($i >> 16) & BIT8;
		$b3 = ($i >> 24) & BIT8;
		return chr($b0) . chr($b1) . chr($b2) . chr($b3);
	}
	//////////////////////////////
	private function pack( string $zipfn, array &$list, string $dir ) : void
	{
		$fp = fopen($zipfn, 'wb');
		if ( ! $fp )  return;

		$cent = '';
		$pos  = 0;
		$cnt  = 0;
		$c1 = [$this, 'chr'];
		$c2 = [$this, 'chr2'];
		$c4 = [$this, 'chr4'];
		foreach ( $list as $k => $v )
		{
			if ( ! empty($v['data']) )
				$data = &$v['data'];
			else
				$data = file_get_contents($dir .'/'. $v['name']);
			$len  = strlen($v['name']);
			$size = strlen($data);
			$crc  = hash('crc32b', $data, true);
				$crc = strrev($crc);

			$pk3  = "PK\x03\x04"; // 00  magic
			$pk3 .= $c2(10);    // 04  ver 1.0
			$pk3 .= $c2( 0);    // 06  flags
			$pk3 .= $c2( 0);    // 08  compression
			$pk3 .= $c2( 0);    // 0a  mod time
			$pk3 .= $c2( 0);    // 0c  mod date
			$pk3 .= $crc;       // 0e  crc 32
			$pk3 .= $c4($size); // 12  fsize compressed
			$pk3 .= $c4($size); // 16  fsize uncompressed
			$pk3 .= $c2($len ); // 1a  fname len
			$pk3 .= $c2( 0);    // 1c  extra field size
			$pk3 .= $v['name'];
			$pk3 .= $data;
				fwrite($fp, $pk3);

			$pk1  = "PK\x01\x02"; // 00  magic
			$pk1 .= $c1(10);    // 04  made by ver 1.0
			$pk1 .= $c1( 0);    // 05  made by host (msdos)
			$pk1 .= $c2(10);    // 06  ver 1.0
			$pk1 .= $c2( 0);    // 08  flags
			$pk1 .= $c2( 0);    // 0a  compression
			$pk1 .= $c2( 0);    // 0c  mod time
			$pk1 .= $c2( 0);    // 0e  mod date
			$pk1 .= $c4($crc ); // 10  crc 32
			$pk1 .= $c4($size); // 14  fsize compressed
			$pk1 .= $c4($size); // 18  fsize uncompressed
			$pk1 .= $c2($len ); // 1c  fname len
			$pk1 .= $c2( 0);    // 1e  extra field size
			$pk1 .= $c2( 0);    // 20  comment len
			$pk1 .= $c2( 0);    // 22  disk .z01 .z02 ...
			$pk1 .= $c2( 0);    // 24  internal attr
			$pk1 .= $c4( 0);    // 26  external attr (made by host)
			$pk1 .= $c4($pos ); // 2a  PK34 offset
			$pk1 .= $v['name'];
				$cent .= $pk1;

			//tool::trace($pos, $fsize, $zipn, $fnm);
			$pos += strlen($pk3);
			$cnt++;
		} // foreach ( $list as $fsize => $flist )

		fwrite($fp, $cent);
		$len = strlen($cent);

		$pk5  = "PK\x05\x06"; // 00  magic
		$pk5 .= $c2(0);    // 04  count .z01 .z02...
		$pk5 .= $c2(0);    // 06  disk w/central dir
		$pk5 .= $c2($cnt); // 08  disk  entry
		$pk5 .= $c2($cnt); // 0a  total entry
		$pk5 .= $c4($len); // 0c  central dir len
		$pk5 .= $c4($pos); // 10  central dir offset
		$pk5 .= $c2(0);    // 14  comment len

		fwrite($fp, $pk5);
		fclose($fp);
	}

	private function addfile( array &$list, string $dir ) : void
	{
		if ( empty($dir) || ! is_dir($dir) )
			return;
		if ( is_link($dir) ) // DO NOT follow symlinks
			return;

		$func = [$this, __FUNCTION__];
		foreach ( scandir($dir) as $ent )
		{
			if ( $ent[0] === '.' )
				continue;

			$full = $dir . '/' . $ent;
			if ( is_dir($full) )
				$func($list, $full);
			else
			if ( is_file($full) )
			{
				// SKIP file without extension
				$p = strrpos($ent, '.');
				if ( $p === false )
					continue;
				$ext = substr($ent, $p+1);

				$t = [
					'name' => substr($full, 2),
					'size' => filesize($full),
					'ext'  => strtolower($ext),
					'data' => '',
				];
				$list[] = $t;
			}
		} // foreach ( scandir($dir) as $d )
	}
	//////////////////////////////
	public function sort_size_asc( array $a, array $b ) : int
	{
		$r = (int)($a['size'] - $b['size']);
		if ( $r === 0 )
			$r = strcmp($a['ext' ], $b['ext' ]);
		if ( $r === 0 )
			$r = strcmp($a['name'], $b['name']);
		return $r;
	}

	public function scan( string $dir ) : array
	{
		if ( ! is_dir($dir) )
			return [];

		$real = realpath($dir);
		if ( empty($real) )
			return [];

		$list = [];
		$this->addfile($list, $real);
		if ( count($list) < 1 )
			return [];

		$len = strlen($real) + 1;
		foreach ( $list as $k => $v )
			$list[$k]['name'] = substr($v['name'], $len);

		return $list;
	}

	public function save( string $zipfx, array &$list, string $dir='.' ) : void
	{
		if ( empty($zipfx) || empty($list) )
			return;

		$p = strrpos($zipfx, '.');
		if ( $p === false )
			return;

		$nam = substr($zipfx, 0, $p);
		$ext = substr($zipfx, $p+1);
		if ( count($list) > 0xfff0 )
		{
			$id = 0;
			while ( ! empty($list) )
			{
				$l = array_splice($list, 0, 0xfff0);
				$fn = sprintf('%s.%d.%s', $nam, $id, $ext);
					$id++;
				$this->pack($fn, $l, $dir);
			} // while ( ! empty($list) )
		}
		else
		{
			$fn = sprintf('%s.%s', $nam, $ext);
			$this->pack($fn, $list, $dir);
		}
	}
	//////////////////////////////
	public static function raw( string &$data ) : string
	{
		// +5 bytes for every 0xffff bytes data
		$zlib = '';
		$len  = strlen($data);
		$pos  = 0;
		while ( $len > BIT16 )
		{
			// 0   = 00 non end block
			// 1 2 = length LE
			// 3 4 = NOT length LE
			$zlib .= ZERO . BYTE . BYTE . ZERO . ZERO;
			$zlib .= substr($data, $pos, BIT16);
				$pos += BIT16;
				$len -= BIT16;
		} // while ( $len > BIT16 )

		// 1   = 01 end block
		// 1 2 = length LE
		// 3 4 = NOT length LE
		$b1a = ($len >> 0) & BIT8;
		$b1b = ($len >> 8) & BIT8;
		$b2a = $b1a ^ BIT8;
		$b2b = $b1b ^ BIT8;
		$zlib .= "\x01" . chr($b1a) . chr($b1b) . chr($b2a) . chr($b2b);
		$zlib .= substr($data, $pos);
		return $zlib;
	}

	public static function gzip( string &$data ) : string
	{
		// +a bytes header
		//    *same as raw*
		// +8 bytes footer
		$zlib  = GZIP_MAGIC . ZERO . "\x00\x00\x00\x00" . "\x04\x03"; // magic
		$zlib .= self::raw($data);

		// crc LE
		$crc = crc32($data);
		for ( $i=0; $i < 4; $i++ )
		{
			$b = ($crc >> ($i*8)) & BIT8;
			$zlib .= chr($b);
		}
		// length LE
		$len = strlen($data);
		for ( $i=0; $i < 4; $i++ )
		{
			$b = ($len >> ($i*8)) & BIT8;
			$zlib .= chr($b);
		}
		return $zlib;
	}

	public static function deflate( string &$data ) : string
	{
		// +2 bytes header
		//    *same as raw*
		// +4 bytes footer
		$zlib  = "\x78\x01"; // magic
		$zlib .= self::raw($data);

		$len = strlen($data);
		$sum1 = 0;
		$sum2 = 1;
		for ( $i=0; $i < $len; $i++ )
		{
			$b1 = ord( $data[$i] );
			$sum2 += $b1;
			while ( $sum2 >= 0xfff1 )
				$sum2 -= 0xfff1;
			$sum1 += $sum2;
			while ( $sum1 >= 0xfff1 )
				$sum1 -= 0xfff1;
		} // for ( $i=0; $i < $len; $i++ )

		// Adler-32 checksum
		// 0 1 = sum of sum bytes BE
		// 2 3 = sum bytes BE
		$b1a = ($sum1 >> 8) & BIT8;
		$b1b = ($sum1 >> 0) & BIT8;
		$b2a = ($sum2 >> 8) & BIT8;
		$b2b = ($sum2 >> 0) & BIT8;
		$zlib .= chr($b1a) . chr($b1b) . chr($b2a) . chr($b2b);
		return $zlib;
	}
} // class zipstore

// enum
//   ZLIB_ENCODING_RAW     = -15
//   ZLIB_ENCODING_GZIP    =  31
//   ZLIB_ENCODING_DEFLATE =  15
// level = -1 to 9
//   -1 = auto (same as level 6)
//    0 = store
//    9 = best
// zip file methods
//  unzip -v ZIPFILE
//  zipinfo  ZIPFILE
//  0   = Stored
//  1-2 = Defl:F
//  3-7 = Defl:N
//  8-9 = Defl:X
if ( ! function_exists('zlib_encode') )
{
	// $encoding = ZLIB_ENCODING_DEFLATE
	// $level    = 0
	function zlib_encode( string $data, int $encoding=15, int $level=-1) : string
	{
		return zipstore::deflate($data);
	}
}
