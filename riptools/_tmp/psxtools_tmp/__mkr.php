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
require "common.inc";

function mkrpicevt()
{
	// sub_800678f4+d0
	// li    $a0, 0x8000042
	// la    $a1, aDataMrgmPicevt  # "data/mrgM/PicEvt.mrg"
	// sll   $a3, $s0, 1 // 10
	// addu  $a3, $s0    // 11
	// sll   $a3, 3      // 11000
	// subu  $a3, $s0    // 10111
	// sll   $a3, 3      // 10111000
	// addiu $a3, 0x97   // 97 + b8 (4b800 + 5c000)

	// sub_800678f4+11c
	// ori  $a0, 0x4A
	// la   $a1, aDataMrgmPicevt  # "data/mrgM/PicEvt.mrg"
	// la   $a2, word_8016E000
	// sll  $a3, $s0, 1 // 10
	// addu $a3, $s0    // 11
	// sll  $a3, 3      // 11000
	// subu $a3, $s0    // 10111
	// sll  $a3, 3      // 10111000

	// sub_80067ad0+38
	// ori  $a0, 0x4A
	// la   $s0, aDataMrgmPicevt  # "data/mrgM/PicEvt.mrg"
	// move $a1, $s0
	// la   $a2, dword_800C2330
	// li   $a3, 0x3BA8  // 1dd4000

	// sub_80067ad0+78
	// li    $a0, 0x8000002
	// move  $a1, $s0
	// sll   $a3, $v0, 1 // 10
	// addu  $a3, $v0    // 11
	// sll   $a3, 7      // 110000000
	// addiu $a3, 0x3CD4 // 3cd4 + 180 (1e6a000 + c0000)

	// sub_80067ad0+c0
	// li  $a0, 0x800004A
	// la  $a1, aDataMrgmPicevt  # "data/mrgM/PicEvt.mrg"
	// la  $a2, dword_800C2330
	// li  $a3, 0x45D4  // 22ea000

	return;
}

function mkrmerge()
{
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

	return;
}

function mkrwa()
{
	// sub_8001a474+f0
	// li    $a0, 0x40000042
	// addiu $v0, $v1, 1
	// sll   $v0, 6
	// addu  $v0, $a1
	// lb    $a3, 0($v0)
	// la    $a1, aDataMrgWa_mrg_  # "data\\mrg\\wa_mrg.mrg"
	// sll   $a3, 4
	// addiu $a3, 0xED

	// sub_8001a694+90
	// li    $a0, 0x40000042
	// la    $a1, aDataMrgWa_mrg_  # "data\\mrg\\wa_mrg.mrg"
	// sll   $a3, $v1, 1
	// addu  $a3, $v1
	// sll   $a3, 3
	// subu  $a3, $v1
	// addiu $a3, 0x1B5

	// sub_8001d0d0+48
	// li   $a0, 0x4000004A
	// la   $s1, aDataMrgWa_mr_2  # "data\\mrg\\wa_mrg.mrg"
	// move $a1, $s1
	// la   $s0, word_8016E000
	// move $a2, $s0
	// li   $a3, 0x2C

	// sub_8001dc68+18
	// li   $a0, 0x4000004A
	// la   $s2, aDataMrgWa_mr_2  # "data\\mrg\\wa_mrg.mrg"
	// move $a1, $s2
	// la   $s1, word_8016E000
	// move $a2, $s1
	// move $a3, $0

	// sub_80036d8c+8c
	// li $a0, 0x4000004A
	// la $a1, aDataMrgWa_mr_0  # "data\\mrg\\wa_mrg.mrg"
	// la $a2, word_8016E000
	// li $a3, 0xA6

	// sub_800423d4+34
	// li  $a0, 0x4000004A
	// lui $a1, 0x8001
	// lui $a2, 0x8017
	// la  $a1, aDataMrgWa_mr_1  # "data\\mrg\\wa_mrg.mrg"
	// la  $a2, word_8016E000
	// li  $a3, 0xA6

	return;
}

function mkrmap()
{
	// sub_80019ca8+54
	// li    $a0, 0x400004A
	// la    $a1, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// la    $a2, word_8016E000
	// la    $v1, word_8009EA10
	// sll   $v0, $s5, 1
	// addu  $v0, $v1
	// lhu   $a3, 0($v0)
	// lbu   $v0, 0($s4)
	// addiu $a3, 0x33 // 33 (19800)

	// sub_80019ca8+bc
	// ori   $a0, 0x4A
	// la    $s3, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// move  $a1, $s3
	// la    $s0, word_8016E000
	// move  $a2, $s0
	// la    $v0, word_8009EA10
	// sll   $s2, $s5, 1
	// addu  $s2, $v0
	// la    $s1, sub_80019A68
	// lhu   $a3, 0($s2)
	// lbu   $v0, 0($s4)
	// addiu $a3, 0x33
	//// DUP

	// sub_80019f14+f0
	// li   $a0, 0x400004A
	// la   $a1, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// la   $a2, word_8016E000
	// sll  $a3, $t0, 2 // 100
	// addu $a3, $t0    // 101
	// sll  $v0, $a3, 4 // 1010000
	// addu $a3, $v0    // 1010101
	// + 55 ( + 2a800)

	// sub_80019f14+200
	// li   $a0, 0x4000042
	// la   $a1, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// lui  $a2, 0x801B
	// la   $v1, word_8009EA10
	// sll  $v0, $s1, 1
	// addu $v0, $v1
	// la   $a2, dword_801B0000
	// lhu  $a3, 0($v0)

	// sub_80019f14+26c
	// li   $a0, 0x4000042
	// la   $a1, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// lui  $a2, 0x801B
	// la   $v1, word_8009EA10
	// sll  $v0, $s1, 1
	// addu $v0, $v1
	// la   $a2, dword_801B0000
	// lhu  $a3, 0($v0)

	// sub_80019f14+2d8
	// li   $a0, 0x400004A
	// la   $a1, aDataMrgMapdat_  # "data\\mrg\\mapdat.mrg"
	// la   $a2, word_8016E000
	// la   $v0, word_8009EA10
	// sll  $a3, $s1, 1
	// addu $a3, $v0
	// la   $v0, (dword_8009EA14+0xF8)
	// lhu  $t0, 0($a3)
	// sll  $a3, $s2, 6
	// lh   $v1, 0x200($gp)
	// addu $a3, $s2

	return;
}

function mkr( $fname )
{
	// for *.ext only
	//if ( stripos($fname, '.ext') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	//if ( substr($file, 0, 4) != "FILE" )
		//return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkr( $argv[$i] );

/*
800d83f0
	+0
	+4 size
	+8 fname

800594cc
	a0 2000004a
	a1 80010d2c
	a2 8016e000 // palette
	a3 i * ae + 1fee // pixel data
	jal 8003099c
		s2 = a0
		s3 = a2
		s0 = a3
		-7c10 = 800d83f0
		-7ba0 = 800d8460
		-7b88 = 800d8478
 */
