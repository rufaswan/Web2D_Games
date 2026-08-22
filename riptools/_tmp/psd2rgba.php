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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');
tool::require('class-clutfile');
//tool::require('class-zipstore');
tool::extension('zlib');

function readsect( &$file, &$pos, $fn )
{
	$len = ieee754::ordstr($file, $pos, 4);
		$pos += 4;
	tool::trace(__FUNCTION__, $fn, $pos, $len);

	$sub = substr($file, $pos, $len);
		$pos += $len;
	tool::save($fn, $sub);
	return $sub;
}

function unrle_layer( &$dat, $comp, $w )
{
	$pix = '';
	foreach ( $dat as $v )
	{
		$dec = '';
		switch ( $comp )
		{
			case 0: // raw
				$dec = $v;
				break;
			case 1: // rle
				$ed = strlen($v);
				$st = 0;
				while ( $st < $ed )
				{
					$op = ord( $v[$st] );
						$st++;

					if ( $op >= 0x80 ) // dup
					{
						$op   = 0x100 - $op;
						$dec .= str_repeat($v[$st], $op+1);
						$st  += 1;
					}
					else // copy
					{
						$dec .= substr($v, $st, $op+1);
						$st  += ($op+1);
					}
				} // while ( $st < $ed )
				break;
			case 2: // zip w/o prediction
			case 3: // zip w/prediction
				$dec = zlib_decode($v);
				break;
			default:
				$dec = '';
				break;
		} // switch ( $comp )

		while ( strlen($dec) < $w )
			$dec .= ZERO;
		$pix .= substr($dec, 0, $w);
	} // foreach ( $dat as $v )

	//echo bin2hex($pix) . "\n";
	return $pix;
}

function save_layers( &$lyr, $dir )
{
	$w = $lyr['rect'][2];
	$h = $lyr['rect'][3];
	$sz = $w * $h;

	$name = $lyr['name'];
	$img = new clutdata;
	$img->w = $w;
	$img->h = $h;
	$img->pix = str_repeat(BYTE, $sz*4);

	// grayscale has channel 0/red + -1/alpha only
	foreach ( $lyr['chan'] as $ck => $cv )
	{
		//save_file("$dir/$name-$ck.bin", $cv[2]);
		switch ( $cv[0] )
		{
			case  0: $c = 0; break; // red
			case  1: $c = 1; break; // green
			case  2: $c = 2; break; // blue
			case -1: $c = 3; break; // alpha
		}

		for ( $i=0; $i < $sz; $i++ )
			$img->pix[$i*4+$c] = $cv[2][$i];
	} // foreach ( $lyr['chan'] as $ck => $cv )

	$fn = sprintf('%s/%s', $dir, $lyr['name']);
	clutfile::save($fn, $img);
	return;
}
//////////////////////////////
function image_data( &$file, &$pos, $dir, $w, $h, $ch )
{
	// last section has no length header
	$fn   = sprintf('%s/%s.bin', $dir, __FUNCTION__);
	$sect = substr ($file, $pos);
	tool::trace($fn, $pos, strlen($sect));
	save_file($fn, $sect);
	if ( empty($sect) )
		return '';

	// final merged layer
	// can be calculated from layer_mask_info()
	$rle = ieee754::ordstr($sect, 0, 2);
	$st = 2;
	$ed = $st + ($h * 2 * $ch);

	$pix = [];
	for ( $i=0; $i < $ch; $i++ )
	{
		$dat = [];
		for ( $y=0; $y < $h; $y++ )
		{
			$siz = ieee754::ordstr($sect, $st, 2);
				$st += 2;
			$b1 = tool::substr($sect, $ed, $siz);
			//echo bin2hex($b1) . "\n";
			$dat[] = $b1;
				$ed += $siz;
		}
		$pix[$i] = unrle_layer($dat, $rle, $w);
		//save_file($ck, $lv['chan'][$ck][2]);
	} // for ( $i=0; $i < $ch; $i++ )

	$c = [];
	if ( $ch === 4 )  $c = [0,1,2,3];
	if ( $ch === 3 )  $c = [0,1,2];

	$sz = $w * $h;
	$img = new clutdata;
	$img->w = $w;
	$img->h = $h;
	$img->pix = str_repeat(BYTE, $sz*4);

	for ( $i=0; $i < $ch; $i++ )
	{
		$cp = $c[$i];
		for ( $j=0; $j < $sz; $j++ )
			$img->pix[$j*4+$cp] = $pix[$i][$j];
	} // for ( $i=0; $i < $ch; $i++ )

	clutfile::save("$dir/psd-final", $img);
	return $sect;
}

