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
 *
 * Special Thanks
 *   CriPakTools-mod
 *   http://github.com/wmltogether/CriPakTools/
 *     Falo
 *     Nanashi3
 *     esperknight
 *     yjulian
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');

//define('NO_TRACE', true);
$gp_fp = 0;
function fp2str( int $pos, int $len ) : string
{
	global $gp_fp;
	fseek($gp_fp, $pos, SEEK_SET);
	$bin = fread($gp_fp, $len);
	if ( strlen($bin) !== $len )
		tool::error('fpstr not enough data', strlen($bin), $len);
	return $bin;
}
//////////////////////////////
function cpk_decrypt( string &$str ) : void
{
	if ( substr($str,0,4) === '@UTF' )
		return;

	// CriPakTools-20190920/LibCPK/CPK.cs
	$len = strlen($str);
	$m = 0x655f;
	$t = 0x4115;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );

		$b = ($b ^ $m) & BIT8;
		$m = ($m * $t) & BIT16;

		$str[$i] = chr($b);
	} // for ( $i=0; $i < $len; $i++ )
}

function cpklist( string &$meta ) : array
{
	if ( substr($meta,0,4) !== '@UTF' )
		return [];

	$off1 = ieee754::ordstr($meta,  8, 4) + 8; // row
	$off2 = ieee754::ordstr($meta, 12, 4) + 8; // str
	$off3 = ieee754::ordstr($meta, 16, 4) + 8; // data

	$tbl1 = ieee754::ordstr($meta, 20, 4); // name
	$tbl2 = ieee754::ordstr($meta, 24, 2); // no col
	$tbl3 = ieee754::ordstr($meta, 26, 2); // row len
	$tbl4 = ieee754::ordstr($meta, 28, 4); // no row

	$cols = [];
	$pos = 32;
	for ( $c=0; $c < $tbl2; $c++ )
	{
		$b1 = ieee754::ordstr($meta, $pos+0, 1);
		$b2 = ieee754::ordstr($meta, $pos+1, 4);
			$pos += 5;
		$s = tool::substr0($meta, $off2+$b2);
		$cols[$c] = [$s, $b1];
	} // for ( $c=0; $c < $tbl2; $c++ )

	$list = [];
	for ( $r=0; $r < $tbl4; $r++ )
	{
		$pos = $off1 + ($r * $tbl3);
		for ( $c=0; $c < $tbl2; $c++ )
		{
			list($cnam,$cflg) = $cols[$c];
			$stor = ($cflg >> 4) & BIT4;
			$type = ($cflg >> 0) & BIT4;

			switch ( $stor )
			{
				case 0:
				case 1:
					$list[$r][$cnam] = 0;
					break;
				case 3:
				case 5:
					switch ( $type )
					{
						case 0:
						case 1:
							$v = ieee754::ordstr($meta, $pos, 1);
							$pos++;
							$list[$r][$cnam] = $v;
							break;
						case 2:
						case 3:
							$v = ieee754::ordstr($meta, $pos, 2);
							$pos += 2;
							$list[$r][$cnam] = $v;
							break;
						case 4:
						case 5:
							$v = ieee754::ordstr($meta, $pos, 4);
							$pos += 4;
							$list[$r][$cnam] = $v;
							break;
						case 6:
						case 7:
							$v = ieee754::ordstr($meta, $pos, 8);
							$pos += 8;
							$list[$r][$cnam] = $v;
							break;
						case 8:
							$v = substrrev($meta, $pos, 4);
							$v = ieee754_decode::float32($v);
							$list[$r][$cnam] = $v;
							break;
						case 9:
							break;
						case 10:
							$v1 = ieee754::ordstr($meta, $pos, 4);
							$v2 = substr0($meta, $off2+$v1);
							$pos += 4;
							$list[$r][$cnam] = $v2;
							break;
						case 11:
							$v1 = ieee754::ordstr($meta, $pos+0, 4);
							$v2 = ieee754::ordstr($meta, $pos+4, 4);
							$pos += 8;
							$list[$r][$cnam] = substr($meta, $off3+$v1, $v2);
							break;
						default:
							tool::error('unknown cflg', $cflg);
					} // switch ( $type )
					break;
				default:
					tool::error('unknown cflg', $cflg);
			} // switch ( $stor )

		} // for ( $c=0; $c < $tbl2; $c++ )
	} // for ( $r=0; $r < $tbl4; $r++ )

	return $list;
}
//////////////////////////////
function crilay_bits( string &$file, int &$pos, atring &$bybit, int $bitned ) : int
{
	// get bits = 1 , 2 , 8
	// 76543210  76543210
	// 333-----  01133333
	while ( strlen($bybit) < $bitned )
	{
		$b = ord( $file[$pos] );
			$pos++;
		//tool::trace('BIT', $pos+1, $b, $bybit);

		$i = 8;
		while ( $i > 0 )
		{
			$i--;
			$bybit .= ($b >> $i) & 1;
		}
	} // while ( $bits > $bylen )

	$bits = 0;
	for ( $i=0; $i < $bitned; $i++ )
	{
		$bits <<= 1;
		if ( $bybit[$i] )
			$bits |= 1;
	}
	$bybit = substr($bybit, $bitned);
	//tool::trace('RET', $bits, $bybit);
	return $bits;
}

