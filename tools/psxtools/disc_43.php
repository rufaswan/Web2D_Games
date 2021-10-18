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
 *   ScummVM
 *   https://github.com/scummvm/scummvm/tree/master/engines/tinsel/graphics.cpp
 */
require "common.inc";
require "disc.inc";

//////////////////////////////
function dwn_rle2( &$file, $pos, $siz )
{
	// SLES 015.49 , sub_800554d8
	$dec = '';
	while ( $siz > 0 )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$b1 = $b & 0xc0;
		$b2 = $b & 0x3f;

		switch ( $b1 )
		{
			case 0xc0:
				$dec .= str_repeat(BYTE, $b2*2);
				break;
			case 0x80:
				$b = substr($file, $pos, 2);
					$pos += 2;
				$dec .= str_repeat($b, $b2);
				break;
			case 0x40:
				$dec .= str_repeat(ZERO, $b2*2);
				break;
			default:
				$dec .= substr($file, $pos, $b2*2);
				$pos += ($b2 * 2);
				break;
		} // switch ( $b1 )

		$siz -= ($b2 * 2);
	} // while ( $siz > 0 )

	$dec = pal555($dec);
	return $dec;
}

function dwn_rle1( &$file, $pos, $siz )
{
	// SLES 015.49 , sub_800551e0
	$dec = '';
	while ( $siz > 0 )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$b1 = $b & 0xc0;
		$b2 = $b & 0x3f;

		switch ( $b1 )
		{
			case 0xc0:
				$dec .= str_repeat(BYTE, $b2);
				break;
			case 0x80:
				$b = $file[$pos];
					$pos++;
				$dec .= str_repeat($b, $b2);
				break;
			case 0x40:
				$dec .= str_repeat(ZERO, $b2);
				break;
			default:
				$dec .= substr($file, $pos, $b2);
				$pos += $b2;
				break;
		} // switch ( $b1 )

		$siz -= $b2;
	} // while ( $siz > 0 )

	return $dec;
}

function dwn_rle0( &$file, $pos, $siz )
{
	// SLES 015.49 , sub_80056be4
	// index for global palette
	$clr = substr($file, $pos, 0x10);
		$pos += 0x10;

	$dec = '';
	$buf = array();
	while ( $siz > 0 )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$b1 = $b & 0x80;
		$b2 = $b & 0x7f;

		if ( $b1 )
		{
			get_big4($buf, $file, $pos, 1);

			$b = array_shift($buf);
			$dec .= str_repeat($clr[$b], $b2);
			$siz -= $b2;
		}
		else
		{
			get_big4($buf, $file, $pos, $b2);

			for ( $i=0; $i < $b2; $i++ )
			{
				$b = array_shift($buf);
				$dec .= $clr[$b];
				$siz--;
			} // for ( $i=0; $i < $b2; $i++ )
		}
	} // while ( $siz > 0 )

	return $dec;
}

function dwn_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[0x19]) )  return; //
	if ( ! isset($sect[   6]) )  return; // sprite data -> 19 , 5 optional
	printf("== dwn_scn( $dir )\n");

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x14);
			$st6 += 0x14;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = $b1;
			$h = $b2;

		$b1 = str2int($sub6,  8, 4);
		$b2 = str2int($sub6, 12, 4);
		$b3 = str2int($sub6, 16, 4);
			$st19p = $b1 & BIT24;
			$st19c = $b3 & BIT24;
			$st19h = ($b2 >> 24) & BIT4; // if skip height * 4

		$img = array('w' => $w , 'h' => $h);
		switch ( $st19h )
		{
			case 0: // 4-bpp , indexed
				$img['cc']  = str2int($file, $st19c+0, 4);
				$img['pal'] = substr ($file, $st19c+4, $img['cc']*4);
				palbyte( $img['pal'] );

				$img['pix'] = dwn_rle0($file, $st19p, $w*$h);
				break;
			case 1: // 8-bpp , indexed
				$img['cc']  = str2int($file, $st19c+0, 4);
				$img['pal'] = substr ($file, $st19c+4, $img['cc']*4);
				palbyte( $img['pal'] );

				$st19p += ($h * 4);
				$img['pix'] = dwn_rle1($file, $st19p, $w*$h);
				break;
			case 2: // 16-bpp , RGB
				$st19p += ($h * 4);
				$img['pix'] = dwn_rle2($file, $st19p, $w*$h*2);
				break;
			default:
				return php_error("UNKNOWN st19h %x", $st19h);
		} // switch ( $st19h )

		save_clutfile("$fn.clut", $img);

	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function dw2_pak( &$file, $pos, $siz, $clr )
{
	// SCUS 946.05 , sub_80043e04
	echo debug($clr, 'pakpal');

	$dec = '';
	$buf = array();

	while ( $siz > 0 )
	{
		$b = ord( $file[$pos] );
			$pos++;

		$b1 = $b & 0x80;
		$b2 = $b & 0x7f;

		if ( $b1 )
		{
			get_big4($buf, $file, $pos, 1);

			$b = array_shift($buf);
			$dec .= str_repeat($clr[$b], $b2);
			$siz -= $b2;
		}
		else
		{
			get_big4($buf, $file, $pos, $b2);

			for ( $i=0; $i < $b2; $i++ )
			{
				$b = array_shift($buf);
				$dec .= $clr[$b];
				$siz--;
			} // for ( $i=0; $i < $b2; $i++ )
		}
	} // while ( $siz > 0 )

	return $dec;
}