function layer_mask_info( &$file, &$pos, $dir )
{
	$sect = readsect($file, $pos, sprintf('%s/%s.bin', $dir, __FUNCTION__));
	if ( empty($sect) )
		return '';

	// layer info
	$len = ieee754::ordstr($sect, 0, 4);
	$cnt = ieee754::ordstr($sect, 4, 2, true);
		$st = 6;
	$cnt = abs($cnt);

	$layer = [];
	for ( $i=0; $i < $cnt; $i++ )
	{
		// layer records
		$y1 = ieee754::ordstr($sect, $st+ 0, 4, true); // top
		$x1 = ieee754::ordstr($sect, $st+ 4, 4, true); // left
		$y2 = ieee754::ordstr($sect, $st+ 8, 4, true); // bottom
		$x2 = ieee754::ordstr($sect, $st+12, 4, true); // right
		printf("layer[%x]  %x,%x  %x %x\n", $i, $x1, $y1, $x2, $y2);

		$ch = ieee754::ordstr($sect, $st+16, 2);
			$st += 18;
		if ( $ch !== 4 ) // gray=2
			tool::notice('layer channel count !== 4', $ch);

		$channel = [];
		for ( $j=0; $j < $ch; $j++ )
		{
			$id = ieee754::ordstr($sect, $st+0, 2, true);
			$sz = ieee754::ordstr($sect, $st+2, 4);
				$st += 6;
			tool::trace('channel', $j, $id, $sz);
			$channel[$j] = [$id,$sz];
		}

		$blend1 = substr($sect, $st+0, 4);
		$blend2 = substr($sect, $st+4, 4);
			$st += 8;
		tool::trace('layer blend', $i, $blend2);

		$opacity  = ord( $sect[$st+0] );
		$clipping = ord( $sect[$st+1] );
		$flags    = ord( $sect[$st+2] );
		$padding  = ord( $sect[$st+3] );

		$len = ieee754::ordstr($sect, $st+4, 4);
		$ext = substr ($sect, $st+8, $len);
			$st += ($len + 8);

		$lyr_name  = tool::substr0($ext, 8);
		$layer[$i] = [
			'rect'  => [$x1 , $y1 , $x2-$x1 , $y2-$y1],
			'chan'  => $channel,
			'blend' => $blend2,
			'name'  => trim($lyr_name),
		];
	} // for ( $i=0; $i < $cnt; $i++ )
	//print_r($layer);

	// channel image data
	foreach ( $layer as $lk => $lv )
	{
		$w = $lv['rect'][2];
		$h = $lv['rect'][3];
		tool::trace('layer', $lk, $w, $h);

		foreach ( $lv['chan'] as $ck => $cv )
		{
			tool::trace('lyr chan', $lk, $cv[0], $cv[1], $st);
			$bak = $st;
				$st += $cv[1];

			$rle = ieee754::ordstr($sect, $bak, 2);
				$bak += 2;
			$ed = $bak + ($h * 2);

			$dat = [];
			for ( $cy=0; $cy < $h; $cy++ )
			{
				$siz = ieee754::ordstr($sect, $bak, 2);
					$bak += 2;
				$b1 = tool::substr($sect, $ed, $siz);
				//echo bin2hex($b1) . "\n";
				$dat[] = $b1;
					$ed += $siz;
			}
			$lv['chan'][$ck][2] = unrle_layer($dat, $rle, $w);
			//save_file($ck, $lv['chan'][$ck][2]);

		} // foreach ( $lv['chan'] as $ck => $cv )

		save_layers($lv, $dir);
	} // foreach ( $layer as $lk => $lv )

	// global layer mask
	// extra data
	return $sect;
}

