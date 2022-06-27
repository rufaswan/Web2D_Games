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
define('SHA1FILE', '83098573fa7479044c11180de28f03402ed8996a');

// sub_80054d54+10
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerge_  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $v0, 3 // 1000
// addu  $a3, $v0    // 1001
// sll   $a3, 2      // 100100
// subu  $a3, $v0    // 100011
// sll   $a3, 2      // 10001100
// addiu $a3, 0x6BF  // 6bf + 8c (35f800 + 46000)

// sub_800577bc+40
// la   $a1, aDataMrgmMerg_0  # "data/mrgM/merge.mrg"
// la   $a2, word_8016E000
// move $a3, $0

// sub_8005934c+68
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 1 // 10
// addu  $a3, $t0    // 11
// sll   $a3, 2      // 1100
// subu  $a3, $t0    // 1011
// sll   $a3, 3      // 1011000
// subu  $a3, $t0    // 1010111
// sll   $a3, 1      // 10101110
// addiu $a3, 0x1FEE // 1fee + ae (ff7000 + 57000)

// sub_8005934c+c0
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 1 // 10
// addu  $a3, $t0    // 11
// sll   $a3, 2      // 1100
// subu  $a3, $t0    // 1011
// sll   $a3, 3      // 1011000
// subu  $a3, $t0    // 1010111
// sll   $a3, 1      // 10101110
// addiu $a3, 0x1FBE // 1fbe + ae (fdf000 + 57000)

// sub_8005934c+118
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 1 // 10
// addu  $a3, $t0    // 11
// sll   $a3, 2      // 1100
// subu  $a3, $t0    // 1011
// sll   $a3, 3      // 1011000
// subu  $a3, $t0    // 1010111
// sll   $a3, 1      // 10101110
// addiu $a3, 0x1FD6 // 1fd6 + ae (feb000 + 57000)

// sub_8005934c+1a0
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 1
// addu  $a3, $t0
// sll   $a3, 2
// subu  $a3, $t0
// sll   $a3, 3
// subu  $a3, $t0
// sll   $a3, 1
// addiu $a3, 0x1FEE
//// DUP

// sub_8005934c+23c
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 1
// addu  $a3, $t0
// sll   $a3, 2
// subu  $a3, $t0
// sll   $a3, 3
// subu  $a3, $t0
// sll   $a3, 1
// addiu $a3, 0x1FEE
//// DUP

// sub_800596bc+7c
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 2 // 100
// addu  $a3, $t0    // 101
// sll   $a3, 4      // 1010000
// addu  $a3, $t0    // 1010001
// addiu $a3, 0x2325 // 2325 + 51 (1192800 + 28800)

// sub_800596bc+ac
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 2 // 100
// addu  $a3, $t0    // 101
// sll   $a3, 4      // 1010000
// addu  $a3, $t0    // 1010001
// addiu $a3, 0x2311 // 2311 + 51 (1188800 + 28800)

// sub_800596bc+f0
// li    $a0, 0x2000000A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 2 // 100
// addu  $a3, $t0    // 101
// sll   $a3, 4      // 1010000
// addu  $a3, $t0    // 1010001
// addiu $a3, 0x231B // 231b + 51 (118d800 + 28800)

// sub_800596bc+168
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 2
// addu  $a3, $t0
// sll   $a3, 4
// addu  $a3, $t0
// addiu $a3, 0x2325
//// DUP

// sub_800596bc+1c0
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $t0, 2
// addu  $a3, $t0
// sll   $a3, 4
// addu  $a3, $t0
// addiu $a3, 0x2311
//// DUP

// sub_80059930+194
// li   $a0, 0x20000002
// la   $s0, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// move $a1, $s0
// lw   $a2, dword_800A7DFC
// li   $a3, 0x1123
//// DUP

// sub_80059930+2d8
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s4, 1 // 10
// addu  $a3, $s4    // 11
// sll   $a3, 3      // 11000
// addu  $a3, $s4    // 11001
// addiu $a3, 0x40CF // 40cf + 19 (2067800 + c800)

// sub_80059930+380
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s1, 1 // 10
// addu  $a3, $s1    // 11
// sll   $a3, 2      // 1100
// addu  $a3, $s1    // 1101
// sll   $a3, 1      // 11010
// addiu $a3, 0x42F5 // 42f5 + 1a (217a800 + d000)

// sub_80059930+420
// li    $a0, 0x20000042
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// sll   $a3, $s0, 5 // 100000
// subu  $a3, $s0    // 011111
// sll   $a3, 2      // 01111100
// addiu $a3, 0x114D // 114d + 7c (8a6800 + 3e000)

