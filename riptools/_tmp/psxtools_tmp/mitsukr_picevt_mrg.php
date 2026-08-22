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
define('SHA1FILE', 'effc1054bafd7f3090c29d038d72bfd36ee0c653');

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

// 0/0 + 53 * b8/5c000
//   sub_800678f4+11c    -/-         b8/5c000 [event bg]
//   sub_800678f4+d0    97/4b800     b8/5c000 [event meta]
// sub_80067ad0+38    3ba8/1dd4000    -/-     [noel ending]
// 3cd4/1e6a000 + 6 * 180/c0000
//   sub_80067ad0+78  3cd4/1e6a000  180/c0000 [5 girls ending]
// sub_80067ad0+c0    45d4/22ea000    -/-     [credit roll]
//////////////////////////////
function save_0_src( $fn, &$src, &$pal )
{
	$bak = $src;
	$pos = 0;
	for ( $x=0; $x < 0x480; $x += 0x80 )
	{
		for ( $y=0; $y < 0x100; $y++ )
		{
			$s = substr($bak, $pos, 0x80);
				$pos += 0x80;
			$dxx = ($y * 0x480) + $x;
			str_update($src, $dxx, $s);
		} // for ( $y=0; $y < 0x100; $y++ )
	} // for ( $x=0; $x < 0x480; $x += 0x80 )

	$img = [
		'w'   => 0x480,
		'h'   => 0x100,
		'cc'  => 0x100,
		'pal' => $pal,
		'pix' => $src,
	];
	save_clutfile($fn, $img);
	return;
}

function xywh1123( &$meta, $pos )
{
	$cnt = str2int($meta, $pos+0, 1);
	$b0  = str2int($meta, $pos+1, 3);
		$pos += 4;
	printf("== xywh1123( %x ) = %x , %06x\n", $pos-4, $cnt, $b0);
	if ( $cnt === 0 )
		return;

	$data = [];
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

		$xywh = [
			'dx' => sint_bit($dx, 2+8),
			'dy' => sint_bit($dy, 2+8),
			'w'  => ($w + 1) * 8,
			'h'  => ($h + 1) * 8,
			'sx' => $sx * 8,
			'sy' => $sy * 8,
			'fx' => $fx,
		];

		trace('  %3x , %3x , %3x , %3x , %3x , %3x', $dx, $dy, $xywh['w'], $xywh['h'], $xywh['sx'], $xywh['sy']);
		$t = sprintf(' , %04x  %04x', $b23 & 0x2000, $b45 & 0x201f);
		$t = str_replace('0', '-', $t);
		trace("$t\n");

		$data[] = $xywh;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $data;
}
//////////////////////////////
function picevt_0_meta2( &$meta, &$off )
{
	$op = str2int($meta, $off, 2);
		$off += 2;

	//  0 = -
	//  1 = -
	//  2 = -
	//  3 = -
	//  4 = pos
	//  5 = -
	//  6 = -
	//  7 = -
	//  8 = - , -
	//  9 = -
	//  a = id , - , - , - , - , dx , dy , - , - , - , - , - , - , cid , -
	//  b = id , dx , dy , w , h , - , - , - , - , - , cid , -
	//  c = -
	//  d = - , -
	//  e = - , - , - , -
	//  f = - , - , - , - , -
	// 10 =
	// 11 = - , - , -
	// 12 = - , - , - , -
	// 13 = - , - , - , -
	// 14 = - , - , - , -
	$lh = [
		// 0  1 2 3 4 5 6 7 8 9
		   1, 1,1,1,1,1,1,1,2,1,
		  15,12,1,2,4,5,0,3,4,4,
		   4,
	];
	if ( ! isset($lh[$op]) )
		return php_error('meta2 unknown op [%x] @ %x', $op, $bak);

	$var = [];
	for ( $i=0; $i < $lh[$op]; $i++ )
	{
		$var[] = str2int($meta, $off, 2, true);
		$off += 2;
	}
	return [$op, $var];
}

function load_0_meta2( &$meta )
{
	$off = [];
	for ( $i=0; $i < 83; $i++ )
		$off[] = str2int($meta, $i*2, 2);
	$off[] = strlen($meta);
	sort($off);

	$data = [];
	for ( $i=0; $i < 83; $i++ )
	{
		$st = $off[$i+0];
		$ed = $off[$i+1];
		$bak = $st;

		$list = [];
		while ( $st < $ed )
			$list[] = picevt_0_meta2($meta, $st);

		$data[$bak] = $list;
	}
	return $data;
}

function picevt_0( &$file, $dir )
{
	// all 83 meta are the same
	$meta1 = substr($file, 0x48000, 0x1f2c);
	$meta2 = substr($file, 0x4b800, 0x2a48);
	//$meta3 = substr($file, 0x4f000, 0x84f3);
	save_file("$dir/1.meta", $meta1);
	save_file("$dir/2.meta", $meta2);
	//save_file("$dir/3.meta", $meta3);

	$m2d = load_0_meta2($meta2);
	for ( $i=0; $i < 83; $i++ )
	{
		$pos = ($i * 0xb8) * 0x800;

		$src = substr($file, $pos        , 0x48000);
		$pal = substr($file, $pos+0x5b000, 0x200);
			$pal = pal555($pal);

		$fn = sprintf('%s/%04d-src.clut', $dir, $i);
		save_0_src($fn, $src, $pal);

/*
		$off = str2int($meta1, $i*2 , 2); // a2
		$p1  = str2int($meta1, $off , 2); // a4
		$p1  = str2int($meta1, $p1  , 2); // 46c
		$p1  = str2int($meta1, $p1+1, 2); // b7c
		$data = xywh1123($meta1, $p1);
*/

		$off = str2int($meta2, $i*2 , 2);
		$data = $m2d[$off];
		foreach ( $data as $dv )
		{
			$op  = $dv[0];
			$var = $dv[1];
			switch ( $op )
			{
				case 4:
					printf('  goto %x', $var[0]);
					break;
				default:
					printf('%2x = ', $op);
					foreach ( $var as $v )
						printf('%4d  ', $v);
			} // switch ( $op )
			echo "\n";
		} // foreach ( $data as $dv )

		$fn = sprintf('%s/%04d', $dir, $i);
		echo "$fn\n";

	} // for ( $i=0; $i < 83; $i++ )
	return;
}
//////////////////////////////
function picevt_45d4( &$file, $dir )
{
	// no meta = nothing to do
	return;
}
//////////////////////////////
function mkrpicevt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( sha1($file) !== SHA1FILE )
		return php_error('sha1sum not match [%s]', sha1($file));

	$dir = str_replace('.', '_', $fname);
	picevt_0($file, "$dir/0");
	picevt_45d4($file, "$dir/45d4");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkrpicevt( $argv[$i] );
