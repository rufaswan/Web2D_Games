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
require 'common-guest.inc';
require 'common-json.inc';
require 'class-iee754.inc';
require 'quad_vanillaware.inc';

$gp_share = array();

// Legend
//   f4 = 32-bit float
//   1  =  8-bit int
//   2  = 16-bit int
//   4  = 32-bit int
//   c* = char[] / string
//   c4 = RGBA color
//////////////////////////////
function FMBS_s0( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim':
			case 'ps2_odin':
				$s0 = ps2_quad20c($s);
				break;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':
			case 'swi_sent':
			case 'swi_grim':
			case 'swi_unic':
			case 'ps4_unic':
				$s0 = nds_quad18c($s);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = $s0;
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s0.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s1( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim':
			case 'ps2_odin':
				$s1 = ps2_quad20p($s);
				break;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':
			case 'swi_sent':
			case 'swi_grim':
			case 'swi_unic':
			case 'ps4_unic':
				$s1 = nds_quad30p($s);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = $s1;
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s1.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s2( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim':
			case 'ps2_odin':
				$s2 = ps2_quad20p($s);
				break;

			case 'nds_kuma':
			case 'wii_mura':
			case 'ps3_drag':
			case 'ps3_odin':
			case 'ps4_odin':
			case 'ps4_drag':
			case 'ps4_sent':
			case 'swi_sent':
			case 'swi_grim':
			case 'swi_unic':
			case 'ps4_unic':
				$s2 = nds_quad30p($s);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = $s2;
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s2.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s0s1s2( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$dat0 = array();
	$dat1 = array();
	$dat2 = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		switch ( $gp_share['tag'] )
		{
			case 'psp_gran':
				list($s0,$s1,$s2) = psp_quad60pc($s);
				break;

			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':
				list($s0,$s1,$s2) = vit_quad78pc($s);
				break;
		} // switch ( $gp_share['tag'] )

		$dat0[$i] = $s0;
		$dat1[$i] = $s1;
		$dat2[$i] = $s2;
	} // for ( $i=0; $i < $sc; $i++ )

	// s0s1 = unused
	$fn = sprintf('%s/s2.txt', $gp_share['dir']);
	save_file($fn, $text);
	return array($dat0, $dat1, $dat2);
}
//////////////////////////////
function FMBS_s3( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$rect = array(0,0   , 0,0   , 0,0   , 0,0  );
		$xyz  = array(0,0,0 , 0,0,0 , 0,0,0 , 0,0,0);

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps2_odin': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'nds_kuma': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'wii_mura': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'psp_gran': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'vit_mura': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'vit_drag': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'vit_odin': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps3_drag': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps3_odin': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps4_odin': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps4_drag': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'ps4_sent': // 50
				//                                 1                               2                               3                               4
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      f4      f4      f4      f4      - - - - f4      f4      - - - - f4      f4      - - - - f4      f4      - - - -
			case 'swi_sent': // 50
			case 'swi_grim': // 50
			case 'swi_unic': // 50
			case 'ps4_unic': // 50
				$b00 = van_float32($s, 0x00); // x1
				$b04 = van_float32($s, 0x04); // y1
				$b08 = van_float32($s, 0x08); // x2
				$b0c = van_float32($s, 0x0c); // y2
				$b10 = van_float32($s, 0x10); // x3
				$b14 = van_float32($s, 0x14); // y3
				$b18 = van_float32($s, 0x18); // x4
				$b1c = van_float32($s, 0x1c); // y4
				$b20 = van_float32($s, 0x20); // nx1
				$b24 = van_float32($s, 0x24); // ny1
				$b28 = van_float32($s, 0x28); // nz1 = 0
				$b2c = van_float32($s, 0x2c); // nx2
				$b30 = van_float32($s, 0x30); // ny2
				$b34 = van_float32($s, 0x34); // nz2 = 0
				$b38 = van_float32($s, 0x38); // nx3
				$b3c = van_float32($s, 0x3c); // ny3
				$b40 = van_float32($s, 0x40); // nz3 = 0
				$b44 = van_float32($s, 0x44); // nx4
				$b48 = van_float32($s, 0x48); // ny4
				$b4c = van_float32($s, 0x4c); // nz4 = 0

				$rect = array(
					$b00,$b04 ,
					$b08,$b0c ,
					$b10,$b14 ,
					$b18,$b1c ,
				);
				$xyz = array(
					$b20,$b24,$b28 ,
					$b2c,$b30,$b34 ,
					$b38,$b3c,$b40 ,
					$b44,$b48,$b4c ,
				);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'    => "s3 $i" ,
			'rect' => $rect   ,
			'xyz'  => $xyz    ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s3.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s4( $id )
{
	global $gp_share;
	$big = $gp_share['data']['bigend'];

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$flags    = 0;
		$blend_id = 0;
		$tex_id   = 0;
		$s0s1s2   = array(0,0,0);
		$attrib   = 0;
		$color_id = 0;

		// c , v66+
		// 10 , v72+
		// 14 , v76+

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 1 - 1 1 2   - - 2   2   2   2   2   2   2   2
			case 'ps2_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 1 - 1 1 2   - - 2   2   2   2   2   2   2   2
				$b00 = van_int($s, 0x00, 2); // flags
				$b02 = van_int($s, 0x02, 1); // 0 1 2  blend id
				$b03 = van_int($s, 0x03, 1); // tex id
				$b04 = van_int($s, 0x04, 2); // s1 id
				// 00 00
				$b08 = van_int($s, 0x08, 2); // s0 id *inter 0 1 2*
				$b0a = van_int($s, 0x0a, 2); // s0 id *inter     2*
				$b0c = van_int($s, 0x0c, 2); // s0 id *inter   1 2*
				$b0e = van_int($s, 0x0e, 2); // s0 id *inter     2*
				$b10 = van_int($s, 0x10, 2); // s2 id *inter 0 1 2*
				$b12 = van_int($s, 0x12, 2); // s2 id *inter     2*
				$b14 = van_int($s, 0x14, 2); // s2 id *inter   1 2*
				$b16 = van_int($s, 0x16, 2); // s2 id *inter     2*

				$flags    = $b00;
				$blend_id = $b02;
				$tex_id   = $b03;
				$s0s1s2   = array($b08,$b04,$b10);
				break;

			case 'nds_kuma': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - - 1 2   - - 1 - 2
			case 'wii_mura': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// - 1 1 1 2   - - 2   2
				$b00 = van_int($s, 0x00, 2); // flags
				$b02 = van_int($s, 0x02, 1); // blend id
				$b03 = van_int($s, 0x03, 1); // tex id
				$b04 = van_int($s, 0x04, 2); // s1 id
				// 00 00
				$b08 = van_int($s, 0x08, 2); // s0 id
				$b0a = van_int($s, 0x0a, 2); // s2 id

				$flags    = $b00;
				$blend_id = $b02;
				$tex_id   = $b03;
				$s0s1s2   = array($b08,$b04,$b0a);
				break;

			case 'psp_gran': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 2   2   2   2
			case 'vit_mura': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 - - 2   2   2
			case 'vit_drag': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 2   2   2   2
				$b00 = van_int($s, 0x00, 2); // flags
				$b02 = van_int($s, 0x02, 1); // blend id
				$b03 = van_int($s, 0x03, 1); // tex id
				$b04 = van_int($s, 0x04, 2); // attrib
				$b06 = van_int($s, 0x06, 2); // s1 id *unused*
				$b08 = van_int($s, 0x08, 2); // s0 id *unused*
				$b0a = van_int($s, 0x0a, 2); // s2 id

				$flags    = $b00;
				$blend_id = $b02;
				$tex_id   = $b03;
				$s0s1s2   = array($b0a,$b0a,$b0a);
				$attrib   = $b04;
				break;

			case 'ps3_drag': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// - 1 1 1 2   2   2   2
				$b00 = van_int($s, 0x00, 2); // flags
				$b02 = van_int($s, 0x02, 1); // 0 1 2 6  blend id
				$b03 = van_int($s, 0x03, 1); // tex id
				$b04 = van_int($s, 0x04, 2); // attrib
				$b06 = van_int($s, 0x06, 2); // s1 id
				$b08 = van_int($s, 0x08, 2); // s0 id
				$b0a = van_int($s, 0x0a, 2); // s2 id

				$flags    = $b00;
				$blend_id = $b02;
				$tex_id   = $b03;
				$s0s1s2   = array($b08,$b06,$b0a);
				$attrib   = $b04;
				break;

			case 'vit_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - - 1 1 1 1 - 2   2   2
				$b00 = van_int($s, 0x00, 4);
				$b04 = van_int($s, 0x04, 1); // color id
				$b05 = van_int($s, 0x05, 1); // flags
				$b06 = van_int($s, 0x06, 1); // 0 1 2 6  blend id
				$b07 = van_int($s, 0x07, 1); // tex id
				$b08 = van_int($s, 0x08, 2); // attrib
				$b0a = van_int($s, 0x0a, 2); // s1 id *unused*
				$b0c = van_int($s, 0x0c, 2); // s0 id *unused*
				$b0e = van_int($s, 0x0e, 2); // s2 id

				$flags    = $b05;
				$blend_id = $b06;
				$tex_id   = $b07;
				$s0s1s2   = array($b0e,$b0e,$b0e);
				$attrib   = $b08;
				$color_id = $b04;
				break;

			case 'ps3_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// - - 2   1 1 1 1 - 1 2   2   2
			case 'ps4_drag': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - - 1 1 1 2   2   2   2
			case 'ps4_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 1 1 1 1 1 - 2   2   2
				$b00 = van_int($s, 0x00, 4);
				$b04 = van_int($s, 0x04, 1); // color id
				$b05 = van_int($s, 0x05, 1); // flags
				$b06 = van_int($s, 0x06, 1); // blend id
				$b07 = van_int($s, 0x07, 1); // tex id
				$b08 = van_int($s, 0x08, 2); // attrib
				$b0a = van_int($s, 0x0a, 2); // s1 id
				$b0c = van_int($s, 0x0c, 2); // s0 id
				$b0e = van_int($s, 0x0e, 2); // s2 id

				$flags    = $b05;
				$blend_id = $b06;
				$tex_id   = $b07;
				$s0s1s2   = array($b0c,$b0a,$b0e);
				$attrib   = $b08;
				$color_id = $b04;
				break;

			case 'ps4_sent': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 1 1 1 4       2   2   2   - -
			case 'swi_sent': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 1 1 1 4       2   2   2   - -
			case 'swi_grim': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - - 1 1 1 - - - - 2   2   2   - -
			case 'swi_unic': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 1 1 1 4       2   2   2   - -
			case 'ps4_unic': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 4       1 1 1 1 4       2   2   2   - -
				$b00 = van_int($s, 0x00, 4);
				$b04 = van_int($s, 0x04, 1); // color id
				$b05 = van_int($s, 0x05, 1); // flags
				$b06 = van_int($s, 0x06, 1); // 0 1 2 3  blend id
				$b07 = van_int($s, 0x07, 1); // tex id
				$b08 = van_int($s, 0x08, 4); // attrib
				$b0c = van_int($s, 0x0c, 2); // s1 id
				$b0e = van_int($s, 0x0e, 2); // s0 id
				$b10 = van_int($s, 0x10, 2); // s2 id

				$flags    = $b05;
				$blend_id = $b06;
				$tex_id   = $b07;
				$s0s1s2   = array($b0e,$b0c,$b10);
				$attrib   = $b08;
				$color_id = $b04;
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'      => "s4 $i"   ,
			'blend'  => $blend_id ,
			'tex'    => $tex_id   ,
			's0s1s2' => $s0s1s2   ,
			'bits'   => '0x' . dechex($flags ) ,
			'attr'   => '0x' . dechex($attrib) ,
			'color'  => $color_id ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s4.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s5( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$s3_id = 0;
		$flags = 0;

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 8
				// 0 1 2 3 4 5 6 7
				// 1 - 1 - - - - -
			case 'ps2_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - 2   - -
			case 'nds_kuma': // 8
				// 0 1 2 3 4 5 6 7
				// - - - - 1 - - -
			case 'wii_mura': // 8
				// 0 1 2 3 4 5 6 7
				// - 1 1 - - - - 1
			case 'psp_gran': // 8
				// 0 1 2 3 4 5 6 7
				// 1 - - 1 2   - -
			case 'vit_mura': // 8
				// 0 1 2 3 4 5 6 7
				// 1 - 1 - 1 - - -
			case 'vit_drag': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - 1 - - -
			case 'vit_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 1 2   - -
			case 'ps3_drag': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - - - - 1
			case 'ps3_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 1 - - 2
			case 'ps4_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 1 2   - -
			case 'ps4_drag': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - 1 - - -
			case 'ps4_sent': // 8
				// 0 1 2 3 4 5 6 7
				// 1 - 1 - 1 - - -
			case 'swi_sent': // 8
			case 'swi_grim': // 8
			case 'swi_unic': // 8
			case 'ps4_unic': // 8
				$b00 = van_int($s, 0x00, 2); // s3 id
				$b02 = van_int($s, 0x02, 1);
				$b03 = van_int($s, 0x03, 1);
				$b04 = van_int($s, 0x04, 4); // flags

				$s3_id = $b00;
				$flags = $b04;
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'    => "s5 $i" ,
			's3'   => $s3_id  ,
			'bits' => '0x' . dechex($flags) ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s5.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s6( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$rect = array(0,0,0,0);
		$s4   = array(0,0);
		$s5   = array(0,0);
		$flags = 0;

		// 18 , s4 set no++ , flags-- , v6b+
		// 1c , v6e+

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   1 - 1 1 1 -
			case 'ps2_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 1 1 -
			case 'nds_kuma': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   1 - 1 1 1 -
			case 'wii_mura': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 1 - 1
				$b00 = van_float32($s, 0x00); // rect.left
				$b04 = van_float32($s, 0x04); // rect.top
				$b08 = van_float32($s, 0x08); // rect.right
				$b0c = van_float32($s, 0x0c); // rect.bottom
				$b10 = van_int($s, 0x10, 2); // s4 set id
				$b12 = van_int($s, 0x12, 2); // s5 set id
				$b14 = van_int($s, 0x14, 1); // s4 set no
				$b15 = van_int($s, 0x15, 1); // s5 set no
				$b16 = van_int($s, 0x16, 2); // flags

				$rect = array($b00,$b04,$b08,$b0c);
				$s4   = array($b10,$b14);
				$s5   = array($b12,$b15);
				$flags = $b16;
				break;

			case 'psp_gran': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 - 1 1
			case 'vit_mura': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 - 1 1
				$b00 = van_float32($s, 0x00); // rect.left
				$b04 = van_float32($s, 0x04); // rect.top
				$b08 = van_float32($s, 0x08); // rect.right
				$b0c = van_float32($s, 0x0c); // rect.bottom
				$b10 = van_int($s, 0x10, 2); // s4 set id
				$b12 = van_int($s, 0x12, 2); // s5 set id
				$b14 = van_int($s, 0x14, 2); // s4 set no
				$b16 = van_int($s, 0x16, 1); // s5 set no
				$b17 = van_int($s, 0x17, 1); // flags

				$rect = array($b00,$b04,$b08,$b0c);
				$s4   = array($b10,$b14);
				$s5   = array($b12,$b16);
				$flags = $b17;
				break;

			case 'vit_drag': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      3     - 2   1 - 1 1 - -
			case 'vit_odin': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      2   - - 2   1 - 1 1 - -
			case 'ps3_drag': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      - 3     2   - 1 1 1 - -
			case 'ps3_odin': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      - 3     2   2   1 1 - -
			case 'ps4_drag': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      3     - 2   1 - 1 1 - -
			case 'ps4_odin': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      3     - 2   2   1 1 - -
			case 'ps4_sent': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      3     - 1 - 2   1 1 - -
			case 'swi_sent': // 1c
			case 'swi_grim': // 1c
			case 'swi_unic': // 1c
			case 'ps4_unic': // 1c
				$b00 = van_float32($s, 0x00); // rect.left
				$b04 = van_float32($s, 0x04); // rect.top
				$b08 = van_float32($s, 0x08); // rect.right
				$b0c = van_float32($s, 0x0c); // rect.bottom
				$b10 = van_int($s, 0x10, 4); // s4 set id
				$b14 = van_int($s, 0x14, 2); // s5 set id
				$b16 = van_int($s, 0x16, 2); // s4 set no
				$b18 = van_int($s, 0x18, 1); // s5 set no
				$b19 = van_int($s, 0x19, 1); // flags

				$rect = array($b00,$b04,$b08,$b0c);
				$s4   = array($b10,$b16);
				$s5   = array($b14,$b18);
				$flags = $b19;
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'    => "s6 $i" ,
			'rect' => $rect   ,
			's4'   => $s4     ,
			's5'   => $s5     ,
			'bits' => '0x' . dechex($flags) ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s6.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s7( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$fog    = '';
		$move   = array(0,0,0);
		$rotate = array(0,0,0);
		$scale  = array(0,0);

		// 24 , v66+

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      - - - - f4      f4      f4      f4      f4
			case 'ps2_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      f4      f4      - - - - f4      f4      f4      f4      f4
				$b00 = van_float32($s, 0x00); // red
				$b04 = van_float32($s, 0x04); // green
				$b08 = van_float32($s, 0x08); // blue
				$b0c = van_float32($s, 0x0c); // alpha
				$b10 = van_float32($s, 0x10); // move x
				$b14 = van_float32($s, 0x14); // move y
				$b18 = van_float32($s, 0x18); // move z
				$b1c = van_float32($s, 0x1c); // rotate x
				$b20 = van_float32($s, 0x20); // rotate y
				$b24 = van_float32($s, 0x24); // rotate z
				$b28 = van_float32($s, 0x28); // scale x
				$b2c = van_float32($s, 0x2c); // scale y

				$move   = array($b10,$b14,$b18);
				$rotate = array($b1c,$b20,$b24);
				$scale  = array($b28,$b2c);
				$fog    = sprintf('#%02x%02x%02x%02x',
					($b00 * 255) & BIT8,
					($b04 * 255) & BIT8,
					($b08 * 255) & BIT8,
					($b0c * 255) & BIT8);
				break;

			case 'nds_kuma': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - - - - - - - - - - - - - f4      f4      c4
			case 'wii_mura': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'psp_gran': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'vit_mura': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'vit_drag': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'vit_odin': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'ps3_drag': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      f4      f4      f4      f4      f4      f4      c4
			case 'ps3_odin': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      f4      f4      f4      f4      f4      f4      c4
			case 'ps4_drag': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      f4      f4      f4      f4      f4      f4      c4
			case 'ps4_odin': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      - - - - f4      f4      f4      f4      f4      c4
			case 'ps4_sent': // 24
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// f4      f4      f4      f4      f4      f4      f4      f4      c4
			case 'swi_sent': // 24
			case 'swi_grim': // 24
			case 'swi_unic': // 24
			case 'ps4_unic': // 24
				$b00 = van_float32($s, 0x00); // move x
				$b04 = van_float32($s, 0x04); // move y
				$b08 = van_float32($s, 0x08); // move z
				$b0c = van_float32($s, 0x0c); // rotate x
				$b10 = van_float32($s, 0x10); // rotate y
				$b14 = van_float32($s, 0x14); // rotate z
				$b18 = van_float32($s, 0x18); // scale x
				$b1c = van_float32($s, 0x1c); // scale y
				$b20 = substr($s,0x20,4);

				$move   = array($b00,$b04,$b08);
				$rotate = array($b0c,$b10,$b14);
				$scale  = array($b18,$b1c);
				$fog    = '#' . bin2hex($b20);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'      => "s7 $i" ,
			'move'   => $move   ,
			'rotate' => $rotate ,
			'scale'  => $scale  ,
			'fog'    => $fog    ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s7.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s8( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$s6_id = 0;
		$s7_id = 0;
		$flags = 0;
		$time  = 0;
		$loop  = 0;
		$sfx   = 0;
		$in_s5s3   = -1;
		$in_s7     = -1;
		$in_s6     = -1;
		$in_s0s1s2 = -1;

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - - - - - 1 - - -
			case 'ps2_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
			case 'nds_kuma': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - - 1 - - - - - - - - - - 1 - 2   - -
			case 'wii_mura': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   - 1 1 1 1 1 1 - - - - - - 1 - - - - - -
			case 'psp_gran': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 - - - - 1 - 1 - - - - -
			case 'vit_mura': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - - - - - 1 - - - - - - -
			case 'vit_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
			case 'vit_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
			case 'ps3_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   - 1 1 1 1 1 1 - - - - 1 - 1 - - - - - -
			case 'ps3_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   2   1 1 1 1 1 1 - - - 1 - 1 - 1 - - 2
			case 'ps4_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
			case 'ps4_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 2   1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
			case 'ps4_sent': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
			case 'swi_sent': // 20
			case 'swi_grim': // 20
			case 'swi_unic': // 20
			case 'ps4_unic': // 20
				$b00 = van_int($s, 0x00, 2); // s6 id
				// 00 00
				$b04 = van_int($s, 0x04, 2); // s7 id
				$b06 = van_int($s, 0x06, 2); // frames
				$b08 = van_int($s, 0x08, 4); // flags
				$b0c = van_int($s, 0x0c, 2); // loop s8 id
				$b0e = van_int($s, 0x0e, 1); // 0 1 2  s5s3 interpolation
				$b0f = van_int($s, 0x0f, 1); // interpolation rate
				$b10 = van_int($s, 0x10, 1); // 0 1 2  s7 interpolation
				$b11 = van_int($s, 0x11, 1); // 0 1 2  s6 interpolation
				$b12 = van_int($s, 0x12, 1); // 0 1 2  s0s1s2 interpolation
				$b13 = van_int($s, 0x13, 1); // n*180
				$b14 = van_int($s, 0x14, 4);
				$b18 = van_int($s, 0x18, 2);
				$b1a = van_int($s, 0x1a, 2); // 0 1  sfx mute
				$b1c = van_int($s, 0x1c, 4); // sfx id

				$s6_id = $b00;
				$s7_id = $b04;
				$flags = $b08;
				$time  = $b06;
				$loop  = $b0c;
				$sfx   = $b1c;

				$in_s5s3   = $b0e;
				$in_s7     = $b10;
				$in_s6     = $b11;
				$in_s0s1s2 = $b12;
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'    => "s8 $i",
			's6'   => $s6_id ,
			's7'   => $s7_id ,
			'time' => $time  ,
			'loop' => $loop  ,
			'sfx'  => $sfx   ,
			'bits' => '0x' . dechex($flags),
			'in_s5s3'   => $in_s5s3   ,
			'in_s7'     => $in_s7     ,
			'in_s6'     => $in_s6     ,
			'in_s0s1s2' => $in_s0s1s2 ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s8.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_s9( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$rect = array(0,0,0,0);
		$name = '';
		$sa   = array(0,0,0);

		// 30 , v72+

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'ps2_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'nds_kuma': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'wii_mura': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'psp_gran': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'vit_mura': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'vit_drag': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'ps3_drag': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
				$b00 = van_float32($s, 0x00); // rect.left
				$b04 = van_float32($s, 0x04); // rect.top
				$b08 = van_float32($s, 0x08); // rect.right
				$b0c = van_float32($s, 0x0c); // rect.bottom
				$b10 = substr0($s, 0x10);
				$b28 = van_int($s, 0x28, 2); // sa_set_id
				$b2a = van_int($s, 0x2a, 1); // sa_set_no
				$b2b = van_int($s, 0x2b, 1); // sa_set_main
				$b2c = van_int($s, 0x2c, 1); // 0 1

				$rect = array($b00 , $b04 , $b08 , $b0c);
				$name = $b10;
				$sa   = array($b28 , $b2a);
				break;

			case 'vit_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
			case 'ps3_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
			case 'ps4_drag': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
			case 'ps4_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
			case 'ps4_sent': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
			case 'swi_sent': // 30
			case 'swi_grim': // 30
			case 'swi_unic': // 30
			case 'ps4_unic': // 30
				$b00 = van_float32($s, 0x00); // rect.left
				$b04 = van_float32($s, 0x04); // rect.top
				$b08 = van_float32($s, 0x08); // rect.right
				$b0c = van_float32($s, 0x0c); // rect.bottom
				$b10 = substr0($s, 0x10);
				$b28 = van_int($s, 0x28, 2); // sa_set_id
				$b2a = van_int($s, 0x2a, 1); // sa_set_no
				$b2b = van_int($s, 0x2b, 1); // sa_set_main
				$b2c = van_int($s, 0x2c, 2); // sa_sb_set_id
				$b2e = van_int($s, 0x2e, 1); // sa_sb_set_no
				$b2f = van_int($s, 0x2f, 1); // 0 1

				$rect = array($b00 , $b04 , $b08 , $b0c);
				$name = $b10;
				$sa   = array($b28 , $b2a);
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'    => "s9 $i" ,
			'rect' => $rect   ,
			'name' => $name   ,
			'sa'   => $sa     ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/s9.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_sa( $id )
{
	global $gp_share;

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		$s8_set_id  = 0;
		$s8_set_no  = 0;
		$s8_set_sum = 0;
		$s8_set_st  = 0;

		// 10 , v66+
		// 14 , v6b+
		// 18 , v72+

		switch ( $gp_share['tag'] )
		{
			case 'ps2_grim': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - 2   1 -
			case 'ps2_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   2   2   1 -
				$b00 = van_int($s, 0x00, 2); // s8_set_id
				$b02 = van_int($s, 0x02, 2); // s8_set_no
				$b04 = van_int($s, 0x04, 2); // s8_set_sum
				$b06 = van_int($s, 0x06, 1); // 0 1  s8_set_st

				$s8_set_id  = $b00;
				$s8_set_no  = $b02;
				$s8_set_sum = $b04;
				$s8_set_st  = $b06;
				break;

			case 'nds_kuma': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   1 - 2   - - 4       1 - - -
			case 'wii_mura': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - 1 - - 2   4       1 - - -
				$b00 = van_int($s, 0x00, 2); // s8_set_id
				$b02 = van_int($s, 0x02, 2); // s8_set_no
				$b04 = van_int($s, 0x04, 4); // s8_set_sum
				$b08 = van_int($s, 0x08, 4, true); // s8_set_sum_once
				$b0c = van_int($s, 0x0c, 1); // 0 1  s8_set_st

				$s8_set_id  = $b00;
				$s8_set_no  = $b02;
				$s8_set_sum = $b04;
				$s8_set_st  = $b0c;
				break;

			case 'psp_gran': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   2   - - 4       4       1 - - -
			case 'vit_mura': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   1 - 2   - - 4       4       1 - - -
			case 'vit_drag': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   2   - - 4       4       1 - - -
			case 'ps3_drag': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   - - 2   4       4       1 - - -
				$b00 = van_int($s, 0x00, 2); // s8_set_id
				$b02 = van_int($s, 0x02, 2); // s8_set_no
				$b04 = van_int($s, 0x04, 4); // s8_set_sum
				$b08 = van_int($s, 0x08, 4, true); // s8_set_sum_once
				$b0c = van_int($s, 0x0c, 4, true); //
				$b10 = van_int($s, 0x10, 1); // 0 1  s8_set_st

				$s8_set_id  = $b00;
				$s8_set_no  = $b02;
				$s8_set_sum = $b04;
				$s8_set_st  = $b10;
				break;

			case 'vit_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   3     - 4       4       1 - 1 1 1 - - -
			case 'ps3_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   - 3     4       4       2   1 1 1 - - -
			case 'ps4_drag': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   2   - - 4       4       2   1 1 1 - - -
			case 'ps4_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   3     - 4       4       2   1 1 1 - - -
			case 'ps4_sent': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   3     - 4       4       1 - 1 1 1 - - -
			case 'swi_sent': // 18
			case 'swi_grim': // 18
			case 'swi_unic': // 18
			case 'ps4_unic': // 18
				$b00 = van_int($s, 0x00, 2); // s8_set_id
				$b02 = van_int($s, 0x02, 2); // s8_set_no
				$b04 = van_int($s, 0x04, 4); // s8_set_sum
				$b08 = van_int($s, 0x08, 4, true); // s8_set_sum_once
				$b0c = van_int($s, 0x0c, 4, true); //
				$b10 = van_int($s, 0x10, 2); // sb_set_id
				$b12 = van_int($s, 0x12, 1); // sb_set_no
				$b13 = van_int($s, 0x13, 1); // 0 1  s8_set_st
				$b14 = van_int($s, 0x14, 2);

				$s8_set_id  = $b00;
				$s8_set_no  = $b02;
				$s8_set_sum = $b04;
				$s8_set_st  = $b13;
				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i'  => "sa $i" ,
			's8' => array(
				$s8_set_id + $s8_set_st ,
				$s8_set_no - $s8_set_st ,
				$s8_set_sum
			) ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/sa.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}

function FMBS_sb( $id )
{
	global $gp_share;

	if ( ! isset( $gp_share['data']['sect'][$id] ) )
	{
		php_notice('%s has no sb.txt', $gp_share['tag']);
		return array();
	}

	list($sp,$sc,$sk) = sect_head($id);
	$data = array();
	$text = '';

	for ( $i=0; $i < $sc; $i++ )
	{
		$p = $sp + ($i * $sk);
		$s = substr($gp_share['file'], $p, $sk);
		$text .= sect_data_txt($i, $s);

		switch ( $gp_share['tag'] )
		{
			case 'vit_odin': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 - - - 1 - - - 1 - 1 - 2   - -
			case 'ps3_odin': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// - - 2   - 1 - - - - - 1 - 1 - 1 - - 2
			case 'ps4_odin': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 - - - 1 - - - 1 - 1 - 2   - -
			case 'ps4_drag': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 - - - 1 - - - 1 - - - - - - -
			case 'ps4_sent': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 - - - 1 - - - 1 - - - - - - -
			case 'swi_sent': // 14
			case 'swi_grim': // 14
			case 'swi_unic': // 14
			case 'ps4_unic': // 14
				$b00 = van_int($s, 0x00, 4);
				$b04 = van_int($s, 0x04, 2);
				// 00 00
				$b08 = van_int($s, 0x08, 4);
				$b0c = van_int($s, 0x0c, 2);
				$b0e = van_int($s, 0x0e, 2);
				$b10 = van_int($s, 0x10, 4);

				break;
		} // switch ( $gp_share['tag'] )

		$data[$i] = array(
			'i' => "sb $i" ,
		);
	} // for ( $i=0; $i < $sc; $i++ )

	$fn = sprintf('%s/sb.txt', $gp_share['dir']);
	save_file($fn, $text);
	return $data;
}
//////////////////////////////
function detect_tag( &$file )
{
	switch ( substr($file,0,4) )
	{
		case 'FMBP':
			$ver = str2int($file, 0x14, 2);
			switch ( $ver )
			{
				case 0xc9:  return 'ps2_grim';
				case 0x55:  return 'ps2_odin';
			} // switch ( $ver )
			return '';
		case 'FMBS':
			// big endian test
			$ver = str2big($file, 0x14, 2);
			switch ( $ver )
			{
				case 0x66:  return 'wii_mura';
				case 0x6e:  return 'ps3_drag';
				case 0x72:  return 'ps3_odin';
			} // switch ( $ver )

			// little endian test
			$ver = str2int($file, 0x14, 2);
			switch ( $ver )
			{
				case 0x66:  return 'nds_kuma';
				case 0x6b:  return 'psp_gran';
				case 0x6d:  return 'vit_mura';
				case 0x6e:  return 'vit_drag';
				case 0x72:
					// s0 test
					if ( str2int($file,0xc8,4) === 0x120 || str2int($file,0xe0,4) === 0x120 )
						return 'vit_odin';
					if ( str2int($file,0xb0,4) === 0x120 )
					{
						$poss = array('ps4_odin' , 'ps4_sent');
						php_warning('unable to auto-detect %s', implode(' , ', $poss));
					}
					return '';
				case 0x76:  return 'ps4_sent';
				case 0x77:
					$poss = array(
						'swi_sent' , 'swi_grim' , 'swi_unic' ,
						'ps4_grim' , 'ps4_unic' ,
						'ps5_grim' , 'ps5_unic' ,
						'xbx_unic'
					);
					php_warning('unable to auto-detect %s', implode(' , ', $poss));
					return '';
			} // switch ( $ver )

			// test failed
			return '';
	} // switch ( substr($file,0,4) )
	return '';
}

function vanilla( $tag, $fname )
{
	global $gp_data, $gp_share;
	$gp_share = array(
		'tag'  => '',
		'data' => '',
		'file' => '',
		'dir'  => '',
		'idx'  => 0,
	);

	$gp_share['file'] = file_get_contents($fname);
	if ( empty($gp_share['file']) )  return;

	$t = detect_tag($gp_share['file']);
	if ( ! empty($t) )
	{
		printf("[AUTO] tag = %s\n", $t);
		$tag = $t;
	}
	else
		printf("tag = %s\n", $tag);

	if ( ! isset($gp_data[$tag]) )
		return php_error('Unknown tag [%s] = %s', $tag, $fname);

	$gp_share['data'] = $gp_data[$tag];
	$gp_share['tag']  = $tag;
	$gp_share['dir']  = str_replace('.', '_', $fname);

	global $gp_big;
	$gp_big = $gp_share['data']['bigend'];

	switch ( $gp_share['tag'] )
	{
		case 'ps2_grim':
		case 'ps2_odin':
		case 'nds_kuma':
		case 'wii_mura':
		case 'ps3_drag':
		case 'ps3_odin':
		case 'ps4_odin':
		case 'ps4_drag':
		case 'ps4_sent':
		case 'swi_sent':
		case 'swi_grim':
		case 'swi_unic':
		case 'ps4_unic':
			$json = array(
				's0' => FMBS_s0(0),
				's1' => FMBS_s1(1),
				's2' => FMBS_s2(2),
				's3' => FMBS_s3(3),
				's4' => FMBS_s4(4),
				's5' => FMBS_s5(5),
				's6' => FMBS_s6(6),
				's7' => FMBS_s7(7),
				's8' => FMBS_s8(8),
				's9' => FMBS_s9(9),
				'sa' => FMBS_sa(10),
				'sb' => FMBS_sb(11),
			);
			break;

		case 'psp_gran':
		case 'vit_mura':
		case 'vit_drag':
			$s012 = FMBS_s0s1s2(8);
			$json = array(
				's0' => $s012[0],
				's1' => $s012[1],
				's2' => $s012[2],
				's3' => FMBS_s3(0),
				's4' => FMBS_s4(1),
				's5' => FMBS_s5(2),
				's6' => FMBS_s6(3),
				's7' => FMBS_s7(4),
				's8' => FMBS_s8(5),
				's9' => FMBS_s9(6),
				'sa' => FMBS_sa(7),
				'sb' => FMBS_sb(-1),
			);
			break;

		case 'vit_odin':
			$s012 = FMBS_s0s1s2(9);
			$json = array(
				's0' => $s012[0],
				's1' => $s012[1],
				's2' => $s012[2],
				's3' => FMBS_s3(0),
				's4' => FMBS_s4(1),
				's5' => FMBS_s5(2),
				's6' => FMBS_s6(3),
				's7' => FMBS_s7(4),
				's8' => FMBS_s8(5),
				's9' => FMBS_s9(6),
				'sa' => FMBS_sa(7),
				'sb' => FMBS_sb(8),
			);
			break;
	} // switch ( $tag )

	$json['tag'] = $tag;
	$json['id3'] = $gp_share['data']['idtag'];
	$json['ver'] = '55';

	$txt = json_pretty::encode($json);
	save_file("$fname.v55", $txt);
	return;
}
//////////////////////////////
$err = <<<_ERR
{$argv[0]}  [TAG]  MBP/MBS_FILE...
TAG
  ps2_grim  2007  PS2   [AUTO] GrimGrimoire
  ps2_odin  2007  PS2   [AUTO] Odin Sphere
  nds_kuma  2008  NDS   [AUTO] Kumatanchi
  wii_mura  2009  Wii   [AUTO] Muramasa - The Demon Blade
  ps3_drag  2013  PS3   [AUTO] Dragon's Crown
  ps3_odin  2016  PS3   [AUTO] Odin Sphere Leifthsar
  ps4_odin  2016  PS4   Odin Sphere Leifthsar
  ps4_drag  2018  PS4   Dragon's Crown Pro
  ps4_sent  2019  PS4   [AUTO] 13 Sentinels: Aegis Rim

  psp_gran  2011  PSP   [AUTO] Gran Knights History
  vit_mura  2013  Vita  [AUTO] Muramasa Rebirth + DLC
  vit_drag  2013  Vita  [AUTO] Dragon's Crown
  vit_odin  2016  Vita  [AUTO] Odin Sphere Leifthsar
  swi_sent  2022  Swit  13 Sentinels: Aegis Rim
  swi_grim  2022  Swit  GrimGrimoire OnceMore
  swi_unic  2024  Swit  Unicorn Overlord
  ps4_unic  2024  PS4   Unicorn Overlord

  Upcoming
  ps4_grim  2022  PS4   GrimGrimoire OnceMore
  ps5_grim  2023  PS5   GrimGrimoire OnceMore
  ps5_unic  2024  PS5   Unicorn Overlord
  xbx_unic  2024  XBSX  Unicorn Overlord

_ERR;

if ( $argc == 1 )  exit($err);
$tag = '';
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		vanilla($tag, $argv[$i]);
	else
		$tag = $argv[$i];
}