function dw2_rle( &$file, $pos, $siz )
{
	// SCUS 946.05 , sub_800433f0
	$dec = '';
	while ( $siz > 0 )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$b1 = $b & 0x80;
		$b2 = $b & 0x7f;

		if ( $b1 )
		{
			$b = $file[$pos];
				$pos++;
			$dec .= str_repeat($b, $b2);
		}
		else
		{
			$dec .= substr($file, $pos, $b2);
			$pos += $b2;
		}
		$siz -= $b2;
	} // while ( $siz > 0 )
	return $dec;
}

function dw2_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[0x19]) )  return; //
	if ( ! isset($sect[   5]) )  return; // palette
	if ( ! isset($sect[   6]) )  return; // sprite data -> 5
	printf("== dw2_scn( $dir )\n");

	// SHARED PALETTE w/backgrounds
	// using US title screen palette @ 5a598
	//   00 - 10  common object
	//   11 - bf  background / unique per map
	//   c0 - df  common object
	//   e0 - ef  luggage   / per map?
	//   f0 - ff  rincewind / per map?
	//$sys = 'ff00000083000000008300008383000000008300830083000083830083838300c3c3c300ff00000000ff0000ffff00000000ff00ff00ff0000ffff00ffffff00000000000000000000000000430b1300531b2300330b13005b333b004b2b3300731b330087334b009f4b6300732b430087234b00633b4b00731b43008f43630097235b00331323003b332b00af33730087235b00af2b7b0087533b00bf338f00bf7ba7006b536300c7339f00978f4300ffe7b7007b2363008f2b7300d733af00972b87009f2b9700a733a7002b132b0043334300735b7300b79fb700635b6300877b87007b737b00af33bf00bf3bd700d743ff00c743ef004b235b0073637b00b7a7bf007b6b8700231b2b002b1b3b00330b63000b001b00978fa700cfc7df0023134b002b0b7b0033235b00231b3b00e7dfff0023135300130b2b006b6387007b739700d7cfef0023135b00230b8f00230b9f00130b3b001b1343001b134b00231b5b001b135b001b135300231b6b00ffe7b7002b236b002b236300332b6b003b337b002b237300130bbf005b3333005b2b2300000013000b0bd7003b3bff002b2b6b0013132b003333730033336b003b3b73003b3b6b0043437b008f8fff0043437300535373006b6b97008787af008f8fb7006b6b7300000bff002b33ff00434b87008f97cf008f97c70043539f00535b8700435baf0097a7df004363bf004363b7005b6b9700d7d70000cfcf0000b7b700008f8f000087870000737300005b5b0000333300002b2b000023230000dfdf0b00c7c70b00b7b70b00afaf0b0097970b00efef1300afaf130043430b007b7b1300f7f72300efef2300dfdf23006b6b1300ffff2b0097971b00f7f72b00c7c7230087871b00dfdf2b00afaf230053531300cfcf2b0063631b00efef4300ffff4b0053531b00dfdf4b00c7c74300a7a73b0087873300ffff630063632b00e7e76300ffff7b008787430013130b00dfdf7300bfbf63009f9f5300ffff8f00a7a76300e7e78f00ffffaf00bfbf87005b5b430063634b0087876300ffffc700ffffef00f7f7e700fffff700afa70000330b0000531b1300a7a7af00cb5300006b2f0000834b00009f6b0000d78b5b00ffd30000a7430000f36300006700a3008b00ff00bf00ff00ff83a7005b4b7b006363af003b732b0053af430063c78700ffeb8700c7733b009b000000ab2793009797d700d7f7d700ff53bf0097879b0000001f00070023000f0727001b0f2f00571f0b00632b0b00733b1300974b1300c79f1b00ffc70000ffdf2b00bf835f00dfa78300ffc79f00736b6b008b8b8b00b7b7b700afbbe700c78bd70000ff000043130b003b2b7b002b43d700732b1b008b432b00635b73008f879f0083000b0097000b00d75b0000a75b3f00cf7b5b00c77b1b00df971b00e7c7cf00f7e7d700';
		//$sys = hex2bin($sys);
		//palbyte($sys);

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w   = $b1 & 0x0fff;
			$h   = $b2 & 0x0fff;
			$pak = $b2 & 0xf000;

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st19 = $b1;
			$st5  = $b2;

		$fn = sprintf("%s/%d_%d/%04d.clut", $dir, ($st5 !== 0), $pak >> 12, $id6-1);
		$img = array(
			'cc' => 0x100,
			'w'  => $w,
			'h'  => $h,
		);

		if ( $st5 !== 0 )
		{
			$img['pal'] = substr($file, $st5, 0x400);
			palbyte( $img['pal'] );
		}
		else
		{
			$img['pal'] = dw2_syspal($file, $sect[5], $sect[6]);
		}

		switch ( $pak )
		{
			case 0xc000: // +10 clut , 4-bpp
				$clr = substr($file, $st19, 0x10);
					$st19 += 0x10;
				$img['pix'] = dw2_pak($file, $st19, $w*$h, $clr);
				break;

			//case 0x8000:
				//break;

			case 0x4000: // +1 clut , 4-bpp
				$b1 = ord( $file[$st19] );
					$st19++;

				$clr = '';
				for ( $i=0; $i < 0x10; $i++ )
				{
					$b2 = ($b1 - 1 + $i) & BIT8;
					$clr .= chr($b2);
				}
				$img['pix'] = dw2_pak($file, $st19, $w*$h, $clr);
				break;

			case 0: // +0 clut , 8-bpp
				$img['pix'] = dw2_rle($file, $st19, $w*$h);
				break;

			default:
				return php_error("UNKNOWN pak %x", $pak);
		} // switch ( $pak )

		save_clutfile($fn, $img);
	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function dw1_rle( &$file, $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle )
{
	printf("== dw1_rle( %s , %x , %x , %x , %x , %x , %d , %d )\n", $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle);

	$pix = COPYPIX_DEF($w,$h);
	$pix['src']['w'] = 4;
	$pix['src']['h'] = 4;

	// set pallete data
	if ( $bpp == 8 )
	{
		$b1 = substr($file, $st5, 0x400);
		palbyte($b1);
		$pix['src']['pal'] = $b1;
	}
	if ( $bpp == 4 )
	{
		$b1 = substr($file, $st5, 0x20);
		$b1 = pal555($b1);
		$pix['src']['pal'] = $b1;
	}

	// set pixel data
	$pcnt = 0;
	$ptyp = 0;
	$pind = 0;
	for ( $y=0; $y < $h; $y += 4 )
	{
		for ( $x=0; $x < $w; $x += 4 )
		{
			$pix['dx'] = $x;
			$pix['dy'] = $y;
			$ssid = 0;

			if ( $rle ) // 0xcc
			{
				if ( $pcnt <= 0 )
				{
					$b1 = str2int($file, $st3, 2);
						$st3 += 2;

					$pcnt = $b1 & 0x3fff;
					$ptyp = 0;
					if ( $b1 & 0x8000 )  $ptyp = 1; // DUP
					if ( $b1 & 0x4000 )  $ptyp = 2; // ID++

					if ( $ptyp != 0 )
					{
						$pind = str2int($file, $st3, 2);
							$st3 += 2;
					}
				} // if ( $pcnt == 0 )

				switch ( $ptyp )
				{
					case 0:
						$ssid = str2int($file, $st3, 2);
							$st3 += 2;
						break;
					case 1:
						$ssid = $pind;
						break;
					case 2:
						$ssid = $pind;
						$pind++;
						break;
				} // switch ( $ptyp )

				$pcnt--;
			}
			else // 0xdd
			{
				$ssid = str2int($file, $st3, 2);
					$st3 += 2;
			}

			if ( $bpp == 8 )
				$pix['src']['pix'] = substr($file, $st4+$ssid*16, 16);
			else
			if ( $bpp == 4 )
			{
				$pix['src']['pix'] = substr($file, $st4+$ssid*8, 8);
				bpp4to8( $pix['src']['pix'] );
			}

			copypix_fast($pix);
		} // for ( $x=0; $x < $w; $x += 4 )
	} // for ( $y=0; $y < $h; $y += 4 )

	savepix($fn, $pix, false);
	return;
}

