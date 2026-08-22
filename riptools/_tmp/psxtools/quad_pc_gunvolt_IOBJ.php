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
require 'common-json.inc';
require 'class-ieee754.inc';
require 'quad.inc';

function is_textile8( &$quad )
{
	if ( ! isset($quad['keyframe']) )
		return false;

	$has_tile = false;
	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		foreach ( $kv['layer'] as $lk => $lv )
		{
			if ( empty($lv) )
				continue;
			$has_tile = true;

			// h  16  24  32  40  48  56  64  184
			// => n * 8
			// h  18  26  34  42  50  58  66  186
			// => 2 + (n * 8)
			for ( $i=0; $i < 8; $i++ )
			{
				$src = $lv['srcquad'][$i];
				if ( $src & 7 )
					return false;
			}
		} // foreach ( $kv['layer'] as $lk => $lv )
	} // foreach ( $quad['keyframe'] as $kk => $kv )

	// is 8x8 tiles
	// make sure it is not empty
	return $has_tile;
}

function textile8_fix( &$quad )
{
	if ( ! is_textile8($quad) )
		return;

	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		foreach ( $kv['layer'] as $lk => $lv )
		{
			if ( empty($lv) )
				continue;

			$src = $lv['srcquad'];
			$cx = ($src[0] + $src[2] + $src[4] + $src[6]) / 4;
			$cy = ($src[1] + $src[3] + $src[5] + $src[7]) / 4;

			for ( $i=0; $i < 8; $i += 2 )
			{
				$x = $lv['srcquad'][$i+0];
				$y = $lv['srcquad'][$i+1];
				( $x < $cx ) ? $x++ : $x--;
				( $y < $cy ) ? $y++ : $y--;
				$lv['srcquad'][$i+0] = $x;
				$lv['srcquad'][$i+1] = $y;
			}
		} // foreach ( $kv['layer'] as $lk => $lv )
	} // foreach ( $quad['keyframe'] as $kk => $kv )
	return;
}

function sectquad( &$file, $off, $w, $h, &$dqd, &$sqd )
{
	$float = [];
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		$p = $off + $i;
		$b = tool::ordstr($file, $p, 4);
		$float[] = ieee754::to($b, 4);
	}

	// dqd    sqd
	//  0  1   2  3  c1
	//  4  5   6  7  c2
	//  8  9  10 11  c3
	// 12 13  14 15  c4
	$dqd = [
		$float[ 0] , $float[ 1] ,
		$float[ 4] , $float[ 5] ,
		$float[ 8] , $float[ 9] ,
		$float[12] , $float[13] ,
	];
	$sqd = [
		$float[ 2]*$w , $float[ 3]*$h ,
		$float[ 6]*$w , $float[ 7]*$h ,
		$float[10]*$w , $float[11]*$h ,
		$float[14]*$w , $float[15]*$h ,
	];
	return;
}

function sect_spr( &$quad, &$file, $ptgt_off, $img )
{
	$cnt = tool::ordstr($file, $ptgt_off+8, 4);
	$off1 = $ptgt_off + 12;
	$off2 = $ptgt_off + 12 + ($cnt * 8);

	$quad['keyframe'] = [];
	for ( $i1=0; $i1 < $cnt; $i1++ )
	{
		// 0 1 2 3  4 5  6 7
		// off?     no   - -
		$no  = tool::ordstr($file, $off1+4, 1);
		$tid = tool::ordstr($file, $off1+5, 1);
			$off1 += 8;

		$layer = [];
		for ( $i2=0; $i2 < $no; $i2++ )
		{
			$dqd = [];
			$sqd = [];
			sectquad($file, $off2, $img[$tid]['w'], $img[$tid]['h'], $dqd, $sqd);
				$off2 += 0x40;

			$lv = [
				'dstquad'  => $dqd,
				'srcquad'  => $sqd,
				'tex_id'   => $tid,
				'blend_id' => 0,
			];
			quad_convexfix($lv);

			$layer[] = $lv;
		} // for ( $i2=0; $i2 < $no; $i2++ )

		$key = [
			'name'  => "keyframe $i1",
			'layer' => $layer,
		];
		list_add($quad['keyframe'], $i1, $key);
	} // for ( $i1=0; $i1 < $cnt; $i1++ )

	return;
}

