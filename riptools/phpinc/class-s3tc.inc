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

// https://en.m.wikipedia.org/wiki/S3_Texture_Compression
// https://www.khronos.org/opengl/wiki/S3_Texture_Compression
// https://www.khronos.org/registry/OpenGL/extensions/EXT/EXT_texture_compression_s3tc.txt

// https://docs.microsoft.com/en-us/windows/win32/direct3d11/texture-block-compression-in-direct3d-11
// https://docs.microsoft.com/en-us/windows/desktop/direct3ddds/dx-graphics-dds-pguide

// per OpenGL spec , read in Little-Endian order
class s3tc_texture
{
	private static function int16( string &$data, int $pos ) : int
	{
		$b1 = ord($data[$pos+0]);
		$b2 = ord($data[$pos+1]);
		return $b1 | ($b2 << 8);
	}

	private static function clamp( float $num ) : int
	{
		if ( $num < 0    )  return 0;
		if ( $num > BIT8 )  return BIT8;
		return (int)$num;
	}

	private function bc1_index( string &$data, int $pos ) : array
	{
		// 4*4  2 bit index
		$index = [];
		for ( $i=0; $i < 4; $i++ )
		{
			$b1 = ord( $data[$pos] );
				$pos++;
			for ( $j=0; $j < 8; $j += 2 )
				$index[] = ($b1 >> $j) & 3;
		} // for ( $i=0; $i < 4; $i++ )
		return $index;
	}

	private function bc3_index( string &$data, int $pos ) : array
	{
		// 4*4  3 bit index
		$index = [];
		for ( $i=0; $i < 6; $i += 3 )
		{
			$b1 = ord( $data[$pos+0] );
			$b2 = ord( $data[$pos+1] );
			$b3 = ord( $data[$pos+2] );
				$pos += 3;

			$int = $b1 | ($b2 << 8) | ($b3 << 16);
			for ( $j=0; $j < 24; $j += 3 )
				$index[] = ($int >> $j) & 7;
		} // for ( $i=0; $i < 6; $i += 3 )
		return $index;
	}

	private function rgb565( int $int ) : array
	{
		// fedcba98 76543210
		// rrrrrggg gggbbbbb
		$r = ($int >> 8) & 0xf8; // >> 11 << 3
		$g = ($int >> 3) & 0xfc; // >>  5 << 2
		$b = ($int << 3) & 0xf8; // >>  0 << 3
		return [$r,$g,$b,BIT8];
	}

	private function rgb_interpolate( array $rgb1, float $fact1, array $rgb2, float $fact2 ) : array
	{
		$r = ($rgb1[0] * $fact1) + ($rgb2[0] * $fact2);
		$g = ($rgb1[1] * $fact1) + ($rgb2[1] * $fact2);
		$b = ($rgb1[2] * $fact1) + ($rgb2[2] * $fact2);
		return [$r,$g,$b,BIT8];
	}

	private function rgba_mix( array $rgb, array $alp ) : string
	{
		$pix = '';
		for ( $i=0; $i < 16; $i++ )
		{
			$r = self::clamp($rgb[$i][0]);
			$g = self::clamp($rgb[$i][1]);
			$b = self::clamp($rgb[$i][2]);
			$a = self::clamp($alp[$i]   );
			$pix .= chr($r) . chr($g) . chr($b) . chr($a);
		}
		return $pix;
	}
	//////////////////////////////
	//      Color      index
	// BC1  16*2  RGB  2*16 = 32+32
	// BC2   4*16 A         = 64    + BC1 = 128
	// BC3   8*2  A    3*16 = 16+48 + BC1 = 128
	private function bc1_color( string &$data, int $pos, int $bc ) : array
	{
		$int1 = self::int16($data, $pos+0);
		$int2 = self::int16($data, $pos+2);
		$index = $this->bc1_index($data, $pos+4);

		$rgb = [];
		$rgb[0] = $this->rgb565($int1);
		$rgb[1] = $this->rgb565($int2);

		if ( $bc != 1 || $int1 > $int2 )
		{
			$rgb[2] = $this->rgb_interpolate( $rgb[0], 2/3, $rgb[1], 1/3 );
			$rgb[3] = $this->rgb_interpolate( $rgb[0], 1/3, $rgb[1], 2/3 );
		}
		else
		{
			$rgb[2] = $this->rgb_interpolate( $rgb[0], 1/2, $rgb[1], 1/2 );
			$rgb[3] = PIX_ALPHA;
		}

		$pix = [];
		foreach ( $index as $v )
			$pix[] = $rgb[$v];
		return $pix;
	}

	private function bc2_alpha( string &$data, int $pos ) : array
	{
		// 4*4  4 bit alpha
		$alp = [];
		for ( $i=0; $i < 8; $i++ )
		{
			$b = ord( $data[$pos] );
				$pos++;
			$b1 = ($b >> 0) & BIT4;
			$b2 = ($b >> 4) & BIT4;

			$alp[] = $b1 * 0x11;
			$alp[] = $b2 * 0x11;
		}
		return $alp;
	}

