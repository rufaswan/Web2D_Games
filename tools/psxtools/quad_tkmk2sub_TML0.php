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

function sect_tim( &$file, &$pix, &$pal, $cnt_tim, $pos_tim )
{
	printf("== sect_tim( %x , %x )\n", $cnt_tim, $pos_tim);
	for ( $i=0; $i < $cnt_tim; $i++ )
	{
		$pos = 0x20 + ($i * 4);
		$pos = str2int($file, $pos, 3);

		$s = substr($file, $pos, 8);
		echo debug($s, dechex($pos));
			$pos += 8;

		if ( $i === 0 )
		{
			$pos += 12;
			$s = substr($file, $pos, 0x200);
				$pos += 0x200;
			$pal = pal555($s);
		}

		$w = str2int($file, $pos+ 8, 2) * 2; // 8-bpp
		$h = str2int($file, $pos+10, 2);
			$pos += 12;

		$pix[$i] = array(
			'w' => $w,
			'h' => $h,
			'pix' => substr($file, $pos, $w*$h),
		);
	} // for ( $i=0; $i < $cnt_tim; $i++ )
	return;
}

function sect_data( &$file, &$atlas, &$pix, &$pal, $cnt_dat, $pos_dat )
{
	printf("== sect_data( %x , %x )\n", $cnt_dat, $pos_dat);
	$data = array();
	for ( $i=0; $i < $cnt_dat; $i++ )
	{
		$pos = $pos_dat + ($i * 4);
		$pos = str2int($file, $pos, 3);

		$fnm = str2int($file, $pos+0, 3);
		$siz = str2int($file, $pos+4, 3);
		$dat = str2int($file, $pos+8, 3);
		if ( $siz === 0 )
			continue;

		$name = substr0($file, $fnm);
		$siz *= 12;
		$anim = array();
		$kid  = 0;
		for ( $j=0; $j < $siz; $j += 12 )
		{
			$s = substr($file, $dat + $j, 12);
			echo debug($s, $name);

			// 0   1    2   3   4  5  6 7  8 9  a b
			// op  tid  sx  sy  w  h  dx   dy   fps
			$op  = str2int($s,  0, 1);
			$tid = str2int($s,  1, 1);
			$sx  = str2int($s,  2, 1);
			$sy  = str2int($s,  3, 1);
			$w   = str2int($s,  4, 1);
			$h   = str2int($s,  5, 1);
			$dx  = str2int($s,  6, 2) - 0x100; // 200 / 2
			$dy  = str2int($s,  8, 2) - 0x100; // 200 / 2
			$fps = str2int($s, 10, 2);

			$tex = $pix[$tid];
			$src = rippix8($tex['pix'], $sx, $sy, $w, $h, $tex['w'], $tex['h']);
			$aid = $atlas->putclut($w, $h, $pal, $src);

			$ent = array(
				'atlas' => $aid ,
				'dx'    => $dx  ,
				'dy'    => $dy  ,
				'fps'   => $fps ,
			);

			switch ( $op )
			{
				case 0:
					$anim[$kid] = array($ent);
					$kid++;
				case 1:
					if ( ! isset($anim[$kid]) )
						$anim[$kid] = array();
					$anim[$kid][] = $ent;
					break;
				case 2:
					$anim[$kid][] = $ent;
					$kid++;
					break;
				default:
					return php_error('[%s + %x] unknown op %x', $name, $j, $op);
			} // switch ( $op )
		} // for ( $i=0; $i < $siz; $i += 12 )

		$data[$i] = array(
			'name' => $name ,
			'anim' => $anim ,
		);
	} // for ( $i=0; $i < $cnt_dat; $i++ )
	return $data;
}

