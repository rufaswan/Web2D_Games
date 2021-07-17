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
require "common-guest.inc";
require "quad.inc";

$gp_json = array();
$gp_tag  = '';

function sectquad( &$file, $off, $w, $h, &$sqd, &$dqd )
{
	$float =array();
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		$p = $off + $i;
		$b = substr($file, $p, 4);
		$float[] = float32($b);
	}

	// dqd    sqd
	//  0  1   2  3  c1
	//  4  5   6  7  c2
	//  8  9  10 11  c3
	// 12 13  14 15  c4
	$dqd = array(
		$float[ 0] , $float[ 1] ,
		$float[ 4] , $float[ 5] ,
		$float[ 8] , $float[ 9] ,
		$float[12] , $float[13] ,
	);
	$sqd = array(
		$float[ 2]*$w , $float[ 3]*$h ,
		$float[ 6]*$w , $float[ 7]*$h ,
		$float[10]*$w , $float[11]*$h ,
		$float[14]*$w , $float[15]*$h ,
	);

	// auto-shrink quad
	// 0,0           32,0  <- even number , need 1 pixel padding
	//     1,1  31,1       <-  odd number , all OK
	//     1,31 31,31
	// 0,32          32,32

	// even x
	if ( ((int)$sqd[0] & 1) === 0 )
	{
		$cx = ($sqd[0] + $sqd[4]) / 2;
		if ( $sqd[0] < $cx )
		{
			$sqd[0]++;  $sqd[6]--;
			$sqd[2]++;  $sqd[4]--;
		}
		else
		{
			$sqd[0]--;  $sqd[6]++;
			$sqd[2]--;  $sqd[4]++;
		}
	} // if ( ((int)$sqd[0] & 1) === 0 )

	// even y
	if ( ((int)$sqd[1] & 1) === 0 )
	{
		$cy = ($sqd[1] + $sqd[5]) / 2;
		if ( $sqd[1] < $cy )
		{
			$sqd[1]++;  $sqd[7]++;
			$sqd[3]--;  $sqd[5]--;
		}
		else
		{
			$sqd[1]--;  $sqd[7]--;
			$sqd[3]++;  $sqd[5]++;
		}
	} // if ( ((int)$sqd[1] & 1) === 0 )

	return;
}

function sect_spr( &$file, $ptgt_off, $img )
{
	global $gp_json;
	$cnt = str2int($file, $ptgt_off+8, 4);
	$off1 = $ptgt_off + 12;
	$off2 = $ptgt_off + 12 + ($cnt * 8);

	for ( $i1=0; $i1 < $cnt; $i1++ )
	{
		// 0 1 2 3  4 5  6 7
		// off?     no   - -
		$no  = str2int($file, $off1+4, 1);
		$tid = str2int($file, $off1+5, 1);
			$off1 += 8;

		$data = array();
		for ( $i2=0; $i2 < $no; $i2++ )
		{
			$sqd = array();
			$dqd = array();
			sectquad($file, $off2, $img[$tid]['w'], $img[$tid]['h'], $sqd, $dqd);
				$off2 += 0x40;

			$data[$i2] = array(
				'SrcQuad' => $sqd,
				'DstQuad' => $dqd,
				'TexID'   => $tid,
			);
		} // for ( $i2=0; $i2 < $no; $i2++ )

		$gp_json['Frame'][$i1] = $data;
	} // for ( $i1=0; $i1 < $cnt; $i1++ )

	return;
}
//////////////////////////////
function sect_anim( &$file, $off1, $off2 )
{
	$sub = substr($file, $off1, $off2-$off1);

	global $gp_json;
	$cnt = str2int($sub, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$p1 = str2int($sub, $p+0, 4);
		if ( ($i+1) < $cnt )
			$p2 = str2int($sub, $p+4, 4);
		else
			$p2 = $off2 - $off1;

		$len = $p2 - $p1;
		$dat = substr($sub, $p1, $len);

		$ent = array(
			'FID' => array(),
			'FPS' => array(),
		);
		$name = sprintf("anim_%d", $i);
		for ( $i2=0; $i2 < $len; $i2 += 4 )
		{
			// 1 2  3 4
			// id   no
			$id = str2int($dat, $i2+0, 2);
			$no = str2int($dat, $i2+2, 1, true);
			if ( $no < 0 )
			{
				php_notice("%s[%x] = %x , %d\n", $name, $i2, $id, $no);
				continue;
			}
			$ent['FID'][] = $id;
			$ent['FPS'][] = $no;
		}

		$gp_json['Animation'][$name][0] = $ent;
	} // for ( $i=0; $i < $cnt; $i++ )

	return;
}
//////////////////////////////
function gv_pixd( &$file, $pos )
{
	$siz = str2int($file, $pos+ 0, 4);
	$w   = str2int($file, $pos+ 4, 4);
	$h   = str2int($file, $pos+ 8, 4);
	$typ = str2int($file, $pos+12, 4);
		$pos += 0x80;

	if ( ! isset( $file[$siz-1] ) )
		return php_error("gv_pixd not enough data");

	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => '',
	);
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
			return php_error("UNKNONW pixd type %x", $typ);
	} // switch ( $typ )
	return $img;
}
//////////////////////////////
function sect_TLPI( &$sect, &$img, $pfx )
{
	if ( ! isset( $sect['TLPI'] ) )
		return;
	$pix = $img[0]['pix'];
	$w = $img[0]['w'];
	$h = $img[0]['h'];

	$cn = str2int($sect['TLPI'], 4, 2);
	$cc = str2int($sect['TLPI'], 6, 2);
	if ( $cc !== 0x100 )
		return php_error("TPLI not 256 colors = %x", $cc);

	$buf = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = ord( $pix[$i] ) & 0x7f;
		$buf .= chr($b);
	}

	$blk = $cc * 4;
	$img = array();
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
		$clut = array(
			'cc'  => $cc,
			'w'   => $w,
			'h'   => $h,
			'pal' => $pal,
			'pix' => $buf,
		);
		$img[] = $clut;
	}
	return;
}

