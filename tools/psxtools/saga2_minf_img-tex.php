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
 * 00 4 HEAD end
 * 04 4
 * 08 4
 * 0c 4 CHR offset(+4)
 * 10 4 CHR end
 * 1c 4 MAP TEX data(+4)
 * 20 4 MAP TEX end
 * 28 4
 * 2c 4
 * 30 4 MOVE data
 * 34 4 MOVE end
 *
 * MAP TEX data
 * 00 4 [loop] data head start(+4)
 *   [loop]
 *   00 4 [loop] bg start/data head end
 *     [loop]
 *     00 2 img_tex 16*16 offsets (in 16s)
 *     ...
 *   ...
 * 31 1 map width (in tiles)
 *
 * bg data
 * = 00 0e 1c 2a ... 01 0f 1d 2b ... 02 10 1e 2c ...
 *
 * tex in 16*16 tile
 * 00 0e 1c 2a ...
 * 01 0f 1d 2b ...
 * 02 10 1e 2c ...
 * ...
 *
 * ffff = dummy
 * 00   =    c in map%03x.tex (size = 0x100)
 * 01   =  10c in map%03x.tex (size = 0x100)
 * 10   = 100c in map%03x.tex (size = 0x100)
 */
require 'common.inc';
$gp_pix  = '';
$gp_clut = array();

function sectmap( &$img, $dir, $id, $meta, $base )
{
	printf("=== sectmap( $dir, $id, %x )\n", $base);
	$map_w = ord( $meta[0x25] ) * 0x10;
	$map_h = ord( $meta[0x27] ) * 0x10;
	echo "map : $map_w x $map_h\n";

	$fn = sprintf('%s/%04d', $dir, $id);

	$pix = copypix_def($map_w,$map_h,PIX_BLACK);
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_pix, $gp_clut;
	$cid = ord( $meta[0x2b] ) & 0x0f;
	$pix['src']['pal'] = substr($gp_clut, $cid*0x400, 0x400);

	$pos = $base + ($id * 4);
	$pos = $base + str2int($img, $pos, 4);
	$map = '';
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$dat = str2int($img, $pos, 2, true);
				$pos += 2;

			if ( $dat < 0 )
			{
				$map .= '---- ';
				continue;
			}
			else
				$map .= sprintf('%4x ', $dat);

			$pix['src']['pix'] = substr($gp_pix, $dat*0x100, 0x100);
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix_fast($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )

		$map .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	echo "$map\n";
	savepix($fn, $pix);
	return;
}

function savemap( &$img, $dir )
{
	$off = str2int($img, 0x1c, 4);
	$siz = str2int($img, $off + 4, 4);

	$ed = $off + $siz;
	$st = $off + 0x18;
	$id = 0;
	while ( $st < $ed )
	{
		$meta = substr($img, $st, 0x54);

		sectmap( $img, $dir, $id, $meta, $off + $siz );
		$st += 0x54;
		$id++;
	}
	return;
}

function saga2( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$img = load_file("$pfx.img");
	$tex = load_file("$pfx.tex");
	if ( empty($img) )  return;
	if ( empty($tex) )  return;

	$dir = "{$pfx}_imgtex";

	global $gp_pix, $gp_clut;
	$off = str2int($tex, 4, 4);
	$gp_pix = substr($tex, 12, $off-12);

	$cc = str2int($tex, $off+0, 2); // always 0x100
	$cn = str2int($tex, $off+2, 2);
	printf("cc %x  cn %x\n", $cc, $cn);
	$pal = substr($tex, $off+4, $cc*$cn*2);
	$gp_clut = pal555($pal);

	$tex = '';
	savemap($img, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );

/*
	/mout/map.out is loaded to 800ac000
	data is loaded to 801a0000
 */
