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
 *   ScummVM
 *   https://github.com/scummvm/scummvm/tree/master/engines/scumm/akos.cpp
 *   https://github.com/scummvm/scummvm/tree/master/engines/scumm/gfx.cpp
 *   https://github.com/scummvm/scummvm/tree/master/engines/scumm/he/wiz_he.cpp
 */
require "common.inc";
require "common-guest.inc";

//define('DRY_RUN', 1);

function sect_riptag( &$file, $pos )
{
	$size = str2big($file, $pos+4, 4);
	$sub  = substr ($file, $pos, $size);
	$file = str_replace($sub, '', $file);
	return substr($sub, 8);
}

function sect_riptag_all( &$file, $tag )
{
	$ret = array();
	while (1)
	{
		$p = strpos($file, $tag);
		if ( $p === false )
			break;
		$ret[] = sect_riptag($file, $p);
	} // while (1)
	return $ret;
}

function check_one_tag( &$err, &$file, $name )
{
	if ( count($file) !== 1 )
	{
		$err = true;
		return php_notice("%s > 1 [%x]", $name, count($file));
	}
	return;
}

function sect_rippal( &$file )
{
	$pal = array();
	$apal = sect_riptag_all($file, "APAL\x00\x00\x03\x08");
	$clut = sect_riptag_all($file, "CLUT\x00\x00\x03\x08");
	$rgbs = sect_riptag_all($file, "RGBS\x00\x00\x03\x08");

	foreach ( $apal as $k => $v )
	{
		echo "rip APAL $k\n";
		$p = '';
		for ( $i=0; $i < 0x300; $i += 3 )
			$p .= $v[$i+0] . $v[$i+1] . $v[$i+2] . BYTE;
		$pal[] = $p;
	}
	foreach ( $clut as $k => $v )
	{
		echo "rip CLUT $k\n";
		$p = '';
		for ( $i=0; $i < 0x300; $i += 3 )
			$p .= $v[$i+0] . $v[$i+1] . $v[$i+2] . BYTE;
		$pal[] = $p;
	}
	foreach ( $rgbs as $k => $v )
	{
		echo "rip RGBS $k\n";
		$p = '';
		for ( $i=0; $i < 0x300; $i += 3 )
			$p .= $v[$i+0] . $v[$i+1] . $v[$i+2] . BYTE;
		$pal[] = $p;
	}
	return $pal;
}

function sect_rippix( &$file, $w, $h )
{
	if ( $w === 0 || $h === 0 )
		return;
	$pix = array();
	$smap = sect_riptag_all($file, "SMAP\x00");
	$bmap = sect_riptag_all($file, "BMAP\x00");

	foreach ( $smap as $mk => $mv )
	{
		echo "rip SMAP $mk\n";
		$mv = sect_SMAP($mv);
		SMAP2BMAP($mv, $w, $h);
		$pix[] = $mv;
	}
	foreach ( $bmap as $mk => $mv )
	{
		echo "rip BMAP $mk\n";
		$pix[] = sect_BMAP($mv, $w, $h);
	}

	return $pix;
}

function save_palpix( $dir, &$pal, &$pix, $w, $h )
{
	if ( $w === 0 || $h === 0 )
		return;
	foreach ( $pal as $pk => $pv )
	{
		foreach ( $pix as $xk => $xv )
		{
			$img = array(
				'cc'  => 0x100,
				'w'   => $w,
				'h'   => $h,
				'pal' => $pv,
				'pix' => $xv,
			);
			save_clutfile("$dir/$pk.$xk.clut", $img);
		} // foreach ( $pix as $xk => $xv )
	} // foreach ( $pal as $pk => $pv )

	return;
}
//////////////////////////////
function get_bits( &$bits, &$file, &$pos, $c )
{
	while ( count($bits) < $c )
	{
		$by = ord( $file[$pos] );
			$pos++;
		for ( $i=0; $i < 8; $i++ )
		{
			$bits[] = $by & 1;
			$by >>= 1;
		}
	} // while ( count($bits) < $c )

	$int = 0;
	for ( $i=0; $i < $c; $i++ )
	{
		$b = array_shift($bits);
		$int |= ($b << $i);
	}
	return $int;
}

