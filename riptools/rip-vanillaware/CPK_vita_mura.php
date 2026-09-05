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
tool::require('func-hexnum');

//define('NO_TRACE', true);
$gp_fp = 0;

function fp2str( int $pos, int $len ) : string
{
	if ( $pos < 0 || $len < 1 )
		tool::error('fp2str < 0', $pos, $len);

	global $gp_fp;
	fseek($gp_fp, $pos, SEEK_SET);
	$bin = fread($gp_fp, $len);
	if ( strlen($bin) !== $len )
		tool::error('fpstr not enough data', strlen($bin), $len);
	return $bin;
}

function sort_fileoffset( array $a, array $b ) : int
{
	return $a['FileOffset'] - $b['FileOffset'];
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

function cpklist( string &$meta, string $pfx ) : array
{
	if ( substr($meta,0,4) !== '@UTF' )
		return [];

	$prow = ieee754::ordstr($meta, 0x08, 4) + 8; // row
	$pstr = ieee754::ordstr($meta, 0x0c, 4) + 8; // str
	$pdat = ieee754::ordstr($meta, 0x10, 4) + 8; // data

	$tbl1 = ieee754::ordstr($meta, 0x14, 4); // name
	$no_col = ieee754::ordstr($meta, 0x18, 2); // no col
	$sz_row = ieee754::ordstr($meta, 0x1a, 2); // row len
	$no_row = ieee754::ordstr($meta, 0x1c, 4); // no row

	$sub = tool::substr($meta, $prow, $sz_row * $no_row);
	$ent = hexnum::dump_r($sub, $sz_row);
	tool::save($pfx.'.hex', $ent);

	$cols = [];
	$pos  = 0x20;
	for ( $c=0; $c < $no_col; $c++ )
	{
		$cflg = ieee754::ordstr($meta, $pos+0, 1);
		$pnam = ieee754::ordstr($meta, $pos+1, 4);
			$pos += 5;
		$cnam = tool::substr0($meta, $pstr + $pnam);
		$cols[$c] = [$cnam, $cflg];
	} // for ( $c=0; $c < $tbl2; $c++ )

	$list = [];
	for ( $r=0; $r < $no_row; $r++ )
	{
		$pos = $prow + ($r * $sz_row);
		$sub = tool::substr($meta, $pos, $sz_row);

		$pos = 0;
		$ent = [];
		foreach ( $cols as $cv )
		{
			list($cnam,$cflg) = $cv;
			switch ( $cflg )
			{
				case 0x10: // int8
				case 0x12: // int16
				case 0x14: // int32
				case 0x16: // int64
					$ent[$cnam] = 0;
					break;
				case 0x1a: // string
				case 0x3a: // string
					$ent[$cnam] = '';
					break;

				case 0x50: // int8
					$i8 = ieee754::ordstr($sub, $pos, 1);
						$pos += 1;
					$ent[$cnam] = $i8;
					break;
				case 0x52: // int16
					$i16 = ieee754::ordstr($sub, $pos, 2);
						$pos += 2;
					$ent[$cnam] = $i16;
					break;
				case 0x54: // int32
					$i32 = ieee754::ordstr($sub, $pos, 4);
						$pos += 4;
					$ent[$cnam] = $i32;
					break;
				case 0x56: // int64
					$i64 = ieee754::ordstr($sub, $pos, 8);
						$pos += 8;
					$ent[$cnam] = $i64;
					break;
				case 0x5a: // string
					$v = ieee754::ordstr($sub, $pos, 4);
					$s = tool::substr0($meta, $pstr + $v);
						$pos += 4;
					$ent[$cnam] = $s;
					break;
/*
				case 8: // float32
					$v1 = ieee754::ordstr($sub, $pos, 4);
					$v2 = ieee754::to($v1, 4);
						$pos += 4;
					$ent[$cnam] = $v2;
					break;
				case 11: // char[]
					$v1 = ieee754::ordstr($sub, $pos+0, 4);
					$v2 = ieee754::ordstr($sub, $pos+4, 4);
					$s  = substr($meta, $pdat + $v1, $v2);
						$pos += 8;
					$ent[$cnam] = $s;
					break;
*/
				default:
					tool::error('unknown cflg', $cflg);
			} // switch ( $cflg )
		} // foreach ( $cols as $cv )

		$list[] = $ent;
	} // for ( $r=0; $r < $tbl4; $r++ )

	return $list;
}
//////////////////////////////
function crilay_bits( string &$file, int &$encpos, array &$bybit, int $bitned ) : int
{
	// get bits = 1 , 2 , 8
	// 76543210  76543210
	// 333-----  01133333
	while ( count($bybit) < $bitned )
	{
		$b = ord( $file[$encpos] );
			$encpos--;

		$i = 8;
		while ( $i > 0 )
		{
			$i--;
			$bybit[] = ($b >> $i) & 1;
		}
	} // while ( $bits > $bylen )

	$bits = 0;
	for ( $i=0; $i < $bitned; $i++ )
	{
		$bits <<= 1;
		$b = array_shift($bybit);
		$bits |= $b;
	}
	return $bits;
}

function crilay_len( string &$file, int &$encpos, array &$bybit ) : int
{
	$len = 0;
bit_2:
	$b = crilay_bits($file, $encpos, $bybit, 2);
	$len += $b;
	if ( $b !== 0x03 )
		goto done;
bit_3:
	$b = crilay_bits($file, $encpos, $bybit, 3);
	$len += $b;
	if ( $b !== 0x07 )
		goto done;
bit_5:
	$b = crilay_bits($file, $encpos, $bybit, 5);
	$len += $b;
	if ( $b !== 0x1f )
		goto done;
bit_8:
	$b = crilay_bits($file, $encpos, $bybit, 8);
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

	$data = tool::substr($file, 0x10, $size);
	$head = tool::substr($file, 0x10+$size, 0x100);

	$file = $head . str_repeat(ZERO, $len);
	$encpos = strlen($data) - 1;
	$decpos = strlen($file) - 1;

	$bybit = [];
	while (1)
	{
		if ( $encpos < 0 )
			return;
		if ( $decpos < 0x100 )
			return;

		$flg = crilay_bits($data, $encpos, $bybit, 1);
		if ( $flg ) // 1
		{
			$b = crilay_bits($data, $encpos, $bybit, 13);
			$dpos = $b + 3;

			$b = crilay_len($data, $encpos, $bybit);
			$dlen = $b + 3;

			for ( $i=0; $i < $dlen; $i++ )
			{
				$p = $decpos + $dpos;
				$file[$decpos] = $file[$p];
					$decpos--;
			} // for ( $i=0; $i < $dlen; $i++ )
		}
		else // 0
		{
			$b = crilay_bits($data, $encpos, $bybit, 8);
			$file[$decpos] = chr($b);
				$decpos--;
		}
	} // while (1)
}
//////////////////////////////
function sect_toc( string $type, string $dir, int $off, int $siz ) : void
{
	if ( $off === 0 || $siz === 0 )
		return;
	tool::trace(__FUNCTION__, $type, $dir, $off, $siz);

	$meta = fp2str($off+0x10, $siz-0x10);
	cpk_decrypt($meta);
	tool::save("$dir/$type.bin", $meta);

	$list = cpklist($meta, "$dir/$type");
	if ( empty($list) )
		return;
	//usort($list, 'sort_fileoffset');
	//print_r($list);

	$buf = '';
	foreach ( $list as $lv )
	{
		$pos = $lv['FileOffset'] + $off;
		$sz1 = $lv['FileSize'];
		$sz2 = $lv['ExtractSize'];
		$nam = sprintf('%s/%s', $lv['DirName'], $lv['FileName']);

		$log = sprintf("%8x , %8x , %s\n", $pos, $sz1, $nam);
		echo $log;
		$buf .= $log;

		$meta = fp2str($pos, $sz1);
		if ( $sz1 !== $sz2 )
		{
			//tool::save("$dir/$nam.enc", $meta);
			crilayla_decode($meta);
		}
		tool::save("$dir/$nam", $meta);
	} // foreach ( $list as $lv )
	tool::save("$dir/$type.txt", $buf);
}

function sect_cpk( string &$meta, string $dir ) : void
{
	tool::trace(__FUNCTION__, $dir);

	cpk_decrypt($meta);
	tool::save("$dir/CPK.bin", $meta);
	$list = cpklist($meta, "$dir/CPK");
	if ( empty($list) )
		return;
	//print_r($list);

	$ent = &$list[0];
	sect_toc('TOC' , $dir, $ent['TocOffset' ], $ent['TocSize' ]);
	sect_toc('ETOC', $dir, $ent['EtocOffset'], $ent['EtocSize']);
	sect_toc('ITOC', $dir, $ent['ItocOffset'], $ent['ItocSize']);
	sect_toc('GTOC', $dir, $ent['GtocOffset'], $ent['GtocSize']);
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

tool::argv_callback($argv, 'cpkfile');