// sub_80059930+488
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s1, 1 // 10
// addu  $a3, $s1    // 11
// sll   $a3, 2      // 1100
// addu  $a3, $s1    // 1101
// sll   $a3, 1      // 11010
// addiu $a3, 0x42FE // 42fe + 1a (217f000 + d000)

// sub_80059930+510
// li    $a0, 0x20000042
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, byte_801DC000
// sll   $a3, $s0, 5 // 100000
// subu  $a3, $s0    // 011111
// sll   $a3, 2      // 01111100
// addiu $a3, 0x1123 // 1123 + 7c (891800 + 3e000)

// sub_80059930+570
// li    $a0, 0x20000042
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, byte_801DC000
// sll   $a3, $s0, 5
// subu  $a3, $s0
// sll   $a3, 2
// addiu $a3, 0x1123
//// DUP

// sub_80059930+5d8
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s3, 2 // 100
// addu  $a3, $s3    // 101
// sll   $a3, 4      // 1010000
// addu  $a3, $s3    // 1010001
// addiu $a3, 0x2331 // 2331 + 51 (1198800 + 28800)

// sub_80059930+6e0
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s4, 1
// addu  $a3, $s4
// sll   $a3, 3
// addu  $a3, $s4
// addiu $a3, 0x40CF
//// DUP

// sub_80059930+758
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s1, 1
// addu  $a3, $s1
// sll   $a3, 2
// addu  $a3, $s1
// sll   $a3, 1
// addiu $a3, 0x42F5
//// DUP

// sub_8005a458+6c
// li    $a0, 0x2000004A
// lui   $a1, 0x8001
// lui   $a2, 0x8017
// sll   $a3, $t0, 4 // 10000
// addu  $a3, $t0    // 10001
// sll   $a3, 1      // 100010
// la    $a1, aDataMrgmMerg_1  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// addiu $a3, 0x4E3  // 4e3 + 22 (271800 + 11000)

// sub_8006393c+1fc
// li    $a0, 0x2000004A
// la    $a1, aDataMrgmMerg_2  # "data/mrgM/merge.mrg"
// la    $a2, word_8016E000
// sll   $a3, $s2, 1 // 10
// addu  $a3, $s2    // 11
// sll   $a3, 4      // 110000
// addu  $a3, $s2    // 110001
// addiu $a3, 0xAD   // ad + 31 (56800 + 18800)

// sub_8006425c+3c
// li  $a0, 0x2000004A
// lui $a1, 0x8001
// lui $a2, 0x8017
// la  $a1, aDataMrgmMerg_2  # "data/mrgM/merge.mrg"
// la  $a2, word_8016E000
// li  $a3, 0x85 // 42800