function decode_BMAP( &$bmap )
{
	if ( defined('DRY_RUN') )
		return array(0,'');
	$code = ord( $bmap[0] );
	$clr  = ord( $bmap[1] );
	$dec  = $bmap[1];
	list($div,$mod) = var_div($code, 10);
	printf("decode %x [%d ,  %d]\n", $code, $div, $mod);

	$off1 = 2;
	$off2 = strlen($bmap);
	$bits = array();
	switch ( $div )
	{
		case  0:
			if ( $mod === 1 )
				$dec = substr($bmap, 1);
			else
				goto error;
			return array(0,$dec);

		case  1: // basic v
		case  2: // basiv h
		case  3: // basic v + trns
		case  4: // basiv h + trns
			$inc = -1;
			while ( $off1 < $off2 )
			{
				$b1 = get_bits($bits, $bmap, $off1, 1);
				if ( $b1 ) // 1
				{
					$b2 = get_bits($bits, $bmap, $off1, 1);
					if ( $b2 ) // 1,1
					{
						$b3 = get_bits($bits, $bmap, $off1, 1);
						if ( $b3 ) // 1,1,1
							$inc = -$inc;
						$clr += $inc;
					}
					else // 1,0
					{
						$clr = get_bits($bits, $bmap, $off1, $mod);
						$inc = -1;
					}
				}
				//else // 0
					//$clr = $clr;

				$clr &= BIT8;
				$dec .= chr($clr);
			} // while ( $off1 < $off2 )
			return array($div,$dec);

		case  6: // complex
		case  8: // complex + trns
		case 10: // complex
		case 12: // complex + trns
			$rep = 1;
			while ( $off1 < $off2 )
			{
				$b1 = get_bits($bits, $bmap, $off1, 1);
				if ( $b1 ) // 1
				{
					$b2 = get_bits($bits, $bmap, $off1, 1);
					if ( $b2 ) // 1,1
					{
						$incm = get_bits($bits, $bmap, $off1, 3) - 4;
						if ( $incm !== 0 )
							$clr += $incm;
						else
							$rep = get_bits($bits, $bmap, $off1, 8);
					}
					else // 1,0
						$clr = get_bits($bits, $bmap, $off1, $mod);
				}
				//else // 0
					//$clr = $clr;

				$clr &= BIT8;
				$dec .= str_repeat( chr($clr), $rep );
				$rep  = 1;
			} // while ( $off1 < $off2 )
			return array($div,$dec);

		case 13: // he
		case 14: // he + trns
			$delta = array(-4, -3, -2, -1, 1, 2, 3, 4);
			while ( $off1 < $off2 )
			{
				$b1 = get_bits($bits, $bmap, $off1, 1);
				if ( $b1 ) // 1
				{
					$b2 = get_bits($bits, $bmap, $off1, 1);
					if ( $b2 ) // 1,1
					{
						$incm = get_bits($bits, $bmap, $off1, 3);
						$clr += $delta[$incm];
					}
					else // 1,0
						$clr = get_bits($bits, $bmap, $off1, $mod);
				}
				//else // 0
					//$clr = $clr;

				$clr &= BIT8;
				$dec .= chr($clr);
			} // while ( $off1 < $off2 )
			return array($div,$dec);

		case 15: // fill
			return array($div,$dec);

		default:
			goto error;
	} // switch ( $div )

error:
	return php_error('SMAP = UNKNOWN code %x [%d , %d]', $code, $div, $mod);
}

function SMAP2BMAP( &$pix, $w, $h )
{
	$canv = str_repeat(ZERO, $w*$h);
	$strp = 8 * $h;
	foreach ( $pix as $k => $v )
	{
		$dx = $k * 8;
		$ps = 0;
		switch ( $v[0] )
		{
			case 1: // basic v
			case 3: // basic v
				while ( strlen($v[1]) < $strp )
					$v[1] .= ZERO;

				for ( $x=0; $x < 8; $x++ )
				{
					for ( $y=0; $y < $h; $y++ )
					{
						$b = $v[1][$ps];
							$ps++;
						$dxx = ($y * $w) + $dx + $x;
						$canv[$dxx] = $b;
					} // for ( $y=0; $y < $h; $y++ )
				} // for ( $x=0; $x < 8; $x++ )
				break;

			case 15: // fill
				$v[1] = str_repeat($v[1], $strp);
			default:
				while ( strlen($v[1]) < $strp )
					$v[1] .= ZERO;

				for ( $y=0; $y < $h; $y++ )
				{
					$s = substr($v[1], $ps, 8);
						$ps += 8;
					$dxx = ($y * $w) + $dx;
					str_update($canv, $dxx, $s);
				} // for ( $y=0; $y < $h; $y++ )
				break;
		} // switch ( $v[0] )
	} // foreach ( $pix as $k => $v )

	$pix = $canv;
	return;
}