	private function bc3_alpha( string &$data, int $pos ) : array
	{
		$a = [];
		$a[0] = ord( $data[$pos+0] );
		$a[1] = ord( $data[$pos+1] );
		$index = $this->bc3_index($data, $pos+2);

		if ( $a[0] > $a[1] )
		{
			$a[2] = ($a[0] * 6/7) + ($a[1] * 1/7);
			$a[3] = ($a[0] * 5/7) + ($a[1] * 2/7);
			$a[4] = ($a[0] * 4/7) + ($a[1] * 3/7);
			$a[5] = ($a[0] * 3/7) + ($a[1] * 4/7);
			$a[6] = ($a[0] * 2/7) + ($a[1] * 5/7);
			$a[7] = ($a[0] * 1/7) + ($a[1] * 6/7);
		}
		else
		{
			$a[2] = ($a[0] * 4/5) + ($a[1] * 1/5);
			$a[3] = ($a[0] * 3/5) + ($a[1] * 2/5);
			$a[4] = ($a[0] * 2/5) + ($a[1] * 3/5);
			$a[5] = ($a[0] * 1/5) + ($a[1] * 4/5);
			$a[6] = 0;
			$a[7] = BIT8;
		}

		$pix = [];
		foreach ( $index as $v )
			$pix[] = $a[$v];
		return $pix;
	}
	//////////////////////////////
	public function bc1( string &$data ) : string // RGB565 + A 0-1
	{
		$pix = '';
		$len = strlen($data) & ~7;
		for ( $i=0; $i < $len; $i += 8 )
		{
			$rgb = $this->bc1_color($data, $i+0, 1);
			$alp = [];
			foreach ( $rgb as $v )
				$alp[] = $v[3];
			$pix .= $this->rgba_mix($rgb, $alp);
		} // for ( $i=0; $i < $len; $i += 8 )
		return $pix;
	}

	public function bc2( string &$data ) : string // RGB565 + A4
	{
		$pix = '';
		$len = strlen($data) & ~15;
		for ( $i=0; $i < $len; $i += 16 )
		{
			$alp = $this->bc2_alpha($data, $i+0);
			$rgb = $this->bc1_color($data, $i+8, 2);
			$pix .= $this->rgba_mix($rgb, $alp);
		} // for ( $i=0; $i < $len; $i += 16 )
		return $pix;
	}

	public function bc3( string &$data ) : string // RGB565 + A8
	{
		$pix = '';
		$len = strlen($data) & ~15;
		for ( $i=0; $i < $len; $i += 16 )
		{
			$alp = $this->bc3_alpha($data, $i+0);
			$rgb = $this->bc1_color($data, $i+8, 3);
			$pix .= $this->rgba_mix($rgb, $alp);
		} // for ( $i=0; $i < $len; $i += 16 )
		return $pix;
	}

	public function bc4( string &$data ) : string // A8
	{
		$pix = '';
		$len = strlen($data) & ~7;
		for ( $i=0; $i < $len; $i += 8 )
		{
			$alp = $this->bc3_alpha($data, $i+0);
			for ( $j=0; $j < 16; $j++ )
			{
				$a = self::clamp($alp[$j]);
				$pix .= chr($a);
			}
		} // for ( $i=0; $i < $len; $i += 16 )
		return $pix;
	}

	public function dxt1( string &$data ) : string  { return $this->bc1($data); }
	public function dxt2( string &$data ) : string  { return $this->bc2($data); }
	public function dxt3( string &$data ) : string  { return $this->bc2($data); }
	public function dxt4( string &$data ) : string  { return $this->bc3($data); }
	public function dxt5( string &$data ) : string  { return $this->bc3($data); }
	//////////////////////////////
	private function draw( string &$data, int $bw, int $bh, int $bpp, array &$map=[] ) : string
	{
		tool::trace(__FUNCTION__, $bw, $bh);
		$size = $bw * $bh * 0x10 * $bpp;
		if ( strlen($data) < $size )
			tool::error('data len not enough', strlen($data), $size);

		$size = $bw * $bh;
		if ( empty($map) || count($map) < $size  )
		{
			tool::notice('map reset', count($map), $size);
			$map = [];
			for ( $p=0; $p < $size; $p++ )
				$map[$p] = $p;
		}

		// S3TC blocks are always 4x4 pixels
		$canvas = '';
		$row_size = $bpp * 4; // bpp * 4 column
		$bc_size  = 4 * $row_size; // 4 row
		$p = 0;
		for ( $y=0; $y < $bh; $y++ )
		{
			$row = ['','','',''];
			for ( $x=0; $x < $bw; $x++ )
			{
				$id = $map[$p];
					$p++;
				$sub = tool::substr($data, $id*$bc_size, $bc_size);

				$row[0] .= substr($sub, 0*$row_size, $row_size);
				$row[1] .= substr($sub, 1*$row_size, $row_size);
				$row[2] .= substr($sub, 2*$row_size, $row_size);
				$row[3] .= substr($sub, 3*$row_size, $row_size);
			} // for ( $x=0; $x < $bw; $x++ )
			$canvas .= $row[0] . $row[1] . $row[2] . $row[3];
		} // for ( $y=0; $y < $bh; $y++ )
		return $canvas;
	}

	public function s3tc_draw( string &$data, int $bw, int $bh, array &$map=[] ) : string
	{
		tool::trace(__FUNCTION__, $bw, $bh);
		// 4x4 * RGBA = 10 * 4 = 40
		return $this->draw($data, $bw, $bh, 4, $map);
	}

	public function bc4_draw( string &$data, int $bw, int $bh, array &$map=[] ) : string
	{
		tool::trace(__FUNCTION__, $bw, $bh);
		// 4x4 * C = 10 * 1 = 10
		return $this->draw($data, $bw, $bh, 1, $map);
	}
} // class s3tc_texture