// sub_800577bc+40        0/0         -/-
// sub_8006425c+3c       85/42800     -/-
// ad/56800 + 16 * 31/18800
//   sub_8006393c+1fc    ad/56800    31/18800  []
// 4e3/271800 + e * 22/11000
//   sub_8005a458+6c    4e3/271800   22/11000  [talk ] npc
// 6bf/35f800 + 13 * 8c/46000
//   sub_80054d54+10    6bf/35f800   8c/46000  []
// 1123/891800 + 1e * 7c/3e000
//   sub_80059930+194  1123/891800   7c/3e000  [dress] meta + bg*6
//   sub_80059930+510  1123/891800   7c/3e000
//   sub_80059930+570  1123/891800   7c/3e000
//   sub_80059930+420  114d/8a6800   7c/3e000  [dress] weapon*4 + girl
// 1fab/fd5800 + 5 * ae/5700
//   sub_8005934c+c0   1fbe/fdf000   ae/57000  [talk ] girl/side
//   sub_8005934c+118  1fd6/feb000   ae/57000  [talk ] girl/back
//   sub_8005934c+68   1fee/ff7000   ae/57000  [talk ] girl/front + [map] sprite
//   sub_8005934c+1a0  1fee/ff7000   ae/57000
//   sub_8005934c+23c  1fee/ff7000   ae/57000
// 2311/1188800 + 5e * 51/28800
//   sub_800596bc+ac   2311/1188800  51/28800  [talk ] dress/side
//   sub_800596bc+1c0  2311/1188800  51/28800
//   sub_800596bc+f0   231b/118d800  51/28800  [talk ] dress/back
//   sub_800596bc+7c   2325/1192800  51/28800  [talk ] dress/front
//   sub_800596bc+168  2325/1192800  51/28800
//   sub_80059930+5d8  2331/1198800  51/28800  [dress] fullset/upper
// 40cf/2067800 + 16 * 19/c800
//   sub_80059930+2d8  40cf/2067800  19/c800   [dress] lower
//   sub_80059930+6e0  40cf/2067800  19/c800
// 42f5/217a800 + 138 * 1a/d000
//   sub_80059930+380  42f5/217a800  1a/d000   [dress] shoes
//   sub_80059930+758  42f5/217a800  1a/d000
//   sub_80059930+488  42fe/217f000  1a/d000   [dress] shield
//////////////////////////////
function merge_src( $fn, &$src, &$pal )
{
	$img = array(
		'w'   => 0x80,
		'h'   => strlen($src) / 0x80,
		'cc'  => strlen($pal) / 4,
		'pal' => $pal,
		'pix' => $src,
	);
	save_clutfile($fn, $img);
	return;
}
//////////////////////////////
function xywh1123( &$meta, $pos )
{
	$cnt = str2int($meta, $pos+0, 1);
	$b0  = str2int($meta, $pos+1, 3);
		$pos += 4;
	printf("== xywh1123( %x ) = %x , %06x\n", $pos-4, $cnt, $b0);
	if ( $cnt === 0 )
		return;

	$data = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$b0  = str2int($meta, $pos+0, 1);
		$b1  = str2int($meta, $pos+1, 1);
		$b23 = str2int($meta, $pos+2, 2);
		$b45 = str2int($meta, $pos+4, 2);
			$pos += 6;

		// fedc ba98 7654 3210
		// 44xf ffff fffe eeee
		$sx =  ($b23 >>  0) & 0x1f; // sx
		$sy =  ($b23 >>  5) & 0xff; // sy
		$dx = (($b23 >> 14) << 8) | $b0; // dx
		$fx =  ($b23 >> 13) & 1; // horizontal flip

		// fedc ba98 7654 3210
		// 66-a aaa8 888- ----
		$w  =  ($b45 >>  5) & 0x0f; // width
		$h  =  ($b45 >>  9) & 0x0f; // height
		$dy = (($b45 >> 14) << 8) | $b1; // dy

		$xywh = array(
			'dx' => sint_bit($dx, 2+8),
			'dy' => sint_bit($dy, 2+8),
			'w'  => ($w + 1) * 8,
			'h'  => ($h + 1) * 8,
			'sx' => $sx * 8,
			'sy' => $sy * 8,
			'fx' => $fx,
		);

		trace('  %3x , %3x , %3x , %3x , %3x , %3x', $dx, $dy, $xywh['w'], $xywh['h'], $xywh['sx'], $xywh['sy']);
		$t = sprintf(' , %04x  %04x', $b23 & 0x2000, $b45 & 0x201f);
		$t = str_replace('0', '-', $t);
		trace("$t\n");

		$data[] = $xywh;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $data;
}

function data1123( &$meta, $st )
{
	$data = array();
	while (1)
	{
		if ( $meta[$st] === ZERO )
			break;

		$b0 = str2int($meta, $st+0, 2);
			$st += 2;
		$b1 = str2int($meta, $b0+1, 2);
		$data[] = xywh1123($meta, $b1);
	} // while (1)
	return $data;
}

