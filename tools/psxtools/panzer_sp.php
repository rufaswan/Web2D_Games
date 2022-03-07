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

//define('DRY_RUN', true);

// callback for copypix()
function pz_alpha0( $fg, $bg )
{
	if ( $fg[3] === ZERO )  return $bg;
	if ( $bg[3] === ZERO )  return $fg;
	// (FG + BG) / 2
	return $fg;
}
function pz_alpha2( $fg, $bg )
{
	if ( $fg[3] === BYTE )  return $fg;
	// FG + BG
	return alpha_add($fg, $bg);
}
function pz_alpha4( $fg, $bg )
{
	if ( $fg[3] === ZERO )  return $bg;
	if ( $bg[3] === ZERO )  return $fg;
	// BG - FG
	return $fg;
}
function pz_alpha6( $fg, $bg )
{
	if ( $fg[3] === ZERO )  return $bg;
	if ( $bg[3] === ZERO )  return $fg;

	// (FG / 4) + BG
	for ( $i=0; $i < 3; $i++ )
	{
		$c = ord( $fg[$i] );
		$c >>= 2;
		$fg[$i] = chr($c);
	}
	return alpha_add($fg, $bg);
}
//////////////////////////////
function clr_stp_on( &$clr )
{
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		if ( $clr[$i+3] !== "\x7f" )
			continue;

		$r = ord( $clr[$i+0] );
		$g = ord( $clr[$i+1] );
		$b = ord( $clr[$i+2] );
		$a = var_max($r, $g, $b);

		$r = int_clamp($r * BIT8 / $a, 0, BIT8);
		$g = int_clamp($g * BIT8 / $a, 0, BIT8);
		$b = int_clamp($b * BIT8 / $a, 0, BIT8);

		$c = chr($r) . chr($g) . chr($b) . chr($a);
		str_update($clr, $i, $c);
	}
	return;
}

function clr_stp_off( &$clr )
{
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		if ( $clr[$i+3] !== "\x7f" )
			continue;
		$clr[$i+3] = BYTE;
	}
	return;
}
//////////////////////////////
function sect_part( &$file, $off, $fn, &$pal, &$src )
{
	$cnt = str2int($file, $off, 4);
		$off += 4;
	printf("== sect_part( %x , %s ) = %x\n", $off, $fn, $cnt);

	$data = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$b1 = substr($file, $off, 0x10);
			$off += 0x10;
		$data[] = $b1;
	} // for ( $i=0; $i < $cnt; $i++ )

	$pix = COPYPIX_DEF(0x200,0x200);
	$pix['bgzero'] = 0;

	foreach ( $data as $v )
	{
		// 0  1  2  3   4 5 6  7    8 9 a  b   c d  e f
		// sx sy dx dy  clr    tsz  - - -  pg  - fx - fy
		echo debug($v);
		$sx = ord( $v[0] );
		$sy = ord( $v[1] );
		$dx = ord( $v[2] ) + 0x80;
		$dy = ord( $v[3] ) + 0x80;

		// texture pages
		$b11 = ord( $v[11] );
			$sx += ($b11 >> 6) * 0x100;

		// 10 = normal , f0 = flip
		$b13 = ord( $v[13] );
		$b15 = ord( $v[15] );
			$pix['hflip'] = ( $b13 === 0xf0 );
			$pix['vflip'] = ( $b15 === 0xf0 );

		// fedc ba98 7654 3210 fedc ba98 7654 3210
		// tttt ---- -aa- ---- a--- --cc cc-- ----
		$b4 = str2int($v, 4, 4);
			$cid = ($b4 >> 6) & BIT4;

		$b428 = $b4 >> 28;
			$tsz = ($b428 == 2) ? 0x10 : 0x20;

		$clr = substr($pal, $cid*0x40, 0x40);
		$pix['alpha'] = '';
		if ( ($b4 >> 15) & 1 )
		{
			clr_stp_on($clr);
			//$ty = sprintf('pz_alpha%d', ($b4 >> 20) & 6);
			//echo "$ty\n";
			//$pix['alpha'] = $ty;
		}
		else
			clr_stp_off($clr);

		$pix['src']['w'] = $tsz;
		$pix['src']['h'] = $tsz;
		$pix['dx'] = $dx;
		$pix['dy'] = $dy;
		$pix['src']['pal'] = $clr;
		$pix['src']['pix'] = rippix8($src, $sx, $sy, $tsz, $tsz, 0x400, 0x200);

		//printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $tsz, $tsz);
		//printf(" , %x\n", $cid);
		copypix($pix);
	} // foreach ( $data as $v )

	savepix($fn, $pix, true);
	return;
}
//////////////////////////////
function pixtex( &$pix )
{
	$len  = strlen($pix);
	if ( $len & 0x7fff )
		return php_error('pix not in 0x8000 [%x]', $len);

	// 1 page = 80*100
	$page = $len / 0x8000;
	$tex  = str_repeat(ZERO, 0x80*4*0x100*2);

	$pos = 0;
	for ( $p=0; $p < $page; $p++ )
	{
		echo "PAGE $p\n";
		for ( $y=0; $y < 0x100; $y += 0x20 )
		{
			for ( $x=0; $x < 0x80; $x += 0x10 )
			{
				$sub = substr($pix, $pos, 0x200);
					$pos += 0x200;

				if ( empty($sub) )
					goto done;

				for ( $ty=0; $ty < 0x20; $ty++ )
				{
					$dyy = ($y + $ty) * 0x200;
					$dxx = $dyy + ($p * 0x80) + $x;

					$b1 = substr($sub, $ty*0x10, 0x10);
					str_update($tex, $dxx, $b1);
				} // for ( $ty=0; $ty < 0x20; $ty++ )

			} // for ( $x=0; $x < 0x80; $x += 0x10 )
		} // for ( $y=0; $y < 0x100; $y += 0x20 )
	} // for ( $p=0; $p < $page; $i++ )

done:
	bpp4to8($tex);
	$pix = $tex;
	return;
}

