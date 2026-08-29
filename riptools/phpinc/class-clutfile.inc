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
	public $is_flipx  = false;
	public $is_flipy  = false;
	public $is_rotate = false;
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
			'w'   => $img->w,
			'h'   => $img->h,
			'pal' => $img->pal,
			'pix' => $img->pix,
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
		$data->w   = $img['w'];
		$data->h   = $img['w'];
		$data->pal = $img['pal'];
		$data->pix = $img['pix'];
		return $data;
	}
	//////////////////////////////
	// palette series
	public static function webpal16() : string
	{
		// https://en.wikipedia.org/wiki/Web_color
		// Basic colors
		$clr  = '---f  --8f  -8-f  -88f'; // K   8B  8G  8C
		$clr .= '8--f  8-8f  88-f  cccf'; // 8R  8M  8Y  cW
		$clr .= '888f  --ff  -f-f  -fff'; // 8W  B   G   C
		$clr .= 'f--f  f-ff  ff-f  ffff'; // R   M   Y   W

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

		$r1 = ord( $src[0] );
		$g1 = ord( $src[1] );
		$b1 = ord( $src[2] );
		$a1 = ord( $src[3] );

		$r2 = ord( $dst[0] );
		$g2 = ord( $dst[1] );
		$b2 = ord( $dst[2] );
		$a2 = ord( $dst[3] );

		$sr = ($r2 - $r1) / ($cc - 1);
		$sg = ($g2 - $g1) / ($cc - 1);
		$sb = ($b2 - $b1) / ($cc - 1);
		$sa = ($a2 - $a1) / ($cc - 1);

		$pal = '';
		for ( $i=0; $i < $cc; $i++ )
		{
			$pal .= chr($r1) . chr($g1) . chr($b1) . chr($a1);
			$r1 = tool::int_clamp($r1 + $sr, 0, BIT8);
			$g1 = tool::int_clamp($g1 + $sg, 0, BIT8);
			$b1 = tool::int_clamp($b1 + $sb, 0, BIT8);
			$a1 = tool::int_clamp($a1 + $sa, 0, BIT8);
		}
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
		if ( $x < 0 )  return null;
		if ( $y < 0 )  return null;
		if ( ($x+$w) > $canvas->w )  return null;
		if ( ($y+$h) > $canvas->h )  return null;

		$img = new clutdata;
		$img->x = $x;
		$img->y = $y;
		$img->w = $w;
		$img->h = $h;
		$img->pal = &$canvas->pal;

		$byte = ( empty($img->pal) ) ? 4 : 1;
		for ( $iy=0; $iy < $h; $iy++ )
		{
			$sx  = ($iy * $canvas->w) + $x;
			$sub = tool::substr($canvas->pix, $sx*$byte, $w*$byte);
			$img->pix .= $sub;
		} // for ( $iy=0; $iy < $h; $iy++ )
		return $img;
	}

	public static function drawsrc( clutdata &$canvas, clutdata &$image ) : void
	{
		self::clut2rgba($canvas);
		self::clut2rgba($image );
		if ( $image->x < 0 )  return;
		if ( $image->y < 0 )  return;
		if ( ($image->x + $image->w) >= $canvas->w )  return;
		if ( ($image->y + $image->h) >= $canvas->h )  return;

		// src=texture , 0...w or h only
		$row = $image->w * 4;
		for ( $y=0; $y < $image->h; $y++ )
		{
			$sub = tool::substr($image->pix, $y*$row, $row);
			$dx  = ($image->y + $y) + $image->x;
			tool::str_update($canvas->pix, $dx*4, $sub);
		} // for ( $y = $image->y; $y < $y2; $y++ )
	}

	public static function drawdst( clutdata &$canvas, clutdata &$image ) : void
	{
		self::clut2rgba($canvas);
		self::clut2rgba($image );
		$x1 = $image->x + ($canvas->w >> 1);
		$y1 = $image->y + ($canvas->h >> 1);
		if ( ($x1 + $image->w) >= $canvas->w )  return;
		if ( ($y1 + $image->h) >= $canvas->h )  return;

		// dst=canvas , -hw...+hw or -hh...+hh
		$row = $image->w * 4;
		for ( $y=0; $y < $image->h; $y++ )
		{
			$sub = tool::substr($image->pix, $y*$row, $row);
			$dx  = ($y1 + $y) + $x1;
			tool::str_update($canvas->pix, $dx*4, $sub);
		} // for ( $y = $image->y; $y < $y2; $y++ )
	}

	public static function dst_hwhh( array &$list ) : array
	{
		$hw = -1;
		$hh = -1;
		foreach ( $list as $img )
		{
			$x1 = abs($img->x);
			$y1 = abs($img->y);
			$x2 = abs($x1 + $img->w);
			$y2 = abs($y1 + $img->h);
			$hw = max($hw, $x1, $x2);
			$hh = max($hh, $h1, $h2);
		} // foreach ( $list as $img )

		if ( $hw < 0 )  return [];
		if ( $hh < 0 )  return [];
		return [$hw,$hh];
	}

	public static function rotate( clutdata &$img ) : void
	{
		$byte = ( empty($img->pal) ) ? 4 : 1;
		$w = $img->w;
		$h = $img->h;
		$pix = '';

		if ( ! $img->is_rotate )
		{
			$x = $img->w;
			while ( $x > 0 )
			{
				$x--;
				for ( $y=0; $y < $h; $y++ )
				{
					$sx = ($y * $w) + $x;
					$sub = tool::substr($img->pix, $sx*$byte, $byte);
					$pix .= $sub;
				} // for ( $y=0; $y < $w; $y++ )
			} // while ( $x > 0 )
		}
		else
		{
			for ( $x=0; $x < $w; $x++ )
			{
				$y = $img->h;
				while ( $y > 0 )
				{
					$y--;
					$sx = ($y * $w) + $x;
					$sub = tool::substr($img->pix, $sx*$byte, $byte);
					$pix .= $sub;
				} // while ( $y > 0 )
			} // for ( $x=0; $x < $w; $x++ )
		}

		$img->is_rotate = ! $img->is_rotate;
		$img->w = $h;
		$img->h = $w;
		$img->pix = $pix;
	}

	public static function flipped( clutdata &$img, string $flip='' ) : void
	{
		$f = 0;
		switch ( $flip )
		{
			case  'x':
				$f = 1;
				$img->is_flipx = ! $img->is_flipx;
				break;
			case  'y':
				$f = 2;
				$img->is_flipy = ! $img->is_flipy;
				break;
			case 'xy':
			case 'yx':
				$f = 3;
				$img->is_flipx = ! $img->is_flipx;
				$img->is_flipy = ! $img->is_flipy;
				break;
			default:
				return;
		}

		$byte = ( empty($img->pal) ) ? 4 : 1;
		$w = $img->w;
		$h = $img->h;
		$pix = '';

		for ( $y=0; $y < $h; $y++ )
		{
			$sy = $y;
			if ( $f & 2 )
				$sy = $h - 1 - $y;

			for ( $x=0; $x < $w; $x++ )
			{
				$sx = $x;
				if ( $f & 1 )
					$sx = $w - 1 - $x;

				$sxx = ($y * $w) + $x;
				$sub = tool::substr($img->pix, $sxx*$byte, $byte);
				$pix .= $sub;
			} // for ( $x=0; $x < $w; $x++ )
		} // for ( $y=0; $y < $h; $y++ )

		$img->pix = $pix;
	}
} // class clutfile
