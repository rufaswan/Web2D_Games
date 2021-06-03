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
require "common.inc";

$gp_nscr = array();
$gp_nclr = '';
$gp_ncgr = array();

function sect_RCSN( &$file, $fname )
{
	echo "== sect_RCSN( $fname )\n";
	if ( substr($file, 16, 4) != "NRCS" )
		return;

	$sw = str2int($file, 0x18, 2, true);
	$sh = str2int($file, 0x1a, 2, true);
	$sz = str2int($file, 0x20, 4);
	printf("%x x %x = %x [%x]\n", $sw, $sh, $sw*$sh, $sz);

	$file = substr($file, 0x24, $sz);

	$base = 0;
	// for gallery abc set
	if ( stripos($fname, 'gallery') )
	{
		if ( stripos($fname, 'b.nscr') )
			$base = 0x400;
		if ( stripos($fname, 'c.nscr') )
			$base = 0x800;
	}

	global $gp_ncgr, $gp_nclr;
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $sw;
	$pix['rgba']['h'] = $sh;
	$pix['rgba']['pix'] = canvpix($sw,$sh);

	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$pos = 0;
	for ( $by=0; $by < $sh; $by += 0x100 )
	{
		for ( $bx=0; $bx < $sw; $bx += 0x100 )
		{
			for ( $y=0; $y < 0x100; $y += 8 )
			{
				for ( $x=0; $x < 0x100; $x += 8 )
				{
					if ( ($bx+$x) >= $sw )  continue;
					if ( ($by+$y) >= $sh )  continue;

					$b = str2int($file, $pos, 2);
						$pos += 2;

					// fedc  b   a   98 76543210
					// cid   vf  hf  tid
					$tid = ($b & 0x03ff) + $base;
					$cid = $b >> 12;
					$pix['hflip'] = ( $b & 0x0400 );
					$pix['vflip'] = ( $b & 0x0800 );

					$pix['dx'] = $bx + $x;
					$pix['dy'] = $by + $y;
					$pix['src']['pix'] = $gp_ncgr['pix'][$tid];

					if ( $gp_ncgr['4bpp'] )
						$pix['src']['pal'] = substr($gp_nclr, $cid*0x40, 0x40);
					else
						$pix['src']['pal'] = $gp_nclr;

					copypix_fast($pix, 1);
				} // for ( $x=0; $x < 0x100; $x += 8 )
			} // for ( $y=0; $y < 0x100; $y += 8 )
		} // for ( $bx=0; $bx < $sw; $bx += 0x100 )
	} // for ( $by=0; $by < $sh; $by += 0x100 )

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	savepix($pfx, $pix);
	return;
}

function sect_RLCN( &$file, $fname )
{
	echo "== sect_RLCN( $fname )\n";
	global $gp_nclr;
	$gp_nclr = '';
	if ( substr($file, 16, 4) != "TTLP" )
		return;

	$file = substr($file, 0x28);

	printf("added %x palette\n", strlen($file)>>1);
	$gp_nclr = pal555($file);
	return;
}

function sect_RGCN( &$file, $fname )
{
	echo "== sect_RGCN( $fname )\n";
	global $gp_ncgr;
	$gp_ncgr = array();
	if ( substr($file, 16, 4) != "RAHC" )
		return;

	$w = str2int($file, 0x1a, 2, true);
	$h = str2int($file, 0x18, 2, true);
	if ( $w < 0 || $h < 0 )
		return;

	$bpp4 = ( $file[0x1c] === "\x03" );
	$siz  = str2int($file, 0x28, 4);
	$file = substr ($file, 0x30, $siz);

	if ( $bpp4 )
		bpp4to8($file);

	$pix = array();
	$len = strlen($file);
	for ( $i=0; $i < $len; $i += 0x40 )
		$pix[] = substr($file, $i, 0x40);

	printf("added %x x %x = %x [%x] [%x tiles]\n", $w, $h, $w*$h, $siz, count($pix));
	$gp_ncgr['w'] = $w * 8;
	$gp_ncgr['h'] = $h * 8;
	$gp_ncgr['pix']  = $pix;
	$gp_ncgr['4bpp'] = $bpp4;
	return;
}
//////////////////////////////
function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc === "RCSN" )
		return sect_RCSN($file, $fname);
	if ( $mgc === "RLCN" )
		return sect_RLCN($file, $fname);
	if ( $mgc === "RGCN" )
		return sect_RGCN($file, $fname);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

