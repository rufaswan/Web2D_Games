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
declare(strict_types=1);

define('PIX_BLACK', ZERO . ZERO . ZERO . BYTE);
define('PIX_WHITE', BYTE . BYTE . BYTE . BYTE);
define('PIX_ALPHA', ZERO . ZERO . ZERO . ZERO);

class clutdata
{
	public $t   = ''; // for CLUT with shared/unknown $pal ,
	public $id  = -1; // for atlastex
	public $x   =  0; // for image -> canvas
	public $y   =  0; // for image -> canvas
	public $hw  = -1; // for canvas
	public $hh  = -1; // for canvas
	public $w   = -1;
	public $h   = -1;
	public $pal = '';
	public $pix = '';
} // class clutdata

class clutfile
{
	public static function verify( clutdata &$img ) : int
	{
		if ( empty($img) )
			return tool::warning('img null');

		$img->t = '';
		$error  = 0;
		if ( $img->w < 1 )
			$error += tool::warning('img->w < 1', $img->w);
		if ( $img->h < 1 )
			$error += tool::warning('img->h < 1', $img->h);

		$cc = strlen($img->pal) & 3;
		if ( $cc > 0 )
			$error += tool::warning('img->pal incomplete RGBA', $cc);

		$size = $img->w * $img->h;
		$len  = strlen($img->pix);
		if ( ! empty($img->pal) )
		{
			if ( $len !== $size )
				$error += tool::warning('img->pix incomplete CLUT', $len, $size);
		}
		else
		{
			if ( $len !== ($size*4) )
				$error += tool::warning('img->pix incomplete RGBA', $len, $size*4);
		}

		if ( $error < 0 )
			return $error;

		$img->t = ( $size === $len ) ? 'CLUT' : 'RGBA';
		return 0;
	}

	public static function loadfile( string $pfx ) : string
	{
		// find .clut or .rgba
		if ( is_file("$pfx.clut") )
			return file_get_contents("$pfx.clut");
		if ( is_file("$pfx.rgba") )
			return file_get_contents("$pfx.rgba");

		// or has x,y position
		// TODO
		return '';
	}

	public static function load( string $fname ) : ?clutdata
	{
		$img = new clutdata;
		$file = tool::load($fname);
		if ( strlen($file) < 16 )
			return null;

		if ( substr($file,0,4) === 'RGBA' )
		{
			$img->w = tool::ordstr($file, 4, 4);
			$img->h = tool::ordstr($file, 8, 4);
			$sz = $img->w * $img->h * 4;

			$img->t = 'RGBA';
			$img->pix = tool::substr($file, 12, $sz);

			$file = substr($file, 12 + $sz);
			if ( strlen($file) >= 8 )
			{
				$img->x = tool::ordstr($file, 0, 4, true);
				$img->y = tool::ordstr($file, 4, 4, true);
			}
			return $img;
		}
		if ( substr($file,0,4) === 'CLUT' )
		{
			$cc = tool::ordstr($file, 4, 4) * 4;
			$img->w = tool::ordstr($file,  8, 4);
			$img->h = tool::ordstr($file, 12, 4);
			$sz = $img->w * $img->h;

			$img->t = 'CLUT';
			$img->pal = tool::substr($file, 16, $cc);
			$img->pix = tool::substr($file, 16+$cc, $sz);

			$file = substr($file, 16 + $cc + $sz);
			if ( strlen($file) >= 8 )
			{
				$img->x = tool::ordstr($file, 0, 4, true);
				$img->y = tool::ordstr($file, 4, 4, true);
			}
			return $img;
		}
		return null;
	}