function sect_BMAP( &$file, $w, $h )
{
	$pix = decode_BMAP($file);
	if ( $pix[0] === 15 ) // fill
		$pix[1] .= str_repeat($pix[1][0], $w*$h);
	return $pix[1];
}

function sect_SMAP( &$file )
{
	$base = 0;
	while (1)
	{
		$mgc = substr($file, $base, 4);
		switch ( $mgc )
		{
			case 'BSTR':
			case 'OFFS':
			case 'WRAP':
				$base += 8;
				break;
			default:
				break 2;
		} // switch ( $mgc )
	} // while (1)

	$hded = str2int($file, $base, 4) - 8;
	$pix  = array();
	for ( $i=0; $i < $hded; $i += 4 )
	{
		$off1 = str2int($file, $base+$i+0, 4) - 8;
		if ( ($i+4) === $hded )
			$off2 = strlen($file);
		else
			$off2 = str2int($file, $base+$i+4, 4) - 8;
		$size = $off2 - $off1;
		printf('%x - %x [%x]  ', $off1, $off2, $size);

		$sub = substr($file, $base+$off1, $size);
		$pix[] = decode_BMAP($sub);
	} // for ( $i=0; $i < $hded; $i += 4 )
	return $pix;
}
//////////////////////////////
function sect_IMHD( &$file )
{
	$size = strlen($file);

imhd_48:
	if ( $size < 0x48 )
		goto imhd_12;

	//  0  str[28]  name
	// 28  int32  -
	// 2c  int32  num
	// 30  int32  -
	// 34  int32  -
	// 38  int32  width [SMAP]
	// 3c  int32  height
	// 40  int32  -
	// 44  int32  -
	// int32  x
	// int32  y
	$num = str2int($file, 0x2c, 4);
	$b = 0x48 + ($num * 8);
	if ( $size !== $b )
		goto imhd_12;

	$w = str2int($file, 0x38, 4);
	$h = str2int($file, 0x3c, 4);
	printf("IMHD 48 = %x x %x\n", $w, $h);
	return array($w,$h);

imhd_12:
	if ( $size < 0x12 )
		goto imhd_10;

	//  0  int16  -
	//  2  int16  -
	//  4  int16  -
	//  6  int16  -
	//  8  int16  -
	//  a  int16  -
	//  c  int16  width [SMAP]
	//  e  int16  height
	// 10  int16  num
	// int16  x
	// int16  y
	$num = str2int($file, 0x10, 2);
	$b = 0x12 + ($num * 4);
	if ( $size !== $b )
		goto imhd_10;

	$w = str2int($file, 0x0c, 2);
	$h = str2int($file, 0x0e, 2);
	printf("IMHD 12 = %x x %x\n", $w, $h);
	return array($w,$h);

imhd_10:
	if ( $size !== 0x10 )
		goto error;

	//  0  int16  -
	//  2  int16  -
	//  4  int16  -
	//  6  int16  -
	//  8  int16  -
	//  a  int16  -
	//  c  int16  width [SMAP]
	//  e  int16  height
	$w = str2int($file, 0x0c, 2);
	$h = str2int($file, 0x0e, 2);
	printf("IMHD 10 = %x x %x\n", $w, $h);
	return array($w,$h);

error:
	return php_error('IMHD  UNKNOWN size [%x]', $size);
}