function image_resource( &$file, &$pos, $dir )
{
	$sect = readsect($file, $pos, sprintf('%s/%s.bin', $dir, __FUNCTION__));
	if ( empty($sect) )
		return '';

	$ed = strlen($sect);
	$st = 0;
	while ( $st < $ed )
	{
		$mgc = substr($sect, $st+0, 4);
		$typ = ieee754::ordstr($sect, $st+4, 2);
			$st += 6;
		if ( $mgc !== '8BIM' )
			break;

		$nam = tool::substr0($sect, $st);
			$st += strlen($nam) + 1;
			if ( $st & 1 ) // pad to even
				$st++;

		$siz = ieee754::ordstr($sect, $st+0, 4);
		$sub = tool::substr($sect, $st+4, $siz);
			if ( $siz & 1 ) // pad to even
				$siz++;
			$st += ($siz + 4);

		tool::trace('8BIM', $typ, $siz);
		switch ( $typ )
		{
			case 0x3ed:  echo "Resolution  Info\n"; break;
			case 0x400:  echo "Layer State Info\n"; break;
			case 0x402:  echo "Layer Group Info\n"; break;
			case 0x3ee:  echo "Alpha Names     \n"; break;
			case 0x40f:  echo "ICC Profile     \n"; break;
			default:     echo "unsupported     \n"; break;
		} // switch ( $typ )
	} // while ( $st < $ed )
	return $sect;
}

function color_mode_data( string &$file, int &$pos, string $dir ) : string
{
	$sect = readsect($file, $pos, sprintf('%s/%s.bin', $dir, __FUNCTION__));
	if ( empty($sect) )
		return '';
	if ( strlen($sect) !== 0x300 )
	{
		tool::warning('colormode != 0x300', strlen($sect));
		return '';
	}

	$pal = '';
	for ( $i=0; $i < 0x100; $i++ )
	{
		$r = $sect[$i];
		$g = $sect[$i+0x100];
		$b = $sect[$i+0x200];
		$pal .= $r . $g . $b . BYTE;
	}
	return $pal;
}
//////////////////////////////
function psd2rgba( string $fname ) : int
{
	tool::trace(__FUNCTION__, $fname);
	$file = file_get_contents($fname);
	if ( empty($file) )  return -1;

	if ( substr($file,0,6) !== "8BPS\x00\x01" )
		return -1;

	$dir = str_replace('.', '_', $fname);

	// header
	// 00  4  =8BPS
	// 04  2  =1 , version
	// 06  6  =00 00 00 00 00 00
	// 0c  2  channel
	// 0e  4  height
	// 12  4  width
	// 16  2  depth
	// 18  2  color mode
	$channel = ieee754::ordstr($file, 0x0c, 2); // 1-56
	$height  = ieee754::ordstr($file, 0x0e, 4);
	$width   = ieee754::ordstr($file, 0x12, 4);
	$depth   = ieee754::ordstr($file, 0x16, 2); // 1 8 16 32
	$mode    = ieee754::ordstr($file, 0x18, 2);
	tool::trace('PSD', $fname, $width, $height, $depth);

	// color mode
	// 0 bitmap
	// 1 grayscale  (GIMP image mode)
	// 2 indexed    (GIMP image mode , no layers support)
	// 3 rgb        (GIMP image mode)
	// 4 cmyk
	// 5 -
	// 6 -
	// 7 multichannel
	// 8 duotone
	// 9 lab
	if ( $mode !== 3 ) // rgb only
		return tool::warning('is not RGB mode');
	if ( $depth !== 8 )
		return tool::warning('is not R8G8B8/A8 format');
	if ( $channel !== 3 && $channel !== 4 )
		return tool::warning('is not RGB/A image');

	// PSD has 5 sections
	// file header is already read
	$pos = 0x1a;
	$pal = color_mode_data($file, $pos, $dir); // rgb=0 , gray=0
	$res = image_resource ($file, $pos, $dir);
	$lyr = layer_mask_info($file, $pos, $dir); // indexed=0
	$img = image_data     ($file, $pos, $dir, $width, $height, $channel);
	return 0;
}

printf("Only accept RGB24 or RGBA32 *.PSD file\n");
for ( $i=1; $i < $argc; $i++ )
	psd2rgba( $argv[$i] );
//tool::argv_callback($argv, 'psd2rgba');
