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
require 'quad.inc';
require 'quad_vanillaware.inc';

//define('NO_TRACE', true);
define('FLAG_SKIP'        , 1 <<  0);
define('FLAG_VERTEX_COLOR', 1 <<  1);

define('BLEND_RGBA'   , 0);
define('BLEND_RGB_ADD', 1 << 0);
define('BLEND_RGB_SUB', 1 << 1);

$gp_tag  = '';
$gp_file = '';
$gp_ord  = '';

// Legend
//   f4 = 32-bit float
//   1  =  8-bit int
//   2  = 16-bit int
//   4  = 32-bit int
//   c* = char[] / string
//   c4 = RGBA color
//////////////////////////////
function key_layers( $layer_s, $layer_id, $layer_no )
{
	global $gp_file, $gp_tag, $gp_ord;
	$layer = array();

	$mbs = &$gp_file[$layer_s];
	for ( $id=0; $id < $layer_no; $id++ )
	{
		$sub = substr($mbs['d'], ($layer_id+$id)*$mbs['k'], $mbs['k']);
			$s4p = &$sub;
			$s1p = &$sub;

		$tex_id = 0;
		$src = array();
		$dst = array();
		$clr = array();
		$flag = 0;
		$blend = BLEND_RGBA;
		$DEBUG = '';

		switch ( $gp_tag )
		{
			//layer_s == 4
			case 'ps2_grim': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 1 - 1 1 2   - - 2   2   2   2   2   2   2   2
			case 'ps2_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 1 - 1 1 2   - - 2   2   2   2   2   2   2   2
				$b00 = $gp_ord($s4p, 0x00, 1);
				$b02 = $gp_ord($s4p, 0x02, 1); // 0 1 2
				$b03 = $gp_ord($s4p, 0x03, 1); // tex
				$b04 = $gp_ord($s4p, 0x04, 2); // s1 id
				$b08 = $gp_ord($s4p, 0x08, 2); //  8  a  c  e = s0 id set
				//$b0a = $gp_ord($s4p, 0x0a, 2);
				//$b0c = $gp_ord($s4p, 0x0c, 2);
				//$b0e = $gp_ord($s4p, 0x0e, 2);
				$b10 = $gp_ord($s4p, 0x10, 2); // 10 12 14 16 = s2 id set
				//$b12 = $gp_ord($s4p, 0x12, 2);
				//$b14 = $gp_ord($s4p, 0x14, 2);
				//$b16 = $gp_ord($s4p, 0x16, 2);
				$DEBUG = subtrace(4, $b00, $b02, $b03, $b04, $b08, $b10);

				$tex_id = $b03;
				ps2_quad20p($gp_file[1], $b04, $src, $gp_ord);
				ps2_quad20p($gp_file[2], $b10, $dst, $gp_ord);
				ps2_quad20c($gp_file[0], $b08, $clr);

				if ( $b00 & 2 )  $flag |= FLAG_SKIP;
				if ( $b00 & 4 )  $flag |= FLAG_VERTEX_COLOR;

				if ( $b02 == 1 )  $blend = BLEND_RGB_ADD;
				if ( $b02 == 2 )  $blend = BLEND_RGB_SUB;
				break;
			case 'nds_kuma': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - - 1 2   - - 1 - 2
				$b00 = $gp_ord($s4p, 0x00, 1); // 0 2 4 6
				$b03 = $gp_ord($s4p, 0x03, 1); // tex
				$b04 = $gp_ord($s4p, 0x04, 2); // s1 id
				$b08 = $gp_ord($s4p, 0x08, 1); // s0 id
				$b0a = $gp_ord($s4p, 0x0a, 2); // s2 id
				$DEBUG = subtrace(4, $b00, $b03, $b04, $b08, $b0a);

				$tex_id = $b03;
				nds_quad30p($gp_file[1], $b04, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0a, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b08, $clr);

				if ( $b00 & 2 )  $flag |= FLAG_SKIP;
				if ( $b00 & 4 )  $flag |= FLAG_VERTEX_COLOR;
				break;
			case 'wii_mura': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// - 1 1 1 2   - - 2   2
				$b01 = $gp_ord($s4p, 0x01, 1);
				$b02 = $gp_ord($s4p, 0x02, 1); // 0 1 2
				$b03 = $gp_ord($s4p, 0x03, 1); // tex
				$b04 = $gp_ord($s4p, 0x04, 2); // s1 id
				$b08 = $gp_ord($s4p, 0x08, 2); // s0 id
				$b0a = $gp_ord($s4p, 0x0a, 2); // s2 id
				$DEBUG = subtrace(4, $b01, $b02, $b03, $b04, $b08, $b0a);

				$tex_id = $b03;
				nds_quad30p($gp_file[1], $b04, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0a, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b08, $clr);
				break;
			case 'ps3_drag': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// - 1 1 1 2   2   2   2
				$b01 = $gp_ord($s4p, 0x01, 1);
				$b02 = $gp_ord($s4p, 0x02, 1); // 0 1 2 6
				$b03 = $gp_ord($s4p, 0x03, 1); // tex
				$b04 = $gp_ord($s4p, 0x04, 2);
				$b06 = $gp_ord($s4p, 0x06, 2); // s1 id
				$b08 = $gp_ord($s4p, 0x08, 2); // s0 id
				$b0a = $gp_ord($s4p, 0x0a, 2); // s2 id
				$DEBUG = subtrace(4, $b01, $b02, $b03, $b04, $b06, $b08, $b0a);

				$tex_id = $b03;
				nds_quad30p($gp_file[1], $b06, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0a, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b08, $clr);
				break;
			case 'ps3_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// - - 2   2   1 1 - 1 2   2   2
				$b02 = $gp_ord($s4p, 0x02, 2);
				$b04 = $gp_ord($s4p, 0x04, 2);
				$b06 = $gp_ord($s4p, 0x06, 1); // 0 1 2 6
				$b07 = $gp_ord($s4p, 0x07, 1); // tex
				$b09 = $gp_ord($s4p, 0x09, 1);
				$b0a = $gp_ord($s4p, 0x0a, 2); // s1 id
				$b0c = $gp_ord($s4p, 0x0c, 2); // s0 id
				$b0e = $gp_ord($s4p, 0x0e, 2); // s2 id
				$DEBUG = subtrace(4, $b02, $b04, $b06, $b07, $b09, $b0a, $b0c, $b0e);

				$tex_id = $b07;
				nds_quad30p($gp_file[1], $b0a, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0e, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b0c, $clr);
				break;
			case 'ps4_drag': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - - 1 1 1 2   2   2   2
				$b00 = $gp_ord($s4p, 0x00, 2);
				$b05 = $gp_ord($s4p, 0x05, 1);
				$b06 = $gp_ord($s4p, 0x06, 1); // 0 1 2 5 6
				$b07 = $gp_ord($s4p, 0x07, 1); // tex
				$b08 = $gp_ord($s4p, 0x08, 2);
				$b0a = $gp_ord($s4p, 0x0a, 2); // s1 id
				$b0c = $gp_ord($s4p, 0x0c, 2); // s0 id
				$b0e = $gp_ord($s4p, 0x0e, 2); // s2 id
				$DEBUG = subtrace(4, $b00, $b05, $b06, $b07, $b08, $b0a, $b0c, $b0e);

				$tex_id = $b07;
				nds_quad30p($gp_file[1], $b0a, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0e, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b0c, $clr);
				break;
			case 'ps4_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 1 1 1 1 1 - 2   2   2
				$b00 = $gp_ord($s4p, 0x00, 2);
				$b04 = $gp_ord($s4p, 0x04, 1);
				$b05 = $gp_ord($s4p, 0x05, 1);
				$b06 = $gp_ord($s4p, 0x06, 1); // 0 1 2 6
				$b07 = $gp_ord($s4p, 0x07, 1); // tex
				$b08 = $gp_ord($s4p, 0x08, 1);
				$b0a = $gp_ord($s4p, 0x0a, 2); // s1 id
				$b0c = $gp_ord($s4p, 0x0c, 2); // s0 id
				$b0e = $gp_ord($s4p, 0x0e, 2); // s2 id
				$DEBUG = subtrace(4, $b00, $b04, $b05, $b06, $b07, $b08, $b0a, $b0c, $b0e);

				$tex_id = $b07;
				nds_quad30p($gp_file[1], $b0a, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b0e, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b0c, $clr);
				break;
			case 'ps4_sent': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   - - 1 1 1 1 4       2   2   2   - -
				$b00 = $gp_ord($s4p, 0x00, 2);
				$b04 = $gp_ord($s4p, 0x04, 1);
				$b05 = $gp_ord($s4p, 0x05, 1);
				$b06 = $gp_ord($s4p, 0x06, 1); // 0 1 2 3
				$b07 = $gp_ord($s4p, 0x07, 1); // tex
				$b08 = $gp_ord($s4p, 0x08, 4);
				$b0c = $gp_ord($s4p, 0x0c, 2); // s1 id
				$b0e = $gp_ord($s4p, 0x0e, 2); // s0 id
				$b10 = $gp_ord($s4p, 0x10, 2); // s2 id
				$DEBUG = subtrace(4, $b00, $b04, $b05, $b06, $b07, $b08, $b0c, $b0e, $b10);

				$tex_id = $b07;
				nds_quad30p($gp_file[1], $b0c, $src, $gp_ord);
				nds_quad30p($gp_file[2], $b10, $dst, $gp_ord);
				nds_quad18c($gp_file[0], $b0e, $clr);
				break;

			//layer_s == 1
			case 'psp_gran': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 2   2   2   2
				$b00 = $gp_ord($s1p, 0x00, 1);
				$b02 = $gp_ord($s1p, 0x02, 1); // 0 1 2
				$b03 = $gp_ord($s1p, 0x03, 1); // tex
				$b04 = $gp_ord($s1p, 0x04, 2);
				$b06 = $gp_ord($s1p, 0x06, 2); // *unused*
				$b08 = $gp_ord($s1p, 0x08, 2); // *unused*
				$b0a = $gp_ord($s1p, 0x0a, 2); // s8 id
				$DEBUG = subtrace(1, $b00, $b02, $b03, $b04, $b06, $b08, $b0a);

				$tex_id = $b03;
				psp_quad60pc($gp_file[8], $b0a, $src, $dst, $clr, $gp_ord);
				break;
			case 'vit_mura': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 - - 2   2   2
				$b00 = $gp_ord($s1p, 0x00, 1);
				$b02 = $gp_ord($s1p, 0x02, 1); // 0 1 2 6
				$b03 = $gp_ord($s1p, 0x03, 1); // tex
				$b06 = $gp_ord($s1p, 0x06, 2); // *unused* , original s1 id
				$b08 = $gp_ord($s1p, 0x08, 2); // *unused* , original s0 id
				$b0a = $gp_ord($s1p, 0x0a, 2); // s8 id
				$DEBUG = subtrace(1, $b00, $b02, $b03, $b06, $b08, $b0a);

				$tex_id = $b03;
				vit_quad78pc($gp_file[8], $b0a, $src, $dst, $clr, $gp_ord);
				break;
			case 'vit_drag': // c
				// 0 1 2 3 4 5 6 7 8 9 a b
				// 1 - 1 1 2   2   2   2
				$b00 = $gp_ord($s1p, 0x00, 1);
				$b02 = $gp_ord($s1p, 0x02, 1); // 0 1 2 6
				$b03 = $gp_ord($s1p, 0x03, 1); // tex
				$b04 = $gp_ord($s1p, 0x04, 2);
				$b06 = $gp_ord($s1p, 0x06, 2); // *unused* , original s1 id
				$b08 = $gp_ord($s1p, 0x08, 2); // *unused* , original s0 id
				$b0a = $gp_ord($s1p, 0x0a, 2); // s8 id
				$DEBUG = subtrace(1, $b00, $b02, $b03, $b04, $b06, $b08, $b0a);

				$tex_id = $b03;
				vit_quad78pc($gp_file[8], $b0a, $src, $dst, $clr, $gp_ord);
				break;
			case 'vit_odin': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - - 1 1 1 1 - 2   2   2
				$b00 = $gp_ord($s1p, 0x00, 2);
				$b05 = $gp_ord($s1p, 0x05, 1);
				$b06 = $gp_ord($s1p, 0x06, 1); // 0 1 2 6
				$b07 = $gp_ord($s1p, 0x07, 1); // tex
				$b08 = $gp_ord($s1p, 0x08, 1);
				$b0a = $gp_ord($s1p, 0x0a, 2); // *unused* , original s1 id
				$b0c = $gp_ord($s1p, 0x0c, 2); // *unused* , original s0 id
				$b0e = $gp_ord($s1p, 0x0e, 2); // s9 id
				$DEBUG = subtrace(1, $b00, $b05, $b06, $b07, $b08, $b0a, $b0c, $b0e);

				$tex_id = $b03;
				vit_quad78pc($gp_file[9], $b0e, $src, $dst, $clr, $gp_ord);
				break;
		} // switch ( $gp_tag )

		$data = array();
		if ( ($flag & FLAG_SKIP) === 0 )
		{
			$data['DstQuad'] = $dst;
			if ( ! empty($clr) )
				$data['ColorQuad'] = $clr;

			if ( $flag & FLAG_VERTEX_COLOR )
			{
			}
			else
			{
				$data['SrcQuad'] = $src;
				$data['TexID'] = $tex_id;
			}
			if ( ! empty($DEBUG) )
				$data['_DEBUG_'] = $DEBUG;
		}

		$layer[$id] = $data;
	} // for ( $id=0; $id < $mbs['h']; $id++ )

	return $layer;
}

