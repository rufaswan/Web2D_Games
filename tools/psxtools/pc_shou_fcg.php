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

define('NO_TRACE', true);

function row2column( &$pix )
{
	// sub_401a60
	// row to column
	$bak = $pix;
	$off = 0;
	for ( $c=0; $c < 8; $c++ )
	{
		for ( $r=0; $r < 8; $r++ )
		{
			$b = $bak[$off];
				$off++;

			$dy = ($r * 8) + $c;
			$pix[$dy] = $b;
		} // for ( $r=0; $r < 8; $r++ )
	} // for ( $c=0; $c < 8; $c++ )
	return;
}

function rgb2x( &$file, &$st, &$pix )
{
	// sub_401e90
	// ??? , not used
	$sub = substr($file, $st, 8);
		$st += 8;

	for ( $i=0; $i < 0x40; $i++ )
	{
		$b1 = ord( $pix[$i] );
		$b1 = int_clamp($b1 << 1, 0, BIT8);
		$pix[$i] = chr($b1);
	} // for ( $i=0; $i < 0x40; $i++ )
	return;
}
//////////////////////////////
function bit2s0( &$str, $st, $len )
{
	$dec = array();
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$st+$i] );
		$dec[] = ($b >> 6) & 3;
		$dec[] = ($b >> 4) & 3;
		$dec[] = ($b >> 2) & 3;
		$dec[] = ($b >> 0) & 3;
	}
	return $dec;
}

function bit4s8( &$str, $st, $len )
{
	$dec = array();
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$st+$i] );
		$dec[] = ($b >> 0x4) - 8;
		$dec[] = ($b &  0xf) - 8;
	}
	return $dec;
}
//////////////////////////////
// 0e 0f-x 10-f 11-fx
function case0e( &$file, &$st, &$pix )
{
	// sub_401f20
	$s1 = substr($file, $st, 0x20);
		$st += 0x20;

	$d4 = bit4s8($s1, 0, 0x20);

	$prv = ord($file[$st]);
		$st++;

	// first row
	for ( $c=0; $c < 8; $c++ )
	{
		$d = array_shift($d4);
		if ( $d === -8 )
		{
			$prv = ord( $file[$st] );
				$st++;
		}
		else
			$prv += $d;

		$col[0][$c] = $prv;
	} // for ( $c=0; $c < 8; $c++ )

	// remaining 7 rows
	for ( $r=1; $r < 8; $r++ )
	{
		for ( $c=0; $c < 8; $c++ )
		{
			$d = array_shift($d4);
			if ( $d === -8 )
			{
				$prv = ord($file[$st]);
					$st++;
			}
			else
				$prv = $col[$r-1][$c] + $d;

			$col[$r][$c] = $prv;
		} // for ( $c=0; $c < 8; $c++ )
	} // for ( $r=1; $r < 8; $r++ )

	// join pixels
	for ( $r=0; $r < 8; $r++ )
	{
		for ( $c=0; $c < 8; $c++ )
		{
			$b = $col[$r][$c] & BIT8;
			$pix .= chr($b);
		} // for ( $c=0; $c < 8; $c++ )
	} // for ( $r=0; $r < 8; $r++ )
	return;
}

// 12 13-x 16-f 17-fx
function case12( &$file, &$st, &$pix )
{
	// sub_402070
	$s1 = substr($file, $st, 0x12);
		$st += 0x12;
	$col = array();

	$prv = ord($file[$st]);
		$st++;

	$d4 = bit4s8($s1, 0,  4);
	$d2 = bit2s0($s1, 4, 14);

	// first row
	for ( $c=0; $c < 8; $c++ )
	{
		if ( $d4[$c] === -8 )
		{
			$prv = ord( $file[$st] );
				$st++;
		}
		else
			$prv += $d4[$c];

		$col[0][$c] = $prv;
	} // for ( $c=0; $c < 8; $c++ )

	// remaining 7 rows
	for ( $r=1; $r < 8; $r++ )
	{
		for ( $c=0; $c < 8; $c++ )
		{
			$d2v = array_shift($d2);
			$prv = $col[$r-1][$c];
			$col[$r][$c] = $prv + $d2v;
		} // for ( $c=0; $c < 8; $c++ )
	} // for ( $r=1; $r < 8; $r++ )

	// join pixels
	for ( $r=0; $r < 8; $r++ )
	{
		for ( $c=0; $c < 8; $c++ )
		{
			$b = $col[$r][$c] & BIT8;
			$pix .= chr($b);
		} // for ( $c=0; $c < 8; $c++ )
	} // for ( $r=0; $r < 8; $r++ )
	return;
}

