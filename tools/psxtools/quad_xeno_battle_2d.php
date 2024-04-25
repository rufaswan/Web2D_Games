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
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';

function quad_keys( &$quad, &$meta, &$atlas )
{
	$quad['keyframe'] = array();
	foreach ( $meta as $mk => $mv )
	{
		$layer = array();
		foreach ( $mv as $lk => $lv )
		{
			list($x,$y,$w,$h) = $atlas->getxywh( $lv['atlas'] );
			$src = xywh_quad($w, $h);
			xywh_move($src, $x, $y);

			$ent = array(
				'dstquad'  => $lv['dst'],
				'srcquad'  => $src,
				'blend_id' => 0,
				'tex_id'   => 0,
				'_xywh'    => array($x,$y,$w,$h),
			);
			$layer[] = $ent;
		} // foreach ( $mv as $lk => $lv )

		$key = array(
			'name'  => "keyframe $mk",
			'layer' => $layer ,
		);
		list_add($quad['keyframe'], $mk, $key);
	} // foreach ( $meta as $mk => $mv )
	return;
}

function sectquad( &$meta, &$anim, &$atlas, $dir )
{
	$quad = load_idtagfile('psx xenogears');
	$quad['blend'] = array( blend_modes('normal') );

	quad_keys($quad, $meta, $atlas);

	save_quadfile($dir, $quad);
	return;
}
//////////////////////////////
function loadtex_256( &$file, $tex_off )
{
	$tex = array();
	$num = str2int($file, $tex_off, 3);
	for ( $i=0; $i < $num; $i++ )
	{
		$off = $tex_off + 4 + ($i * 4);
		$pos = str2int($file, $off, 3);

		$off = $tex_off + $pos;
		$w = str2int($file, $off + 0, 2);
		$h = str2int($file, $off + 2, 2);
			$off += 4;

		$siz = $w * $h * 2;
		$pix = substr($file, $off, $siz);
		bpp4to8($pix);

		$img = array(
			'w' => $w * 4,
			'h' => $h,
			'pix' => $pix,
		);
		$tex[$i] = $img;
	} // for ( $i=0; $i < $num; $i++ )
	return $tex;
}