function save1123( $fn, &$data, &$src, $sw, $sh, &$pal, $bgzero )
{
	printf("== save1123( %s , %x , %x )\n", $fn, $sw, $sh);

	$is_mid = false;
	$ceil = 0;
	foreach ( $data as $v )
	{
		if ( $v['dx'] < 0 )  $is_mid = true;
		if ( $v['dy'] < 0 )  $is_mid = true;
		if ( abs($v['dx']) > $ceil )  $ceil = abs($v['dx']);
		if ( abs($v['dy']) > $ceil )  $ceil = abs($v['dy']);
		if ( abs($v['dx']+$v['w']) > $ceil )  $ceil = abs($v['dx']+$v['w']);
		if ( abs($v['dy']+$v['h']) > $ceil )  $ceil = abs($v['dy']+$v['h']);
	} // foreach ( $data as $v )

	$ceil = int_ceil($ceil+1, 2);
	$half = 0;

	if ( $is_mid )
	{
		$half = $ceil;
		$ceil *= 2;
	}

	$pix  = COPYPIX_DEF($ceil, $ceil);
	printf("canvas = %d , half = %d\n", $ceil, $half);
	$pix['bgzero'] = ( $bgzero ) ? 0 : -1;

	foreach ( $data as $v )
	{
		$pix['dx'] = $half + $v['dx'];
		$pix['dy'] = $half + $v['dy'];

		$pix['src']['w'] = $v['w'];
		$pix['src']['h'] = $v['h'];

		$pix['hflip'] = $v['fx'];

		$pix['src']['pal'] = $pal;
		$pix['src']['pix'] = rippix8($src, $v['sx'], $v['sy'], $v['w'], $v['h'], $sw, $sh);

		printf("  %5d , %5d , %5d , %5d , %5d , %5d\n", $pix['dx'], $pix['dy'], $v['w'], $v['h'], $v['sx'], $v['sy']);
		copypix($pix, 1);
	} // foreach ( $data as $v )

	savepix($fn, $pix);
	return;
}
//////////////////////////////
function merge_1123_bg( &$meta, &$file, $dir )
{
	$off = str2int($meta, 0, 2);
	printf("== merge_1123_bg( %s ) = %x\n", $dir, $off);

	// 5 girls * 6 patterns
	$pix_off = array(0x4000,0x6000,0x8000,0xc000,0xe000,0x10000);
	for ( $i=0; $i < 5; $i++ )
	{
		$pos = (0x1123 + ($i * 6 * 0x7c)) * 0x800;
		foreach ( $pix_off as $pk => $pv )
		{
			$p1 = str2int($meta, $off, 2);
				$off += 2;

			$pal_off = $pos + 0x14000 + ($pk * 0x200);
			$src = substr($file, $pos+$pv, 0x2000);
			$pal = substr($file, $pal_off, 0x200);
				$pal = pal555($pal);

			$fn = sprintf('%s/%04d-%d-src.clut', $dir, $i, $pk);
			merge_src($fn, $src, $pal);

			$data = data1123($meta, $p1);
			foreach ( $data as $dk => $dv )
			{
				$fn = sprintf('%s/%04d-%d-%d', $dir, $i, $pk, $dk);
				save1123($fn, $dv, $src, 0x80, 0x40, $pal, false);
			} // foreach ( $data as $dk => $dv )
		} // foreach ( $pix_off as $pk => $pv )
	} // for ( $i=0; $i < 5; $i++ )
	return;
}

function merge_1123_upper( &$meta, &$file, $dir )
{
	$off = str2int($meta, 2, 2);
	printf("== merge_1123_upper( %s ) = %x\n", $dir, $off);

	for ( $i=0; $i < 94; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0x2331 + ($i * 0x51)) * 0x800;
		$src = substr($file, $pos        , 0x18000);
		$pal = substr($file, $pos+0x18000, 0x200);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		merge_src($fn, $src, $pal);

		$data = data1123($meta, $p1);
		foreach ( $data as $dk => $dv )
		{
			foreach( $dv as $k => $v )
			{
				$f1 = $dv[$k]['sx'] >> 7;
				$f2 = $dv[$k]['sy'] >> 7;
				$dv[$k]['sx'] &= 0x7f;
				$dv[$k]['sy'] &= 0x7f;

				$flg = ($f1 << 4) | $f2;
				switch ( $flg )
				{
					case 0x00: break;
					case 0x01: $dv[$k]['sy'] += 0x80 ; break;
					case 0x10: $dv[$k]['sy'] += 0x100; break;
					case 0x11: $dv[$k]['sy'] += 0x180; break;
					case 0x02: $dv[$k]['sy'] += 0x200; break;
					case 0x03: $dv[$k]['sy'] += 0x280; break;
					default: // not exists
						php_error('sx sy flg = %x', $flg);
						break;
				} // switch ( $flg )
			} // foreach( $dv as $k => $v )

			$fn = sprintf('%s/%04d-%d', $dir, $i, $dk);
			save1123($fn, $dv, $src, 0x80, 0x300, $pal, true);
		} // foreach ( $data as $dk => $dv )
	} // for ( $i=0; $i < 94; $i++ )
	return;
}

