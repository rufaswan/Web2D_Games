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

// Pseudocode                          strlen  uchar  hash
// https://en.wikipedia.org/wiki/MD5       32     16  uint32_le[4]
// https://en.wikipedia.org/wiki/SHA-1     40     20  uint32_be[5]
// https://en.wikipedia.org/wiki/SHA-2
//                         sha-256         64     32  uint32_be[8]
//                         sha-224         56     28  uint32_be[7+1]
//                         sha-512        128     64  uint64_be[8]
//                         sha-384         96     48  uint64_be[6+2]
//
// https://en.wikipedia.org/wiki/Merkle%E2%80%93Damg%C3%A5rd_construction
class Merkel_Damgard
{
	private $chunk;
	private $K; // constant
	private $h; // hash

	// FIX for 32-bit PHP.exe
	//   right shift will copy the negative bit , generate incorrect hash
	private function  LEFT_SHIFT ($a,$n,$b) { $mask = (1 <<  $b    ) - 1; return ($a << $n) & $mask; }
	private function RIGHT_SHIFT ($a,$n,$b) { $mask = (1 << ($b-$n)) - 1; return ($a >> $n) & $mask; }
	private function  LEFT_ROTATE($a,$n,$b) { return ($this->LEFT_SHIFT ($a,$n,$b) | $this->RIGHT_SHIFT($a,$b-$n,$b)); }
	private function RIGHT_ROTATE($a,$n,$b) { return ($this->RIGHT_SHIFT($a,$n,$b) | $this->LEFT_SHIFT ($a,$b-$n,$b)); }