function key_list( &$json, $key_s )
{
	printf("== key_list( %x )\n", $key_s);
	global $gp_file, $gp_tag, $gp_ord;
	$list = array();

	$mbs = &$gp_file[$key_s];
	for ( $id=0; $id < $mbs['h']; $id++ )
	{
		echo "\n";
		$sub = substr($mbs['d'], $id*$mbs['k'], $mbs['k']);
			$s6p = &$sub;
			$s3p = &$sub;

		$layer_s  = 0;
		$layer_id = 0;
		$layer_no = 0;
		$flag = 0;
		$DEBUG = '';

		switch ( $gp_tag )
		{
			//key_s == 6
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
				$b00 = float32( $gp_ord($s6p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s6p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s6p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s6p, 0x0c, 4) ); // y2
				$b10 = $gp_ord($s6p, 0x10, 2); // s4 set id
				$b12 = $gp_ord($s6p, 0x12, 2); // s5 set id
				$b14 = $gp_ord($s6p, 0x14, 1); // s4 set no
				$b15 = $gp_ord($s6p, 0x15, 1); // s5 set no
				$b16 = $gp_ord($s6p, 0x16, 2);
				$DEBUG = subtrace(6, $b00, $b04, $b08, $b0c, $b10, $b12, $b14, $b15, $b16);

				$layer_s  = 4;
				$layer_id = $b10;
				$layer_no = $b14;
				break;
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
				$b00 = float32( $gp_ord($s6p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s6p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s6p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s6p, 0x0c, 4) ); // y2
				$b10 = $gp_ord($s6p, 0x10, 4); // s4 set id
				$b14 = $gp_ord($s6p, 0x14, 2); // s5 set id
				$b16 = $gp_ord($s6p, 0x16, 2); // s4 set no
				$b18 = $gp_ord($s6p, 0x18, 1); // s5 set no
				$b19 = $gp_ord($s6p, 0x19, 1);
				$DEBUG = subtrace(6, $b00, $b04, $b08, $b0c, $b10, $b14, $b16, $b18, $b19);

				$layer_s  = 4;
				$layer_id = $b10;
				$layer_no = $b16;
				break;

			//key_s == 3
			case 'psp_gran': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 - 1 1
			case 'vit_mura': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// f4      f4      f4      f4      2   2   1 - 1 1
				$b00 = float32( $gp_ord($s3p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s3p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s3p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s3p, 0x0c, 4) ); // y2
				$b10 = $gp_ord($s3p, 0x10, 2); // s1 set id
				$b12 = $gp_ord($s3p, 0x12, 2); // s2 set id
				$b14 = $gp_ord($s3p, 0x14, 1); // s1 set no
				$b16 = $gp_ord($s3p, 0x16, 1); // s2 set no
				$b17 = $gp_ord($s3p, 0x17, 1);
				$DEBUG = subtrace(3, $b00, $b04, $b08, $b0c, $b10, $b12, $b14, $b16, $b17);

				$layer_s  = 1;
				$layer_id = $b10;
				$layer_no = $b14;
				break;
			case 'vit_drag': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      3     - 2   1 - 1 1 - -
			case 'vit_odin': // 1c
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b
				// f4      f4      f4      f4      2   - - 2   1 - 1 1 - -
				$b00 = float32( $gp_ord($s3p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s3p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s3p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s3p, 0x0c, 4) ); // y2
				$b10 = $gp_ord($s3p, 0x10, 4); // s1 set id
				$b14 = $gp_ord($s3p, 0x14, 2); // s2 set id
				$b16 = $gp_ord($s3p, 0x16, 1); // s1 set no
				$b18 = $gp_ord($s3p, 0x18, 1); // s2 set no
				$b19 = $gp_ord($s3p, 0x19, 1);
				$DEBUG = subtrace(3, $b00, $b04, $b08, $b0c, $b10, $b14, $b16, $b18, $b19);

				$layer_s  = 1;
				$layer_id = $b10;
				$layer_no = $b16;
				break;
		} // switch ( $gp_tag )

		$data = array();
		if ( ($flag & FLAG_SKIP) === 0 )
		{
			$data['Layers'] = key_layers($layer_s, $layer_id, $layer_no);
			if ( ! empty($DEBUG) )
				$data['_DEBUG_'] = $DEBUG;
		}

		$list[$id] = $data;
	} // for ( $id=0; $id < $mbs['h']; $id++ )

	$json['KeyFrames' ] = $list;
	return;
}
//////////////////////////////
function anim_meta( $meta_s, $meta_id )
{
	global $gp_file, $gp_tag, $gp_ord;
	$meta = array();

	$mbs = &$gp_file[$meta_s];
	$sub = substr($mbs['d'], $meta_id*$mbs['k'], $mbs['k']);
		$s7p = &$sub;
		$s4p = &$sub;

	$move_x  = 0.0;
	$move_y  = 0.0;
	$scale_x = 1.0;
	$scale_y = 1.0;
	$rotate  = 0.0;
	$color   = 255;
	switch ( $gp_tag )
	{
		//meta_s == 7
		case 'ps2_grim': // 30
			// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
			// f4      f4      f4      f4      f4      f4      - - - - f4      f4      f4      f4      f4
		case 'ps2_odin': // 30
			// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
			// f4      f4      f4      f4      f4      f4      - - - - f4      f4      f4      f4      f4
 			$b00 = float32( $gp_ord($s7p, 0x00, 4) );
 			$b04 = float32( $gp_ord($s7p, 0x04, 4) );
 			$b08 = float32( $gp_ord($s7p, 0x08, 4) );
 			$b0c = float32( $gp_ord($s7p, 0x0c, 4) );
 			$b10 = float32( $gp_ord($s7p, 0x10, 4) ); // move x
 			$b14 = float32( $gp_ord($s7p, 0x14, 4) ); // move y
 			$b1c = float32( $gp_ord($s7p, 0x1c, 4) );
 			$b20 = float32( $gp_ord($s7p, 0x20, 4) );
 			$b24 = float32( $gp_ord($s7p, 0x24, 4) ); // rotate
 			$b28 = float32( $gp_ord($s7p, 0x28, 4) ); // scale x
 			$b2c = float32( $gp_ord($s7p, 0x2c, 4) ); // scale y
			$DEBUG = subtrace(7, $b00, $b04, $b08, $b0c, $b10, $b14, 0, $b1c, $b20, $b24, $b28, $b2c);

			$move_x  = $b10;
			$move_y  = $b14;
			$scale_x = $b28;
			$scale_y = $b2c;
			//$rotate  = float32($b24) * 360;
			break;
		case 'nds_kuma': // 24
			// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
 			// f4      f4      - - - - - - - - - - - - - - - - f4      f4      c4
		case 'wii_mura': // 24
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
 			$b00 = float32( $gp_ord($s7p, 0x00, 4) ); // move x
 			$b04 = float32( $gp_ord($s7p, 0x04, 4) ); // move y
 			$b08 = float32( $gp_ord($s7p, 0x08, 4) );
 			$b0c = float32( $gp_ord($s7p, 0x0c, 4) );
 			$b10 = float32( $gp_ord($s7p, 0x10, 4) );
 			$b14 = float32( $gp_ord($s7p, 0x14, 4) ); // rotate
 			$b18 = float32( $gp_ord($s7p, 0x18, 4) ); // scale x
 			$b1c = float32( $gp_ord($s7p, 0x1c, 4) ); // scale y
			$b20 = substr($s7p, 0x20, 4);
			$DEBUG = subtrace(7, $b00, $b04, $b08, $b0c, $b10, $b14, $b18, $b1c, bin2hex($b20));

			$move_x  = $b00;
			$move_y  = $b04;
			$scale_x = $b18;
			$scale_y = $b1c;
			$color   = colorhex32($b20);
			break;

		//meta_s == 4
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
 			$b00 = float32( $gp_ord($s4p, 0x00, 4) ); // move x
 			$b04 = float32( $gp_ord($s4p, 0x04, 4) ); // move y
 			$b0c = float32( $gp_ord($s4p, 0x0c, 4) );
 			$b10 = float32( $gp_ord($s4p, 0x10, 4) );
 			$b14 = float32( $gp_ord($s4p, 0x14, 4) ); // rotate
 			$b18 = float32( $gp_ord($s4p, 0x18, 4) ); // scale x
 			$b1c = float32( $gp_ord($s4p, 0x1c, 4) ); // scale y
			$b20 = substr($s4p, 0x20, 4);
			$DEBUG = subtrace(4, $b00, $b04, 0, $b0c, $b10, $b14, $b18, $b1c, bin2hex($b20));

			$move_x  = $b00;
			$move_y  = $b04;
			$scale_x = $b18;
			$scale_y = $b1c;
			$color   = colorhex32($b20);
			//$?  = float32($b0c);
			//$?  = float32($b10);
			//$rotate  = float32($b14) * 360;
			break;
	} // switch ( $gp_tag )

	if ( $move_x != 0.0 || $move_y != 0.0 )
		$meta['Move'] = array($move_x, $move_y);

	if ( $scale_x != 1.0 || $scale_y != 1.0 )
		$meta['Scale'] = array($scale_x, $scale_y);

	if ( $rotate != 0.0 )
		$meta['Rotate'] = $rotate;

	if ( $color !== 255 )
		$meta['Color'] = $color;

	return $meta;
}

