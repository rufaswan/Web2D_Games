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
/*
 * prefixes
 *  pl00  ryu
 *  pl01  ken
 *  pl02  chun-li
 *  pl03  guile
 *  pl04  zangief
 *  pl05  dhalsim
 *  pl06  e.honda
 *  pl07  blanka
 *  pl08  balrog (claw)
 *  pl09  sagat
 *  pl0a  vega (dictator)
 *  pl0b  sakura
 *  pl0c  cammy
 *  pl0d  *unlock* gouki
 *  pl0e  *unlock* morrigan
 *  pl0f  *unlock* evil ryu
 *  pl10  *+ex* kyo
 *  pl11  iori
 *  pl12  terry
 *  pl13  ryo
 *  pl14  *+ex* mai
 *  pl15  kim
 *  pl16  geese
 *  pl17  *+ex* yamazaki
 *  pl18  raiden
 *  pl19  *+ex* rugal
 *  pl1a  *+ex* vice
 *  pl1b  benimaru
 *  pl1c  yuri
 *  pl1d  king
 *  pl1e  *unlock* nakoruru
 *  pl1f  *unlock* orochi iori
 *  pl20  m.bison (boxer)
 *  pl21  dan
 *  pl22  joe
 */
require 'common.inc';

function capsnk_decode( &$file, $pos, $len )
{
	$dec = '';
	trace("== begin sub_8001ea14()\n");

	$bycod = 0;
	$bylen = 0;

	while ( $len > 0 )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$pos] );
				$pos++;
			$bylen = 8;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;
		if ( $flg )
		{
			$b1 = ord($file[$pos]);
				$pos++;

			if ( $b1 < 0x10 )
			{
				$b2 = $file[$pos];
					$pos++;
				$dec .= str_repeat($b2, $b1+1);
				$len -= ($b1+1);
			}
			else
			{
				$dpos = ($b1 >> 4);
				$dlen = ($b1 & 0xf);
				for ( $i=0; $i < $dlen; $i++ )
				{
					$dp = strlen($dec) - $dpos;
					$dec .= $dec[$dp];
					$len--;
				} // for ( $i=0; $i < $dlen; $i++ )
			}
		}
		else
		{
			$b = $file[$pos];
				$pos++;
			$dec .= $b;
			$len--;
		}
	} // while ( $len > 0 )

	trace("== end sub_8001ea14()\n");
	return $dec;
}

function sectspr( &$spr )
{
	$pos = 0;
	$len = 0;
	while (1)
	{
		$off = str2int($spr, $pos + 0, 2) * 4;
		$w   = str2int($spr, $pos + 2, 1);
		$h   = str2int($spr, $pos + 3, 1);
			$pos += 12;
		if ( $w < 1 || $h < 1 )
			continue;
		$len = $off;
		break;
	} // while (1)

	$data = array();
	for ( $i=0; $i < $len; $i += 12 )
	{
		$off = str2int($spr, $i + 0, 2) * 4;
		$w   = str2int($spr, $i + 2, 1);
		$h   = str2int($spr, $i + 3, 1);
		$x   = str2int($spr, $i + 4, 2);
		$y   = str2int($spr, $i + 6, 2);
		$cnt = str2int($spr, $i + 8, 2);

		$head = array($w, $h, $x, $y);
		$body = array();

		$cnt = $w * $h;
		for ( $j=0; $j < $cnt; $j++ )
		{
			$s = str2int($spr, $off, 4);
				$off += 4;
			if ( $s === BIT32 )
				$body[] = -1;
			else
				$body[] = $s;
		} // for ( $j=0; $j < $cnt; $j++ )

		$data[] = array($head,$body);
	} // for ( $i=0; $i < $len; $i += 12 )

	$spr = $data;
	return;
}