function sect_IOBJ( &$sect, $pfx )
{
	if ( ! isset($sect['IOBJ']) )
		return;

	$anim_off = str2int($sect['IOBJ'],  4, 4);
	$ptgt_off = str2int($sect['IOBJ'],  8, 4);
	$pixd_cnt = str2int($sect['IOBJ'], 12, 4);
	$pixd_off = 0x10;
	printf("== sect_IOBJ( %s ) = %d\n", $pfx, $pixd_cnt);

	if ( substr($sect['IOBJ'],$ptgt_off,4) != 'PTGT' )
		php_warning("IOBJ-PTGT not found = %x", $ptgt_off);

	$img = array();
	for ( $i=0; $i < $pixd_cnt; $i++ )
	{
		$p = str2int($sect['IOBJ'], $pixd_off, 4);
			$pixd_off += 4;
		$img[] = gv_pixd($sect['IOBJ'], $p);
	}
	sect_TLPI($sect, $img, $pfx);

	sect_anim($sect['IOBJ'], $anim_off, $ptgt_off);
	sect_spr ($sect['IOBJ'], $ptgt_off, $img);

	global $gp_json, $gp_tag;
	save_quadfile("$pfx/$gp_tag", $gp_json);

	foreach ( $img as $k => $v )
	{
		if ( isset( $sect['TLPI'] ) )
			$fn = sprintf("%s/%s-%d.0.rgba", $pfx, $gp_tag, $k);
		else
			$fn = sprintf("%s/%s.%d.rgba", $pfx, $gp_tag, $k);
		save_clutfile($fn, $v);
	}
	return;
}

function gunvolt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$len = strlen ($file);

	global $gp_json, $gp_tag;
	if ( $gp_tag == '' )
		return php_error('NO TAG %s', $fname);
	$gp_json = load_idtagfile($gp_tag);

	// no duplicate magic in one file
	// TLPI will have IOBJ , with pix_cnt = 1 always
	$cnt = str2int($file, 0, 4);
	$sect = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 0x10);
		$pos = str2int($file, $p+ 8, 4);
		$siz = str2int($file, $p+12, 4);
		$sub = substr($file, $pos, $siz);

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
				$mgc = ordint($mgc);
				if ( ($mgc+0x80) == $siz )
				{
					$type = "pix ";
					$img = gv_pixd($sub, 0);
					save_clutfile("$pfx/$gp_tag.$i.rgba", $img);
				}
				else
					$type = "????";
				break;
		} // switch ( $mgc )

		printf("%8x , %8x , %s , %s.%d\n", $pos, $siz, $type, $pfx, $i);
	} // for ( $i=0; $i < $cnt; $i++ )

	sect_IOBJ($sect, $pfx);
	return;
}

echo "{$argv[0]}  -bsm/-gv/-gv2/-gva/-mgv  FILE...\n";
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-bsm':  $gp_tag = 'pc_bsm'; break;
		case '-gv' :  $gp_tag = 'pc_gv' ; break;
		case '-gv2':  $gp_tag = 'pc_gv2'; break;
		case '-gva':  $gp_tag = 'pc_gva'; break;
		case '-mgv':  $gp_tag = 'pc_mgv'; break;
		default:
			gunvolt( $argv[$i] );
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