function crilay_len( string &$file, int &$pos, string &$bybit ) : int
{
	$len = 0;
bit_2:
	$b = crilay_bits($file, $pos, $bybit, 2);
	$len += $b;
	if ( $b !== 0x03 )
		goto done;
bit_3:
	$b = crilay_bits($file, $pos, $bybit, 3);
	$len += $b;
	if ( $b !== 0x07 )
		goto done;
bit_5:
	$b = crilay_bits($file, $pos, $bybit, 5);
	$len += $b;
	if ( $b !== 0x1f )
		goto done;
bit_8:
	$b = crilay_bits($file, $pos, $bybit, 8);
	$len += $b;
	if ( $b === 0xff )
		goto bit_8;
done:
	return $len;
}

function crilayla_decode( string &$file ) : void
{
	if ( substr($file, 0, 8) !== 'CRILAYLA' )
		return;

	$len  = tool::ordstr($file,  8, 4);
	$size = tool::ordstr($file, 12, 4);
	$head = substr($file, $size+0x10, 0x100);

	$file = substr($file, 0x10, $size);
	$file = strrev($file);

	$bybit = '';
	$dec = '';
	$pos = 0;
	while ( $pos < $size )
	{
		if ( strlen($dec) >= $len )
			break;

		$flg = crilay_bits($file, $pos, $bybit, 1);
		if ( $flg ) // 1
		{
			$b = crilay_bits($file, $pos, $bybit, 13);
			$dpos = $b + 3;

			$b = crilay_len($file, $pos, $bybit);
			$dlen = $b + 3;

			for ( $i=0; $i < $dlen; $i++ )
			{
				$b = strlen($dec) - $dpos;
				$dec .= $dec[$b];
			}
		}
		else // 0
		{
			$b = crilay_bits($file, $pos, $bybit, 8);
			//trace("COPY %2x [%s]\n", $b, $bybit);
			$dec .= chr($b);
		}
	} // while ( $st < $ed )

	//$file = $dec;
	$file = $head . strrev($dec);
}
//////////////////////////////
function sect_toc( string $type, string $dir, int $off, int $siz ) : void
{
	tool::trace(__FUNCTION__, $type, $dir, $off, $siz);
	if ( $off === 0 || $siz === 0 )
		return;

	$meta = fp2str($off+0x10, $siz);
	cpk_decrypt($meta);
	tool::save("$dir/$type.bin", $meta);

	$list = cpklist($meta);
	if ( empty($list) )
		return;
	tool::trace('list', $list);

	$data = [];
	foreach ( $list as $lv )
	{
		if ( ! isset( $lv['FileName'] ) )
			continue;

		$fn = sprintf('%s/%s', $lv['DirName'], $lv['FileName']);
		$v = [ $lv['FileSize'], $fn ];
		$k = $lv['FileOffset'] + $off;
		$data[$k] = $v;
	} // foreach ( $list as $lk => $lv )

	ksort($data);
	$buf = '';
	foreach ( $data as $k => $v )
	{
		$log = sprintf("%8x , %8x , %s\n", $k, $v[0], $v[1]);
		echo $log;
		$buf .= $log;

		$meta = fp2str($k, $v[0]);
		//tool::save("$dir/{$v[1]}.bak", $meta);
		crilayla_decode($meta);
		tool::save("$dir/{$v[1]}", $meta);
	}
	tool::save("$dir/$type.txt", $buf);
}

function sect_cpk( string &$meta, string $dir ) : void
{
	tool::trace(__FUNCTION__, $dir);

	cpk_decrypt($meta);
	tool::save("$dir/CPK.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	//print_r($list);

	sect_toc('TOC' , $dir, $list[0]['TocOffset'] , $list[0]['TocSize']);
	sect_toc('ETOC', $dir, $list[0]['EtocOffset'], $list[0]['EtocSize']);
	sect_toc('ITOC', $dir, $list[0]['ItocOffset'], $list[0]['ItocSize']);
	sect_toc('GTOC', $dir, $list[0]['GtocOffset'], $list[0]['GtocSize']);
}
//////////////////////////////
function cpkfile( string $fname ) : void
{
	global $gp_fp;
	$gp_fp = fopen($fname, 'rb');
	if ( ! $gp_fp )  return;

	$dir = str_replace('.', '_', $fname);

	$head = fp2str(0, 0x10);
	$size = tool::ordstr($head, 8, 4);
	if ( substr($head,0,4) !== 'CPK ' )
		return;

	$meta = fp2str(0x10, $size);
	sect_cpk($meta, $dir);
}

argv_loopfile($argv, 'cpkfile');