/*
SET = *.ncgr *.nclr *.nscr

ground_bg_a.nclr  ground_bg_1a.ncgr  ground_bg_1a.nscr  ground_bg_2a.ncgr  ground_bg_2a.nscr
ground_bg_b.nclr  ground_bg_1b.ncgr  ground_bg_1b.nscr  ground_bg_2b.ncgr  ground_bg_2b.nscr
ground_bg_c.nclr  ground_bg_1c.ncgr  ground_bg_1c.nscr  ground_bg_2c.ncgr  ground_bg_2c.nscr
ground_bg_d.nclr  ground_bg_1d.ncgr  ground_bg_1d.nscr  ground_bg_2d.ncgr  ground_bg_2d.nscr

road_bg_a.nclr  road_bg_1a.ncgr  road_bg_1a.nscr  road_bg_2a.ncgr  road_bg_2a.nscr
road_bg_b.nclr  road_bg_1b.ncgr  road_bg_1b.nscr  road_bg_2b.ncgr  road_bg_2b.nscr
road_bg_c.nclr  road_bg_1c.ncgr  road_bg_1c.nscr  road_bg_2c.ncgr  road_bg_2c.nscr
road_bg_d.nclr  road_bg_1d.ncgr  road_bg_1d.nscr  road_bg_2d.ncgr  road_bg_2d.nscr

gallery_01.nclr  gallery_01.ncgr  gallery_01[abc].nscr
gallery_02.nclr  gallery_02.ncgr  gallery_02[abc].nscr
gallery_03.nclr  gallery_03.ncgr  gallery_03[abc].nscr
gallery_04.nclr  gallery_04.ncgr  gallery_04[abc].nscr
gallery_05.nclr  gallery_05.ncgr  gallery_05[abc].nscr
gallery_06.nclr  gallery_06.ncgr  gallery_06[abc].nscr
gallery_07.nclr  gallery_07.ncgr  gallery_07[abc].nscr
gallery_08.nclr  gallery_08.ncgr  gallery_08[abc].nscr
gallery_09.nclr  gallery_09.ncgr  gallery_09[abc].nscr
gallery_10.nclr  gallery_10.ncgr  gallery_10[abc].nscr
gallery_11.nclr  gallery_11.ncgr  gallery_11[abc].nscr
gallery_12.nclr  gallery_12.ncgr  gallery_12[abc].nscr
gallery_13.nclr  gallery_13.ncgr  gallery_13[abc].nscr
gallery_14.nclr  gallery_14.ncgr  gallery_14[abc].nscr
gallery_15.nclr  gallery_15.ncgr  gallery_15[abc].nscr
gallery_16.nclr  gallery_16.ncgr  gallery_16[abc].nscr
gallery_17.nclr  gallery_17.ncgr  gallery_17[abc].nscr
gallery_18.nclr  gallery_18.ncgr  gallery_18[abc].nscr
gallery_19.nclr  gallery_19.ncgr  gallery_19[abc].nscr
gallery_20.nclr  gallery_20.ncgr  gallery_20[abc].nscr
gallery_21.nclr  gallery_21.ncgr  gallery_21[abc].nscr

gui_bg01.nclr  gui_bg01.ncgr  gui_bg01_[012][0123456789].nscr
gui_bg01.nclr  gui_bg01.ncgr  gui_bg01_[012][0123456789][abcd].nscr

 */
