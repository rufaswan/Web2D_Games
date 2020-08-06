<?php
require "common.inc";

// http://wiki.tockdom.com/w/index.php?title=TPL_%28File_Format%29
// http://wiki.tockdom.com/w/index.php?title=Image_Formats
function cl_i4( $str )
{
	$pal = ord($str);
	$p1 = ($pal >> 4) & 0x0f;
	$p2 = ($pal >> 0) & 0x0f;
	$c1 = $p1 * 0x11;
	$c2 = $p2 * 0x11;
	return chr($c1) . chr($c1) . chr($c1) . BYTE . chr($c2) . chr($c2) . chr($c1) . BYTE;
}

function cl_i8( $str )
{
	return $str . $str . $str . BYTE;
}

function cl_ia4( $str )
{
	$pal = ord($str);
	$p1 = ($pal >> 4) & 0x0f;
	$p2 = ($pal >> 0) & 0x0f;
	$c1 = $p1 * 0x11;
	$c2 = $p2 * 0x11;
	return chr($c2) . chr($c2) . chr($c2) . chr($c1);
}

function cl_ia8( $str )
{
	return $str[0] . $str[0] . $str[0] . $str[1];
}

function cl_rgb565( $str )
{
	$pal = ordint($str);
	$b = ($pal << 3) & 0xf8; // << 11 >> 8
	$g = ($pal >> 3) & 0xfc; // <<  5 >> 8
	$r = ($pal >> 8) & 0xf8; // <<  0 >> 8
	$a = BYTE;
	return chr($r) . chr($g) . chr($b) . $a;
}

function cl_rgb5a3( $str )
{
	$pal = ordint($str);
	if ( $pal & 0x8000 )
	{
		$b = ($pal << 3) & 0xf8; // << 11 >> 8
		$g = ($pal >> 2) & 0xf8; // <<  6 >> 8
		$r = ($pal >> 7) & 0xf8; // <<  1 >> 8
		$a = BIT8;
	}
	else
	{
		$b = ($pal << 4) & 0xf0; // << 12 >> 8
		$g = ($pal >> 0) & 0xf0; // <<  8 >> 8
		$r = ($pal >> 4) & 0xf0; // <<  4 >> 8
		$a = ($pal >> 7) & 0xe0; // <<  1 >> 8
	}
	return chr($r) . chr($g) . chr($b) . chr($a);
}

function cl_rgba32( $block )
{
	$pix = "";
	for ( $i=0; $i < 0x20; $i += 2 )
	{
		$r = $block[$i+0x01];
		$g = $block[$i+0x20];
		$b = $block[$i+0x21];
		$a = $block[$i+0x00];
		$pix .= $r . $g . $b . $a;
	}
	return $pix;
}
////////////////////////////////////////
function tplimage( &$pix, $iw, $ih, $byte, $bw, $bh )
{
	printf("== tplimage( $iw , $ih , $byte , $bw , $bh )\n");
	$cw = $iw / $bw;
	$ch = $ih / $bh;
	$row_sz = $bw * $bh * $cw * $byte;

	$res = "";
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
					$buf[$y] = "";
				$buf[$y] .= substr($pix, $st, $bw*$byte);
				$st += ($bw * $byte);
			}
		}
		$res .= implode('', $buf);
	}
	$pix = $res;
	return;
}

function tplformat( &$file, $pos, $fmt, $iw, $ih, &$gp_clut )
{
	printf("== tplformat( %x , $fmt , $iw , $ih )\n", $pos);
	$pix = "";
	switch ( $fmt )
	{
		case  0: // im_i4
			$iwb = int_ceil($iw, 4);
			$ihb = int_ceil($ih, 8);
			$byte = 4;
			$bw = 8;
			$bh = 8;

			$siz = $iwb / 2 * $ihb;
			while ( $siz > 0 )
			{
				$pix .= cl_i4( $file[$pos] );
				$siz--;
				$pos++;
			}
			break;
		case  1: // im_i8
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 8;
			$bh = 4;

			$siz = $iwb * $ihb;
			while ( $siz > 0 )
			{
				$pix .= cl_i8( $file[$pos] );
				$siz--;
				$pos++;
			}
			break;
		case  2: // im_ia4
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 8;
			$bh = 4;

			$siz = $iwb * $ihb;
			while ( $siz > 0 )
			{
				$pix .= cl_ia4( $file[$pos] );
				$siz--;
				$pos++;
			}
			break;
		case  3: // im_ia8
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			while ( $siz > 0 )
			{
				$pix .= cl_ia8( $file[$pos+0] . $file[$pos+1] );
				$siz -= 2;
				$pos += 2;
			}
			break;
		case  4: // im_rgb565
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			while ( $siz > 0 )
			{
				$pix .= cl_rgb565( $file[$pos+0] . $file[$pos+1] );
				$siz -= 2;
				$pos += 2;
			}
			break;
		case  5: // im_rgb5a3
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			while ( $siz > 0 )
			{
				$pix .= cl_rgb5a3( $file[$pos+0] . $file[$pos+1] );
				$siz -= 2;
				$pos += 2;
			}
			break;
		case  6: // im_rgba32
			$iwb = int_ceil($iw, 4);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 4;
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
			$pix = substr($file, $pos, $siz);
			break;
		case 10: // im_c14x2
			$iwb = int_ceil($iw, 8);
			$ihb = int_ceil($ih, 4);
			$byte = 4;
			$bw = 4;
			$bh = 4;

			$siz = $iwb * $ihb * 2;
			while ( $siz > 0 )
			{
				$pal = ordint( $file[$pos+1] . $file[$pos+0] );
				$b1 = $pal & 0x3fff;
				$pix .= substr($gp_clut, $b1*4, 4);
				$siz -= 2;
				$pos += 2;
			}
			break;
		//case 14: // im_cmpr
		default:
			printf("UNKNOWN tpl im_fmt %d\n", $ih1);
			return array();
	}
	return array($iwb,$ihb,$byte,$bw,$bh,$pix);
}

