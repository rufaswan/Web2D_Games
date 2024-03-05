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
 *
 * Special Thanks
 *   TPL File Format
 *   http://wiki.tockdom.com/w/index.php?title=TPL_%28File_Format%29
 *   http://wiki.tockdom.com/w/index.php?title=Image_Formats
 */
require 'common.inc';
require 'common-guest.inc';

//define('DRY_RUN', true);

$gp_ifmt = array(
	0x6 => 'im_rgba32', // 32-bit , 10*4 = 40
	0x8 => 'im_c4',     //  4-bit ,  4*8 = 20
	0x9 => 'im_c8',     //  8-bit ,  8*4 = 20
	0xa => 'im_c14x2',  // 16-bit ,  8*4 = 20
	0xe => 'im_cmpr',   //  4-bit ,  4*8 = 20
);
$gp_pfmt = array(
	0 => 'cl_ia8',
	1 => 'cl_rgb565',
	2 => 'cl_rgb5a3',
);

//////////////////////////////
function cl_ia8( $str )
{
	if ( strlen($str) != 2 )
		php_error('cl_ia8() is not 2 [%x]', strlen($str));

	// fedc ba98 7654 3210
	// aaaa aaaa cccc cccc
	return $str[0] . $str[0] . $str[0] . $str[1];
	//return $str[1] . $str[1] . $str[1] . $str[0];
}

function cl_rgb565( $str )
{
	if ( strlen($str) != 2 )
		php_error('cl_rgb565() is not 2 [%x]', strlen($str));

	// fedc ba98  7654 3210
	// rrrr rggg  gggb bbbb
	$pal = ordint($str);
	$b = ($pal << 3) & 0xf8; // << 11 >> 8
	$g = ($pal >> 3) & 0xfc; // <<  5 >> 8
	$r = ($pal >> 8) & 0xf8; // <<  0 >> 8
	return chr($r) . chr($g) . chr($b) . BYTE;
}

function cl_rgb5a3( $str )
{
	if ( strlen($str) != 2 )
		php_error('cl_rgb5a3() is not 2 [%x]', strlen($str));

	$pal = ordint($str);
	if ( $pal & 0x8000 )
	{
		// fedc ba98 7654 3210
		// -rrr rrgg gggb bbbb
		$b = ($pal << 3) & 0xf8; // << 11 >> 8
		$g = ($pal >> 2) & 0xf8; // <<  6 >> 8
		$r = ($pal >> 7) & 0xf8; // <<  1 >> 8
		$a = BIT8;
	}
	else
	{
		// fedc ba98 7654 3210
		// -aaa rrrr gggg bbbb
		$b = ($pal << 4) & 0xf0; // << 12 >> 8
		$g = ($pal >> 0) & 0xf0; // <<  8 >> 8
		$r = ($pal >> 4) & 0xf0; // <<  4 >> 8
		$a = ($pal >> 7) & 0xe0; // <<  1 >> 8
	}
	return chr($r) . chr($g) . chr($b) . chr($a);
}