function sectefdat( &$efdat, $dir )
{
	$data = array();
	$len = str2int($efdat, 0, 3);

	// first entry , always 18f00
	//   x =  00- d0 , y = 20-50 [+2a00 ,  2a00 , y+ 60]
	//   x = 100-1d0 , y = 00-50 [+2a00 ,  5400 , y+ c0]
	//   x = 200-2d0 , y = 00-50 [+2a00 ,  7e00 , y+120]
	//   x = 300-3d0 , y = 00-d0 [+6200 ,  e000 , y+200]
	//   x = 400-4d0 , y = 00-60 [+3100 , 11100 , y+270]
	//   x = 500-5d0 , y = 20-50 [+2a00 , 13b00 , y+2d0]
	//   x = 600-6d0 , y = 20-50 [+2a00 , 16500 , y+330]
	//   x = 700-7d0 , y = 20-50 [+2a00 , 18f00 , y+390]
	$pix = substr($efdat, $len + 4, 0x18f00);
		bpp4to8($pix);
	$img = array(
		'w'   => 0x70 * 2, // 224
		'h'   => 0x390, // 912
		'pix' => $pix ,
	);
	$data[] = $img;

	// second and above , all TIM
	for ( $i=4; $i < $len; $i += 4 )
	{
		$pos = str2int($efdat, $i, 3);
		$tim = psxtim($efdat, $pos);
		$fn = sprintf('%s/efdat-tim.%d.clut', $dir, $i >> 2);
		save_clutfile($fn, $tim);

		$data[] = $tim;
	}

	$efdat = $data;
	return;
}
//////////////////////////////
function save_sprfile( $fname, &$spr )
{
	$txt = '';
	foreach ( $spr as $sk => $sv )
	{
		list($w,$h,$x,$y) = $sv[0];
		$txt .= sprintf("== spr %x [%d] = %x , %x , %x , %x\n", $sk, $sk, $w, $h, $x, $y);

		$id = 0;
		for ( $x=0; $x < $w; $x++ )
		{
			for ( $y=0; $y < $h; $y++ )
			{
				$s = $sv[1][$id];
					$id++;
				if ( $s === -1 )
					$txt .= sprintf('%8d', $s);
				else
					$txt .= sprintf('%8x', $s);;

				$txt .= '  ';
			} // for ( $y=0; $y < $h; $y++ )

			$txt .= "\n";
		} // for ( $x=0; $x < $w; $x++ )

		$txt .= "\n";
	} // foreach ( $spr as $sk => $sv )

	save_file($fname, $txt);
	return;
}
//////////////////////////////
function sect_sprgfx( &$spr, &$gfx, &$clt, &$efdat, $dir )
{
	foreach ( $spr as $sk => $sv )
	{
		if ( empty($sv[1]) )
			continue;
		list($w,$h,$x,$y) = $sv[0];

		$pix = copypix_def($w*0x10, $h*0x10);
		$pix['src']['w']   = 0x10;
		$pix['src']['h']   = 0x10;
		$pix['src']['cc']  = 0x10;

		$id = 0;
		for ( $x=0; $x < $w; $x++ )
		{
			for ( $y=0; $y < $h; $y++ )
			{
				$s = $sv[1][$id];
					$id++;
				if ( $s === -1 )
					continue;

				// fedcba98 76543210 fedcba98 76543210
				// --oo---- -------- -------- --------  if oo == 3 use efdat , else use gfx
				// hv--pppp gggggggg gggggggg gggggggg
				// hv--pppp ----xxxx xxxxxxxx yyyyyyyy  x <= 7d0 ,y <= d0
				$cid = ($s >> 24) & 0xf;
				$pal = substr($clt, $cid*0x40, 0x40);
					$pal[3] = ZERO;
				$pix['src']['pal'] = $pal;

				$b28 = $s >> 28;
				$pix['vflip'] = $b28 & 4;
				$pix['hflip'] = $b28 & 8;

				$pix['dx'] = $x * 0x10;
				$pix['dy'] = $y * 0x10;

				if ( ($b28 & 3) === 3 )
				{
					$sp = ($s >> 16) & 0xf;
					$sx = ($s >>  8) & BIT8;
					$sy = ($s >>  0) & BIT8;
					switch ( $sp )
					{
						case 1: $sy += 0x60 ; break;
						case 2: $sy += 0xc0 ; break;
						case 3: $sy += 0x120; break;
						case 4: $sy += 0x200; break;
						case 5: $sy += 0x270; break;
						case 6: $sy += 0x2d0; break;
						case 7: $sy += 0x330; break;
					} // switch ( $sp )

					$srct = &$efdat[0];
					$pix['src']['pix'] = rippix8($srct['pix'], $sx, $sy, 0x10, 0x10, $srct['w'], $srct['h']);
				}
				else
				{
					$gfx_pos = $s & BIT24;
					$srcd = capsnk_decode($gfx, $gfx_pos, 0x80);
					bpp4to8($srcd);

					$pix['src']['pix'] = $srcd;
				}

				copypix_fast($pix, 1);
			} // for ( $y=0; $y < $h; $y++ )
		} // for ( $x=0; $x < $w; $x++ )

		$fn = sprintf('%s/%04d', $dir, $sk);
		savepix($fn, $pix);
	} // foreach ( $spr as $sk => $sv )
	return;
}
//////////////////////////////
function capsnk( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$clt = load_file("$pfx.clt");
	$dat = load_file("$pfx.dat");
	$efdat = load_file("{$pfx}ef.dat");
	if ( empty($clt) || empty($dat) || empty($efdat) )
		return;

	$clt = pal555($clt);
	sectefdat($efdat, $pfx);

	$b00 = str2int($dat,  0, 3);
	$b04 = str2int($dat,  4, 3);
	$b08 = str2int($dat,  8, 3);
	$b0c = str2int($dat, 12, 3);
	$b10 = str2int($dat, 16, 3);
	$b14 = str2int($dat, 20, 3);
	$end = strlen($dat);

	$spr = substr($dat, $b00, $b04 - $b00);
	$gfx = substr($dat, $b04, $b08 - $b04);
	$hit = substr($dat, $b08, $b0c - $b08);
	$anm = substr($dat, $b0c, $b10 - $b0c);
	$s4 = substr($dat, $b10, $b14 - $b10);
	$s5 = substr($dat, $b14, $end - $b14);

	save_file("$pfx/spr.bin", $spr);
	save_file("$pfx/gfx.bin", $gfx);
	save_file("$pfx/hit.bin", $hit);
	save_file("$pfx/anm.bin", $anm);
	save_file("$pfx/s4.bin", $s4);
	save_file("$pfx/s5.bin", $s5);

	sectspr($spr);
	save_sprfile("$pfx/spr.txt", $spr);

	sect_sprgfx($spr, $gfx, $clt, $efdat, "$pfx/sprgfx");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	capsnk( $argv[$i] );

/*
mai idle = spr d8 , 5fe4
	55 06 -- d0  87 cd 87 cc  87 e0 af 87  7f ff f2 d0

	-1-1 -1-1
		e77d8  00 * 6
		e77df  d0
		c77e0  00 * 6
		c77e7  cd
		c77e8  00 * 6
		c77ef  cc
		c77f0  00 * 6
		c77f7  e0
	1-1- 1111
		c77f8  00 * 6
		c77ff  00 * e
		c780e  00 * e
		c781d  00 * 1
		c781f  d0
 */