function merge_1123_lower( &$meta, &$file, $dir )
{
	$off = str2int($meta, 4, 2);
	printf("== merge_1123_lower( %s ) = %x\n", $dir, $off);

	for ( $i=0; $i < 22; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0x40cf + ($i * 0x19)) * 0x800;
		$src = substr($file, $pos       , 0xc000);
		$pal = substr($file, $pos+0xc000, 0x200);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		merge_src($fn, $src, $pal);

		$data = data1123($meta, $p1);
		foreach( $data as $dk => $dv )
		{
			foreach( $dv as $k => $v )
			{
				$f1 = $dv[$k]['sx'] >> 7;
				$f2 = $dv[$k]['sy'] >> 7;
				$dv[$k]['sx'] &= 0x7f;
				$dv[$k]['sy'] &= 0x7f;

				$flg = ($f1 << 4) | $f2;
				switch ( $flg )
				{
					case 0x01: break;
					case 0x10: $dv[$k]['sy'] += 0x80; break;
					default: // not exists
						php_error('sx sy flg = %x', $flg);
						break;
				} // switch ( $flg )
			} // foreach( $dv as $k => $v )

			$fn = sprintf('%s/%04d-%d', $dir, $i, $dk);
			save1123($fn, $dv, $src, 0x80, 0x180, $pal, true);
		} // foreach( $data as $dk => $dv )
	} // for ( $i=0; $i < 22; $i++ )
	return;
}

function merge_1123_shoes( &$meta, &$file, $dir )
{
	$off = str2int($meta, 6, 2);
	printf("== merge_1123_shoes( %s ) = %x\n", $dir, $off);

	// 26 shoes * 12 shield set
	for ( $i=0; $i < 26; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0x42f5 + ($i * 12 * 0x1a)) * 0x800;
		$src = substr($file, $pos       , 0x4000);
		$pal = substr($file, $pos+0x4000, 0x200);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		merge_src($fn, $src, $pal);

		$data = data1123($meta, $p1);
		foreach ( $data as $dk => $dv )
		{
			foreach( $dv as $k => $v )
			{
				$f1 = $dv[$k]['sx'] >> 7;
				$f2 = $dv[$k]['sy'] >> 7;
				$dv[$k]['sx'] &= 0x7f;
				$dv[$k]['sy'] &= 0x7f;

				$flg = ($f1 << 4) | $f2;
				switch ( $flg )
				{
					case 0x01: break;
					default: // not exists
						php_error('sx sy flg = %x', $flg);
						break;
				} // switch ( $flg )
			} // foreach( $dv as $k => $v )

			$fn = sprintf('%s/%04d-%d', $dir, $i, $dk);
			save1123($fn, $dv, $src, 0x80, 0x80, $pal, true);
		} // foreach ( $data as $dk => $dv )
	} // for ( $i=0; $i < 27; $i++ )
	return;
}

function merge_1123_shield( &$meta, &$file, $dir )
{
	$off = str2int($meta, 8, 2);
	printf("== merge_1123_shield( %s ) = %x\n", $dir, $off);

	// 26 shoes * 12 shield set
	for ( $i=0; $i < 12; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0x42fe + ($i * 0x1a)) * 0x800;
		$src = substr($file, $pos       , 0x6000);
		$pal = substr($file, $pos+0x8000, 0x200);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		merge_src($fn, $src, $pal);

		$data = data1123($meta, $p1);
		foreach ( $data as $dk => $dv )
		{
			$fn = sprintf('%s/%04d-%d', $dir, $i, $dk);
			save1123($fn, $dv, $src, 0x80, 0xc0, $pal, true);
		} // foreach ( $data as $dk => $dv )
	} // for ( $i=0; $i < 12; $i++ )
	return;
}

function merge_1123_weapon( &$meta, &$file, $dir )
{
	$off = str2int($meta, 10, 2);
	printf("== merge_1123_weapon( %s ) = %x\n", $dir, $off);

	for ( $i=0; $i < 30; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0x114d + ($i * 0x7c)) * 0x800;

		$data = data1123($meta, $p1);
		foreach ( $data as $dk => $dv )
		{
			// has 4 glove pairs
			for ( $j=0; $j < 4; $j++ )
			{
				$p1  = $pos + ($j * 0x4000);
				$p2  = $pos + 0x10000 + ($j * 0x200);
				$src = substr($file, $p1, 0x4000);
				$pal = substr($file, $p2, 0x200);
					$pal = pal555($pal);

				$fn = sprintf('%s/%04d-%d-%d-src.clut', $dir, $i, $dk, $j);
				merge_src($fn, $src, $pal);

				$fn = sprintf('%s/%04d-%d-%d', $dir, $i, $dk, $j);
				save1123($fn, $dv, $src, 0x80, 0x80, $pal, true);
			} // for ( $j=0; $j < 4; $j++ )
		} // foreach ( $data as $dk => $dv )
	} // for ( $i=0; $i < 30; $i++ )
	return;
}