function cl_rgba32( $block )
{
	if ( strlen($block) != 0x40 )
		php_error('cl_rgba32() is not 0x40 [%x]', strlen($block));

	$pix = '';
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		// planar
		//  a r a r  a r a r  a r a r  a r a r
		//  a r a r  a r a r  a r a r  a r a r
		//  g b g b  g b g b  g b g b  g b g b
		//  g b g b  g b g b  g b g b  g b g b
		$a = $block[$i+0x00];
		$r = $block[$i+0x01];
		$g = $block[$i+0x20];
		$b = $block[$i+0x21];
		$pix .= $r . $g . $b . $a;
	}
	return $pix;
}
////////////////////////////////////////
function cmpr_inter( $c1, $f1, $c2, $f2 )
{
	$c1r = ord( $c1[0] ) * $f1;
	$c1g = ord( $c1[1] ) * $f1;
	$c1b = ord( $c1[2] ) * $f1;
	$c2r = ord( $c2[0] ) * $f2;
	$c2g = ord( $c2[1] ) * $f2;
	$c2b = ord( $c2[2] ) * $f2;
	$cr = int_clamp($c1r + $c2r, 0, BIT8);
	$cg = int_clamp($c1g + $c2g, 0, BIT8);
	$cb = int_clamp($c1b + $c2b, 0, BIT8);
	return chr($cr) . chr($cg) . chr($cb) . BYTE;
}
function tpl_dxt1( $str )
{
	// CMPR blocks are 2x2 DXT1 subblocks
	// DXT1 blocks are 4x4 pixels =  8 bytes
	// CMPR blocks are 8x8 pixels = 32 bytes
	$dxt = array();
	for ( $i=0; $i < 0x20; $i += 8 )
	{
		$bk = '';

		// https://en.wikipedia.org/wiki/S3_Texture_Compression#DXT1
		$c0 = substr($str, $i+0, 2);
		$c1 = substr($str, $i+2, 2);

		$pal = array();
		$pal[] = cl_rgb565($c0[1] . $c0[0]);
		$pal[] = cl_rgb565($c1[1] . $c1[0]);

		$c01 = ordint($c0[1] . $c0[0]);
		$c11 = ordint($c1[1] . $c1[0]);
		if ( $c01 > $c11 )
		{
			$pal[] = cmpr_inter( $pal[0], 2/3, $pal[1], 1/3 );
			$pal[] = cmpr_inter( $pal[0], 1/3, $pal[1], 2/3 );
		}
		else
		{
			$pal[] = cmpr_inter( $pal[0], 1/2, $pal[1], 1/2 );
			$pal[] = PIX_ALPHA;
		}

		$byop = 0;
		$byln = 0;
		$p = $i + 4;
		$ed = 16;
		while ( $ed > 0 )
		{
			if ( $byln == 0 )
			{
				$byop = ord( $str[$p] );
				$p++;
				$byln = 8;
			}

			$b1 = ($byop >> 6) & 3;
			$byop <<= 2;
			$byln -= 2;
			$bk .= $pal[$b1];
			$ed--;
		} // while ( $ed > 0 )

		$dxt[] = $bk;
	} // for ( $i=0; $i < 0x20; $i += 8 )

	// forming 2x2 CMPR block
	$pix = '';
	for ( $i=0; $i < 4; $i++ )
	{
		$pix .= substr($dxt[0], $i*0x10, 0x10);
		$pix .= substr($dxt[1], $i*0x10, 0x10);
	}
	for ( $i=0; $i < 4; $i++ )
	{
		$pix .= substr($dxt[2], $i*0x10, 0x10);
		$pix .= substr($dxt[3], $i*0x10, 0x10);
	}

	return $pix;
}
//////////////////////////////
function tplimage( &$pix, $iw, $ih, $byte, $bw, $bh )
{
	if ( defined('DRY_RUN') )
		return;
	printf("== tplimage( %x , %x , $byte , %x , %x )\n", $iw, $ih, $bw, $bh);
	$cw = $iw / $bw;
	$ch = $ih / $bh;
	$row_sz = $bw * $bh * $cw * $byte;

	// untile pix into normal image
	$res = '';
	$ed = strlen($pix);
	$st = 0;
	while ( $st < $ed )
	{
		$buf = array();
		for ( $x=0; $x < $cw; $x++ )
		{
			for ( $y=0; $y < $bh; $y++ )
			{
				if ( ! isset( $buf[$y] ) )
					$buf[$y] = '';
				$buf[$y] .= substr($pix, $st, $bw*$byte);
				$st += ($bw * $byte);
			}
		}
		$res .= implode('', $buf);
	}
	$pix = $res;
	return;
}

function tplformat( &$file, $pos, $fmt, $iw, $ih, &$wiipal )
{
	printf("== tplformat( %x , $fmt , %x , %x )\n", $pos, $iw, $ih);
	$pix = '';
	switch ( $fmt )
	{
		case  6: // im_rgba32
			$iwb = int_ceil($iw, 4);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 4;
			printf("SIZ %x\n", $siz);
			while ( $siz > 0 )
			{
				$pix .= cl_rgba32( substr($file, $pos, 0x40) );
				$siz -= 0x40;
				$pos += 0x40;
			}
			break;
		case  8: // im_c4
			$iwb = int_ceil($iw, 4);
			$ihb = int_ceil($ih, 8);
			$byte = 1;
			$bw = 8;
			$bh = 8;

			$siz = $iwb / 2 * $ihb;
			printf("SIZ %x\n", $siz);
			while ( $siz > 0 )
			{
				$pal = ord( $file[$pos] );
				$b1 = ($pal >> 4) & 0x0f;
				$b2 = ($pal >> 0) & 0x0f;
				$pix .= chr($b1) . chr($b2);
				$siz--;
				$pos++;
			}
			break;
		case  9: // im_c8
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 1;
			$bw = 8;
			$bh = 4;

			$siz = $iwb * $ihb;
			printf("SIZ %x\n", $siz);
			$pix = substr($file, $pos, $siz);
			$pos += $siz;
			break;
		case 10: // im_c14x2
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			printf("SIZ %x\n", $siz);
			while ( $siz > 0 )
			{
				$pal = ordint( $file[$pos+1] . $file[$pos+0] );
				$b1 = $pal & 0x3fff;
				$pix .= substr($wiipal, $b1*4, 4);
				$siz -= 2;
				$pos += 2;
			}
			break;
		case 14: // im_cmpr
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 8);
			$byte = 4;
			$bw = 8;
			$bh = 8;

			$siz = $iwb/8 * $ihb/8;
			printf("SIZ %x\n", $iwb*$ihb);
			while ( $siz > 0 )
			{
				$b1 = substr($file, $pos, 0x20);
				$pix .= tpl_dxt1($b1);
				$siz -= 1;
				$pos += 0x20;
			}
			break;
		default:
			return php_error('UNKNOWN tpl im_fmt %d', $fmt);
	}

	printf("POS %x\n", $pos);
	tplimage( $pix, $iwb, $ihb, $byte, $bw, $bh );
	return array($iwb,$ihb,$byte,$pix);
}
//////////////////////////////
function wiitpl_pal( &$file, $base, $pos )
{
	if ( $pos == 0 )
		return '';
	printf("== wiitpl_pal( %x , %x )\n", $base, $pos);
	global $gp_pfmt;
	$p = $base + $pos;

	$ph1 = str2big($file, $p+0, 2); // cc
	$ph2 = str2big($file, $p+2, 1);
	$ph3 = str2big($file, $p+4, 4); // format
	$ph4 = str2big($file, $p+8, 4); // palette data

	if ( ! isset( $gp_pfmt[$ph3] ) )
		php_error('UNKNOWN tpl cl_fmt %d', $ph3);
	$c = $gp_pfmt[$ph3];
	printf("DETECT PAL = %s\n", $c);

	$p = $base + $ph4;
	$wiipal = '';
	for ( $j=0; $j < $ph1; $j++ )
	{
		$wiipal .= $c( $file[$p+1] . $file[$p+0] );
		$p += 2;
	}
	printf("add CLUT %s @ %x\n", $c, $ph1);

	return $wiipal;
}

