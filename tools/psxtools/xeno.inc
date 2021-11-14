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
	trace("== begin sub_80032cac\n");
	$ed = strlen($file);
	$st = 0;

	//$lw = str2int($file, $st, 3);
		$st += 4;
	$bycod = 0;
	$bylen = 0;
	$dec = '';
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] ); // t8
				$st++;
			trace("BYTECODE %2x\n", $bycod);
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
			$pos = ($b2 & 0xf) << 8;
				$pos |= $b1;
			$len = ($b2 >> 4) + 3;
			trace("REF  POS -%d LEN %d\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = strlen($dec) - $pos;
				$dec .= $dec[$p];
			}
		}
		else
		{
			$b1 = $file[$st]; // t0
				$st++;
			trace("COPY %2x\n", ord($b1));
			$dec .= $b1;
		}
	} // while ( $st < $ed )
	trace("== end sub_80032cac\n");

	$file = $dec;
	return;
}

/*
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
xeno jp1 / slps 011.60
	0
		1 -17  STR movie
		19  MDEC_rest
		21  SYSTEM.CNF
		22  SLPS_011.60
	23
	34
	51
	116  .wds
	133  .wds/.smds
	390  .seds
	423
	426  compressed spr2 overworld
	491  tim9 faces
	588
	605  map 3d model + tex/spr
	2142  3d models
	2335  spr2
	2464  tim2 event
	2612
	2617  spr1 monsters bosses + 3d models
	2771
	2774  3d models
	2925  spr2 battle + 3d models
		2988 3005  fei
		2989 3006  elly
		2990 3007  bart
		2991 3008  citan    , 2997 3014 (sword)
		2992 3009  billy
		2993 3010  rico
		2994 3011  emeralda , 2998 3015 (adult)
		2995 3012  chuchu
		2996 3013  maria
	3148  3d models
	3380  spr2 battle + code + tex
	3921  spr2 overworld char
		3923  fei
		3924  elly
		3925  citan
		3926  bart
		3927  billy
		3928  rico
		3929  emeralda , 3933 (adult)
		3930  chuchu   , 3947 (gear)
		3931  maria
	3938  spr2 overworld gear
		3940  weltall    , 3941 (weltall 2) , 3955 (xenogear)
		3942  vierge
		3943  heimdal    , 3951 (e-fenrir)
		3944  brigandier , 3952 (e-andvari)
		3945  renmazuo   , 3953 (e-renmazuo)
		3946  stier      , 3954 (e-stier)
		3948  siebzehn
		3949  crescens
	3956
	3959
	3963  world map
	3974  world map
	3985  world map
	3996  world map
	4007  world map
	4018  world map
	4029  world map
	4040  world map
	4051  world map
	4062  world map
	4073  world map
	4084  world map
	4095  world map
	4106  world map
	4117  world map
	4128  world map
	4139  world map
xeno jp2 / slps 011.61
	2610-2761  spr1 monsters bosses , 3d models
	2980-3009  spr2 party
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
	68 00 02 92 = lbu v0, 0x68(s0)
	ff 00 02 34 = li  v0, 0xff

	XENO-JP-1  RAM 80034788
	XENO-US-1  RAM 80034964
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
