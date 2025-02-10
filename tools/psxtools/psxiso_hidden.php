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
require 'common.inc';

//define('DRY_RUN', true);

function chrbase10( $chr )
{
	$b = ord( $chr );
	$b1 = ($b >> 0) & BIT4;
	$b2 = ($b >> 4) & BIT4;
	return ($b2 * 10) + $b1;
}

function cdpos2int( $min , $sec , $frame )
{
	$m = chrbase10($min);
	$s = chrbase10($sec);
	$f = chrbase10($frame);

	$s -= 2;
	$s += ($m * 60);
	$f += ($s * 75);
	if ( $f < 0 )
		return 0;
	return $f * 0x800;
}



function file_ext( &$str )
{
	$len = strlen($str);
	if ( $len < 4 )
		return '0';

	$mgc = bin2hex( substr($str,0,4) );
	switch ( $mgc )
	{
		// ascii magic
		case '50532d58':  return 'psx';
		case '424f4f54':  return 'boot';

		case '414b414f':  return 'akao';
		case '77647320':  return 'wds';
		case '73656473':  return 'seds';
		case '736d6473':  return 'smds';
		case '70424156':  return 'pbav';
		case '70514553':  return 'pqes';
		case '64727000':  return 'drp';

		// known magic
		case '60010180':  return 'str';
		case '10000000':
			$b1 = str2int($str, 4, 4);
			$b2 = str2int($str, 8, 4);
			if ( $b1 === 9 && $b2 === 0x20c )
				return 'tim9';
			if ( $b1 === 8 && $b2 === 0x2c )
				return 'tim8';
			break;

		// dummies
		case '64756d6d': // dummy
		case '49742773': // It's CDMAKE Dummy
			$mgc = substr($str, 0, 0x18);
			if ( stripos($mgc, 'dummy') !== false )
				return 'dummy';
			break;
	} // switch ( $mgc )

	# no match
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $str[$i] !== ZERO )
			return 'bin';
	}
	return '0';
}
//////////////////////////////
function valkyrie_decrypt( &$str, &$dic, $key )
{
	printf("valkyrie TOC key : %8x\n", $key);
	$toc = '';
	$ed = strlen($dic);
	$st = 0;
	$k = $key;
	while ( $st < $ed )
	{
		$w = str2int($str, $st*4, 4);
		$b = ord( $dic[$st] );
			$st++;

		$w ^= $k;
		$k ^= ($k << 1);
		$toc .= chrint($w,4);

		$b ^= $k;
		$k  = ~($k) ^ $key;
	}
	return $toc;
}
function valkyrie_toc( $fp, $dir, &$toc )
{
	$list = array();
	$ed = strlen($toc);
	$st = 4;
	while ( $st < $ed )
	{
		$no = $st / 4;
		$lba = cdpos2int($toc[$st+0], $toc[$st+1], $toc[$st+2]);
			$st += 4;

		if ( $lba != 0 )
			$list[] = array($no, $lba);
	}

	$txt = '';
	$ed = count($list);
	$st = 0;
	while ( $st < $ed )
	{
		list($no,$lba) = $list[$st];
		$txt .= sprintf("%4x , %8x\n", $no, $lba);

		if ( isset( $list[$st+1] ) )
			$sz = $list[$st+1][1] - $lba;
		else
		{
			fseek($fp, 0, SEEK_END);
			$sz = ftell($fp) - $lba;
		}
		$sub = fp2str($fp, $lba, $sz);

		$ext = file_ext($str);
		$fn  = sprintf('%s/%06d.%s', $dir, $no, $ext);

		if ( $ext === 'str' )
			$sub = ZERO;
		save_file($fn, $sub);
		$st++;
	}

	echo "$txt\n";
	save_file("$dir/toc.bin", $toc);
	save_file("$dir/toc.txt", $txt);
	return;
}
//////////////////////////////
function iso_valkyrie( $fp, $dir )
{
	printf("%s [%s]\n", $dir, __FUNCTION__);

	// sub_80011d38 , SLPM_863.79
	$str = fp2str($fp, 0x4b000, 0x5000);
	$dic = fp2str($fp, 0x50000, 0x1400);
	$key = 0x64283921;

	$toc = valkyrie_decrypt( $str, $dic, $key );
	valkyrie_toc($fp, $dir, $toc);
	return;
}

function iso_starocean2nd1( $fp, $dir )
{
	printf("%s [%s]\n", $dir, __FUNCTION__);

	// sub_80011c20 , SLPM_861.05
	$str = fp2str($fp, 0x96000, 0x4800);
	$dic = fp2str($fp, 0x9a800, 0x1200);
	$key = 0x13578642;

	$toc = valkyrie_decrypt( $str, $dic, $key );
	valkyrie_toc($fp, $dir, $toc);
	return;
}
function iso_starocean2nd2( $fp, $dir )  { return iso_starocean2nd1($fp, $dir); }