function wiitpl_pix( &$file, $base, $pos, &$wiipal )
{
	printf("== wiitpl_pix( %x , %x )\n", $base, $pos);
	global $gp_ifmt;
	$p = $base + $pos;

	$ih  = str2big($file, $p+ 0, 2); // height
	$iw  = str2big($file, $p+ 2, 2); // width
	$ih1 = str2big($file, $p+ 4, 4); // format
	$ih2 = str2big($file, $p+ 8, 4); // image data
	//$ih3 = str2big($file, $p+12, 4); // wraps
	//$ih4 = str2big($file, $p+16, 4); // wrapt
	//$ih5 = str2big($file, $p+20, 4); // minfilter
	//$ih6 = str2big($file, $p+24, 4); // magfilter
	//$ih7 = str2big($file, $p+32, 1); // edgelod
	//$ih8 = str2big($file, $p+33, 1); // minlod
	//$ih9 = str2big($file, $p+34, 1); // maxlod
	//$iha = str2big($file, $p+35, 1); // unpacked

	if ( ! isset( $gp_ifmt[$ih1] ) )
		php_error('UNKNOWN tpl im_fmt %d', $ih1);
	$c = $gp_ifmt[$ih1];
	printf("DETECT PIX = %s\n", $c);

	return tplformat($file, $base+$ih2, $ih1, $iw, $ih, $wiipal);
}

function wiitpl( &$file, $base, $pfx, $id )
{
	printf("== wiitpl( %x , $pfx , $id )\n", $base);
	if ( str2big($file, $base+0, 4) != 0x20af30 )
		return php_error('not TPL');

	$cnt = str2big($file, $base+4, 4);
	if ( $cnt != 1 )
		return php_error('%s/%04d is multi-TPL [%d]', $pfx, $id, $cnt);

	$p = $base + 12;
	$p1 = str2big($file, $p+0, 4); // image
	$p2 = str2big($file, $p+4, 4); // palette

	$wiipal = wiitpl_pal($file, $base, $p2);
	list($iw,$ih,$byte,$wiipix) = wiitpl_pix($file, $base, $p1, $wiipal);

	$img = '';
	if ( $byte == 1 )
	{
		$img = 'CLUT';
		$img .= chrint( strlen($wiipal)/4, 4 );
		$img .= chrint( $iw, 4 );
		$img .= chrint( $ih, 4 );
		$img .= $wiipal;
		$img .= $wiipix;
	}
	else
	if ( $byte == 4 )
	{
		$img = 'RGBA';
		$img .= chrint( $iw, 4 );
		$img .= chrint( $ih, 4 );
		$img .= $wiipix;
	}

	$fn = sprintf('%s.%d.tpl', $pfx, $id);
	save_file($fn, $img);
	return;
}
////////////////////////////////////////
function mura( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FTEX' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = str2int($file,  8, 4);
	$cnt  = str2int($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) !== 'FTX0' )
			return php_error('%s 0x%x not FTX0', $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("TPL  %x , %x , %s\n", $st, $sz1, $fn);

		wiitpl($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

argv_loopfile($argv, 'mura');

/*
cl_fmt
	00   64  cl_ia8
	01    3  cl_rgb565
	02  426  cl_rgb5a3
im_fmt
	06    3  im_rgba32
	08   47  im_c4
	09  445  im_c8
	0a    1  im_c14x2
	0e  189  im_cmpr
 */
