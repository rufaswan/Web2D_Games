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

function batmas_decode( &$file )
{
	// in  = 800ff264
	// out = 8010f1a4
	$dec = '';
	trace("== begin sub_800a8354()\n");

	$bylen = 0;
	$bycod = 0;
	$size = str2int($file, 1, 3);
	trace("size = %x\n", $size);

	$st = 1 + 4;
	while ( $size > 0 )
	{
		if ( $bylen === 0 )
		{
			$bycod = str2int($file, $st, 2);
				$st += 2;
			$bylen += 16;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			$b1 = ord( $file[$st+0] );
			$b2 = ord( $file[$st+1] );
				$st += 2;
			$b = ($b2 << 8) | $b1;

			// fedc ba98 7654 3210
			// llll lppp pppp pppp
			$dpos = ($b & 0x7ff) + 1;
			$dlen = ($b >> 11) + 3;
			for ( $i=0; $i < $dlen; $i++ )
			{
				$dp = strlen($dec) - $dpos;
				$dec .= $dec[$dp];
				$size--;
			}
		}
		else
		{
			$dec .= $file[$st];
				$st++;
			$size--;
		}
	} // while ( $size > 0 )

	trace("== end sub_800a8354()\n");
	$file = $dec;
	return;
}

function batmas( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$max = str2int($file, 0, 3) >> 3;
	for ( $i=0; $i < $max; $i++ )
	{
		$p = $i << 3;
		$pos = str2int($file, $p+0, 3);
		$siz = str2int($file, $p+4, 3);
		if ( $siz < 1 )
			continue;

		$s = substr($file, $pos, $siz);
		if ( $s[0] === "\x01" )
			batmas_decode($s);

		$fn  = sprintf('%s/meta.%d', $dir, $i);
		$mgc = substr($s, 0, 4);
		switch ( $mgc )
		{
			case 'pBAV':
				$fn = sprintf('%s/%d.vh' , $dir, $i);
				break;
			case 'pQES':
				$fn = sprintf('%s/%d.seq', $dir, $i);
				break;
			case "\x00\x00\x00\x00":
				if ( substr($s,4,12) === str_repeat(ZERO,12) )
					$fn = sprintf('%s/%d.vb', $dir, $i);
				break;
			case "\x11\x00\x00\x00":
			case "\x10\x00\x00\x00":
				$mgc = str2int($s, 4, 4);
				if ( $mgc === 9 )
					$fn = sprintf('%s/%d.tim9', $dir, $i);
				else
				if ( $mgc === 8 )
					$fn = sprintf('%s/%d.tim8', $dir, $i);
				else
				if ( $mgc === 2 )
					$fn = sprintf('%s/%d.tim2', $dir, $i);
				break;
		} // switch ( $mgc )

		printf("%6x , %6x , %s\n", $pos, $siz, $fn);
		save_file($fn, $s);
	} // for ( $i= 0xc0; $i < 0x100; $i += 8 )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	batmas( $argv[$i] );

/*
- - - -  ms
- 2 g w  acguy
- 2 - w  boss   / hydra
1 2 g w  bygzam
1 2 g -  gp02
- 2 - -  hamma
1 2 - w  hygog
- 2 g -  k0     / ball
- 2 g w  k1     / zaku ii s
1 2 g w  n ziel / neue ziel
1 2 g w  psycho
1 2 g -  qman   / quin mantha
- 2 - -  qube
1 2 g w  rx78
1 2 - w  rx93   / v gundam
1 2 - w  sazabi
1 2 - -  the o
1 2 g -  zaku
- 2 - -  zeta
1 2 - w  ziong
1 2 g -  zz

batmas 1
	ms
		12 13        pVAB sound
		16 17 18 19  TIM 4-bpp
		20 21 22
		23           opcode
		24 25

batmas 2
	ms
		12 13        pVAB sound
		16 17 18 19  TIM 4-bpp
		24 25
		26           opcode
		28 29 30 31

	st
		 8  9 10 11  TIM 4-bpp object
		12
		16 17 18 19  TIM 4-bpp bg
		20 21 22
		24 25 26 27 28 29
		32 33  TIM 4-bpp player
		34
		36
		37  TIM 4-bpp continue
 */