function dw1_scn( &$file, &$sect, $dir )
{
	if ( ! isset($sect[3]) )  return; // tile data   -> 4
	if ( ! isset($sect[4]) )  return; // 4x4 pixel data
	if ( ! isset($sect[5]) )  return; // palette
	if ( ! isset($sect[6]) )  return; // sprite data -> 3,5
	printf("== dw1_scn( $dir )\n");

	$st48 = str2int($file, $sect[3]+0, 4);
	$b1   = str2int($file, $sect[3]+4, 4);
	$st44 = $st48 + $b1 * 16;

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
	$id6 = 0;
	while ( $st6 < $ed6 )
	{
		$fn = sprintf("%s/%04d", $dir, $id6);
			$id6++;

		$sub6 = substr($file, $st6, 0x10);
			$st6 += 0x10;
		echo debug($sub6, $fn);

		$b1 = str2int($sub6, 0, 2);
		$b2 = str2int($sub6, 2, 2);
			$w = int_ceil($b1 & 0x7fff, 4);
			$h = int_ceil($b2 & 0x7fff, 4);

		$b1 = str2int($sub6,  8, 3);
		$b2 = str2int($sub6, 12, 3);
			$st3 = $b1 & 0xfffff;
			$st5 = $b2 & 0xfffff;

		// from SCUS_946.00 , sub_8001588c
		$bpp = -1;
		if ( $file[$st3+0] == "\x88" )  $bpp = 8;
		if ( $file[$st3+0] == "\x44" )  $bpp = 4;

		$rle = -1;
		if ( $file[$st3+1] == "\xcc" )  $rle = 1;
		if ( $file[$st3+1] == "\xdd" )  $rle = 0;

		if ( $bpp < 0 || $rle < 0 )
			return php_error("UNKNOWN st3 type %d , %d", $bpp, $rle);

		if ( $bpp == 8 )
		{
			$st4 = $st48;
			$st3 += 2;
		}
		if ( $bpp == 4 )
		{
			$st5 = $st3 + 2;
			$st4 = $st44;
			$st3 += (2 + 32);
		}
		dw1_rle($file, $fn, $st3, $st4, $st5, $w, $h, $bpp, $rle);

	} // while ( $st6 < $ed6 )
	return;
}
//////////////////////////////
function disc( $tag, $fname )
{
	if ( empty($tag) )
		return php_error("NO TAG");

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir  = str_replace('.', '_', $fname);
	$sect = scnsect($file);

	# http://rewiki.regengedanken.de/wiki/.SCN
	#   DW1 3 4 5 - c d -  -  -  -  -  -  -  -  -  -  -
	#   DW2 - - 5 - c d f 12 13  - 19 1b 1c 1d 1e  -  -
	#   DWN - - - 9 - - f  -  - 18 19 1b 1c 1d  - 20 31
	#   psx - - p - p - -  -  -  -  -  -  -  p  p  -  -
	# psx = Playstation One release of Discworld Noir
	//save_txt($file, $sect, "{$dir}_txt");

	switch ( $tag )
	{
		case 'dw1':
			return dw1_scn($file, $sect, "{$dir}_gfx");
		case 'dw2':
			return dw2_scn($file, $sect, "{$dir}_gfx");
		case 'dwn':
			return dwn_scn($file, $sect, "{$dir}_gfx");
	}
	return;
}