function merge_1123( &$file, $dir )
{
	// all 30 meta files are the same
	//
	// 0/14=bg  8005f6dc
	//   t0 = lbu  50(a2[800bc328])
	//   v0 = lbu   0(a1[801dc000])
	//   v1 = lbu   1(a1[801dc000])
	//   v0 = v0 + (t0 << 1) + (v1 << 8) + 801dc000
	//
	// 2/50=upper/lower/shoe  80018e44
	//   v1 = 801dc000 + (v1 << 1)
	//   v0 = lbu   1(v1)
	//   v1 = lbu   0(v1)
	//   v0 = 801dc000 + (v0 << 8) | v1
	//   v1 = lbu  56(a3[800bc6d0])
	//   v0 = v0 + (v1 << 1)
	//
	// 4/10c=upper/lower/shoe  80018e44
	// 6/138=upper/lower/shoe  80018e44
	// 8/16e=shield  80018e44
	// a/186=weapon  80018e44
	//
	// c/1c2=talk  8005ddac
	// e/1cc=talk  8005ddac
	//10/288=talk  8005ddac
	//12/2b4=talk  8005ddac
	//   v0 = sp + a1
	//   a0 = lw   18(v0)
	//   v1 = lbu   1(v0[801dc00e])
	//   v0 = lbu   0(v0[801dc00e])
	//   v0 = v0 + (a0 << 1) + 801dc000 + (v1 << 8)
	//
	// end/3e62
	// -> RAM 801dc000
	$meta = substr($file, 0x1123*0x800, 0x3e62);
	save_file("$dir/meta.bin", $meta);

	merge_1123_bg    ($meta, $file, "$dir/bg"    ); // 0=30
	merge_1123_upper ($meta, $file, "$dir/upper" ); // 1=94
	merge_1123_lower ($meta, $file, "$dir/lower" ); // 2=22
	merge_1123_shoes ($meta, $file, "$dir/shoes" ); // 3=27-1
	merge_1123_shield($meta, $file, "$dir/shield"); // 4=12
	merge_1123_weapon($meta, $file, "$dir/weapon"); // 5=30
	// 6=5
	// 7=94
	// 8=22
	// 9=26
	return;
}
//////////////////////////////
function merge_a9_card( &$meta, &$file, $dir )
{
	$off = str2int($meta, 2, 2);
	printf("== merge_a9_card( %s ) = %x\n", $dir, $off);

	for ( $i=0; $i < 22; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);

		$pos = (0xad + ($i * 0x31)) * 0x800;
		$src = substr($file, $pos       , 0x18000);
		$pal = substr($file, $pos+0x18000, 0x800);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		merge_src($fn, $src, $pal);

		$data = data1123($meta, $p1);
		foreach ( $data as $dk => $dv )
		{
			if ( $dk === 2 )
				$p = substr($pal, 0, 0x400);
			else
			if ( $dk === 3 )
				$p = substr($pal, 0x400, 0x400);
			else
				continue;

			foreach( $dv as $k => $v )
			{
				$f1 = $dv[$k]['sx'] >> 7;
				$f2 = $dv[$k]['sy'] >> 7;
				$dv[$k]['sx'] &= 0x7f;
				$dv[$k]['sy'] &= 0x7f;

				$flg = ($f1 << 4) | $f2;
				switch ( $flg )
				{
					case 0x00: break;
					case 0x01: $dv[$k]['sy'] += 0x80 ; break;
					case 0x10: $dv[$k]['sy'] += 0x100; break;
					case 0x11: $dv[$k]['sy'] += 0x180; break;
					case 0x02: $dv[$k]['sy'] += 0x200; break;
					case 0x03: $dv[$k]['sy'] += 0x280; break;
					default: // not exists
						php_error('sx sy flg = %x', $flg);
						break;
				} // switch ( $flg )
			} // foreach( $dv as $k => $v )

			$fn = sprintf('%s/%04d-%d', $dir, $i, $dk);
			save1123($fn, $dv, $src, 0x80, 0x300, $p, true);
		} // foreach ( $data as $dk => $dv )
	} // for ( $i=0; $i < 22; $i++ )
	return;
}

