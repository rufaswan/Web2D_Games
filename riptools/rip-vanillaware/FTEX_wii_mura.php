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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');
tool::require('class-clutfile');

//define('DRY_RUN', true);

$gp_ifmt = [
	0x6 => 'im_rgba32', // 32-bit , 10*4 = 40
	0x8 => 'im_c4',     //  4-bit ,  4*8 = 20
	0x9 => 'im_c8',     //  8-bit ,  8*4 = 20
	0xa => 'im_c14x2',  // 16-bit ,  8*4 = 20
	0xe => 'im_cmpr',   //  4-bit ,  4*8 = 20
];
$gp_pfmt = [
	0 => 'cl_ia8',
	1 => 'cl_rgb565',
	2 => 'cl_rgb5a3',
];

//////////////////////////////
function cl_ia8( int $pal ) : string
{
	// fedc ba98 7654 3210
	// aaaa aaaa cccc cccc
	$c = ($pal >> 0) & BIT8;
	$a = ($pal >> 8) & BIT8;
	return chr($c) . chr($c) . chr($c) . chr($a);
}

function cl_rgb565( int $pal ) : string
{
	// fedc ba98  7654 3210
	// rrrr rggg  gggb bbbb
	$b = ($pal << 3) & 0xf8; // << 11 >> 8
	$g = ($pal >> 3) & 0xfc; // <<  5 >> 8
	$r = ($pal >> 8) & 0xf8; // <<  0 >> 8
	return chr($r) . chr($g) . chr($b) . BYTE;
}