function anim_frames( $frame_s, $frame_id, $frame_no )
{
	global $gp_file, $gp_tag, $gp_ord;
	$track = array();

	$mbs = &$gp_file[$frame_s];
	for ( $id=0; $id < $frame_no; $id++ )
	{
		$sub = substr($mbs['d'], ($frame_id+$id)*$mbs['k'], $mbs['k']);
			$s8p = &$sub;
			$s5p = &$sub;

		$meta_s  = 0;
		$meta_id = 0;

		switch ( $gp_tag )
		{
			//frame_s == 8-7
			case 'ps2_grim': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - - - - - 1 - - -
			case 'ps2_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$b0e = $gp_ord($s8p, 0x0e, 1); // 0 1 2  s5s3 interpolation
				$b0f = $gp_ord($s8p, 0x0f, 1);
				$b10 = $gp_ord($s8p, 0x10, 1); // 0 1 2  s7 interpolation
				$b11 = $gp_ord($s8p, 0x11, 1); // 0 1 2  s6 interpolation
				$b12 = $gp_ord($s8p, 0x12, 1); // 0 1 2
				$b13 = $gp_ord($s8p, 0x13, 1);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'nds_kuma': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - - 1 - - - - - - - - - - 1 - 2   - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'wii_mura': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   - 1 1 1 1 1 1 - - - - - - 1 - - - - - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b0a = $gp_ord($s8p, 0x0a, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'ps3_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   - 1 1 1 1 1 1 - - - - 1 - 1 - - - - - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'ps3_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   - - 2   1 1 1 1 1 1 1 1 - - - 1 - 1 - 1 - - 2
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'ps4_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'ps4_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 1 1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;
			case 'ps4_sent': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
				$b00 = $gp_ord($s8p, 0x00, 2); // s6 id
				$b04 = $gp_ord($s8p, 0x04, 2); // s7 id
				$b06 = $gp_ord($s8p, 0x06, 2); // fps
				$b08 = $gp_ord($s8p, 0x08, 2);
				$DEBUG = subtrace(8, $b00, $b04, $b06, $b08);

				$meta_s  = 7;
				$meta_id = $b04;
				break;

			//frame_s == 5-4
			case 'psp_gran': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 - - - - 1 - 1 - - - - -
				$b00 = $gp_ord($s5p, 0x00, 2); // s3 id
				$b04 = $gp_ord($s5p, 0x04, 2); // s4 id
				$b06 = $gp_ord($s5p, 0x06, 2); // fps
				$b08 = $gp_ord($s5p, 0x08, 2);
				$DEBUG = subtrace(5, $b00, $b04, $b06, $b08);

				$meta_s  = 4;
				$meta_id = $b04;
				break;
			case 'vit_mura': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - - - - - 1 - - - - - - -
				$b00 = $gp_ord($s5p, 0x00, 2); // s3 id
				$b04 = $gp_ord($s5p, 0x04, 2); // s4 id
				$b06 = $gp_ord($s5p, 0x06, 2); // fps
				$b08 = $gp_ord($s5p, 0x08, 2);
				$DEBUG = subtrace(5, $b00, $b04, $b06, $b08);

				$meta_s  = 4;
				$meta_id = $b04;
				break;
			case 'vit_drag': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 - 1 - - - 1 - - - - - - -
				$b00 = $gp_ord($s5p, 0x00, 2); // s3 id
				$b04 = $gp_ord($s5p, 0x04, 2); // s4 id
				$b06 = $gp_ord($s5p, 0x06, 2); // fps
				$b08 = $gp_ord($s5p, 0x08, 2);
				$DEBUG = subtrace(5, $b00, $b04, $b06, $b08);

				$meta_s  = 4;
				$meta_id = $b04;
				break;
			case 'vit_odin': // 20
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - - 2   2   2   - - 1 - 1 1 1 1 1 1 1 - - - 1 - 1 - 2   - -
				$b00 = $gp_ord($s5p, 0x00, 2); // s3 id
				$b04 = $gp_ord($s5p, 0x04, 2); // s4 id
				$b06 = $gp_ord($s5p, 0x06, 2); // fps
				$b08 = $gp_ord($s5p, 0x08, 2);
				$DEBUG = subtrace(5, $b00, $b04, $b06, $b08);

				$meta_s  = 4;
				$meta_id = $b04;
				break;
		} // switch ( $gp_tag )

		$data = array();
		anim_meta($meta_s, $meta_id);

		$track[$id] = $data;
	} // for ( $id=0; $id < $frame_no; $id++ )

	return $track;
}

function anim_tracks( $track_s, $track_id, $track_no )
{
	global $gp_file, $gp_tag, $gp_ord;
	$track = array();

	$mbs = &$gp_file[$track_s];
	for ( $id=0; $id < $track_no; $id++ )
	{
		$sub = substr($mbs['d'], ($track_id+$id)*$mbs['k'], $mbs['k']);
			$sap = &$sub;
			$s7p = &$sub;

		$frame_s  = 0;
		$frame_id = 0;
		$frame_no = 0;
		$flag = 0;
		$DEBUG = '';

		switch ( $gp_tag )
		{
			//track_s == 10
			case 'ps2_grim': // 8
				// 0 1 2 3 4 5 6 7
				// 2   1 - 2   1 -
			case 'ps2_odin': // 8
				// 0 1 2 3 4 5 6 7
				// 2   2   2   1 -
				$b00 = $gp_ord($sap, 0x00, 2); // s8 set id
				$b02 = $gp_ord($sap, 0x02, 2); // s8 set no
				$b04 = $gp_ord($sap, 0x04, 2); // s8 set sum[6]
				$b06 = $gp_ord($sap, 0x06, 1); // 0 1
				$DEBUG = subtrace(10, $b00, $b02, $b04, $b06);

				$frame_s  = 8;
				$frame_id = $b00;
				$frame_no = $b02;
				break;
			case 'nds_kuma': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   1 - 2   - - 4       1 - - -
			case 'wii_mura': // 10
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// 2   - 1 - - 2   4       1 - - -
				$b00 = $gp_ord($sap, 0x00, 2); // s8 set id
				$b02 = $gp_ord($sap, 0x02, 2); // s8 set no
				$b04 = $gp_ord($sap, 0x04, 4); // s8 set sum[6]
				$b08 = $gp_ord($sap, 0x08, 4);
				$b0c = $gp_ord($sap, 0x0c, 1); // 0 1
					$b08 = sint32($b08);
				$DEBUG = subtrace(10, $b00, $b02, $b04, $b08, $b0c);

				$frame_s  = 8;
				$frame_id = $b00;
				$frame_no = $b02;
				break;
			case 'ps3_drag': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   - - 2   4       4       1 - - -
				$b00 = $gp_ord($sap, 0x00, 2); // s8 set id
				$b02 = $gp_ord($sap, 0x02, 2); // s8 set no
				$b04 = $gp_ord($sap, 0x06, 2); // s8 set sum[6]
				$b08 = $gp_ord($sap, 0x08, 4);
				$b0c = $gp_ord($sap, 0x0c, 4);
				$b10 = $gp_ord($sap, 0x10, 1); // 0 1
					$b08 = sint32($b08);
					$b0c = sint32($b0c);
				$DEBUG = subtrace(10, $b00, $b02, $b04, $b08, $b0c, $b10);

				$frame_s  = 8;
				$frame_id = $b00;
				$frame_no = $b02;
				break;
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
				$b00 = $gp_ord($sap, 0x00, 2); // s8 set id
				$b02 = $gp_ord($sap, 0x02, 2); // s8 set no
				$b04 = $gp_ord($sap, 0x04, 4); // s8 set sum[6]
				$b08 = $gp_ord($sap, 0x08, 4);
				$b0c = $gp_ord($sap, 0x0c, 4);
				$b10 = $gp_ord($sap, 0x10, 2); // sb set id
				$b12 = $gp_ord($sap, 0x12, 1); // sb set no
				$b13 = $gp_ord($sap, 0x13, 1); // 0 1
				$b14 = $gp_ord($sap, 0x14, 1);
					$b08 = sint32($b08);
					$b0c = sint32($b0c);
				$DEBUG = subtrace(10, $b00, $b02, $b04, $b08, $b0c, $b10, $b12, $b13, $b14);

				$frame_s  = 8;
				$frame_id = $b00;
				$frame_no = $b02;
				break;

			//track_s == 7
			case 'psp_gran': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   2   - - 4       4       1 - - -
			case 'vit_mura': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   1 - 2   - - 4       4       1 - - -
			case 'vit_drag': // 14
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3
				// 2   2   2   - - 4       4       1 - - -
				$b00 = $gp_ord($s7p, 0x00, 2); // s5 set id
				$b02 = $gp_ord($s7p, 0x02, 2); // s5 set no
				$b04 = $gp_ord($s7p, 0x04, 2); // s5 set sum[6]
				$b08 = $gp_ord($s7p, 0x08, 4);
				$b0c = $gp_ord($s7p, 0x0c, 4);
				$b10 = $gp_ord($s7p, 0x10, 1); // 0 1
					$b08 = sint32($b08);
					$b0c = sint32($b0c);
				$DEBUG = subtrace(7, $b00, $b02, $b04, $b08, $b0c, $b10);

				$frame_s  = 5;
				$frame_id = $b00;
				$frame_no = $b02;
				break;
			case 'vit_odin': // 18
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7
				// 2   2   3     - 4       4       1 - 1 1 1 - - -
				$b00 = $gp_ord($s7p, 0x00, 2); // s5 set id
				$b02 = $gp_ord($s7p, 0x02, 2); // s5 set no
				$b04 = $gp_ord($s7p, 0x04, 3); // s5 set sum[6]
				$b08 = $gp_ord($s7p, 0x08, 4);
				$b0c = $gp_ord($s7p, 0x0c, 4);
				$b10 = $gp_ord($s7p, 0x10, 1); // s8 set id
				$b12 = $gp_ord($s7p, 0x12, 1); // s8 set no
				$b13 = $gp_ord($s7p, 0x13, 1); // 0 1
				$b14 = $gp_ord($s7p, 0x14, 1);
					$b08 = sint32($b08);
					$b0c = sint32($b0c);
				$DEBUG = subtrace(7, $b00, $b02, $b04, $b08, $b0c, $b10, $b12, $b13, $b14);

				$frame_s  = 5;
				$frame_id = $b00;
				$frame_no = $b02;
				break;
		} // switch ( $gp_tag )

		$track[$id] = anim_frames($frame_s, $frame_id, $frame_no);
	} // for ( $id=0; $id < $track_no; $id++ )

	return $track;
}

function anim_list( &$json, $anim_s )
{
	printf("== anim_list( %x )\n", $anim_s);
	global $gp_file, $gp_tag, $gp_ord;
	$list = array();

	$mbs = &$gp_file[$anim_s];
	for ( $id=0; $id < $mbs['h']; $id++ )
	{
		echo "\n";
		$sub = substr($mbs['d'], $id*$mbs['k'], $mbs['k']);
			$s9p = &$sub;
			$s6p = &$sub;

		$name = '';
		$track_s  = 0;
		$track_id = 0;
		$track_no = 0;
		$flag = 0;
		$DEBUG = '';

		switch ( $gp_tag )
		{
			//anim_s == 9
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
			case 'ps3_drag': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
				$b00 = float32( $gp_ord($s9p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s9p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s9p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s9p, 0x0c, 4) ); // y2
				$b10 = substr ($s9p, 0x10, 0x18);
				$b28 = $gp_ord($s9p, 0x28, 2); // sa set id
				$b2a = $gp_ord($s9p, 0x2a, 1); // sa set no
				$b2b = $gp_ord($s9p, 0x2b, 1); // sa set max[4]
				$b2c = $gp_ord($s9p, 0x2c, 1); // 0 1
					$b10 = rtrim($b10, ZERO);
				$DEBUG = subtrace(9, $b00, $b04, $b08, $b0c, $b10, $b28, $b2a, $b2b, $b2c);

				$name = $b10;
				$track_s  = 10;
				$track_id = $b28;
				$track_no = $b2a;

				if ( $b2c )
					$flag |= FLAG_SKIP;
				break;
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
				$b00 = float32( $gp_ord($s9p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s9p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s9p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s9p, 0x0c, 4) ); // y2
				$b10 = substr ($s9p, 0x10, 0x18);
				$b28 = $gp_ord($s9p, 0x28, 2); // sa set id
				$b2a = $gp_ord($s9p, 0x2a, 1); // sa set no
				$b2b = $gp_ord($s9p, 0x2b, 1); // sa set max[4]
				$b2c = $gp_ord($s9p, 0x2c, 2); // sb set id
				$b2e = $gp_ord($s9p, 0x2e, 1); // sb set no
				$b2f = $gp_ord($s9p, 0x2f, 1); // 0 1
					$b10 = rtrim($b10, ZERO);
				$DEBUG = subtrace(9, $b00, $b04, $b08, $b0c, $b10, $b28, $b2a, $b2b, $b2c, $b2e, $b2f);

				$name = $b10;
				$track_s  = 10;
				$track_id = $b28;
				$track_no = $b2a;

				if ( $b2f )
					$flag |= FLAG_SKIP;
				break;

			//anim_s == 6
			case 'psp_gran': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'vit_mura': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
			case 'vit_drag': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 1 - - -
				$b00 = float32( $gp_ord($s6p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s6p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s6p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s6p, 0x0c, 4) ); // y2
				$b10 = substr ($s6p, 0x10, 0x18);
				$b28 = $gp_ord($s6p, 0x28, 2); // s7 set id
				$b2a = $gp_ord($s6p, 0x2a, 1); // s7 set no
				$b2b = $gp_ord($s6p, 0x2b, 1); // s7 set max[4]
				$b2c = $gp_ord($s6p, 0x2c, 1); // 0 1
					$b10 = rtrim($b10, ZERO);
				$DEBUG = subtrace(6, $b00, $b04, $b08, $b0c, $b10, $b28, $b2a, $b2b, $b2c);

				$name = $b10;
				$track_s  = 7;
				$track_id = $b28;
				$track_no = $b2a;

				if ( $b2c )
					$flag |= FLAG_SKIP;
				break;
			case 'vit_odin': // 30
				// 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f 0 1 2 3 4 5 6 7 8 9 a b c d e f
				// f4      f4      f4      f4      c[18]                                           2   1 1 2   1 1
				$b00 = float32( $gp_ord($s6p, 0x00, 4) ); // x1
				$b04 = float32( $gp_ord($s6p, 0x04, 4) ); // y1
				$b08 = float32( $gp_ord($s6p, 0x08, 4) ); // x2
				$b0c = float32( $gp_ord($s6p, 0x0c, 4) ); // y2
				$b10 = substr ($s6p, 0x10, 0x18);
				$b28 = $gp_ord($s6p, 0x28, 2); // s7 set id
				$b2a = $gp_ord($s6p, 0x2a, 1); // s7 set no
				$b2b = $gp_ord($s6p, 0x2b, 1); // s7 set max[4]
				$b2c = $gp_ord($s6p, 0x2c, 2); // s8 set id
				$b2e = $gp_ord($s6p, 0x2e, 1); // s8 set no
				$b2f = $gp_ord($s6p, 0x2f, 1); // 0 1
					$b10 = rtrim($b10, ZERO);
				$DEBUG = subtrace(6, $b00, $b04, $b08, $b0c, $b10, $b28, $b2a, $b2b, $b2c, $b2e, $b2f);

				$name = $b10;
				$track_s  = 7;
				$track_id = $b28;
				$track_no = $b2a;

				if ( $b2f )
					$flag |= FLAG_SKIP;
				break;
		} // switch ( $gp_tag )

		$data = array();
		if ( ($flag & FLAG_SKIP) === 0 )
		{
			$data['Name'  ] = $name;
			$data['Tracks'] = anim_tracks($track_s, $track_id, $track_no);
			if ( ! empty($DEBUG) )
				$data['_DEBUG_'] = $DEBUG;
		}

		$list[$id] = $data;
	} // for ( $id=0; $id < $mbs['h']; $id++ )

	$json['Animations'] = $list;
	return;
}
//////////////////////////////
function vanilla( $fname )
{
	global $gp_tag;
	switch ( $gp_tag )
	{
		case 'ps2_grim':
		case 'ps2_odin':
		case 'nds_kuma':
		case 'wii_mura':
		case 'ps3_drag':
		case 'ps3_odin':
		case 'ps4_drag':
		case 'ps4_odin':
		case 'ps4_sent':
			$anim_s = 9;
			$key_s  = 6;
			break;

		case 'psp_gran':
		case 'vit_mura':
		case 'vit_drag':
		case 'vit_odin':
			$anim_s = 6;
			$key_s  = 3;
			break;
		default:
			return php_error('Unknown tag [%s] = %s', $gp_tag, $fname);
	} // switch ( $gp_tag )

	global $gp_data;
	if ( ! isset($gp_data[$gp_tag]) )
		return php_error('no data for tag [%s] = %s', $gp_tag, $fname);

	global $gp_file, $gp_ord;
	$gp_file = file_get_contents($fname);
	if ( empty($gp_file) )  return;

	$gp_ord  = $gp_data[$gp_tag]['ord'];
	$json = load_idtagfile( $gp_data[$gp_tag]['idtag'] );

	load_mbsfile($gp_file, $gp_data[$gp_tag]['sect'], $gp_ord);

	anim_list($json, $anim_s);
	key_list ($json, $key_s );

	save_quadfile($fname, $json);
	return;
}

$err = <<<_ERR
{$argv[0]}  TAG  MBP/MBS_FILE...
TAG
  ps2_grim  2007  PS2   GrimGrimoire
  ps2_odin  2007  PS2   Odin Sphere
  nds_kuma  2008  NDS   Kumatanchi
  wii_mura  2009  Wii   Muramasa - The Demon Blade
  ps3_drag  2013  PS3   Dragon's Crown
  ps3_odin  2016  PS3   Odin Sphere Leifthsar
  ps4_odin  2016  PS4   Odin Sphere Leifthsar
  ps4_drag  2018  PS4   Dragon's Crown Pro
  ps4_sent  2019  PS4   13 Sentinels: Aegis Rim

  psp_gran  2011  PSP   Gran Knights History
  vit_mura  2013  Vita  Muramasa Rebirth + DLC
  vit_drag  2013  Vita  Dragon's Crown
  vit_odin  2016  Vita  Odin Sphere Leifthsar

  Upcoming
  swi_sent  2022  Swit  13 Sentinels: Aegis Rim
  swi_grim  2022  Swit  GrimGrimoire OnceMore
  swi_grim  2022  PS4   GrimGrimoire OnceMore

_ERR;

if ( $argc == 1 )  exit($err);
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		vanilla($argv[$i]);
	else
		$gp_tag = $argv[$i];
}

/*
	global $gp_file, $gp_tag, $gp_ord;
		switch ( $gp_tag )
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

			case 'psp_gran':
			case 'vit_mura':
			case 'vit_drag':
			case 'vit_odin':

			//case 'con_name':
				break;
		} // switch ( $gp_tag )
 */