function wiitpl( &$file, $base, $pfx, $id )
{
	printf("== wiitpl( %x , $pfx , $id )\n", $base);
	$b = chrint(0x20af30, 4);
	$b = strrev($b);
	if ( substr($file, $base+0, 4) != $b  )
		return;
	$cnt = ordint( $file[$base+7] . $file[$base+6] . $file[$base+5] );

	$pfmt = array(
		0 => "cl_ia8",
		1 => "cl_rgb565",
		2 => "cl_rgb5a3",
	);
	$ifmt = array(
		0  => "im_i4",     //  4-bit ,  4*8 = 20
		1  => "im_i8",     //  8-bit ,  8*4 = 20
		2  => "im_ia4",    //  8-bit ,  8*4 = 20
		3  => "im_ia8",    // 16-bit ,  8*4 = 20
		4  => "im_rgb565", // 16-bit ,  8*4 = 20
		5  => "im_rgb5a3", // 16-bit ,  8*4 = 20
		6  => "im_rgba32", // 32-bit , 10*4 = 40
		8  => "im_c4",     //  4-bit ,  4*8 = 20
		9  => "im_c8",     //  8-bit ,  8*4 = 20
		10 => "im_c14x2",  // 16-bit ,  8*4 = 20
		14 => "im_cmpr",   //  4-bit ,  4*8 = 20
	);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $base + 12 + ($i * 8);

		$p1 = ordint( $file[$p+3] . $file[$p+2] . $file[$p+1] ); // image
		$p2 = ordint( $file[$p+7] . $file[$p+6] . $file[$p+5] ); // palette

		// optional - palette
		$gp_clut = "";
		if ( $p2 != 0 )
		{
			$p = $base + $p2;

			$ph1 = ordint( $file[$p+1] . $file[$p+0] ); // cc
			$ph2 = ord( $file[$p+2] );
			$ph3 = ordint( $file[$p+ 7] . $file[$p+ 6] . $file[$p+5] ); // format
			$ph4 = ordint( $file[$p+11] . $file[$p+10] . $file[$p+9] ); // palette data

			$p = $base + $ph4;
			$c = $pfmt[$ph3];
			for ( $j=0; $j < $ph1; $j++ )
			{
				$gp_clut .= $c( $file[$p+1] . $file[$p+0] );
				$p += 2;
			}
		}

		// image
		$p = $base + $p1;
		$ih = ordint( $file[$p+1] . $file[$p+0] );
		$iw = ordint( $file[$p+3] . $file[$p+2] );
		$ih1 = ordint( $file[$p+ 7] . $file[$p+ 6] . $file[$p+5] ); // format
		$ih2 = ordint( $file[$p+11] . $file[$p+10] . $file[$p+9] ); // image data
		$ih3 = ord( $file[$p+0x23] );

		$p = $base + $ih2;
		list($iw,$ih,$byte,$bw,$bh,$gp_pix) = tplformat($file, $p, $ih1, $iw, $ih, $gp_clut);
		tplimage( $gp_pix, $iw, $ih, $byte, $bw, $bh );

		if ( $byte == 1 )
		{
			$bin = "CLUT";
			$bin .= chrint( strlen($gp_clut)/4, 4 );
			$bin .= chrint( $iw, 4 );
			$bin .= chrint( $ih, 4 );
			$bin .= $gp_clut;
			$bin .= $gp_pix;
			$n = $id * 10 + $i;
			save_file("$pfx.$n.clut", $bin);
		}
		if ( $byte == 4 )
		{
			$bin = "RGBA";
			$bin .= chrint( $iw, 4 );
			$bin .= chrint( $ih, 4 );
			$bin .= $gp_pix;
			$n = $id * 10 + $i;
			save_file("$pfx.$n.rgba", $bin);
		}
	}
	return;
}
////////////////////////////////////////
function mura( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = str2int($file,  8, 3);
	$cnt  = str2int($file, 12, 3);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = substr($file, $p1, 0x20);
		$fn = rtrim($fn, ZERO);

		printf("%6x , %6x , %s\n", $p1, $st, $fn);
		if ( substr($file, $st, 4) != "FTX0" )
			return printf("ERROR not FTX0\n");

		wiitpl($file, $st+0x40, $pfx, $i);
		$sz = str2int($file, $st+4, 3);
		$st += ($sz + 0x40);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
