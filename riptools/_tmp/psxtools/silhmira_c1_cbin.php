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
require 'silhmira.inc';

function sectspr( &$spr, $dir, &$clr )
{
	$txt = '';
	for ( $i=0; $i < 0x400; $i += 4 )
	{
		if ( $spr[$i+0] === ZERO )
			continue;

		// fedcba98 76543210 fedcba98 76543210
		// cccccccc 1122pppp pppppppp pppppppp
		$s   = str2big($spr, $i, 4);
		$sno = ($s >> 0x18) & BIT8;
		$yty = ($s >> 0x16) & 3;
		$xty = ($s >> 0x14) & 3;
		$off = $s & 0xfffff;

		// a = 1-1- = anim        or  sint8 + sint8
		// e = 111- = anim + hit  or  ff00  + sint8

		// 0 1  2 3  4  5  6  7
		// hd   - -  -  dx dy wh
		$hdsz = str2big($spr, $off, 2);
			$off += 2;
		$head = substr($spr, $off, $hdsz);

		// 4-bpp big endian pixel data
		$dec = silhmira_decode($spr, $off + $hdsz);
			bpp4to8($dec);
			$dec = big2little16($dec);
		$fn = sprintf('%s/dec.%04d', $dir, $i >> 2);
		save_file($fn, $dec);

		$txt .= sprintf("\n== spr %d [%x]\n", $i>>2, $i>>2);
		$txt .= debug($head, 'head');

		$pix = copypix_def(0x200, 0x200);
		$pix['src']['cc']  = 0x10;

		$pal = substr($clr, 12*0x40, 0x40);
			$pal[3] = ZERO;
		$pix['src']['pal'] = $pal;

		$hpos = 2;
		$dpos = 0;
		for ( $hid=0; $hid < $sno; $hid++ )
		{
			$hno = ord( $head[$hpos] );
				$hpos++;
			for ( $hj=0; $hj < $hno; $hj++ )
			{
				//$dx = str2int($head, $hpos + 0, 1, true);
				//$dy = str2int($head, $hpos + 1, 1, true);
				//$wh = str2int($head, $hpos + 2, 1);
				$dx = silhmira_sint8( $head[$hpos+0], $xty );
				$dy = silhmira_sint8( $head[$hpos+1], $yty );
				$wh = ord( $head[$hpos+2] );
					$hpos += 3;

				$w = ($wh >> 4) & BIT4;
				$h = ($wh >> 0) & BIT4;
					$w = ($w + 1) * 0x10;
					$h = ($h + 1) * 0x10;

				$pix['dx'] = $dx + 0x100;
				$pix['dy'] = $dy + 0x100;
				$pix['src']['w'] = $w;
				$pix['src']['h'] = $h;
				$pix['src']['pix'] = substr($dec, $dpos, $w*$h);
					$dpos += ($w * $h);

				//$fn = sprintf('%s/%d-%d-%d.clut', $dir, $i >> 2, $hid, $hj);
				//save_clutfile($fn, $img);
				copypix_fast($pix, 1);
			} // for ( $hi2 = 0; $hi2 < $hno; $hi2++  )
		} // for ( $hid=0; $hid < $sno; $hid++ )

		$fn = sprintf('%s/%04d', $dir, $i >> 2);
		savepix($fn, $pix);
	} // for ( $i=0; $i < 0x400; $i += 4 )

	save_file("$dir.txt", $txt);
	return;
}
//////////////////////////////
function save_anmfile( $fn, &$anm )
{
	$txt = '';
	for ( $i=0; $i < 0x200; $i += 2 )
	{
		$txt .= sprintf("== anm %d [%x]\n", $i>>1, $i>>1);

		$pos = str2big($anm, $i, 2);
		$id = 0;
		while (1)
		{
			$s = substr($anm, $pos, 12);
				$pos += 12;
			$txt .= debug($s, $id);
				$id++;

			$b = ord($s[0]);
			if ( $b & 0x80 )
				break;
		} // while (1)

		$txt .= "\n";
	} // for ( $i=0; $i < 0x200; $i += 2 )

	save_file($fn, $txt);
	return;
}

function hitbox_10( &$hit, &$pos, $h )
{
	$txt = '';
	$id  = 0;
	while (1)
	{
		$s = substr($hit, $pos, 10);
			$pos += 10;
		$txt .= debug($s, "{$h}-{$id}");
			$id++;
		if ( $s[0] !== ZERO )
			break;
	}
	return $txt;
}

function save_hitfile( $fn, &$hit )
{
	$txt = '';
	for ( $i=0; $i < 0x200; $i += 2 )
	{
		$pos = str2big($hit, $i, 2);
		$b1 = ord( $hit[$pos+0] );
		$b2 = ord( $hit[$pos+1] );
			$pos += 2;

		$txt .= sprintf("== hit %d [%x] = %x , %x\n", $i>>1, $i>>1, $b1, $b2);

		if ( $b1 & 0x40 )
		{
			$s = substr($hit, $pos, 12);
				$pos += 12;
			$txt .= debug($s, '40-head');
			$txt .= hitbox_10($hit, $pos, '40 -1------');
		}
		if ( $b1 & 0x20 )
			$txt .= hitbox_10($hit, $pos, '20 --1-----');
		if ( $b1 & 0x10 )
			$txt .= hitbox_10($hit, $pos, '10 ---1----');
		if ( $b1 & 0x08 )
			$txt .= hitbox_10($hit, $pos, '08 ----1---');

		$txt .= "\n";
	} // for ( $i=0; $i < 0x200; $i += 2 )

	save_file($fn, $txt);
	return;
}
//////////////////////////////
function silhmira( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "== $fname\n";
	$dir = str_replace('.', '_', $fname);
	silhmirabin($file);

	foreach ( $file[0] as $fk => $fv )
		save_file("$dir/spr.$fk", $fv);

	foreach ( $file[1] as $fk => $fv )
	{
		save_file("$dir/anm.$fk", $fv);
		save_anmfile("$dir/anm.$fk.txt", $fv);
	}

	foreach ( $file[2] as $fk => $fv )
	{
		save_file("$dir/hit.$fk", $fv);
		save_hitfile("$dir/hit.$fk.txt", $fv);
	}

	save_file("$dir/pal.bin", $file[3]);

	foreach ( $file[0] as $fk => $fv )
		sectspr($fv, "$dir/$fk", $file[3]);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	silhmira( $argv[$i] );

/*
cmos1.bin , pal = 60
cmos2.bin , pal = 60

missing
	cbkup.bin = no pal
	csik.bin  = no hitbox
	cusa.bin  = no hitbox

set > 1
	casa.bin  = spr    anm*2  hit*2  pal
	cgor.bin  = spr*2  anm    hit    pal
	czom2.bin = spr*2  anm*2  hit*2  pal

	cplay.bin = spr*6  anm*6  hit*2  pal
	ctama.bin = spr*5  anm*5  hit*5  pal
 */