function merge_a9( &$file, $dir )
{
	// 0/c=album/card  80018e44
	// 2/e=album/card/booth  80018e44
	// 4/3a=album  80018e44
	// 6/46=booth  80018e44
	// 8/48=booth  80018e44
	// a/4a=booth  80018e44
	//
	// end/1be4
	// -> RAM 80144a08
	$meta = substr($file, 0xa9*0x800, 0x1be4);
	save_file("$dir/meta.bin", $meta);

	// 0=1
	merge_a9_card($meta, $file, "$dir/card"); // 1=22,[3,4]
	// 2=6
	// 3=1
	// 4=1
	// 5=
	return;
}
//////////////////////////////
function data1fbe( &$meta, $st, &$list, &$txt, $no )
{
	$tmp = array();
	while (1)
	{
		if ( $meta[$st] === ZERO )
			break;

		$b0 = str2int($meta, $st+0, 2);
			$st += 2;

		$b1 = str2int($meta, $b0+1, 2);
		if ( array_search($b1, $list) === false )
			$list[] = $b1;

		$id = array_search($b1, $list);
		$tmp[] = "$id-1";
	} // while (1)

	$txt .= sprintf('anim_%d = ', $no);
	$txt .= implode(' , ', $tmp);
	$txt .= "\n";
	return;
}

function save1fbe( &$list, &$meta, $dir, &$src, &$pal )
{
	$fn = sprintf('%s/src.clut', $dir);
	merge_src($fn, $src, $pal);

	foreach ( $list as $lk => $lv )
	{
		$data = xywh1123($meta, $lv);
		foreach ( $data as $k => $v )
		{
			$f1 = $data[$k]['sx'] >> 7;
			$f2 = $data[$k]['sy'] >> 7;
			$data[$k]['sx'] &= 0x7f;
			$data[$k]['sy'] &= 0x7f;

			$flg = ($f1 << 4) | $f2;
			switch ( $flg )
			{
				case 0x01: break;
				case 0x10: $data[$k]['sy'] += 0x80;  break;
				case 0x11: $data[$k]['sy'] += 0x100; break;
				default: // not exists
					php_error('sx sy flg = %x', $flg);
					break;
			} // switch ( $flg )
		} // foreach ( $data as $k => $v )

		$fn = sprintf('%s/%04d', $dir, $lk);
		save1123($fn, $data, $src, 0x80, 0x180, $pal, true);
	} // foreach ( $list as $lk => $lv )
	return;
}

function merge_1fbe_front( &$meta, &$file, $mrgp, $dir )
{
	$off = str2int($meta, 0, 2);
	printf("== merge_1fbe_front( %x , %s ) = %x\n", $mrgp, $dir, $off);

	$list = array();
	$txt = '';
	for ( $i=0; $i < 16; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);
		data1fbe($meta, $p1, $list, $txt, $i);
	} // for ( $i=0; $i < 16; $i++ )

	save_file("$dir/anim.txt", $txt);

	$src = substr($file, $mrgp+0x17000, 0xc000);
	$pal = substr($file, $mrgp+0x23000, 0x200);
		$pal = pal555($pal);

	save1fbe($list, $meta, $dir, $src, $pal);
	return;
}

function merge_1fbe_side( &$meta, &$file, $mrgp, $dir )
{
	$off = str2int($meta, 2, 2);
	printf("== merge_1fbe_side( %x , %s ) = %x\n", $mrgp, $dir, $off);

	$list = array();
	$txt = '';
	for ( $i=0; $i < 9; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);
		data1fbe($meta, $p1, $list, $txt, $i);
	} // for ( $i=0; $i < 9; $i++ )

	save_file("$dir/anim.txt", $txt);

	$src = substr($file, $mrgp-0x1000 , 0xc000);
	$pal = substr($file, $mrgp+0x23000, 0x200);
		$pal = pal555($pal);

	save1fbe($list, $meta, $dir, $src, $pal);
	return;
}

function merge_1fbe_back( &$meta, &$file, $mrgp, $dir )
{
	$off = str2int($meta, 4, 2);
	printf("== merge_1fbe_back( %x , %s ) = %x\n", $mrgp, $dir, $off);

	$list = array();
	$txt = '';
	for ( $i=0; $i < 1; $i++ )
	{
		$p1 = str2int($meta, $off+$i*2, 2);
		data1fbe($meta, $p1, $list, $txt, $i);
	} // for ( $i=0; $i < 1; $i++ )

	save_file("$dir/anim.txt", $txt);

	$src = substr($file, $mrgp+0xb000 , 0xc000);
	$pal = substr($file, $mrgp+0x23000, 0x200);
		$pal = pal555($pal);

	save1fbe($list, $meta, $dir, $src, $pal);
	return;
}