function cl_rgb5a3( int $pal ) : string
{
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

function cl_rgba32( string $block ) : string
{
	if ( strlen($block) !== 0x40 )
		tool::error('cl_rgba32() is not 0x40', strlen($block));

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
function cmpr_inter( string $c1, float $f1, string $c2, float $f2 ) : string
{
	$c1r = ord( $c1[0] ) * $f1;
	$c1g = ord( $c1[1] ) * $f1;
	$c1b = ord( $c1[2] ) * $f1;
	$c2r = ord( $c2[0] ) * $f2;
	$c2g = ord( $c2[1] ) * $f2;
	$c2b = ord( $c2[2] ) * $f2;
	$cr = tool::int_clamp($c1r + $c2r, 0, BIT8);
	$cg = tool::int_clamp($c1g + $c2g, 0, BIT8);
	$cb = tool::int_clamp($c1b + $c2b, 0, BIT8);
	return chr($cr) . chr($cg) . chr($cb) . BYTE;
}
function tpl_dxt1( string $str ) : string
{
	// CMPR blocks are 2x2 DXT1 subblocks
	// DXT1 blocks are 4x4 pixels =  8 bytes
	// CMPR blocks are 8x8 pixels = 32 bytes
	$dxt = [];
	for ( $i=0; $i < 0x20; $i += 8 )
	{
		$bk = '';

		// https://en.wikipedia.org/wiki/S3_Texture_Compression#DXT1
		$c01 = ieee754::ordstr($str, $i+0, 2);
		$c11 = ieee754::ordstr($str, $i+2, 2);

		$pal = [];
		$pal[] = cl_rgb565($c01);
		$pal[] = cl_rgb565($c11);

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
		$pix .= tool::substr($dxt[0], $i*0x10, 0x10);
		$pix .= tool::substr($dxt[1], $i*0x10, 0x10);
	} // for ( $i=0; $i < 4; $i++ )
	for ( $i=0; $i < 4; $i++ )
	{
		$pix .= tool::substr($dxt[2], $i*0x10, 0x10);
		$pix .= tool::substr($dxt[3], $i*0x10, 0x10);
	} // for ( $i=0; $i < 4; $i++ )

	return $pix;
}
//////////////////////////////
function tplimage( string &$pix, int $iw, int $ih, int $byte, int $bw, int $bh ) : void
{
	if ( defined('DRY_RUN') )
		return;
	tool::trace(__FUNCTION__, $iw, $ih, $byte, $bw, $bh);
	$cw = $iw / $bw;
	$ch = $ih / $bh;
	$row_sz = $bw * $bh * $cw * $byte;

	// untile pix into normal image
	$res = '';
	$ed = strlen($pix);
	$st = 0;
	while ( $st < $ed )
	{
		$buf = [];
		for ( $x=0; $x < $cw; $x++ )
		{
			for ( $y=0; $y < $bh; $y++ )
			{
				if ( ! isset( $buf[$y] ) )
					$buf[$y] = '';
				$buf[$y] .= tool::substr($pix, $st, $bw*$byte);
				$st += ($bw * $byte);
			} // for ( $y=0; $y < $bh; $y++ )
		} // for ( $x=0; $x < $cw; $x++ )
		$res .= implode('', $buf);
	} // while ( $st < $ed )
	$pix = $res;
}

function tplformat( string &$file, int $pos, int $fmt, int $iw, int $ih, string &$wiipal ) : array
{
	tool::trace(__FUNCTION__, $pos, $fmt, $iw, $ih);
	$pix = '';
	switch ( $fmt )
	{
		case  6: // im_rgba32
			$iwb = tool::int_ceil($iw, 4);
			$ihb = tool::int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 4;
			printf("SIZ %x = %x * %x * 4\n", $siz, $iwb, $ihb);
			while ( $siz > 0 )
			{
				$pix .= cl_rgba32( tool::substr($file, $pos, 0x40) );
				$siz -= 0x40;
				$pos += 0x40;
			}
			break;
		case  8: // im_c4
			$iwb = tool::int_ceil($iw, 4);
			$ihb = tool::int_ceil($ih, 8);
			$byte = 1;
			$bw = 8;
			$bh = 8;

			$siz = $iwb / 2 * $ihb;
			printf("SIZ %x = %x / 2 * %x\n", $siz, $iwb, $ihb);
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
			$iwb = tool::int_ceil($iw, 8);
			$ihb = tool::int_ceil($ih, 4);
			$byte = 1;
			$bw = 8;
			$bh = 4;

			$siz = $iwb * $ihb;
			printf("SIZ %x = %x * %x\n", $siz, $iwb, $ihb);
			$pix = tool::substr($file, $pos, $siz);
			$pos += $siz;
			break;
		case 10: // im_c14x2
			$iwb = tool::int_ceil($iw, 8);
			$ihb = tool::int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			printf("SIZ %x = %x * %x * 2\n", $siz, $iwb, $ihb);
			while ( $siz > 0 )
			{
				$pal = tool::ord( $file[$pos+1] . $file[$pos+0] );
				$b1 = $pal & 0x3fff;
				$pix .= tool::substr($wiipal, $b1*4, 4);
				$siz -= 2;
				$pos += 2;
			}
			break;
		case 14: // im_cmpr
			$iwb = tool::int_ceil($iw, 8);
			$ihb = tool::int_ceil($ih, 8);
			$byte = 4;
			$bw = 8;
			$bh = 8;

			$siz = $iwb/8 * $ihb/8;
			printf("SIZ %x = %x / 8 * %x / 8\n", $siz, $iwb, $ihb);
			while ( $siz > 0 )
			{
				$b1 = tool::substr($file, $pos, 0x20);
				$pix .= tpl_dxt1($b1);
				$siz -= 1;
				$pos += 0x20;
			} // while ( $siz > 0 )
			break;
		default:
			tool::error('UNKNOWN tpl im_fmt', $fmt);
	}

	tool::trace('POS', $pos);
	tplimage( $pix, $iwb, $ihb, $byte, $bw, $bh );
	return [$iwb,$ihb,$byte,$pix];
}
//////////////////////////////
function wiitpl_pal( string &$file, int $pos ) : string
{
	if ( $pos === 0 ) // no palette
		return '';
	tool::trace(__FUNCTION__, $pos);
	global $gp_pfmt;

	$ph1 = ieee754::ordstr($file, $pos+0, 2); // cc
	$ph2 = ieee754::ordstr($file, $pos+2, 1);
	$ph3 = ieee754::ordstr($file, $pos+4, 4); // format
	$ph4 = ieee754::ordstr($file, $pos+8, 4); // palette data

	if ( ! isset( $gp_pfmt[$ph3] ) )
		tool::error('UNKNOWN tpl cl_fmt', $ph3);
	$fmt = $gp_pfmt[$ph3];
	tool::trace('DETECT PAL', $c);

	$wiipal = '';
	for ( $j=0; $j < $ph1; $j++ )
	{
		$c = ieee754::ordstr($file, $ph4, 2);
			$ph4 += 2;
		$wiipal .= $fmt($c);
	}
	tool::trace('add CLUT', $c, $ph1);

	return $wiipal;
}

function wiitpl_pix( string &$file, int $pos, string &$wiipal ) : array
{
	tool::trace(__FUNCTION__, $pos);
	global $gp_ifmt;

	$ih  = ieee754::ordstr($file, $pos+ 0, 2); // height
	$iw  = ieee754::ordstr($file, $pos+ 2, 2); // width
	$ih1 = ieee754::ordstr($file, $pos+ 4, 4); // format
	$ih2 = ieee754::ordstr($file, $pos+ 8, 4); // image data
	//$ih3 = ieee754::ordstr($file, $pos+12, 4); // wraps
	//$ih4 = ieee754::ordstr($file, $pos+16, 4); // wrapt
	//$ih5 = ieee754::ordstr($file, $pos+20, 4); // minfilter
	//$ih6 = ieee754::ordstr($file, $pos+24, 4); // magfilter
	//$ih7 = ieee754::ordstr($file, $pos+32, 1); // edgelod
	//$ih8 = ieee754::ordstr($file, $pos+33, 1); // minlod
	//$ih9 = ieee754::ordstr($file, $pos+34, 1); // maxlod
	//$iha = ieee754::ordstr($file, $pos+35, 1); // unpacked

	if ( ! isset( $gp_ifmt[$ih1] ) )
		tool::error('UNKNOWN tpl im_fmt', $ih1);
	$c = $gp_ifmt[$ih1];
	tool::trace('DETECT PIX', $c);

	return tplformat($file, $ih2, $ih1, $iw, $ih, $wiipal);
}

function mura_tpl( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( ieee754::ordstr($file, 0, 4) != 0x20af30 )
		return false;

	$cnt = ieee754::ordstr($file, 4, 4);
	if ( $cnt !== 1 )
		tool::error('is multi-TPL', $fname, $cnt);

	$p = 12;
	$p_pix = ieee754::ordstr($file, $p+0, 4); // image
	$p_pal = ieee754::ordstr($file, $p+4, 4); // palette

	$wiipal = wiitpl_pal($file, $p_pal);
	list($iw,$ih,$byte,$wiipix) = wiitpl_pix($file, $p_pix, $wiipal);

	$img = '';
	if ( $byte == 1 )
	{
		$img = 'CLUT';
		$img .= tool::chr( strlen($wiipal)/4, 4 );
		$img .= tool::chr( $iw, 4 );
		$img .= tool::chr( $ih, 4 );
		$img .= $wiipal;
		$img .= $wiipix;
	}
	else
	if ( $byte == 4 )
	{
		$img = 'RGBA';
		$img .= tool::chr( $iw, 4 );
		$img .= tool::chr( $ih, 4 );
		$img .= $wiipix;
	}

	$fn = sprintf('%s.tpl', $fname);
	tool::save($fn, $img);
	return true;
}
////////////////////////////////////////
function mura_ftex( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 4) !== 'FTEX' )
		return false;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = tool::ordstr($file,  8, 4);
	$cnt  = tool::ordstr($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = tool::substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) !== 'FTX0' )
			tool::error('not FTX0', $fname, $st);

		$sz1 = tool::ordstr($file, $st+4, 4);
		$sz2 = tool::ordstr($file, $st+8, 4);
		tool::trace('TPL', $st, $sz1, $fn);

		$sub = tool::substr($file, $st+$sz2, $sz1);
		mura_tpl($sub, "$pfx.$i");

		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function mura( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = mura_ftex($file, $fname);
	if ( $r )  return;

	$r = mura_tpl($file, $fname);
	if ( $r )  return;
}

tool::argv_callback($argv, 'mura');

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