printf("%s  [-dw1/-dw2/-dwn]  FILE\n", $argv[0]);
$tag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-dw1':  $tag = 'dw1'; break;
		case '-dw2':  $tag = 'dw2'; break;
		case '-dw3':
		case '-dwn':  $tag = 'dwn'; break;
		default:
			disc( $tag, $argv[$i] );
			break;
	} // switch ( $argv[$i] )
}

/*
discworld 2
	RAM 800bb9d0 =     dw2.scn/ 7de4
	RAM 800c8600 = objects.scn/122bc
	RAM 801a0c7c =   title.scn/59d90
		 55,1d  5896e  801f95ea
		 55,1d  591c6  801f9e42
		 55,1d  59069  801f9ce5
			80043ee4  lbu v0[ 0], 0(s1[801f9ce5])
		140,f0     14  801a0c90
		140,d8  216fa  801c2376
		140,d8  11f70  801b2bec
		140,d8  39b62  801da7de
		140,d8  492ab  801e9f27
		140,d8  30e70  801d1aec
			800434a0  lbu s1[ed], 0(s0[801d1aec])

sub_800433f0 - 800438e8
	sb = 435a0  435e4  4360c
		435a0 [55] 1d91c7 -> 7e068
		4360c  ++  1d91c8 -> 7e06b
		435a0 [55] 1d91d8 -> 7e079
		4360c  ++  1d91d9 -> 7e07d
		435a0 [56] 1d91df -> 7e081
		4360c  ++  1d91e2 -> 7e090
		435a0 [57] 1d9230 -> 7e0dc

sub_80043e04 - 80044588
	sb 1f8003f0 = 43ef0  43f24
	sb 8007e068 = 44130  44190  441e0

55 = 0*23  10*5  0*3  10*5  0*25
55 = 0*1d  10*4  0  10*2  c5
	c8*2  c5  10  c5  10  c5
	c8*2  c5  10*2  0  10*4  0*1f
55 = 0*1c  10  c7  c8  c7  10*2  c5  10
	c8  c5  10  c8  c5  10
	c5  c8  10  c5  c8  10
	c5  10  10  c7  c8  c7  10  0*1e
55 = 0*15  10*7  c7  ff*2  c8  c7
	10  c8  10  c5  c8  c5
	10  c8  10  c8  10  c5  c8  c5
	10  c8  10  c7  ff*2
	c8  c7  10*8  0*16

no palette / sect[5]
	act[1-4].scn
	dw2.scn
	lug[01-10].scn
	objects.scn
	rw[01-10].scn
	title.scn => 3 palettes



discworld noir
	RAM 801005d0 =   4.scn/ 3120
	RAM 801b48e0 = 315.scn/48100/title
		140,d8  3e67c+360  801f32bc
			80055320  lbu v1[ff], 0(s2[801f32bc])
		140,d8  2e5c4+360  801e3204
			80055320  lbu v1[ 1], 0(s2[801e3204])
		140,d8     14+360  801b4c54
		140,d8   b9fc+360  801c063c
		140,d8  1726c+360  801cbeac
		140,d8  22bf0+360  801d7830

	font = VRAM 80780 [3c0,100]
		== 4.scn
		!  5 x 9  125  1064  80101634
			80056ca0  lbu a3[2d] 0(s1[80101644])
		"  5 x 4  126  108c  8010165c
		#  7 x 9  127  10a4  80101674
		$  7 x 9  128  10d8  801016a8
		%  7 x 8  129  1108  801016d8

sub_800551e0 - 800554d8
	sh = 5536c  553e4

sub_80056be4 - 80056f84
	sb 1f8003f0 = 56c54
	sb gp = 56d40  56d44  56dc8  56dcc

	RAM 801005d0 =   4.scn
	RAM 8019e030 = 114.scn/lewton office
		140,d8     14+360  8019e3a4
		 8a,d8  14f7c+360  801b330c
		  f, 6  39b90+18   801d4bd8
		  a, 4  39c24+10   801d4c64
		  9, 9  16f78+24   801b4fcc
		  9, 9  172e8+24   801b533c
		  9, 9  17028+24   801b507c
		  9, 9  170d8+24   801b512c
		  9, 9  17188+24   801b51dc
			80055a28  lbu v0[41], 0(a0[801b51dc])
		  9, 9  17238+24   801b528c
		 3b,60  38134+180  801d62e4
		 10, 4  2c8fc+10   801ca93c

sub_800554d8 - 80055918
	sh = 5572c 5581c
 */