	public static function save( string $pfx, clutdata &$img ) : void
	{
		if ( self::verify($img) < 0 )
			return;

		// trim 3/4 file size
		self::rgba2clut($img);

		if ( ! empty($img->pal) )
		{
			$cc = strlen($img->pal) >> 2;
			$bin = 'CLUT';
			$bin .= tool::chr($cc    , 4);
			$bin .= tool::chr($img->w, 4);
			$bin .= tool::chr($img->h, 4);
			$bin .= $img->pal;
			$bin .= $img->pix;
			if ( $img->x !== 0 || $img->y !== 0 )
			{
				$bin .= tool::chr($img->x, 4);
				$bin .= tool::chr($img->y, 4);
			}
			tool::save($pfx.'.clut', $bin);
		}
		else
		{
			$bin  = 'RGBA';
			$bin .= tool::chr($img->w, 4);
			$bin .= tool::chr($img->h, 4);
			$bin .= $img->pix;
			if ( $img->x !== 0 || $img->y !== 0 )
			{
				$bin .= tool::chr($img->x, 4);
				$bin .= tool::chr($img->y, 4);
			}
			tool::save($pfx.'.rgba', $bin);
		}
	}
	//////////////////////////////
	// convert series
	public static function clut2rgba( clutdata &$img ) : void
	{
		if ( empty($img->pal) )
			return;

		$pix  = &$img->pix;
		$rgba = '';
		$len  = strlen($pix);
		for ( $i=0; $i < $len; $i++ )
		{
			$p = ord( $pix[$i] );
			$s = substr($img->pal, $p << 2, 4);
			if ( strlen($s) !== 4 )
				tool::error('clut2rgba pal no data', $p);

			$rgba .= $s;
		} // for ( $i=0; $i < $len; $i++ )

		$img->t   = 'RGBA';
		$img->pal = '';
		$img->pix = $rgba;
	}

	public static function rgba2clut( clutdata &$img ) : void
	{
		if ( ! empty($img->pal) )
			return;

		$pal = [];
		$pix = '';
		$siz = strlen($img->pix);
		for ( $i=0; $i < $siz; $i += 4 )
		{
			$c = substr($img->pix, $i, 4);
			$p = array_search($c, $pal);
			if ( $p === false )
			{
				$p = count($pal);
				$pal[] = $c;
			}

			// if > ff colors
			if ( $p >> 8 )
				return;

			$pix .= chr($p);
		} // for ( $i=0; $i < $siz; $i += 4 )

		$img->t   = 'CLUT';
		$img->pal = implode('', $pal);
		$img->pix = $pix;
	}

	public static function data2array( clutdata &$img ) : array
	{
		$arr = [
			'w'   => (int)$img->w,
			'h'   => (int)$img->h,
			'pal' => "{$img->pal}",
			'pix' => "{$img->pix}",
		];
		return $arr;
	}

	public static function array2data( array &$img ) : ?clutdata
	{
		$tag = ['w','h','pal','pix'];
		foreach ( $tag as $t )
		{
			if ( ! isset($img[$t]) )
				return null;
		}
		$data = new clutdata;
		$data->w   = (int)$img['w'];
		$data->h   = (int)$img['h'];
		$data->pal = "{$img['pal']}";
		$data->pix = "{$img['pix']}";
		return $data;
	}
	//////////////////////////////
	// palette series
	public static function webpal16() : string
	{
		// https://en.wikipedia.org/wiki/Web_color
		// Basic colors
		$clr = '---f  --8f  -8-f  -88f' // K   8B  8G  8C
			. '8--f  8-8f  88-f  cccf'  // 8R  8M  8Y  cW
			. '888f  --ff  -f-f  -fff'  // 8W  B   G   C
			. 'f--f  f-ff  ff-f  ffff'; // R   M   Y   W

		$len = strlen($clr);
		$pal = '';
		for ( $i=0; $i < $len; $i++ )
		{
			switch ( $clr[$i] )
			{
				case '-':  $pal .= "\x00"; break;
				case '8':  $pal .= "\x80"; break;
				case 'c':  $pal .= "\xc0"; break;
				case 'f':  $pal .= "\xff"; break;
			} // switch ( $clr[$i] )
		} // for ( $i=0; $i < 16; $i++ )

		return $pal;
	}