	private function PADSTR32( &$s, $n, $p )
	{
		if    ( ((strlen($s)+8) % $n) !== 0 )  $s .= "\x80";
		while ( ((strlen($s)+8) % $n) !== 0 )  $s .= ZERO;
		$s .= $p;
		return;
	}
	//////////////////////////////
	function md5sum( $str )
	{
		$len = chrint(strlen($str)*8 , 4);
		$this->PADSTR32($str, 0x40, $len.ZERO.ZERO.ZERO.ZERO);
		//echo debug($str);

		// K[64]
		// Use binary integer part of the sines of integers (Radians) as constants
		$this->K = array(
			0xd76aa478, 0xe8c7b756, 0x242070db, 0xc1bdceee, 0xf57c0faf, 0x4787c62a, 0xa8304613, 0xfd469501,
			0x698098d8, 0x8b44f7af, 0xffff5bb1, 0x895cd7be, 0x6b901122, 0xfd987193, 0xa679438e, 0x49b40821,
			0xf61e2562, 0xc040b340, 0x265e5a51, 0xe9b6c7aa, 0xd62f105d, 0x02441453, 0xd8a1e681, 0xe7d3fbc8,
			0x21e1cde6, 0xc33707d6, 0xf4d50d87, 0x455a14ed, 0xa9e3e905, 0xfcefa3f8, 0x676f02d9, 0x8d2a4c8a,
			0xfffa3942, 0x8771f681, 0x6d9d6122, 0xfde5380c, 0xa4beea44, 0x4bdecfa9, 0xf6bb4b60, 0xbebfbc70,
			0x289b7ec6, 0xeaa127fa, 0xd4ef3085, 0x04881d05, 0xd9d4d039, 0xe6db99e5, 0x1fa27cf8, 0xc4ac5665,
			0xf4292244, 0x432aff97, 0xab9423a7, 0xfc93a039, 0x655b59c3, 0x8f0ccc92, 0xffeff47d, 0x85845dd1,
			0x6fa87e4f, 0xfe2ce6e0, 0xa3014314, 0x4e0811a1, 0xf7537e82, 0xbd3af235, 0x2ad7d2bb, 0xeb86d391,
		);

		// h[4]
		$this->h = array(
			0x67452301,
			0xefcdab89,
			0x98badcfe,
			0x10325476,
		);

		$sh = array(
			7,12,17,22,  7,12,17,22,  7,12,17,22,  7,12,17,22,
			5, 9,14,20,  5, 9,14,20,  5, 9,14,20,  5, 9,14,20,
			4,11,16,23,  4,11,16,23,  4,11,16,23,  4,11,16,23,
			6,10,15,21,  6,10,15,21,  6,10,15,21,  6,10,15,21,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
			{
				$b = substr($str, $i+$j, 4);
				$b = ordint($b);
				$this->chunk[] = $b;
			} // for ( $j=0; $j < 0x40; $j += 4 )

			list($A,$B,$C,$D) = $this->h;
			for ( $j=0; $j < 0x40; $j++ )
			{
				switch ( $j >> 4 )
				{
					case 0: // 0-15
						$F = ($B & $C) | (~$B & $D);
						$G = $j & BIT4;
						break;
					case 1: // 16-31
						$F = ($D & $B) | (~$D & $C);
						$G = ($j * 5 + 1) & BIT4;
						break;
					case 2: // 32-47
						$F = ($B ^ $C ^ $D);
						$G = ($j * 3 + 5) & BIT4;
						break;
					case 3: // 48-63
						$F = $C ^ ($B | ~$D);
						$G = ($j * 7) & BIT4;
						break;
				} // switch ( $j >> 4 )

				$F &= BIT32;
				$F = $F + $A + $this->K[$j] + $this->chunk[$G];

				$A = $D;
				$D = $C;
				$C = $B;
				$B = $B + $this->LEFT_ROTATE($F, $sh[$j], 32);
			} // for ( $j=0; $j < 0x40; $j++ )

			$this->h[0] = ($this->h[0] + $A) & BIT32;
			$this->h[1] = ($this->h[1] + $B) & BIT32;
			$this->h[2] = ($this->h[2] + $C) & BIT32;
			$this->h[3] = ($this->h[3] + $D) & BIT32;
		} // for ( $i=0; $i < $len; $i += 0x40 )

		$h  = '';
		$h .= chrint( $this->h[0] , 4 );
		$h .= chrint( $this->h[1] , 4 );
		$h .= chrint( $this->h[2] , 4 );
		$h .= chrint( $this->h[3] , 4 );
		return $h;
	}
	//////////////////////////////
	function sha1sum( $str )
	{
		$len = chrbig(strlen($str)*8 , 4);
		$this->PADSTR32($str, 0x40, ZERO.ZERO.ZERO.ZERO.$len);
		//echo debug($str);

		// K[4]
		$this->K = array(
			0x5a827999,
			0x6ed9eba1,
			0x8f1bbcdc,
			0xca62c1d6,
		);

		// h[5]
		$this->h = array(
			0x67452301,
			0xefcdab89,
			0x98badcfe,
			0x10325476,
			0xc3d2e1f0,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
			{
				$b = substrrev($str, $i+$j, 4);
				$b = ordint($b);
				$this->chunk[] = $b;
			} // for ( $j=0; $j < 0x40; $j += 4 )

			for ( $j=0x10; $j < 0x50; $j++ )
			{
				$b = $this->chunk[$j-3] ^ $this->chunk[$j-8] ^ $this->chunk[$j-14] ^ $this->chunk[$j-16];
				$b = $this->LEFT_ROTATE($b, 1, 32);
				$this->chunk[$j] = $b;
			} // for ( $j=0x10; $j < 0x50; $j++ )

			list($A,$B,$C,$D,$E) = $this->h;
			for ( $j=0; $j < 0x50; $j++ )
			{
				$ah = (int)($j / 20);
				switch ( $ah )
				{
					case 0:
						$F = ($B & $C) | (~$B & $D);
						break;
					case 1:
					case 3:
						$F = ($B ^ $C ^ $D);
						break;
					case 2:
						$F = ($B & $C) | ($B & $D) | ($C & $D);
						break;
				} // switch ( $ah )

				$F &= BIT32;
				$b = $this->LEFT_ROTATE($A, 5, 32) + $F + $E + $this->K[$ah] + $this->chunk[$j];

				$E = $D;
				$D = $C;
				$C = $this->LEFT_ROTATE($B, 30, 32);
				$B = $A;
				$A = $b;
			} // for ( $j=0; $j < 0x50; $j++ )

			$this->h[0] = ($this->h[0] + $A) & BIT32;
			$this->h[1] = ($this->h[1] + $B) & BIT32;
			$this->h[2] = ($this->h[2] + $C) & BIT32;
			$this->h[3] = ($this->h[3] + $D) & BIT32;
			$this->h[4] = ($this->h[4] + $E) & BIT32;
		} // for ( $i=0; $i < $len; $i += 0x40 )

		$h  = '';
		$h .= chrbig( $this->h[0] , 4 );
		$h .= chrbig( $this->h[1] , 4 );
		$h .= chrbig( $this->h[2] , 4 );
		$h .= chrbig( $this->h[3] , 4 );
		$h .= chrbig( $this->h[4] , 4 );
		return $h;
	}
	//////////////////////////////
	function sha256sum( $str )
	{
		$len = chrbig(strlen($str)*8 , 4);
		$this->PADSTR32($str, 0x40, ZERO.ZERO.ZERO.ZERO.$len);
		//echo debug($str);

		// K[64]
		// first 32 bits of the fractional parts of the cube roots of the first 64 primes 2..311
		$this->K = array(
			0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
			0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
			0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
			0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
			0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
			0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
			0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
			0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2,
		);

		// h[8]
		// first 32 bits of the fractional parts of the square roots of the first 8 primes 2..19
		$this->h = array(
			0x6a09e667,
			0xbb67ae85,
			0x3c6ef372,
			0xa54ff53a,
			0x510e527f,
			0x9b05688c,
			0x1f83d9ab,
			0x5be0cd19,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
			{
				$b = substrrev($str, $i+$j, 4);
				$b = ordint($b);
				$this->chunk[] = $b;
			} // for ( $j=0; $j < 0x40; $j += 4 )

			for ( $j=0x10; $j < 0x40; $j++ )
			{
				$s0  = $this->RIGHT_ROTATE( $this->chunk[$j-15],  7, 32);
				$s0 ^= $this->RIGHT_ROTATE( $this->chunk[$j-15], 18, 32);
				$s0 ^= $this->RIGHT_SHIFT ( $this->chunk[$j-15],  3, 32);

				$s1  = $this->RIGHT_ROTATE( $this->chunk[$j- 2], 17, 32);
				$s1 ^= $this->RIGHT_ROTATE( $this->chunk[$j- 2], 19, 32);
				$s1 ^= $this->RIGHT_SHIFT ( $this->chunk[$j- 2], 10, 32);

				$b  = $this->chunk[$j-16] + $s0;
				$b += $this->chunk[$j- 7] + $s1;
					$b &= BIT32;

				$this->chunk[$j] = $b;
			} // for ( $j=0x10; $j < 0x40; $j++ )

			list($A,$B,$C,$D,$E,$F,$G,$H) = $this->h;
			for ( $j=0; $j < 0x40; $j++ )
			{
				$s1  = $this->RIGHT_ROTATE($E,  6, 32);
				$s1 ^= $this->RIGHT_ROTATE($E, 11, 32);
				$s1 ^= $this->RIGHT_ROTATE($E, 25, 32);

				$ch  = ($E & $F) ^ (~$E & $G);
					$ch &= BIT32;
				$b1  = $H + $s1 + $ch + $this->K[$j] + $this->chunk[$j];

				$s0  = $this->RIGHT_ROTATE($A,  2, 32);
				$s0 ^= $this->RIGHT_ROTATE($A, 13, 32);
				$s0 ^= $this->RIGHT_ROTATE($A, 22, 32);

				$maj = ($A & $B) ^ ($A & $C) ^ ($B & $C);
					$maj &= BIT32;
				$b2  = $s0 + $maj;

				$H = $G;
				$G = $F;
				$F = $E;
				$E = ($D + $b1) & BIT32;
				$D = $C;
				$C = $B;
				$B = $A;
				$A = ($b1 + $b2) & BIT32;
			} // for ( $j=0; $j < 0x40; $j++ )

			$this->h[0] = ($this->h[0] + $A) & BIT32;
			$this->h[1] = ($this->h[1] + $B) & BIT32;
			$this->h[2] = ($this->h[2] + $C) & BIT32;
			$this->h[3] = ($this->h[3] + $D) & BIT32;
			$this->h[4] = ($this->h[4] + $E) & BIT32;
			$this->h[5] = ($this->h[5] + $F) & BIT32;
			$this->h[6] = ($this->h[6] + $G) & BIT32;
			$this->h[7] = ($this->h[7] + $H) & BIT32;
		} // for ( $i=0; $i < $len; $i += 0x40 )

		$h  = '';
		$h .= chrbig( $this->h[0] , 4 );
		$h .= chrbig( $this->h[1] , 4 );
		$h .= chrbig( $this->h[2] , 4 );
		$h .= chrbig( $this->h[3] , 4 );
		$h .= chrbig( $this->h[4] , 4 );
		$h .= chrbig( $this->h[5] , 4 );
		$h .= chrbig( $this->h[6] , 4 );
		$h .= chrbig( $this->h[7] , 4 );
		return $h;
	}
	//////////////////////////////
} // class Merkel_Damgard

class HMAC
{
	private function HMACX( $hash, $key, $max, $msg )
	{
		$mer = new Merkel_Damgard;
		if    ( strlen($key) > $max )  $key  = $mer->$hash($key);
		while ( strlen($key) < $max )  $key .= ZERO;

		$outer = $key;
		$inner = $key;
		for ( $i=0; $i < $max; $i++ )
		{
			$b  = ord( $outer[$i] );
			$b ^= 0x5c;
			$outer[$i] = chr($b);

			$b  = ord( $inner[$i] );
			$b ^= 0x36;
			$inner[$i] = chr($b);
		}

		$dg1 = $mer->$hash( $inner.$msg );
		$dg2 = $mer->$hash( $outer.$dg1 );
		return $dg2;
	}

	function  md5sum  ( $key, $msg ) { return $this->HMACX( "md5sum"  , $key, 64, $msg); }
	function sha1sum  ( $key, $msg ) { return $this->HMACX("sha1sum"  , $key, 64, $msg); }
	function sha256sum( $key, $msg ) { return $this->HMACX("sha256sum", $key, 64, $msg); }
} // class HMAC