function sect_anim( &$quad, &$file, $off1, $off2 )
{
	$sub = substr($file, $off1, $off2-$off1);
	$quad['animation'] = [];

	$cnt = tool::ordstr($sub, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$p1 = tool::ordstr($sub, $p+0, 4);
		if ( ($i+1) < $cnt )
			$p2 = tool::ordstr($sub, $p+4, 4);
		else
			$p2 = $off2 - $off1;

		$len = $p2 - $p1;
		$dat = tool::substr($sub, $p1, $len);

		$time = [];
		$loop = -1;
		for ( $i2=0; $i2 < $len; $i2 += 4 )
		{
			// 1 2  3 4
			// id   no
			$fid = $i2 >> 2;
			$kid = tool::ordstr($dat, $i2+0, 2);
			$fps = tool::ordstr($dat, $i2+2, 1, true);

			switch ( $fps )
			{
				case -1: // no loop
					break 2;
				case -2: // loop
					$loop = 0;
					break 2;
				default:
					if ( $fps < 0 )
						php_warning('loop %d < 0', $fps);

					$time[$fid] = [
						'time'   => $fps,
						'attach' => quad_attach('keyframe', $kid),
					];
					break;
			} // switch ( $fps )
		} // for ( $i2=0; $i2 < $len; $i2 += 4 )

		$anim = [
			'name'     => "animation $i",
			'timeline' => $time,
			'loop_id'  => $loop,
		];

		list_add($quad['animation'], $i, $anim);
	} // for ( $i=0; $i < $cnt; $i++ )

	return;
}
//////////////////////////////
function gv_pixd( &$file, $pos )
{
	$siz = tool::ordstr($file, $pos+ 0, 4);
	$w   = tool::ordstr($file, $pos+ 4, 4);
	$h   = tool::ordstr($file, $pos+ 8, 4);
	$typ = tool::ordstr($file, $pos+12, 4);
		$pos += 0x80;

	if ( ! isset( $file[$siz-1] ) )
		tool::error('gv_pixd not enough data');

	$img = [
		'w' => $w,
		'h' => $h,
		'pix' => '',
	];
	switch ( $typ )
	{
		case 4:
			echo "32-bpp BGRA\n";
			for ( $i=0; $i < $siz; $i += 4 )
			{
				$img['pix'] .= $file[$pos+2];
				$img['pix'] .= $file[$pos+1];
				$img['pix'] .= $file[$pos+0];
				$img['pix'] .= $file[$pos+3];
					$pos += 4;
			}
			break;
		case 5:
			echo "16-bpp BGRA\n";
			for ( $i=0; $i < $siz; $i += 2 )
			{
				$b1 = ord( $file[$pos+0] );
				$b2 = ord( $file[$pos+1] );
					$pos += 2;

				$b = ($b1 & BIT4) * 0x11;
				$g = ($b1 >>   4) * 0x11;
				$r = ($b2 & BIT4) * 0x11;
				$a = ($b2 >>   4) * 0x11;

				$img['pix'] .= chr($r) . chr($g) . chr($b) . chr($a);
			}
			break;
		default:
			tool::error('UNKNONW pixd type', $typ);
	} // switch ( $typ )
	return $img;
}

function sect_tpli( &$sect, &$img )
{
	if ( ! isset( $sect['TLPI'] ) )
		return;
	$pix = $img[0]['pix'];
	$w = $img[0]['w'];
	$h = $img[0]['h'];

	$cn = tool::ordstr($sect['TLPI'], 4, 2);
	$cc = tool::ordstr($sect['TLPI'], 6, 2);
	if ( $cc !== 0x100 )
		tool::error('TPLI not 256 colors = %x', $cc);

	$buf = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = ord( $pix[$i] ) & 0x7f;
		$buf .= chr($b);
	}

	$blk = $cc * 4;
	$img = [];
	for ( $i=0; $i < $cn; $i++ )
	{
		$pos = 0x10 + ($i * $blk);
		$pal = '';
		for ( $j=0; $j < $blk; $j += 4 )
		{
			$pal .= $sect['TLPI'][$pos+2];
			$pal .= $sect['TLPI'][$pos+1];
			$pal .= $sect['TLPI'][$pos+0];
			$pal .= $sect['TLPI'][$pos+3];
				$pos += 4;
		}
		$clut = [
			'cc'  => $cc,
			'w'   => $w,
			'h'   => $h,
			'pal' => $pal,
			'pix' => $buf,
		];
		$img[] = $clut;
	}
	return;
}
//////////////////////////////
function pfxname( $pfx )
{
	$pfx = str_replace('\\', '/', $pfx);
	$pos = strrpos($pfx, '/');
	if ( $pos === false )
		return $pfx;

	return substr($pfx, $pos+1);
}