// 14 15-x 18-f 19-fx
function case14( &$file, &$st, &$pix )
{
	// sub_402120
	$s1 = substr($file, $st, 0x12);
		$st += 0x12;
	$col = array();

	$prv = ord($file[$st]);
		$st++;

	$d4 = bit4s8($s1, 0,  4);
	$d2 = bit2s0($s1, 4, 14);

	// last row
	for ( $c=0; $c < 8; $c++ )
	{
		if ( $d4[$c] === -8 )
		{
			$prv = ord( $file[$st] );
				$st++;
		}
		else
			$prv += $d4[$c];

		$col[7][$c] = $prv;
	} // for ( $c=0; $c < 8; $c++ )

	// remaining 7 columns
	$r = 7;
	while ( $r > 0 )
	{
		$r--;
		for ( $c=0; $c < 8; $c++ )
		{
			$d2v = $d2[$r*8+$c];
			$prv = $col[$r+1][$c];
			$col[$r][$c] = $prv + $d2v;
		} // for ( $c=0; $c < 8; $c++ )
	} // while ( $r > 0 )

	// join pixels
	for ( $r=0; $r < 8; $r++ )
	{
		for ( $c=0; $c < 8; $c++ )
		{
			$b = $col[$r][$c] & BIT8;
			$pix .= chr($b);
		} // for ( $c=0; $c < 8; $c++ )
	} // for ( $r=0; $r < 8; $r++ )
	return;
}
//////////////////////////////
function bit3s0( &$str, $st, $len)
{
	$dec = array();
	for ( $i=0; $i < $len; $i += 3 )
	{
		$b0 = ord( $str[$st+$i+0] ) << 16;
		$b1 = ord( $str[$st+$i+1] ) <<  8;
		$b2 = ord( $str[$st+$i+2] ) <<  0;
		$b = $b0 | $b1 | $b2;

		$dec[] = ($b >> 21) & 7;
		$dec[] = ($b >> 18) & 7;
		$dec[] = ($b >> 15) & 7;
		$dec[] = ($b >> 12) & 7;
		$dec[] = ($b >>  9) & 7;
		$dec[] = ($b >>  6) & 7;
		$dec[] = ($b >>  3) & 7;
		$dec[] = ($b >>  0) & 7;
	} // for ( $i=0; $i < $len; $i += 3 )
	return $dec;
}

function bit4s0( &$str, $st, $len)
{
	$dec = array();
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$st+$i] );
		$dec[] = ($b >> 0x4);
		$dec[] = ($b &  0xf);
	} // for ( $i=0; $i < $len; $i++ )
	return $dec;
}
//////////////////////////////
// 06 0c-x
function case06( &$file, &$st, &$pix )
{
	// sub_401d70
	$bc = ord( $file[$st+0] );
	$s1 = substr($file, $st+1, $bc);
		$st += (1 + $bc);

	if ( $bc > 7 )
		php_warning('case06 bc > 7 [%x]', $bc);

	$s2 = substr($file, $st, 0x18);
		$st += 0x18;

	$dec = bit3s0($s2, 0, 0x18);
	foreach ( $dec as $d )
	{
		if ( $d === 0 )
		{
			$pix .= $file[$st];
				$st++;
		}
		else
			$pix .= $s1[$d-1];
	} // foreach ( $dec as $d )
	return;
}

