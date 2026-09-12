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
 *   CVMtool v0.02
 *   https://forum.xentax.com/viewtopic.php?p=67532
 *   https://amicitia.miraheze.org/wiki/CVM
 *     roxfan
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');
tool::require('func-bigint');

$gp_fp = 0;

function is_prime( int $n ) : bool
{
	if ( $n <  0 )
		$n = -$n;
	if ( $n === 1 )  return false;
	if ( $n === 3 )  return true;
	if ( ($n % 2) === 0 )  return false;
	if ( ($n % 3) === 0 )  return false;

	// odd numbers only , so +2 +4 +6...
	// +0=  5  11  17  23  29 -35  41  47  53  59 -65  71 -77  83  89 -95
	// +2=  7  13  19 -25  31  37  43 -49 -55  61  67  73  79 -85 -91  97
	// +4= -9 -15 -21 -27 -33 -39 -45 -51 -57 -63 -69 -75 -81 -87 -93 -99
	$i = 5;
	while ( ($i * $i) <= $n )
	{
		if ( ($n %  $i   ) === 0 )
			return false;
		if ( ($n % ($i+2)) === 0 )
			return false;
		// all +4 are not prime
		$i += 6;
	}
	return true;
}

function list_prime( int $len, int $st=0 ) : array
{
	if ( ($st & 1) === 0 ) // prime is always an odd number
		$st++;
	$res = [];
	while ( count($res) < $len )
	{
		if ( is_prime($st) )
			$res[] = $st;
		$st += 2;
	}
	return $res;
}

// 16411-26681 , 0x401b-0x6839
$gp_prime = list_prime(0x400, 16411);
//print_r($gp_prime);

function calchash( array $key, int $mask, int $val ) : int
{
	global $gp_prime;
	foreach ( $key as $kk => $kv )
	{
		$a = tool::sign($kv, 8) + 0x80;
		$a = $gp_prime[$a];
		$p = bigint::mult([$a] , [$val]);

		$a = (($p[1] << 8) | $p[0]) & 0x3ff;
		$val = $gp_prime[$a];
	}
	return ($val & $mask);
}
//////////////////////////////
function extra_hash( array $key ) : array
{
	$buf = [];
	for ( $i=0; $i < 8; $i += 2 )
	{
		$b  = array_splice($key, 0, 2);
		$hv = calchash($b, BIT16, 18973);
		$buf[] = $hv >> 8;
		$buf[] = $hv & BIT8;
	}
	return $buf;
}

function cvmkey( string $pass ) : array
{
	tool::trace(__FUNCTION__, $pass, bin2hex($pass));

	$len = strlen($pass);
	$sum = [0,0,0,0];
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $pass[$i] );
		$sum = bigint::mult([$b], bigint::add([$b], $sum));
		for ( $j=$i+1; $j < $len; $j++ )
		{
			$b = ord( $pass[$j] );
			$sum = bigint::add([$b], $sum);
		} // for ( $j=$i+1; $j < $len; $j++ )
	} // for ( $i=0; $i < $len; $i++ )

	$l = bigint::shift_left($sum, 20);
	$tmp = bigint::add($l, $sum);

	// le = 30 21 12 03
	// be = 03 12 21 30
	$key = [];
	for ( $i=0; $i < 4; $i++ )
	{
		$key[] = $tmp[3-$i];
		$key[] = $tmp[$i];
	}

	$hash = extra_hash($key);
	$hex  = '';
	foreach ( $hash as $v )
		$hex .= sprintf('%2x ', $v);
	tool::trace('hash', $hex);
	return $hash; // int8[8]
}
//////////////////////////////
function calclocalkey( array &$cvmkey, array &$hash, int $idx ) : array
{
	$scrambles = [
		'^03 . 0 ^37 . 4 . 1 ^26 . 2 ^15',  // 0
		'^12 . 7 . 5 ^23 ^00 . 6 . 4 ^31',  // 1
		'. 1 ^27 . 6 ^12 ^35 . 3 ^00 . 4',  // 2
		'+23 . 6 . 0 . 2 +04 +11 . 7 +35',  // 3
		'. 7 +30 +02 +16 . 4 . 3 . 5 +21',  // 4
		'. 2 +23 . 6 +07 . 0 +11 . 4 +35',  // 5
		'+03 . 7 ^12 . 6 . 1 ^25 . 0 +34',  // 6
		'. 7 ^34 . 3 +21 . 0 . 2 +15 ^06',  // 7
		'. 3 ^10 . 6 +04 ^32 . 7 . 1 +25',  // 8
	];

	$scr = $scrambles[$idx];
	$local = [];
	for ( $p=0; $p < 32; $p += 4 )
	{
		$sop = $scr[$p+0]; // oprerator
		$shs = $scr[$p+1]; // hash
		$sky = $scr[$p+2]; // cvmkey
		//$pad = $scr[$p+3]

		$skyv = $cvmkey[$sky];
		switch ( $sop )
		{
			case '^':
				$shsv = $hash[$shs];
				$local[] = $shsv ^ $skyv;
				break;
			case '+':
				$shsv = $hash[$shs];
				$local[] = ($shsv + $skyv) & BIT8;
				break;
			case '.':
				$local[] = $skyv;
				break;
		} // switch ( $sop )
	} // for ( $p=0; $p < 32; $p += 4 )

	$hex = '';
	foreach ( $local as $v )
		$hex .= sprintf('%2x ', $v);
	tool::trace('local', $hex);
	return $local; // int8[8]
}