	public static function gradient_pal( int $cc, string $src, string $dst ) : string
	{
		if ( $cc < 2 )  return '';
		if ( strlen($src) !== 4 )  return '';
		if ( strlen($dst) !== 4 )  return '';

		$step = 1.0 / ($cc - 1);
		$sc = [];
		$dc = [];
		$st = [];
		for ( $i=0; $i < 4; $i++ )
		{
			$sc[$i] = ord( $src[$i] );
			$dc[$i] = ord( $dst[$i] );
			$st[$i] = ($dc[$i] - $sc[$i]) * $step;
		}

		$pal = '';
		for ( $c=0; $c < $cc; $c++ )
		{
			for ( $i=0; $i < 4; $i++ )
			{
				$p    = $sc[$i] & BIT8;
				$pal .= chr($b);
				$sc[$i] += $st[$i];
			} // for ( $i=0; $i < 4; $i++ )
		} // for ( $c=0; $c < $cc; $c++ )
		return $pal;
	}

	public static function graypal( int $cc ) : string
	{
		return self::gradient_pal($cc, PIX_BLACK, PIX_WHITE);
	}

	//////////////////////////////
	// render series
	public static function ripsrc( clutdata &$canvas, int $x, int $y, int $w, int $h ) : ?clutdata
	{
		// 0...w or h only
		$x2 = $x + $w;
		$y2 = $y + $h;
		if ( $x < 0 )  return null;
		if ( $y < 0 )  return null;
		if ( $x2 > $canvas->w )  return null;
		if ( $y2 > $canvas->h )  return null;

		$img = new clutdata;
		$img->x = $x;
		$img->y = $y;
		$img->w = $w;
		$img->h = $h;
		$img->pal = &$canvas->pal;
		$img->pix = '';

		$byte = ( empty($img->pal) ) ? 4 : 1;
		for ( $cy = $y; $cy < $y2; $cy++ )
		{
			$cxx = ($cy * $canvas->w) + $x;
			$img->pix .= tool::substr($canvas->pix, $cxx*$byte, $w*$byte);
		} // for ( $cy = $y; $cy < $y2; $cy++ )
		return $img;
	}

	public static function drawsrc( clutdata &$canvas, clutdata &$image ) : void
	{
		self::clut2rgba($canvas);
		self::clut2rgba($image );
		if ( $image->x < 0 )  return;
		if ( $image->y < 0 )  return;
		if ( ($image->x + $image->w) > $canvas->w )  return;
		if ( ($image->y + $image->h) > $canvas->h )  return;

		// src=texture , 0...w or h only
		$row = $image->w * 4;
		for ( $y=0; $y < $image->h; $y++ )
		{
			$sub = tool::substr($image->pix, $y*$row, $row);

			$dy  = ($image->y + $y) * $canvas->w;
			$dx  = $dy + $image->x;
			tool::str_update($canvas->pix, $dx*4, $sub);
		} // for ( $y = $image->y; $y < $y2; $y++ )
	}

	public static function hflip( clutdata &$img ) : void
	{
		// horizontal flip or flip x
		$byte = ( empty($img->pal) ) ? 4 : 1;
		$w = $img->w;
		$h = $img->h;

		$pix = '';
		$row = $w * $byte;
		for ( $y=0; $y < $h; $y++ )
		{
			$sub = tool::substr($img->pix, $y*$row, $row);
			$x = $w;
			while ( $x > 0 )
			{
				$x--;
				$pix .= tool::substr($sub, $x*$byte, $byte);
			} // while ( $x > 0 )
		} // for ( $y=0; $y < $h; $y++ )
		$img->pix = $pix;
	}

	public static function vflip( clutdata &$img ) : void
	{
		// vertical flip or flip y
		// BMP pix data is vflip
		$byte = ( empty($img->pal) ) ? 4 : 1;
		$w = $img->w;
		$h = $img->h;

		$pix = '';
		$row = $w * $byte;
		$y = $h;
		while ( $y > 0 )
		{
			$y--;
			$pix .= tool::substr($img->pix, $y*$row, $row);
		} // while ( $y > 0 )
		$img->pix = $pix;
	}
} // class clutfile