// 07 0d-x
function case07( &$file, &$st, &$pix )
{
	// sub_401e00
	$bc = ord( $file[$st+0] );
	$s1 = substr($file, $st+1, $bc);
		$st += (1 + $bc);

	if ( $bc > 0xf )
		php_warning('case07 bc > f [%x]', $bc);

	$s2 = substr($file, $st, 0x20);
		$st += 0x20;

	$dec = bit4s0($s2, 0, 0x20);
	foreach ( $dec as $d )
	{
		if ( $d === 0 )
		{
			$pix .= $file[$st];
				$st++;
		}
		else
			$pix .= $s1[$d-1];
	} // foreach ( $dec as $d )
	return;
}
//////////////////////////////
function bit1c2( &$str, &$file, &$st, $c1, $c0 )
{
	$dec = '';
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$p = 8;
		while ( $p > 0 )
		{
			$p--;
			$bl = ($b >> $p) & 1;
			if ( $bl )
				$dec .= $c1;
			else
			{
				if ( $c0 === -1 )
				{
					$dec .= $file[$st];
						$st++;
				}
				else
					$dec .= $c0;
			}
		} // while ( $esi > 0 )
	} // for ( $i=0; $i < 8; $i++ )
	return $dec;
}

function bit2c4( &$str, &$file, &$st, $c1, $c2, $c3, $c0 )
{
	$dec = '';
	$len = strlen($str);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$p = 8;
		while ( $p > 0 )
		{
			$p -= 2;
			$al = ($b >> $p) & 3;
			switch ( $al )
			{
				case 1:  $dec .= $c1; break;
				case 2:  $dec .= $c2; break;
				case 3:  $dec .= $c3; break;
				case 0:
					if ( $c0 === -1 )
					{
						$dec .= $file[$st];
							$st++;
					}
					else
						$dec .= $c0;
					break;
			} // switch ( $al )
		} // while ( $ebp > 0 )
	} // for ( $i=0; $i < 0x10; $i++ )

	return $dec;
}
//////////////////////////////
// 02 08-x
function case02( &$file, &$st, &$pix )
{
	// sub_401ba0
	$b1 = $file[$st+0];
	$s1 = substr($file, $st+1, 8);
		$st += 9;

	$pix .= bit1c2($s1, $file, $st, $b1, -1);
	return;
}

// 03 09-x
function case03( &$file, &$st, &$pix )
{
	// sub_401c10
	$b2 = $file[$st+0];
	$b1 = $file[$st+1];
	$s1 = substr($file, $st+2, 8);
		$st += 10;

	$pix .= bit1c2($s1, $file, $st, $b1, $b2);
	return;
}

// 04 0a-x
function case04( &$file, &$st, &$pix )
{
	// sub_401c60
	$b3 = $file[$st+0];
	$b2 = $file[$st+1];
	$b1 = $file[$st+2];
	$s1 = substr($file, $st+3, 0x10);
		$st += 0x13;

	$pix .= bit2c4($s1, $file, $st, $b3, $b2, $b1, -1);
	return;
}