function encrypt_sect( string &$sect, int $lba, array &$cvmkey ) : void
{
	tool::trace('== encrypt_sect', $lba);

	$len = strlen($sect);
	for ( $i=0; $i < $len; $i += 0x800 )
	{
		$seed = [ $cvmkey[5] ];

		for ( $j=0; $j < 0x800; $j += 8 )
		{
			$seed = bigint::mult($seed, [$lba]);
			$l    = bigint::shift_left($seed, 20);
			$buf  = bigint::add($seed, $l);

			$key = [ $buf[3] , $buf[2] , $buf[1] , $buf[0] ];
			$hv1 = calchash($key, BIT32, 18973);
			$hv2 = calchash($key, BIT32, 21503);
			$hv3 = calchash($key, BIT32, 24001);

			$idx = $hv1 % 9;
			$hash = [
				$hv2 >> 8 , $hv2 & BIT8 ,
				$hv3 >> 8 , $hv3 & BIT8 ,
			];

			$local = calclocalkey($cvmkey, $hash, $idx);
			$seed  = [$idx + $j];
			for ( $k=0; $k < 8; $k++ )
			{
				$sv = ord( $sect[$i+$j+$k] );
				$lv = $local[$k];

				$xor = $sv ^ $lv;
				$sect[$i+$j+$k] = chr($xor);

				$seed = bigint::mult([$lv], $seed);
			} // for ( $k=0; $k < 8; $k++ )
		} // for ( $j=0; $j < 0x800; $j++ )

		$lba++;
	} // for ( $i=0; $i < $len; $i += 0x800 )
}
//////////////////////////////
$gp_fp = 0;

// mixed $val
function cvmh_sect( int $lba, $val ) : string
{
	global $gp_fp;
	fseek($gp_fp, $lba * 0x800, SEEK_SET);

	// int = size READ
	if ( (int)$val === $val )
	{
		$bin = fread($gp_fp, $val);
		if ( strlen($bin) !== $val )
			tool::error(__FUNCTION__, strlen($bin), $val);
		return $bin;
	}

	// string = data WRITE
	if ( "$val" === $val )
	{
		fwrite($gp_fp, $val);
		return '';
	}

	return '';
}

function cvmh_header( string $head='' ) : string
{
	global $gp_fp;
	fseek($gp_fp, 0, SEEK_SET);

	if ( empty($head) )
		return fread($gp_fp, 0x1000);
	else
	{
		fwrite($gp_fp, $head);
		return '';
	}
}

function isoloopdir( int $isost, int $lba, int $siz, bool $enc, array &$cvmkey ) : void
{
	$sect = cvmh_sect($isost + $lba, $siz);
	if ( $enc )
		encrypt_sect($sect, $lba, $cvmkey);

	$func = __FUNCTION__;
	for ( $i=0; $i < $siz; $i += 0x800 )
	{
		$j = 0;
		while ( $j < 0x800 )
		{
			$esz = ord( $sect[$i+$j+0] );
			if ( $esz === 0 )
			{
				$j += 0x800;
				continue;
			}

			$flg = ord( $sect[$i+$j+0x19] );
			$len = ord( $sect[$i+$j+0x20] );
			if ( $len == 1 || ($flg & 2) == 0 )
			{
				$j += $esz;
				continue;
			}

			$elba = tool::ordstr($sect, $i+$j+ 2, 4);
			$esiz = tool::ordstr($sect, $i+$j+10, 4);
			$func($isost, $elba, $esiz, $enc, $cvmkey);
		} // while ( $j < 0x800 )
	} // for ( $i=0; $i < $siz; $i += 0x800 )

	if ( ! $enc )
		encrypt_sect($sect, $lba, $cvmkey);

	cvmh_sect($isost + $lba, $sect);
}

function ps2cvm( array &$cvmkey, string $fname ) : void
{
	global $gp_fp;
	$gp_fp = fopen($fname, 'rb+');
	if ( ! $gp_fp )  return;

	$head = cvmh_header();
	if ( substr($head,0,4) !== 'CVMH' )
		return;

	$h33 = ord( $head[0x33] );
	$enc = ( $h33 & 0x10 ) ? true : false;
	$isost = ieee754::ordstr($head, 0x88, 4);

	// if encrypted , decrypt it first to loop all dirs
	// if decrypted , loop all dirs to encrypt it back
	$root = cvmh_sect($isost + 16, 0x800);
	if ( $enc )
		encrypt_sect($root, 16, $cvmkey);
	$lba = tool::ordstr($root, 0x9e, 4);
	$siz = tool::ordstr($root, 0xa6, 4);
	isoloopdir($isost, $lba, $siz, $enc, $cvmkey);

	if ( ! $enc )
		encrypt_sect($root, 16, $cvmkey);
	cvmh_sect($isost + 16, $root);

	$head[0x33] = chr( $h33 ^ 0x10 );
	cvmh_header($head);
}
//////////////////////////////
printf("%s  [KEY]  CVMFILE\n", $argv[0]);

$cvmkey = cvmkey('shinobutan');
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file( $argv[$i] ) )
		ps2cvm( $cvmkey, $argv[$i] );
	else
		$cvmkey = cvmkey( $argv[$i] );
}

/*
shinobutan
	=  73 68 69 6e 6f 62 75 74 61 6e
	=> 45 e3 65 53 52 c3 4c 15
cc2fuku
	=  63 63 32 66 75 6b 75
	=> 5b d5 58 ab 55 3d 41 bd
*/