function spfile( &$file, $dir )
{
	$meta = array();

	// animation data
	$of = str2int($file, 0, 4);
	$sz = str2int($file, 4, 4);
	$meta[0] = substr($file, $of, $sz);

	// ???
	$of = str2int($file,  8, 4);
	$sz = str2int($file, 12, 4);
	$meta[1] = substr($file, $of, $sz);

	// sprite data
	$of = str2int($file, 16, 4);
	$sz = str2int($file, 20, 4);
	$meta[2] = substr($file, $of, $sz);

	// pixel data
	$of = str2int($file, 24, 4);
	$sz = str2int($file, 28, 4);
	$sub = substr($file, $of, $sz);

		$pal = substr($sub, 0, 0x200);
		$pix = substr($sub, 0x200);
			$pal = pal555($pal, false);
			pixtex($pix);

	$meta[3] = array($pal, $pix);

	save_file("$dir/meta/0.meta", $meta[0]);
	save_file("$dir/meta/1.meta", $meta[1]);
	save_file("$dir/meta/2.spr" , $meta[2]);
	save_file("$dir/meta/30.pal", $meta[3][0]);
	save_file("$dir/meta/31.pix", $meta[3][1]);
	$file = $meta;
	return;
}

function panzer( $fname )
{
	// for *.sp only
	if ( stripos($fname, '.sp') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	spfile($file, $dir);

	$cnt = str2int($file[2], 8, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x10 + ($i * 4);
		$of = str2int($file[2], $p, 4);

		$fn = sprintf('%s/%04d', $dir, $i);
		sect_part($file[2], $of, $fn, $file[3][0], $file[3][1]);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	panzer( $argv[$i] );

/*
blending
	koh.sp  = 800d6cfc    0.png/+ 1b8=800d6eb4
	kasu.sp = 800d70ec  122.png/+6e44=800ddf30
	syou.sp = 80
	aine.sp = 800d7018    0.png/+ 294=800d72ac

	koh 77,166
		BG 392018 + FG 6a83ee
			80  525283 or (BG +  FG) / 2
			82  a4a4ff or  BG +  FG
			84  000000 or  BG -  FG
			86  524152 or  BG + (FG  / 4)

	headlight error if without blending
		zako06
		zako12

RAM 800d6eb8
	8007a768 lw v0[4a85a0c0], 0(s4[800d6eb8])
		a3 = v0 >> 24
		a1 = v0 >> 16
		t5 = v0 >>  8
		fp = v0

RAM 800d6ebc
	8007a784 lw v1[209c7800], 0(s4[800d6ebc])
		v1 &= 0xf0000000
		if ( v1 == 0x20000000 )
			t1 = s6 = t4 = t2 = s7 = 0x10
		else
			t1 = s6 = t4 = t2 = s7 = 0x20
	8007a7c4 lw a0[209c7800], 0(s4[800d6ebc])
		v0 = (a0 >>  6) & 0x0f
		v0 = (a0 >> 15) & 1
		a0 = (a0 >> 21) & 3

	209c7800 = 2/tile_sz , 0/pal_id , 0/is_alpha , 0/alpha_type
	--1- ---- 1--1 11-- -111 1--- ---- ----
	0000       33       2      11 11       .

RAM 800d6ec0
	8007a7cc lw v1[40010000], 0(s4[800d6ec0])
		a0 = v1 >> 30
		v0 = (v1 >> 26) & 0x0c
		a0 += v0
		v1 = (v1 >> 13) & 0x7f0
		a0 += v1

	40010000 = 1/page + 0 + 0
	-1-- ---- ---- ---1 ---- ---- ---- ----
	0011 ..   2222 222. ...                .

RAM 800d6ec4
	8007a818 lw a0[10001000], 0(s4[800d6ec4])
		v0 = a0 & 0xf0000000
		v1 = ( v0 ^ 0xf0000000 )
		a0 &= 0xf000
		if ( a0 == 0xf000 )
 */