function sect_RMHD( &$file )
{
	$size = strlen($file);
	switch ( $size )
	{
		//  0  int16  width [SMAP]
		//  2  int16  height
		//  4  int16  -
		case 6: // e-8
			$w = str2int($file, 0, 2);
			$h = str2int($file, 2, 2);
			printf("RMHD 6 = %x x %x\n", $w, $h);
			return array($w,$h);

		//  0  int16  -
		//  2  int16  -
		//  4  int16  width [SMAP]
		//  6  int16  height
		//  8  int16  -
		case 10: // 12-8
			$w = str2int($file, 4, 2);
			$h = str2int($file, 6, 2);
			printf("RMHD 10 = %x x %x\n", $w, $h);
			return array($w,$h);

		//  0  int32  -
		//  4  int32  width [SMAP]
		//  8  int32  height
		//  c  int32  -
		// 10  int32  -
		// 14  int32  -
		case 24: // 20-8
			$w = str2int($file, 4, 4);
			$h = str2int($file, 8, 4);
			printf("RMHD 24 = %x x %x\n", $w, $h);
			return array($w,$h);

		default:
			return php_error('RMHD  UNKNOWN size [%x]', $size);
	} // switch ( $size )

	return array($w,$h);
}
//////////////////////////////
function decode_WIZD_1( &$wizd, $w, $h )
{
	printf("== decode_WIZD_1( %x , %x )\n", $w, $h);
	if ( defined('DRY_RUN') )
		return '';
	$dec = '';

	$ed = strlen($wizd);
	$st = 0;
	while ( $st < $ed )
	{
		$siz = str2int($wizd, $st+0, 2);
		$sub = substr ($wizd, $st+2, $siz);
			$st += (2 + $siz);

		$p = 0;
		while ( $p < $siz )
		{
			$b1 = ord( $sub[$p] );
				$p++;

			if ( $b1 & 1 ) // 1
			{
				$len = $b1 >> 1;
				$dec .= str_repeat(ZERO, $len);
				continue;
			}

			if ( $b1 & 2 ) // 1,0
			{
				$len = ($b1 >> 2) + 1;
				$b2  = $sub[$p];
					$p++;
				$dec .= str_repeat($b2, $len);
				continue;
			}

			// 0,0
			$len  = ($b1 >> 2) + 1;
			$dec .= substr($sub, $p, $len);
				$p += $len;
		} // while ( $p < $siz )
	} // while ( $st < $ed )

	return $dec;
}

function decode_WIZD_2( &$wizd, $w, $h )
{
	printf("== decode_WIZD_1( %x , %x )\n", $w, $h);
	if ( defined('DRY_RUN') )
		return '';
	$dec = '';

	// RGB555 image
	$ed = strlen($wizd);
	for ( $i=0; $i < $ed; $i += 2 )
	{
		$c = str2int($wizd, $i, 2);

		// fedc ba98 7654 3210
		// -rrr rrgg gggb bbbb
		$b = ($c << 3) & 0xf8; // >>  0 << 3
		$g = ($c >> 2) & 0xf8; // >>  5 << 3
		$r = ($c >> 7) & 0xf8; // >> 10 << 3

		$dec .= chr($r) . chr($g) . chr($b) . BYTE;
	} // for ( $i=0; $i < $ed; $i += 2 )

	return $dec;
}

