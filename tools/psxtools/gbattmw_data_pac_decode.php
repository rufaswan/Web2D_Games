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

function getbits( &$bits, &$file, &$pos, $bmax )
{
	if ( empty($bits) )
	{
		while (1)
		{
			$b = ord( $file[$pos] );
				$pos++;
			$j = 8;
			while ( $j > 0 )
			{
				$j--;
				$bits[] = $b & 1;
				$b >>= 1;
				$bmax--;
			} // while ( $j > 0 )

			if ( $bmax <= 0 )
				break;
		} // while (1)
	}
	return array_shift($bits);
}

function batmas_decode( &$file )
{
	$dec = '';
	trace("== begin sub_8006f8b4()\n");

	$b24 = str2int($file, 0x24, 3); // 2dc(gp)
	$b28 = str2int($file, 0x28, 2); // 8(v0) , v0 = 94(gp)
	$b2a = str2int($file, 0x2a, 2); // a(v0) , v0 = 94(gp)
		$size = $b24;
		$bmax = $b28;
		$blen = $b2a;
		$mask = 0xffff >> (0x10 - $blen);

	$bits = array();
	$pos  = 0x38;
	while ( $size > 0 )
	{
		$flg = getbits($bits, $file, $pos, $bmax);

		if ( $flg )
		{
			$b1 = $file[$pos];
				$pos++;
			$dec .= $b1;
			$size--;
		}
		else
		{
			$b1 = ord($file[$pos+0]);
			$b2 = ord($file[$pos+1]);
				$pos += 2;
			$b = ($b1 << 8) | $b2;

			$dlen = ($b & $mask) + 3;
			$dpos = $b >> $blen;
			for ( $i=0; $i < $dlen; $i++ )
			{
				$dp = strlen($dec) - $dpos;
				$dec .= $dec[$dp];
				$size--;
			}
		}
	} // while ( $size > 0 )

	trace("== end sub_8006f8b4()\n");
	$file = $dec;
	return;
}

function sectadd( &$file, $dir )
{
	$func = __FUNCTION__;
	$mgc  = substr($file, 0, 4);
	switch ( $mgc )
	{
		case 'add'.ZERO:
			printf("add  , %s\n", $dir);
			$cnt = str2int($file, 12, 3);
			for ( $i=0; $i < $cnt; $i++ )
			{
				$pos = 0x20 + ($i * 0x10);
				$off = str2int($file, $pos, 3);
				if ( $off === 0 )
					continue;

				$siz = str2int($file, $pos+4, 3);
				$fn = sprintf('%s/%04d', $dir, $i);
				$s  = substr($file, $off, $siz);
				$func($s, $fn);
			} // for ( $i=0; $i < $cnt; $i++ )
			return;

		case 'lzs'.ZERO:
			printf("lzs  , %s\n", $dir);
			batmas_decode($file);
			//save_file("$dir.lzs", $file);
			$func($file, $dir);
			return;

		case 'pBAV':
			printf("vh   , %s\n", $dir);
			return save_file("$dir.vh", $file);

		case "\x00\x00\x00\x00":
			printf("vb   , %s\n", $dir);
			return save_file("$dir.vb", $file);

		case "\xe8\xff\xbd\x27":
			printf("mips , %s\n", $dir);
			return save_file("$dir.mips", $file);

		case "\x10\x00\x00\x00":
			$bpp = ord( $file[4] );
			if ( $bpp === 8 )
			{
				printf("tim8 , %s\n", $dir);
				return save_file("$dir.tim8", $file);
			}
			else
			if ( $bpp === 9 )
			{
				printf("tim9 , %s\n", $dir);
				return save_file("$dir.tim9", $file);
			}
			break;
	} // switch ( $mgc )

	php_notice("???  , %s\n", $dir);
	save_file("$dir.unk", $file);
	return;
}

function batmas( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	sectadd($file, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	batmas( $argv[$i] );

/*
- -  ms
g -  00  denim       / zaku ii
- w  01  mikhail     / hygog
- w  02  char        / zeong
- w  03  char        / sazabi
g -  04  puru two    / quin mantha
g w  05  dozul       / bygzam
     06
g w  07  gato        / neue ziel
- w  08  amuro       / v gundam
g w  09  maria       / psycho
g w  0a  amuro       / rx-78
g -  0b  gato        / gp-02a
g -  0c  judau       / zz-gundam
     0d
g w  0e  akahana     / acguy
     0f
     10
- w  11  valder      / hydra
g -  12  shiroh      / ball
g w  13  char        / zaku ii s
g w  14  domon       / god
g w  15  chibodee    / maxter
g w  16  george      / rose
g w  17  argo        / bolt
g w  18  sai         / dragon
g w  19  heero       / wing
g w  1a  duo         / deathscythe
g w  1b  trowa       / heavy arms
g w  1c  quatre      / sandrock
g w  1d  wufei       / altron
g -  1e  kyouji      / devil
g w  1f  master asia / master
- w  20  treize      / epyon
g w  21  zechs       / tallgeese

ms
	1 2 3 4  TIM 4-bpp
	6 7
	8        opcode
	13 14    MIPS overlay
	11/
		6 7   pVAB sound
		8 11
	16/
		1    TIM 4-bpp pilot
		3    TIM 8-bpp cutin
		5 7
 */