function merge_1fbe_dress( &$meta, &$file, $id, $dir )
{
	$st = str2int($meta,  6, 2);
	$ed = str2int($meta, 10, 2);
	printf("== merge_1fbe_dress( %x , %s ) = %x - %x\n", $id, $dir, $st, $ed);

	$list = array();
	$txt = '';
	$no = 0;
	while ( $st < $ed )
	{
		$p1 = str2int($meta, $st, 2);
			$st += 2;
		data1fbe($meta, $p1, $list, $txt, $no);
			$no++;
	} // while ( $st < $ed )

	save_file("$dir/anim.txt", $txt);

	$pos = (0x114d + ($id * 6 * 0x7c)) * 0x800;
	$src = substr($file, $pos+0x10800, 0x18000);
	$pal = substr($file, $pos+0x28800, 0x200);
		$pal = pal555($pal);

	$fn = sprintf('%s/src.clut', $dir);
	merge_src($fn, $src, $pal);

	foreach ( $list as $lk => $lv )
	{
		$data = xywh1123($meta, $lv);
		foreach ( $data as $k => $v )
		{
			$f1 = $data[$k]['sx'] >> 7;
			$f2 = $data[$k]['sy'] >> 7;
			$data[$k]['sx'] &= 0x7f;
			$data[$k]['sy'] &= 0x7f;

			$flg = ($f1 << 4) | $f2;
			switch ( $flg )
			{
				case 0x00: break;
				case 0x01: $data[$k]['sy'] += 0x80;  break;
				case 0x10: $data[$k]['sy'] += 0x100; break;
				case 0x11: $data[$k]['sy'] += 0x180; break;
				case 0x02: $data[$k]['sy'] += 0x200; break;
				case 0x03: $data[$k]['sy'] += 0x280; break;
				default: // not exists
					php_error('sx sy flg = %x', $flg);
					break;
			} // switch ( $flg )
		} // foreach ( $data as $k => $v )

		$fn = sprintf('%s/%04d', $dir, $lk);
		save1123($fn, $data, $src, 0x80, 0x300, $pal, true);
	} // foreach ( $list as $lk => $lv )
	return;
}

function merge_1fbe( &$file, $id, $dir )
{
	// 0/a=front  80018e44
	// 2/2a=side  80018e44
	// 4/3c=back  80018e44
	// 6/3e=body  80018e44
	// 8/6d3,6b7,5c2,6c0,6c0=map  80018e44
	//
	// end/2e58,3382,35c6,3602,352c
	// -> RAM 801d4800 , 801d8000
	$ed = array(0x2e58,0x3382,0x35c6,0x3602,0x352c);

	$off = (0x1fbe + ($id * 0xae)) * 0x800;
	$meta = substr($file, $off+0x23800, $ed[$id]);
	save_file("$dir/meta.bin", $meta);

	merge_1fbe_front($meta, $file, $off, "$dir/front"); // 0=16
	merge_1fbe_side ($meta, $file, $off, "$dir/side" ); // 1=9
	merge_1fbe_back ($meta, $file, $off, "$dir/back" ); // 2=1
	merge_1fbe_dress($meta, $file, $id , "$dir/dress"); // 3=12,9,9,9,9
	// 4=
	return;
}
//////////////////////////////
function mkrmerge( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( sha1($file) !== SHA1FILE )
		return php_error('sha1sum not match [%s]', sha1($file));

	$dir = str_replace('.', '_', $fname);
	merge_1123($file, "$dir/1123");
	merge_a9  ($file, "$dir/a9"  );

	merge_1fbe($file, 0, "$dir/1fbe-0");
	merge_1fbe($file, 1, "$dir/1fbe-1");
	merge_1fbe($file, 2, "$dir/1fbe-2");
	merge_1fbe($file, 3, "$dir/1fbe-3");
	merge_1fbe($file, 4, "$dir/1fbe-4");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkrmerge( $argv[$i] );

/*
80162e0
	switch ( v1 )
		case 0: ram=801d0000; break;
		case 1: ram=801dc000; break;
		case 2: ram=801d4800; break;
		case 3: ram=801db800; break;
		case 4: ram=801d8000; break;
		case 5: ram=801dbc00; break;
		case 6: ram=801df800; break;
		case 7: ram=80164000; break;

NPC
	graphic + palette @ merge.mrg
	data @ mapdat.mrg
Ramias
	 merge.mrg @ 4e3 + 7*22
	mapdat.mrg @ 3b66+298  3bb0+7c8  3bfb+4f8
 */