function decode_AKCD( &$akcd, $pos, $w, $h, $t, $c1sz )
{
	printf("== decode_AKCD( %x , %x , %x , %x )\n", $w, $h, $t, $c1sz);
	if ( defined('DRY_RUN') )
		return '';

	$pix  = '';
	switch ( $t )
	{
		case 1:
			$size = $w * $h;
			$dec  = '';
			while ( $size > 0 )
			{
				$len = get_bits($bits, $akcd, $pos, $c1sz[0]);
				$clr = get_bits($bits, $akcd, $pos, $c1sz[1]);
				if ( $len === 0 )
					$len = get_bits($bits, $akcd, $pos, 8);

				$dec  .= str_repeat( chr($clr), $len);
				$size -= $len;
			} // while ( $size > 0 )

			// $dec is rotated 90 counter-clockwise , for some reason
			$pix = $dec;
			$p = 0;
			for ( $x=0; $x < $w; $x++ )
			{
				for ( $y=0; $y < $h; $y++ )
				{
					$b = $dec[$p];
						$p++;
					$dxx = ($y * $w) + $x;
					$pix[$dxx] = $b;
				} // for ( $y=0; $y < $h; $y++ )
			} // for ( $x=0; $x < $w; $x++ )
			return $pix;

		default:
			return php_error("AKCD  UNKNOWN [%x]", $t);
/*
		case 5:
			return $pix;

		case 16:
			$mod = ord( $akcd[$pos+0] );
			$clr = ord( $akcd[$pos+1] );
			$pix = $akcd[$pos+1];
				$pos += 2;

			$bits = array();
			$size = $w * $h;
			while ( $size > 0 )
			{
				$rep = 1;
				$b1 = get_bits($bits, $akcd, $pos, 1);
				if ( $b1 ) // 1
				{
					$b2 = get_bits($bits, $akcd, $pos, 1);
					if ( $b2 ) // 1,1
					{
						$b3 = get_bits($bits, $akcd, $pos, 3) - 4;
						if ( $b3 !== 0 )
							$clr += $b3;
						else
							$rep = get_bits($bits, $akcd, $pos, 8);
					}
					else // 1,0
						$clr = get_bits($bits, $akcd, $pos, $mod);
				}
				//else // 0
					//$clr = $clr;

				$clr &= BIT8;
				$pix .= str_repeat( chr($clr), $rep );
					//$x -= $rep;
					$size -= $rep;
					$rep   = 1;
			} // while ( $size > 0 )
			return $pix;
*/
	} // switch ( $comp )

	return $pix;
}
//////////////////////////////
function sect_AWIZ( &$file, $dir, $pal=array() )
{
	echo "== sect_AWIZ()\n";
	$rgbs = sect_rippal($file);

	$wizh = sect_riptag_all($file, "WIZH\x00");
	$wizd = sect_riptag_all($file, "WIZD\x00");
		$err = false;
		check_one_tag($err, $wizh, 'WIZH');
		check_one_tag($err, $wizd, 'WIZD');
		if ( $err )  return;

	$t = str2int($wizh[0], 0, 4);
	$w = str2int($wizh[0], 4, 4);
	$h = str2int($wizh[0], 8, 4);

	switch ( $t )
	{
		case 0:
			if ( empty($rgbs) )  $rgbs = $pal;
			if ( empty($rgbs) )  return;

			foreach ( $rgbs as $pk => $pv )
			{
				$img = array(
					'cc'  => 0x100,
					'w'   => $w,
					'h'   => $h,
					'pal' => $pv,
					'pix' => $wizd[0],
				);
				save_clutfile("$dir/$pk.clut", $img);
			} // foreach ( $pal as $pk => $pv )
			return;

		case 1:
			if ( empty($rgbs) )  $rgbs = $pal;
			if ( empty($rgbs) )  return;

			$pix = decode_WIZD_1($wizd[0], $w, $h);
			foreach ( $rgbs as $pk => $pv )
			{
				$img = array(
					'cc'  => 0x100,
					'w'   => $w,
					'h'   => $h,
					'pal' => $pv,
					'pix' => $pix,
				);
				save_clutfile("$dir/$pk.clut", $img);
			} // foreach ( $pal as $pk => $pv )
			return;

		case 2:
			//save_file("$dir.wiz2", $wizd[0]);
			$pix = decode_WIZD_2($wizd[0], $w, $h);
			$img = array(
				'w'   => $w,
				'h'   => $h,
				'pix' => $pix,
			);
			save_clutfile("$dir.rgba", $img);
			return;

		default:
			return php_error("WIZD  UNKNOWN [%x]", $t);
	} // switch ( $t )
	return;
}

function sect_MULT( &$file, $dir, $pal=array() )
{
	echo "== sect_MULT()\n";
	$rgbs = sect_rippal($file);
	if ( empty($rgbs) )  $rgbs = $pal;
	if ( empty($rgbs) )  return;

	$awiz = sect_riptag_all($file, "AWIZ\x00");
	foreach ( $awiz as $k => $v )
		sect_AWIZ($v, "$dir/AWIZ/p$k", $rgbs);
	return;
}

function sect_AKPL( &$akpl, &$pal )
{
	$clr = '';
	$len = strlen($akpl);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $akpl[$i] );
		$clr .= substr($pal, $b*4, 4);
	}
	return $clr;
}

