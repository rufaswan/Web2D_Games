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
//////////////////////////////
function capsnk_sprite( &$spr, &$gfx, &$efdat, &$clt )
{
	// auto=detect header size
	$cnt = -1;
	$pos = 0;
	while (1)
	{
		$off = str2int($spr, $pos + 0, 2);
		$w   = str2int($spr, $pos + 2, 1);
		$h   = str2int($spr, $pos + 3, 1);
			$pos += 12;
		if ( $w < 1 || $h < 1 )
			continue;
		//$cnt = $off * 4 / c;
		$cnt = $off / 3;
		goto part2;
	} // while (1)

part2:
	$keys = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = $i * 12;

		// 0 1  2 3  4 5  6 7  8 9  a b
		// off  w h  x    y    no   - -
		$w = str2int($spr, $pos + 2, 1);
		$h = str2int($spr, $pos + 3, 1);
		if ( $w < 1 || $h < 1 )
			continue;

		$off = str2int($spr, $pos + 0, 2) << 2;
		$x   = str2int($spr, $pos + 4, 2, true);
		$y   = str2int($spr, $pos + 6, 2, true);
		$no  = str2int($spr, $pos + 8, 2);

		$txt = sprintf("== spr %d [%x] = %x , %x , %x , %x\n", $i, $i, $w, $h, $x, $y);

		$pix = copypix_def($w*0x10, $h*0x10);
		$pix['rgba']['cx'] = $x;
		$pix['rgba']['cy'] = $y;
		$pix['src']['w']   = 0x10;
		$pix['src']['h']   = 0x10;
		$pix['src']['cc']  = 0x10;

		for ( $x=0; $x < $w; $x++ )
		{
			for ( $y=0; $y < $h; $y++ )
			{
				$s = str2int($spr, $off, 4);
					$off += 4;
				if ( $s === BIT32 )
				{
					$txt .= sprintf('%8d  ', -1);
					continue;
				}
				$txt .= sprintf('%8x  ', $s);

				// fedcba98 76543210 fedcba98 76543210
				// --oo---- -------- -------- --------  if oo == 3 use efdat , else use gfx
				// hv--pppp gggggggg gggggggg gggggggg
				// hv--pppp ----xxxx xxxxxxxx yyyyyyyy  x <= 7d0 , y <= d0
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

					$pix['src']['pix'] = rippix8($efdat['pix'], $sx, $sy, 0x10, 0x10, $efdat['w'], $efdat['h']);
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
			$txt .= "\n";
		} // for ( $x=0; $x < $w; $x++ )

		trace($txt);
		$keys[$i] = $pix['rgba'];
	} // for ( $i=0; $i < $cnt; $i++ )
	return $keys;
}
//////////////////////////////
function capsnk_efdat( &$efdat )
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
		$data[] = $tim;
	}
	return $data;
}

function capsnk_load( $pfx )
{
	$clt = load_file("$pfx.clt");
	$dat = load_file("$pfx.dat");
	$efdat = load_file("{$pfx}ef.dat");
	if ( empty($clt) || empty($dat) || empty($efdat) )
		return -1;

	$b00 = str2int($dat,  0, 3);
	$b04 = str2int($dat,  4, 3);
	$b08 = str2int($dat,  8, 3);
	$b0c = str2int($dat, 12, 3);
	$b10 = str2int($dat, 16, 3);
	$b14 = str2int($dat, 20, 3);
	$end = strlen ($dat);

	$ret = array(
		'clt' => pal555($clt),
		'dat' => array(
			substr($dat, $b00, $b04 - $b00), // 0 spr
			substr($dat, $b04, $b08 - $b04), // 1 gfx
			substr($dat, $b08, $b0c - $b08), // 2 hit
			substr($dat, $b0c, $b10 - $b0c), // 3 anm
			substr($dat, $b10, $b14 - $b10), // 4
			substr($dat, $b14, $end - $b14), // 5
		),
		'efdat' => capsnk_efdat($efdat),
	);
	return $ret;
}