function iso_xenogears( $fp, $dir )
{
	printf("%s [%s]\n", $dir, __FUNCTION__);
	$str = fp2str($fp, 0xc000, 0x8000);

	$txt = '';
	$dn = '';
	for ( $i=0; $i < 0x8000; $i += 7 )
	{
		$no = $i / 7;
		$lba = str2int($str, $i+0, 3, true);
		$siz = str2int($str, $i+3, 4, true);

		if ( $lba == -1 )
			break;
		if ( $lba == 0 || $siz == 0 )
			continue;

		if ( $siz < 0 )
		{
			$dn = $no;
			$siz *= -1;
			$txt .= sprintf("%8x , DIR  , %8x , %s\n", $lba*0x800, $siz, $dn);
		}
		else
		{
			$sub = fp2str($fp, $lba*0x800, $siz);

			$ext = file_ext($str);
			$fn  = sprintf('%s/%06d.%s', $dn, $no, $ext);
			$txt .= sprintf("%8x , FILE , %8x , %s\n", $lba*0x800, $siz, $fn);

			if ( $ext === 'str' )
				$sub = ZERO;
			save_file("$dir/$fn", $sub);
		}
	} // for ( $i=0; $i < 0x8000; $i += 7 )

	echo "$txt\n";
	save_file("$dir/toc.bin", $str);
	save_file("$dir/toc.txt", $txt);
	return;
}

function iso_chronocross( $fp, $dir )
{
	printf("%s [%s]\n", $dir, __FUNCTION__);
	$str = fp2str($fp, 0xc000, 0x6800);

	$txt = '';
	for ( $i=0; $i < 0x67fc; $i += 4 )
	{
		$pos = str2int($str, $i, 3);
		if ( $pos === 0 )
			goto done;
		if ( $pos & 0x800000 )
			continue;

		$nxt = str2int($str, $i+4, 3);
		$nxt &= (~0x800000);

		$siz = $nxt - $pos;
		$sub = fp2str($fp, $pos*0x800, $siz*0x800);

		$ext = file_ext($sub);
		$fn  = sprintf('%s/%06d.%s', $ext, $i >> 2, $ext);
		$txt .= sprintf("%8x , FILE , %8x , %s\n", $pos*0x800, $siz*0x800, $fn);

		if ( $ext === 'str' )
			$sub = ZERO;
		save_file("$dir/$fn", $sub);
	} // for ( $i=0; $i < 0x67fc; $i += 4 )

done:
	echo "$txt\n";
	save_file("$dir/toc.bin", $str);
	save_file("$dir/toc.txt", $txt);
	return;
}

function iso_dewprism( $fp, $dir )
{
	printf("%s [%s]\n", $dir, __FUNCTION__);
	$str = fp2str($fp, 0xc000, 0x4cd8);

	$txt = '';
	$ed = strlen($str) - 4;
	$st = 0;
	$dn = '';
	while ( $st < $ed )
	{
		$no = $st / 4;
		$lba1 = str2int($str, $st+0, 3) & 0x7fffff;
		$lba2 = str2int($str, $st+4, 3) & 0x7fffff;
			$st += 4;

		$sz = $lba2 - $lba1;
		if ( $sz > 0 )
		{
			$sub = fp2str($fp, $lba1*0x800, $sz*0x800);

			$ext = file_ext($str);
			$fn  = sprintf('%s/%06d.%s', $dn, $no, $ext);
			$txt .= sprintf("%8x , FILE , %8x , %s\n", $lba1*0x800, $sz*0x800, $fn);

			if ( $ext === 'str' )
				$sub = ZERO;
			save_file("$dir/$fn", $sub);
		}
		else
		{
			$dn = $no;
			$txt .= sprintf("%8x , DIR  , %8x , %s\n", $lba1*0x800, 0, $dn);
		}
	} // while ( $st < $ed )

	echo "$txt\n";
	save_file("$dir/toc.bin", $str);
	save_file("$dir/toc.txt", $txt);
	return;
}
//////////////////////////////
function isofile( $fname )
{
	$fp = fopen_file($fname);
	if ( ! $fp )  return;

	$root = fp2str($fp, 0x8000, 0x800);
	if ( substr($root, 1, 5) !== 'CD001' )
		return printf("%s is not an ISO 2048/sector file\n", $fname);

	$dir = str_replace('.', '_', $fname);

	$mgc = substr($root, 0x28, 0x20);
	$mgc = strtolower( trim($mgc, ' '.ZERO) );

	$func = "iso_" . $mgc;
	if ( ! function_exists($func) )
		return printf("%s [%s] is not supported (yet)\n", $fname, $func);

	$func($fp, $dir);
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isofile( $argv[$i] );