function sect_AKOS( &$akos, $dir, $pal=array() )
{
	echo "== sect_AKOS()\n";
	$rgbs = sect_rippal($file);
	if ( empty($rgbs) )  $rgbs = $pal;
	if ( empty($rgbs) )  return;

	$akhd = sect_riptag_all($akos, "AKHD\x00");
	$akpl = sect_riptag_all($akos, "AKPL\x00");
	$akof = sect_riptag_all($akos, "AKOF\x00");
	$akci = sect_riptag_all($akos, "AKCI\x00");
	$akcd = sect_riptag_all($akos, "AKCD\x00");
		$err = false;
		check_one_tag($err, $akhd, 'AKHD');
		check_one_tag($err, $akpl, 'AKPL');
		check_one_tag($err, $akof, 'AKOF');
		check_one_tag($err, $akci, 'AKCI');
		check_one_tag($err, $akcd, 'AKCD');
		if ( $err )  return;

	$comp = str2int($akhd[0], 0, 2);
	$c1sz = array(4,4); // len , color
	$plsz = strlen($akpl[0]);
	switch ( $plsz )
	{
		case 1:
			//$c1sz = array(7,1);
			// black + white image
				return;
			break;
		case 16:
			$c1sz = array(4,4);
			break;
		case 32:
			$c1sz = array(3,5);
			break;
		case 64:
			$c1sz = array(2,6);
			break;
		case 256:
			//$c1sz = array(0,8);
			if ( $comp === 1 ) // basically just remap , no decompression
				return php_error("comp 1 + akpl 256");
			break;
		//case 256:  $c1sz = array(4,4); break;
	} // switch ( $plsz )

	$sz_of = strlen($akof[0]);
	$id_of = 0;
	for ( $p_of=0; $p_of < $sz_of; $p_of += 6 )
	{
		$p_cd = str2int($akof[0], $p_of+0, 4);
		$p_ci = str2int($akof[0], $p_of+4, 2);
		printf("akcd %x  akci %x\n", $p_cd, $p_ci);

		$w = str2int($akci[0], $p_ci+0, 2);
		$h = str2int($akci[0], $p_ci+2, 2);
		$pix = decode_AKCD($akcd[0], $p_cd, $w, $h, $comp, $c1sz);

		foreach ( $rgbs as $pk => $pv )
		{
			$clr = sect_AKPL($akpl[0], $pv);
			$img = array(
				'cc'  => 0x100,
				'w'   => $w,
				'h'   => $h,
				'pal' => $clr,
				'pix' => $pix,
			);
			save_clutfile("$dir/$id_of.$pk.clut", $img);
				$id_of++;
		} // foreach ( $pal as $pk => $pv )
	} // for ( $p_of=0; $p_of < $sz_of; $p_of += 6 )
	return;
}

function sect_OBIM( &$obim, $dir, $pal=array() )
{
	echo "== sect_OBIM()\n";
	if ( empty($pal) )
		return;

	$imhd = sect_riptag_all($obim, "IMHD\x00");
	list($w,$h) = sect_IMHD($imhd[0]);

	$pix = sect_rippix($obim, $w, $h);
	save_palpix($dir, $pal, $pix, $w, $h);
	return;
}
//////////////////////////////
function sect_ROOM( &$room, &$akos, $dir )
{
	echo "== sect_ROOM()\n";
	// Lucas Arts games , LA 5/6/7/8
	//save_file("$dir.room", $file);

	$obim = sect_riptag_all($room, "OBIM\x00");
	$rmhd = sect_riptag_all($room, "RMHD\x00");
	list($w,$h) = sect_RMHD($rmhd[0]);

	$pal = sect_rippal($room);
	$pix = sect_rippix($room, $w, $h);
	save_palpix($dir, $pal, $pix, $w, $h);

	foreach ( $obim as $ok => $ov )
		sect_OBIM($ov, "$dir/OBIM/p$ok", $pal);
	foreach ( $akos as $ok => $ov )
		sect_AKOS($ov, "$dir/AKOS/p$ok", $pal);
	return;
}

function sect_RMIM_RMDA( &$rmim, &$rmda, $dir, &$pal)
{
	echo "== sect_RMIM_RMDA()\n";
	$obim = sect_riptag_all($rmda, "OBIM\x00");
	$rmhd = sect_riptag_all($rmda, "RMHD\x00");
	list($w,$h) = sect_RMHD($rmhd[0]);

	$pal = sect_rippal($rmda);
	$pix = sect_rippix($rmim, $w, $h);
	save_palpix($dir, $pal, $pix, $w, $h);

	foreach ( $obim as $ok => $ov )
		sect_OBIM($ov, "$dir/OBIM/p$ok", $pal);
	return;
}