function sect_quad( &$atlas, &$data, &$quad )
{
	printf("== sect_quad()\n");
	$quad['keyframe' ] = array();
	$quad['animation'] = array();
	$kid = 0;
	foreach ( $data as $dk => $dv )
	{
		$time = array();
		foreach ( $dv['anim'] as $ak => $av )
		{
			$fps = 0;

			$layer = array();
			foreach ( $av as $avk => $avv )
			{
				$fps = $avv['fps'];

				list($x,$y,$w,$h) = $atlas->getxywh( $avv['atlas'] );
				$src = xywh_quad($w, $h);
				xywh_move($src, $x, $y);

				$dst = xywh_quad($w, $h);
				xywh_move($dst, $avv['dx'], $avv['dy']);

				$ent = array(
					'dstquad'  => $dst,
					'srcquad'  => $src,
					'blend_id' => 0,
					'tex_id'   => 0,
					'_xywh'    => array($x,$y,$w,$h),
				);
				$layer[] = $ent;
			} // foreach ( $av as $avk => $avv )

			$name = sprintf('%s %d', $dv['name'], $ak);
			$key = array(
				'name'  => $name,
				'layer' => $layer ,
			);
			list_add($quad['keyframe'], $kid, $key);
				$kid++;

			$tent = array(
				'time'   => $fps,
				'attach' => quad_attach('keyframe', $kid-1),
			);
			$time[] = $tent;
		} // foreach ( $dv['anim'] as $ak => $av )

		if ( $dv['name'] === 'base' )
			continue;

		$anim = array(
			'name'     => $dv['name'],
			'timeline' => $time ,
			'loop_id'  => 0 ,
		);
		list_add($quad['animation'], $dk, $anim);
	} // foreach ( $data as $dk => $dv )


	$quad['skeleton'] = array();
	foreach ( $quad['animation'] as $kk => $kv )
	{
		if ( $kv['name'][0] !== 'k' ) // is mouth
			continue;
		foreach ( $quad['animation'] as $mk => $mv )
		{
			if ( $mv['name'][0] !== 'm' ) // is eyes
				continue;

			// still
			$bone = array();
			$bone[0] = array(
				//'name'   => 'base',
				'attach' => quad_attach('keyframe', 0),
			);
			$bone[1] = array(
				//'name'   => $mv['name'],
				'attach' => quad_attach('animation', $mk),
			);
			$bone[2] = array(
				//'name'   => $kv['name'],
				'attach' => $kv['timeline'][0]['attach'],
			);

			$skel = array(
				'name' => sprintf('%s %s', $kv['name'], $mv['name']),
				'bone' => $bone,
			);
			$quad['skeleton' ][] = $skel;

			// animated
			$bone[2] = array(
				//'name'   => $kv['name'],
				'attach' => quad_attach('animation', $kk),
			);

			$skel = array(
				'name' => sprintf('%s %s (talking)', $kv['name'], $mv['name']),
				'bone' => $bone,
			);
			$quad['skeleton' ][] = $skel;
		} // foreach ( $quad['animation'] as $mv )
	} // foreach ( $quad['animation'] as $kv )
	return;
}
//////////////////////////////
function tm2sub( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0,4) !== 'TML0' )
		return;

	$cnt_tim = str2int($file,  8, 3);
	$cnt_dat = str2int($file, 12, 3);
	$pos_tim = str2int($file, 16, 3);
	$pos_dat = str2int($file, 20, 3);

	$pal = '';
	$pix = array();
	sect_tim($file, $pix, $pal, $cnt_tim, $pos_tim);
		$pal[3] = ZERO; // transparent background

	$atlas = new atlas_tex;
	$atlas->init();
	$data = sect_data($file, $atlas, $pix, $pal, $cnt_dat, $pos_dat);

	$atlas->sort();
	$atlas->save("$fname.0");

	$quad = load_idtagfile('psx tokimemo 2 substories');
	$quad['blend'] = array( blend_modes('normal') );
	sect_quad($atlas, $data, $quad);

	save_quadfile($fname, $quad);
	return;
}

argv_loopfile($argv, 'tm2sub');