// 05 0b-x
function case05( &$file, &$st, &$pix )
{
	// sub_401cf0
	$bl = $file[$st+0];
	$b3 = $file[$st+1];
	$b2 = $file[$st+2];
	$b1 = $file[$st+3];
	$s1 = substr($file, $st+4, 0x10);
		$st += 0x14;

	$pix .= bit2c4($s1, $file, $st, $b3, $b2, $b1, $bl);
	return;
}
//////////////////////////////
function shou_decode( &$file, $w, $h )
{
	trace("== begin sub_402250\n");
	$pix = '';
	$list = array();

	$st = 0x28;
	foreach( array('Blue','Green','Red') as $ch )
	{
		$sh = $h;
		while ( $sh > 0 )
		{
			$sh -= 8;
			for ( $sw=0; $sw < $w; $sw += 8 )
			{
				$flg = ord( $file[$st] );
					$st++;
				trace("%-5s  %3d,%3d  %8x  FLAG %2x\n", $ch, $sw, $sh, $st-1, $flg);
				if ( ! isset($list[$flg]) )
					$list[$flg] = 0;
				$list[$flg]++;

				$sub = '';
				switch ( $flg )
				{
					case 0x00:
						// sub_401b60
						$sub = substr($file, $st, 0x40);
							$st += 0x40;
						break;

					case 0x01:
						// sub_401b80
						$b = $file[$st];
							$st++;
						$sub = str_repeat($b, 4*0x10);
						break;

					case 0x02:  case02($file, $st, $sub); break;
					case 0x03:  case03($file, $st, $sub); break;
					case 0x04:  case04($file, $st, $sub); break;
					case 0x05:  case05($file, $st, $sub); break;
					case 0x06:  case06($file, $st, $sub); break;
					case 0x07:  case07($file, $st, $sub); break;

					case 0x08:  case02($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x09:  case03($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x0a:  case04($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x0b:  case05($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x0c:  case06($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x0d:  case07($file, $st, $sub); rgb2x($file, $st, $sub); break;

					case 0x0e:  case0e($file, $st, $sub); break;
					case 0x0f:  case0e($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x10:  case0e($file, $st, $sub); row2column($sub); break;
					case 0x11:  case0e($file, $st, $sub); row2column($sub); rgb2x($file, $st, $sub); break;

					case 0x12:  case12($file, $st, $sub); break;
					case 0x13:  case12($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x16:  case12($file, $st, $sub); row2column($sub); break;
					case 0x17:  case12($file, $st, $sub); row2column($sub); rgb2x($file, $st, $sub); break;

					case 0x14:  case14($file, $st, $sub); break;
					case 0x15:  case14($file, $st, $sub); rgb2x($file, $st, $sub); break;
					case 0x18:  case14($file, $st, $sub); row2column($sub); break;
					case 0x19:  case14($file, $st, $sub); row2column($sub); rgb2x($file, $st, $sub); break;

					default:
						save_file("pix.bin", $pix);
						return php_error("UNKNOWN flag %x [%x]", $flg, $st-1);
				} // switch ( $flg )

				$pix .= substr($sub, 0, 0x40);
			} // for ( $sw=0; $sw < $w; $sw += 8 )
		} // while ( $sh > 0 )
	} // foreach( array('Blue','Green','Red') as $ch )

	trace("== end sub_402250\n");

	$sum = 0;
	foreach ( $list as $k => $v )
	{
		printf("list[%2x] = %8x\n", $k, $v);
		$sum += $v;
	}
	printf("SUM %x\n", $sum);
	return $pix;
}
//////////////////////////////
function pixrgba( &$pix, $w, $h )
{
	$img = canvpix($w, $h, PIX_BLACK);
	$pos = 0;
	foreach( array(2,1,0) as $ch )
	{
		$sh = $h;
		while ( $sh > 0 )
		{
			$sh -= 8;
			for ( $sw=0; $sw < $w; $sw += 8 )
			{
				$th = 8;
				while ( $th > 0 )
				{
					$th--;
					for ( $tw=0; $tw < 8; $tw++ )
					{
						$dyy = ($sh + $th) * $w * 4;
						$dxx = $dyy + ($sw + $tw) * 4;
						$img[$dxx+$ch] = $pix[$pos];
							$pos++;
					} // for ( $tw=0; $tw < 8; $tw++ )
				} // while ( $th > 0 )
			} // for ( $sw=0; $sw < $w; $sw += 8 )
		} // while ( $sh > 0 )
	} // foreach( array(2,1,0) as $ch )

	$pix = $img;
	return;
}

function shoukan( $fname )
{
	// for *.fcg only
	if ( stripos($fname, '.fcg') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( str2int($file,0,4) != 0x28 )
		return;

	$w = str2int($file, 4, 4);
	$h = str2int($file, 8, 4);
	printf("== %s  %x x %x\n", $fname, $w, $h);

	$pix = shou_decode($file, $w, $h);
	//save_file("$fname.bgr", $pix);
	//if ( strlen($pix) !== ($w*$h*3) )
		//return php_error("LEN %x [expect %x]\n", strlen($pix), $w*$h*3);
	pixrgba($pix, $w, $h);

	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => $pix,
	);
	save_clutfile("$fname.rgba", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	shoukan( $argv[$i] );

/*
black.fcg / frame.fcg / null.fcg
	1
logo.fcg
	1 2 3
select.fcg
	1 2 3 4

pixel in BGR planar format
	03  44 88  01 02 04 08 80 40 20 10
	  - - - 1 - - - -  10
	  - - 1 - - - - -  20
	  - 1 - - - - - -  40
	  1 - - - - - - -  80
	  - - - - 1 - - -  08
	  - - - - - 1 - -  04
	  - - - - - - 1 -  02
	  - - - - - - - 1  01
tile in 8x8 pixels
tile in bottom-to-top pixels order
screen in bottom-to-top , left to right tiles order

	   0 Blue
	12c0 Green
	2580 Red
	3840 end
 */