function sect_LFLF( &$file, $dir )
{
	echo "== sect_LFLF()\n";
	if ( substr($file,8,5) === "ROOM\x00" )
	{
		$room = sect_riptag($file, 8);
		$akos = sect_riptag_all($file, "AKOS\x00");
		return sect_ROOM($room, $akos, $dir);
	}
	// Humongous Entertainment games , LA 6 , HE 6.x/7.x/8.x/9.x/10.x
	//save_file("$dir.lflf", $file);

	$pal  = array();
	$rmim = sect_riptag_all($file, "RMIM\x00");
	$rmda = sect_riptag_all($file, "RMDA\x00");
	sect_RMIM_RMDA($rmim[0], $rmda[0], $dir, $pal);

	$akos = sect_riptag_all($file, "AKOS\x00");
	$mult = sect_riptag_all($file, "MULT\x00");
	$awiz = sect_riptag_all($file, "AWIZ\x00");

	foreach ( $akos as $k => $v )
		sect_AKOS($v, "$dir/AKOS/p$k", $pal);
	foreach ( $mult as $k => $v )
		sect_MULT($v, "$dir/MULT/p$k", $pal);
	foreach ( $awiz as $k => $v )
		sect_AWIZ($v, "$dir/AWIZ/p$k", $pal);
	return;
}
//////////////////////////////
function trim_LFLF( &$lflf )
{
	$func = __FUNCTION__;
	$new  = substr($lflf, 0, 8);

	$st = 8;
	$ed = strlen($lflf);
	while ( $st < $ed )
	{
		$mgc = substr ($lflf, $st+0, 4);
		$siz = str2big($lflf, $st+4, 4);

		switch ( $mgc )
		{
			case 'SCRP': // script
			case 'RMSC': // room script
			case 'LSCR': case 'LSC2': // local script
			case 'OBCD': // object code
			case 'ENCD': case 'EXCD':

			case 'TALK': case 'TLKE': // talkie
			case 'SOUN': // sound
			case 'DIGI':

			case 'BOXD': case 'BOXM':
			case 'CHAR':
			case 'POLD':
				break;

			case 'ROOM':
			case 'RMDA':
				$sub = substr($lflf, $st, $siz);
				$func($sub);
				$new .= $sub;
				break;

			case "\x00\x00\x00\x00": // full throttle fix
				break 2;

			default:
				$sub = substr($lflf, $st, $siz);
				$new .= $sub;
				break;
		} // switch ( $mgc )

		$st += $siz;
	} // while ( $st < $ed )

	$len = strlen($new);
	$len = chrbig($len, 4);
	str_update($new, 4, $len);

	$lflf = $new;
	return;
}

function sect_LECF( &$file, $dir )
{
	echo "== sect_LECF()\n";
	$ed = strlen($file);
	$st = 8;
	$fid = 0;
	while ( $st < $ed )
	{
		$mgc = substr ($file, $st+0, 4);
		$siz = str2big($file, $st+4, 4);
		printf("%x  %x  %s\n", $st, $siz, $mgc);

		if ( $mgc === 'LFLF' )
		{
			$sub = substr($file, $st, $siz);
				trim_LFLF($sub);

			$fn = sprintf('%s/%04d.lflf', $dir, $fid);
				$fid++;
			save_file($fn, $sub);
		}
		$st += $siz;
	} // while ( $st < $ed )
	return;
}

function spyfox( $fname )
{
	$file = load_file($fname);
	if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	switch ( $mgc )
	{
		case "\x3b\x27\x28\x24": // RNAM  .0
		case "\x25\x2c\x2a\x2f": // LECF  .1  .(a)  .he1
		case "\x24\x28\x31\x3a": // MAXS  .he0
		case "\x3d\x25\x22\x2b": // TLKB  .he2
			printf("[^69] %s\n", $fname);
			$len = strlen($file);
			for ( $i=0; $i < $len; $i++ )
			{
				$c  = ord($file[$i]);
				$c ^= 0x69;
				$file[$i] = chr($c);
			}
			save_file($fname, $file);
			break;
	} // switch ( $mgc )

	$dir = str_replace('.', '_', $fname);
	$mgc = substr($file, 0, 4);
	if ( $mgc === 'LECF' )
		return sect_LECF($file, $dir);
	if ( $mgc === 'LFLF' )
		return sect_LFLF($file, $dir);
	if ( $mgc === 'MULT' )
		return sect_MULT($file, $dir);
	return;
}
for ( $i=1; $i < $argc; $i++ )
	spyfox( $argv[$i] );