function sectmeta( &$meta, &$atlas, &$tex, &$pal )
{
	$data = array();
	// fedc ba98 7654 3210
	// p--- ---c cccc cccc
	$b = str2int($meta, 0, 2);
		$cnt  = $b & 0x1ff;
		$p256 = $b & 0x8000;

	// p256 = fixed 256x256 texpage
	//   4 bytes header
	//     = 1 byte (& 80 = int16 , & 3f = count)
	//     = 3 bytes ???
	//   2 bytes entry
	//     -> 5 bytes data
	//        = (tex id , sx , sy , w , h)
	// else = pixel data
	//   6 bytes header
	//     = 1 byte (& 80 = int16 , & 3f = count)
	//     = 5 bytes ???
	//   4 bytes entry
	//     = 2 bytes offset * 4
	//       -> 4 bytes pixel header
	//          = (w , h , 8-bpp , 0)
	//       -> w*h bytes pixel data
	//     = 2 bytes ???
	if ( $p256 && $tex === -1 )
		return php_error('expect 256x256 texture , but has -1');

	for ( $i=0; $i < $cnt; $i++ )
	{
		$off = 2 + ($i * 2);
		$pos = str2int($meta, $off, 2);

		$b = str2int($meta, $pos, 1);
		$num   = $b & 0x3f;
		$int16 = $b & 0x80;

		if ( $p256 )
		{
			$img_off = $pos + 4;
			$dat_off = $img_off + ($num * 2);
		}
		else
		{
			$img_off = $pos + 6;
			$dat_off = $img_off + ($num * 4);
		}

		$bak   = $num;
		$layer = array();

		$flipy = false;
		$mx = 0;
		$my = 0;
		$rotate = 0;
		while ( $num > 0 )
		{
			// sub_8001dc10
			$bycod = str2int($meta, $dat_off, 1);
				$dat_off++;

			// 00-7f
			if ( ($bycod & 0x80) === 0 )
			{
				if ( $int16 )
				{
					$dx = str2int($meta, $dat_off + 0, 2, true);
					$dy = str2int($meta, $dat_off + 2, 2, true);
						$dat_off += 4;
				}
				else
				{
					$dx = str2int($meta, $dat_off + 0, 1, true);
					$dy = str2int($meta, $dat_off + 1, 1, true);
						$dat_off += 2;
				}

				// 7654 3210
				// -x?? cccc
				$flipx  = $bycod & 0x40;
				$cid    = $bycod & 0x0f;
				$srcpal = substr($pal, $cid*0x40, 0x40); // 16 colors
					$srcpal[3] = ZERO;

				if ( $p256 )
				{
					$p = str2int($meta, $img_off, 2);
						$img_off += 2;
					$s = substr($meta, $p, 5);

					$tid = ord($s[0]) >> 1;
					$sx  = ord($s[1]);
					$sy  = ord($s[2]);
					$w   = ord($s[3]);
					$h   = ord($s[4]);

					$curtex = &$tex[$tid];
					$srcpix = rippix8($curtex['pix'], $sx, $sy, $w, $h, $curtex['w'], $curtex['h']);
				}
				else
				{
					$p = str2int($meta, $img_off + 0, 2) * 4;
					$b = str2int($meta, $img_off + 2, 2); // ???
						$img_off += 4;
					$s = substr($meta, $p, 4);
						$p += 4;

					$w    = ord($s[0]);
					$h    = ord($s[1]);
					$bpp8 = ord($s[2]);
					if ( $bpp8 )
						$srcpix = substr($meta, $p, $w*$h);
					else
					{
						$srcpix = substr($meta, $p, ($w*$h)>>1);
						bpp4to8($srcpix);
					}
				}

				// w,h to quad -> move dx,dy -> rotate -> move mx,my
				$dst = xywh_quad($w, $h, $flipx, $flipy);
				xywh_move  ($dst, $dx, $dy);
				xywh_rotate($dst, $rotate);
				xywh_move  ($dst, $mx, $my);
				$aid = $atlas->putclut($w, $h, $srcpal, $srcpix);

				// default order is top-to-bottom
				$ent = array(
					'dst'   => $dst,
					'atlas' => $aid,
				);
				array_unshift($layer, $ent); // add in reverse

				$flipy = false;
				$num--;
				continue;
			}

			// command list , $num didnt decrease
			// 80-bf
			if ( ($bycod & 0x40) === 0 )
			{
				if ( $bycod & 0x04 )
					$flipy = true;

				if ( $bycod & 0x01 )
				{
					// ???
					$b = str2int($meta, $dat_off, 1);
						$dat_off++;
				}

				if ( $bycod & 0x02 )
				{
					// ???
					$b = str2int($meta, $dat_off, 1);
						$dat_off++;
				}
				continue;
			}

			// c0 = clear
			$mx = 0;
			$my = 0;
			$rotate = 0;

			// e0 = move
			if ( $bycod & 0x20 )
			{
				$mx = str2int($meta, $dat_off + 0, 1, true);
				$my = str2int($meta, $dat_off + 1, 1, true);
					$dat_off += 2;
			}

			// f0 = move + rotate
			if ( $bycod & 0x10 )
			{
				$b = str2int($meta, $dat_off, 1);
					$dat_off++;
				$rotate = ($b / 0x80) * pi();
			}
		} // while ( $num > 0 )

		$data[$i] = $layer;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $data;
}

function sectsprite( &$file, $met_off, &$tex, $dir )
{
	// 2 - 3d (data , seds)
	//     4 - data (clut + texture , ??? , ??? , ???)
	// 3 - 2d (anim , parts , clut)
	// 4 - 2d (anim , parts , clut , seds)
	// 5 - 2d (anim , parts , clut , seds , wds)
	// 6 - 2d (anim , parts , clut , file , seds , wds)
	$num = str2int($file, $met_off, 3);
	printf("=== sectsprite( %s , %x ) = %x\n", $dir, $met_off, $num);

	if ( $num === 2 )
		return php_warning('SKIP %s is 3D model', $dir);

	$b04 = str2int($file, $met_off +  4, 3);
	$b08 = str2int($file, $met_off +  8, 3);
	$b0c = str2int($file, $met_off + 12, 3);
	$b10 = str2int($file, $met_off + 16, 3);

	$anim = substr($file, $met_off + $b04, $b08 - $b04);
	$meta = substr($file, $met_off + $b08, $b0c - $b08);
	$pal  = substr($file, $met_off + $b0c, $b10 - $b0c);
		$pal = substr($pal, 4);
		$pal = pal555($pal);

	save_file("$dir.meta.0", $anim);
	save_file("$dir.meta.1", $meta);

	$atlas = new AtlasTex;
	$atlas->init();
	$meta = sectmeta($meta, $atlas, $tex, $pal);

	$atlas->sort();
	$atlas->save("$dir.0");

	sectquad($meta, $anim, $atlas, $dir);
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$num = str2int($file, 0, 3);
	$b04 = str2int($file, 4, 3);
	$tex = -1;

	// single sprite
	if ( ($num+2)*4 === $b04 )
		sectsprite($file, 0, $tex, "$dir/$dir");

	// multiple sprite set
	for ( $i=0; $i < $num; $i++ )
	{
		$off = 8 + ($i * 12);
		$met_off = str2int($file, $off + 0, 3);
		$tex_off = str2int($file, $off + 4, 3);
		if ( $tex_off < $b04 )
			continue;
		$tex = loadtex_256($file, $tex_off);
		sectsprite($file, $met_off, $tex, "$dir/$dir-$i");
	} // for ( $i=0; $i < $num; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
spr1 data loaded to 801a52e0
	then appended to 8010791c
spr2 data loaded + appended to 80109db4

over 256x256 canvas
	2625 2701
mixed spr1 + spr2 / ramsus fight (+fei)
	2709
mixed spr2 + 3d models
	3838

DEBUG 2998,0111.png , 1-1ac , 2-2528
	( 898 + 2528 + 6 + (a*4) = 2dee )
*/