function sect_iobj( &$quad, &$sect, $pfx )
{
	if ( ! isset($sect['IOBJ']) )
		return;

	$anim_off = tool::ordstr($sect['IOBJ'],  4, 4);
	$ptgt_off = tool::ordstr($sect['IOBJ'],  8, 4);
	$pixd_cnt = tool::ordstr($sect['IOBJ'], 12, 4);
	$pixd_off = 0x10;
	printf("== sect_iobj( %s ) = %d\n", $pfx, $pixd_cnt);

	if ( substr($sect['IOBJ'],$ptgt_off,4) !== 'PTGT' )
		tool::warning('IOBJ-PTGT not found', $ptgt_off);

	$img = [];
	for ( $i=0; $i < $pixd_cnt; $i++ )
	{
		$p = tool::ordstr($sect['IOBJ'], $pixd_off, 4);
			$pixd_off += 4;
		$img[] = gv_pixd($sect['IOBJ'], $p);
	}
	sect_tpli($sect, $img);

	sect_spr ($quad, $sect['IOBJ'], $ptgt_off, $img);
	sect_anim($quad, $sect['IOBJ'], $anim_off, $ptgt_off);

	$pfx2 = pfxname($pfx);
	save_quadfile("$pfx/$pfx2", $quad);

	foreach ( $img as $k => $v )
	{
		if ( isset( $sect['TLPI'] ) )
			$fn = sprintf('%s/%s-%d.0.rgba', $pfx, $pfx2, $k);
		else
			$fn = sprintf('%s/%s.%d.rgba', $pfx, $pfx2, $k);
		save_clutfile($fn, $v);
	}
	return;
}

function gunvolt( $fname, $idtag )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$len = strlen($file);

	if ( $idtag == '' )
		tool::error('NO TAG', $fname);
	$quad = load_idtagfile($idtag);
	$quad['blend'] = [ blend_modes('normal') ];

	// no duplicate magic in one file
	// TLPI will have IOBJ , with pix_cnt = 1 always
	$cnt = tool::ordstr($file, 0, 4);
	$sect = [];
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 0x10);
		$pos = tool::ordstr($file, $p+ 8, 4);
		$siz = tool::ordstr($file, $p+12, 4);
		$sub = tool::substr($file, $pos, $siz);

		$mgc = substr($sub, 0, 4);
		switch ( $mgc )
		{
			case 'IOBJ':
			case 'TLPI':
			case 'ICDB':
			case 'CGFX':
				$type = $mgc;
				$sect[$mgc] = $sub;
				break;
			default:
				$mgc = tool::ord($mgc);
				if ( ($mgc+0x80) == $siz )
				{
					$type = 'pix ';
					$img = gv_pixd($sub, 0);
					save_clutfile("$pfx/img.$i.rgba", $img);
				}
				else
					$type = '????';
				break;
		} // switch ( $mgc )

		printf("%8x , %8x , %s , %s.%d\n", $pos, $siz, $type, $pfx, $i);
	} // for ( $i=0; $i < $cnt; $i++ )

	sect_iobj($quad, $sect, $pfx);
	return;
}

printf("%s  -bmz/-gv/-gv2/-gva/-mgv  FILE...\n", $argv[0]);
$idtag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case 'bsm' :  case '-bsm' :
		case 'bmz' :  case '-bmz' :
			$idtag = 'pc blast master zero';
			break;
		case 'gv'  :  case '-gv'  :
		case 'gv1' :  case '-gv1' :
			$idtag = 'pc gunvolt 1';
			break;
		case 'gv2' :  case '-gv2' :
			$idtag = 'pc gunvolt 2';
			break;
		case 'gva' :  case '-gva' :
		case 'laix':  case '-laix':
			$idtag = 'pc gunvolt laix';
			break;
		case 'mgv' :  case '-mgv' :
			$idtag = 'pc mighty gunvolt';
			break;
		default:
			gunvolt( $argv[$i], $idtag );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )

/*
staff roll
gv1  resarc/resarc_30_add_00/4354.17
gv1  resarc/resarc_30_add_00/4355.17
gv1  resarc/resarc_30_add_00/4356.17
gv1  resarc/resarc_30_add_00/4357.17

gv2  resarc/eu_cmn_arc/1270.17
gv2  resarc/eu_cmn_arc/1276.17

mgv  resarc/resarc_en/4020.17
 */
