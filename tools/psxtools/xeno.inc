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

function xeno_decode( &$file )
{
	$dec = '';
	trace("== begin sub_80032cac()\n");

	$lw = str2int($file, 0, 4);
	$st = 4;

	$bycod = 0;
	$bylen = 0;
	while ( $lw > 0 )
	{
		// no more data to decode
		// the rest is padding for 8-bit bycod
		if ( ! isset( $file[$st] ) )
			break;

		//trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$st] ); // t8
				$st++;
			$bylen = 8; // t9
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = ord( $file[$st+0] ); // t0
			$b2 = ord( $file[$st+1] ); // t4
				$st += 2;
			$dpos = (($b2 & 0xf) << 8) | $b1;
			$dlen =  ($b2 >> 4)  + 3;

			for ( $i=0; $i < $dlen; $i++ )
			{
				$p = strlen($dec) - $dpos;
				$dec .= $dec[$p];
				$lw--;
			}
		}
		else
		{
			$b1 = $file[$st]; // t0
				$st++;
			$dec .= $b1;
			$lw--;
		}
	} // while ( $st < $ed )
	trace("== end sub_80032cac()\n");

	$file = $dec;
	return;
}
//////////////////////////////
function xeno_enc_dict( &$dict )
{
	$cnt = count($dict);
	if ( $cnt < 1 )
		return '';

	$enc = '';
	for ( $pos = 0; $pos < $cnt; $pos += 8 )
	{
		$flg = 0;
		$dat = '';
		for ( $k=0; $k < 8; $k++ )
		{
			$p = $pos + $k;
			$v = $dict[$p];

			// is DICT = flag 1
			if ( (int)$v === $v )
			{
				$flg |= (1 << $k);
				$dat .= chrint($v, 2);
			}
			// is COPY = flag 0
			else
				$dat .= $v;
		} // for ( $k=0; $k < 8; $k++ )

		$enc .= chr($flg) . $dat;
	} // for ( $p=0; $p < $cnt; $p += 8 )

	return $enc;
}

function xeno_enc_search( &$file, $pos )
{
	$len = strlen($file);
	$find_len = 0xf + 3;
	if ( ($pos + $find_len) > $len )
		$find_len = $len - $pos;

	$find_pos = $pos - 0xfff;
	if ( $find_pos < 0 )
		$find_pos = 0;

	while ( $find_len >= 3 )
	{
		$seek = substr($file, $pos , $find_len);
		$skps = strpos($file, $seek, $find_pos);
		if ( $skps < $pos )
		{
			$dpos = $pos - $skps;
			return array($dpos, $find_len);
		}
		$find_len--;
	} // while ( $find_len >= 3 )

	return -1;
}

function xeno_encode( &$file )
{
	$len = strlen($file);

	$dict = array();
	$pos = 0;
	while ( $pos < $len )
	{
		$src = xeno_enc_search($file, $pos);
		if ( $src === -1 )
		{
			// flag 0 copy
			$dict[] = $file[$pos];
			$pos++;
		}
		else
		{
			// flag 1 dict
			list($dpos,$dlen) = $src;

			$dict[] = (($dlen - 3) << 12) | $dpos;
			$pos += $dlen;
		}
	} // while ( $pos < $len )

	// on decode, EOF check is on reading flags
	$pad = 0;
	while ( count($dict) & 7 )
	{
		$pad++;
		$dict[] = ZERO;
	}

	$bin  = xeno_enc_dict($dict);
	$enc  = chrint($len + $pad, 4);
	$enc .= substr($bin, 0, strlen($bin) - $pad);

	// data aligned to 4-bytes
	while ( strlen($enc) & 3 )
		$enc .= ZERO;

	$file = $enc;
	return;
}

/*
xeno encode note
	data = 00 00 00 00
	enc
		01 flag 0  00
		02 flag 1  pos -1 , len 0+3
		04 flag 0  -
		08 flag 0  -
		10 flag 0  -
		20 flag 0  -
		40 flag 0  -
		80 flag 0  -
	= size = 0a , chunk = flag/02  00  0001  -  -  -  -  -  -
	NOTE : EOF check is on reading flags
		size must be padded so pEND === size when reading flags

xeno demo
	1
	12
	23
	40
	553
	586
	589
	654
		655-739  tim dialog portrait
	751
	768
		769-1750 map data + 1200/1201 tex pair
	2305
		2306-2380 3d model
	2434
		2435-2508 spr2 fei event sprite
	2563
		2564-2569 tim gallery
	2644
	2649
		2650-2737 spr1+3d battle sprite
	2739
	2742
	2855
		2856-2899 3d model
	2993
	3190
	3661
	3678
	3695
	3702
//////////////////////////////
battle vierge data
	RAM hp 8006e154  fuel 8006e12c
	after AEROD animation
		801e2a38  lw   v0[   0], 0x60(t1[800ccefc])
		801e2a48  lhu  v1[ 258], 0x38(t1[800ccefc])
		801e2a54  sh   v1[ 258], 0x38(a3[8006e0f4])
		801e2a58  sw   a0[37dc], 0x60(a3[8006e0f4])
	RAM 800ccf34
		8008ce88  lhu   s5[ 190], a1[ c80](800cef34)
		8008cf48  subu  v0[ 258], s5[ 190]
		8008cf54  sh    v0[ 258], s2[ 170](800ccdc4)
	RAM 800cfbb4
		8003f9e4  lbu  v0[90], 0(a1[801c4dc1])
		8003f9f4  sb   v0[90], 0(a0[800cfbb4])
	RAM 801c4dc0
		80032ed8  lbu  t0[90], 0(a0[801de58c])
		80032eec  sb   t0[90], 0(a1[801c4dc0])
	ISO
		1d3650cc  2594.bin + 48cc
		1d365eac  2594.bin + 56ac
		1d44afdc  2614.bin + 87dc / 18.bin
		1d44bdac  2614.bin + 95ac / 26.bin
//////////////////////////////
https://ffhacktics.com/smf/index.php?msg=219487
Fast text mod
	68 -- 02 92 = lbu v0, 0x68(s0)
	ff -- 02 34 = li  v0, 0xff

	XENO-JP-1  RAM 80034788 , +24f88
	XENO-US-1  RAM 80034964 , +25164

	21 10 43 --  68 -- 02 92  -- -- -- --
//////////////////////////////
Debug mode
# title screen == 01ea
	XENO-JP-1  RAM 8004e9f0
	800776d8  lhu  v1[ 1ea], 0(8006efde)
	80077704  sw   v1[ 1ea], 0(8004e9f0)

	XENO-US-1  RAM 8004f34c
	80078044  lhu  v1[ 1ea], 0(8006f94e)
	80078070  sw   v1[ 1ea], 0(8004f34c)
//////////////////////////////
Save anywhere
	# Open MENU first!
	XENO-JP-1  RAM 80058811  00 -> 01
	XENO-US-1  RAM 80059171  00 -> 01
//////////////////////////////
MDEC_rest
	19.bin
	591.bin
	RAM 8010f798
	RAM 801d3000

 */
