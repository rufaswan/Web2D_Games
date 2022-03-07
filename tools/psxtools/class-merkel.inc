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
// https://datatracker.ietf.org/doc/html/rfc1321
// https://datatracker.ietf.org/doc/html/rfc3174
// https://datatracker.ietf.org/doc/html/rfc6234
class Merkel_Damgard
{
	private $chunk;
	private $K; // constant
	private $h; // hash
	private $mask;

	// BUG : 32-bit PHP.exe
	//   right shift will copy the negative bit
	// BUG : ROTR ^ 1
	//   mask = (1 << 63) - 1 = 80 00 00 00 00 00 00 00 (expect 7f ff ff ff ff ff ff ff)
	//   -0      to -0x400  = 80 00 00 00 00 00 00 00
	//   -0x401  to -0xbff  = 7f ff ff ff ff ff f8 00
	//   -0xc00  to -0x1400 = 7f ff ff ff ff ff f0 00
	//   -0x1401 to         = 7f ff ff ff ff ff e8 00
	//   ...
	private function MASK($n)
	{
		$m = 0;
		while ( $n > 0 )
		{
			$m <<= 1;
			$m  |= 1;
			$n--;
		}
		return $m;
	}
	private function LEFT_SHIFT($a,$n,$b)
	{
		$mask = $this->MASK($b);
		return ($a << $n) & $mask;
	}
	private function RIGHT_SHIFT($a,$n,$b)
	{
		$mask = $this->MASK($b-$n);
		return ($a >> $n) & $mask;
	}
	private function  LEFT_ROTATE($a,$n,$b) { return ($this->LEFT_SHIFT ($a,$n,$b) | $this->RIGHT_SHIFT($a,$b-$n,$b)); }
	private function RIGHT_ROTATE($a,$n,$b) { return ($this->RIGHT_SHIFT($a,$n,$b) | $this->LEFT_SHIFT ($a,$b-$n,$b)); }

	private function ADDU()
	{
		$arg = func_get_args();
		$int = 0;
		foreach ( $arg as $a )
		{
			$a  &= $this->mask;
			$int = ($int + $a) & $this->mask;
		}
		return $int;
	}

	private function ORD( &$str, $pos, $byte, $big )
	{
		$s = substr($str, $pos, $byte);
		if ( ! $big )
			$s = strrev($s);

		$int = 0;
		for ( $i=0; $i < $byte; $i++ )
		{
			$b = ord( $s[$i] );
			$int = ($int << 8) | $b;
		}
		return $int;
	}

	private function CHR( $int, $byte, $big )
	{
		$str = '';
		for ( $i=0; $i < $byte; $i++ )
		{
			$b = $int & BIT8;
			$str .= chr($b);
			$int >>= 8;
		}
		if ( $big )
			$str = strrev($str);
		return $str;
	}

	private function PADLEN_64( &$str, $big )
	{
		// len   =  64-bit/ 8-byte
		// chunk = 512-bit/64-byte
		$len = strlen($str) * 8; // in bits
		if ( $len < 0 )
			return php_error('PADLEN_64 < 0');
		$pad = $this->CHR($len, 8, $big);

		// pad str to chunk size
		$str .= "\x80";
		while ( (strlen($str)+8) & 0x3f ) // mod 64
			$str .= ZERO;

		$str .= $pad;
		return;
	}

	private function PADLEN_128( &$str, $big )
	{
		// len   =  128-bit/ 16-byte
		// chunk = 1024-bit/128-byte
		$len = strlen($str) * 8; // in bits
		if ( $len < 0 )
			return php_error('PADLEN_128 < 0');
		$pad = $this->CHR($len, 16, $big);

		// pad str to chunk size
		$str .= "\x80";
		while ( (strlen($str)+16) & 0x7f ) // mod 128
			$str .= ZERO;

		$str .= $pad;
		return;
	}
	//////////////////////////////
	private function md5_calc( &$str )
	{
		$this->mask = $this->MASK(32);
		$this->PADLEN_64($str, false);
		//echo debug($str);

		// K[64]
		// Use binary integer part of the sines of integers (Radians) as constants
		// K[i] := floor( 2^32 * abs( sin(i+1) ) )
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

		// s specifies the per-round shift amounts
		$sh = array(
			7,12,17,22,  7,12,17,22,  7,12,17,22,  7,12,17,22,
			5, 9,14,20,  5, 9,14,20,  5, 9,14,20,  5, 9,14,20,
			4,11,16,23,  4,11,16,23,  4,11,16,23,  4,11,16,23,
			6,10,15,21,  6,10,15,21,  6,10,15,21,  6,10,15,21,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			// break into 512-bit/64-byte/16-int chunks
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$this->chunk[] = $this->ORD($str, $i+$j, 4, false);

			// main compress loop
			list($A,$B,$C,$D) = $this->h;
			for ( $j=0; $j < 64; $j++ )
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

				$F  = $this->ADDU($F, $A, $this->K[$j], $this->chunk[$G]);
				$b1 = $this->LEFT_ROTATE($F, $sh[$j], 32);

				$A = $D;
				$D = $C;
				$C = $B;
				$B = $this->ADDU($B, $b1);
			} // for ( $j=0; $j < 64; $j++ )

			$this->h[0] = $this->ADDU($this->h[0], $A);
			$this->h[1] = $this->ADDU($this->h[1], $B);
			$this->h[2] = $this->ADDU($this->h[2], $C);
			$this->h[3] = $this->ADDU($this->h[3], $D);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}
	function md5( $str )
	{
		// h[4]
		$this->h = array(
			0x67452301,
			0xefcdab89,
			0x98badcfe,
			0x10325476,
		);

		$this->md5_calc($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 4 , false );
		$h .= $this->CHR( $this->h[1] , 4 , false );
		$h .= $this->CHR( $this->h[2] , 4 , false );
		$h .= $this->CHR( $this->h[3] , 4 , false );
		return $h;
	}
	//////////////////////////////
	private function sha1_calc( &$str )
	{
		$this->mask = $this->MASK(32);
		$this->PADLEN_64($str, true);
		//echo debug($str);

		// K[4]
		$this->K = array(
			0x5a827999,
			0x6ed9eba1,
			0x8f1bbcdc,
			0xca62c1d6,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			// break into 512-bit/64-byte/16-int chunks
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$this->chunk[] = $this->ORD($str, $i+$j, 4, true);

			// Message schedule: extend the sixteen 32-bit words into eighty 32-bit words
			for ( $j = 16; $j < 80; $j++ )
			{
				$b  = $this->chunk[$j- 3];
				$b ^= $this->chunk[$j- 8];
				$b ^= $this->chunk[$j-14];
				$b ^= $this->chunk[$j-16];
				$this->chunk[$j] = $this->LEFT_ROTATE($b, 1, 32);
			} // for ( $j = 16; $j < 80; $j++ )

			// main compress loop
			list($A,$B,$C,$D,$E) = $this->h;
			for ( $j=0; $j < 80; $j++ )
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

				$b1 = $this->LEFT_ROTATE($A, 5, 32);
				$b1 = $this->ADDU($b1, $F, $E, $this->K[$ah], $this->chunk[$j]);

				$E = $D;
				$D = $C;
				$C = $this->LEFT_ROTATE($B, 30, 32);
				$B = $A;
				$A = $b1;
			} // for ( $j=0; $j < 80; $j++ )

			$this->h[0] = $this->ADDU($this->h[0], $A);
			$this->h[1] = $this->ADDU($this->h[1], $B);
			$this->h[2] = $this->ADDU($this->h[2], $C);
			$this->h[3] = $this->ADDU($this->h[3], $D);
			$this->h[4] = $this->ADDU($this->h[4], $E);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}
	function sha1( $str )
	{
		// h[5]
		$this->h = array(
			0x67452301,
			0xefcdab89,
			0x98badcfe,
			0x10325476,
			0xc3d2e1f0,
		);

		$this->sha1_calc($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 4 , true );
		$h .= $this->CHR( $this->h[1] , 4 , true );
		$h .= $this->CHR( $this->h[2] , 4 , true );
		$h .= $this->CHR( $this->h[3] , 4 , true );
		$h .= $this->CHR( $this->h[4] , 4 , true );
		return $h;
	}
	//////////////////////////////
	private function sha2_calc32( &$str )
	{
		$this->mask = $this->MASK(32);
		$this->PADLEN_64($str, true);
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

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			// break into 512-bit/64-byte/16-int chunks
			$this->chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$this->chunk[] = $this->ORD($str, $i+$j, 4, true);

			// Extend the first 16 words into the remaining 48 words w[16..63] of the message schedule array
			for ( $j = 16; $j < 64; $j++ )
			{
				// Wt = SSIG1(W(t-2)) + W(t-7) + SSIG0(w(t-15)) + W(t-16)
				// SSIG1(x) = ROTR^17(x) XOR ROTR^19(x) XOR SHR^10(x)
				// SSIG0(x) = ROTR^ 7(x) XOR ROTR^18(x) XOR SHR^ 3(x)
				$s1  = $this->RIGHT_ROTATE($this->chunk[$j- 2], 17, 32);
				$s1 ^= $this->RIGHT_ROTATE($this->chunk[$j- 2], 19, 32);
				$s1 ^= $this->RIGHT_SHIFT ($this->chunk[$j- 2], 10, 32);

				$s0  = $this->RIGHT_ROTATE($this->chunk[$j-15],  7, 32);
				$s0 ^= $this->RIGHT_ROTATE($this->chunk[$j-15], 18, 32);
				$s0 ^= $this->RIGHT_SHIFT ($this->chunk[$j-15],  3, 32);

				$this->chunk[$j] = $this->ADDU($s1, $this->chunk[$j-7], $s0, $this->chunk[$j-16]);
			} // for ( $j = 16; $j < 64; $j++ )

			// main compress loop
			list($A,$B,$C,$D,$E,$F,$G,$H) = $this->h;
			for ( $j=0; $j < 64; $j++ )
			{
				// T1 = h + BSIG1(e) + CH(e,f,g) + Kt + Wt
				// BSIG1(x) = ROTR^6(x) XOR ROTR^11(x) XOR ROTR^25(x)
				// CH( x, y, z) = (x AND y) XOR ( (NOT x) AND z)

				// T2 = BSIG0(a) + MAJ(a,b,c)
				// BSIG0(x) = ROTR^2(x) XOR ROTR^13(x) XOR ROTR^22(x)
				// MAJ( x, y, z) = (x AND y) XOR (x AND z) XOR (y AND z)
				$s1  = $this->RIGHT_ROTATE($E,  6, 32);
				$s1 ^= $this->RIGHT_ROTATE($E, 11, 32);
				$s1 ^= $this->RIGHT_ROTATE($E, 25, 32);

				$ch  = ($E & $F) ^ (~$E & $G);
				$b1  = $this->ADDU($H, $s1, $ch, $this->K[$j], $this->chunk[$j]);

				$s0  = $this->RIGHT_ROTATE($A,  2, 32);
				$s0 ^= $this->RIGHT_ROTATE($A, 13, 32);
				$s0 ^= $this->RIGHT_ROTATE($A, 22, 32);

				$maj = ($A & $B) ^ ($A & $C) ^ ($B & $C);
				$b2  = $this->ADDU($s0, $maj);

				$H = $G;
				$G = $F;
				$F = $E;
				$E = $this->ADDU($D, $b1);
				$D = $C;
				$C = $B;
				$B = $A;
				$A = $this->ADDU($b1, $b2);
			} // for ( $j=0; $j < 64; $j++ )

			$this->h[0] = $this->ADDU($this->h[0], $A);
			$this->h[1] = $this->ADDU($this->h[1], $B);
			$this->h[2] = $this->ADDU($this->h[2], $C);
			$this->h[3] = $this->ADDU($this->h[3], $D);
			$this->h[4] = $this->ADDU($this->h[4], $E);
			$this->h[5] = $this->ADDU($this->h[5], $F);
			$this->h[6] = $this->ADDU($this->h[6], $G);
			$this->h[7] = $this->ADDU($this->h[7], $H);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}

	function sha256( $str )
	{
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

		$this->sha2_calc32($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 4 , true );
		$h .= $this->CHR( $this->h[1] , 4 , true );
		$h .= $this->CHR( $this->h[2] , 4 , true );
		$h .= $this->CHR( $this->h[3] , 4 , true );
		$h .= $this->CHR( $this->h[4] , 4 , true );
		$h .= $this->CHR( $this->h[5] , 4 , true );
		$h .= $this->CHR( $this->h[6] , 4 , true );
		$h .= $this->CHR( $this->h[7] , 4 , true );
		return $h;
	}

	function sha224( $str )
	{
		// h[8]
		// second 32 bits of the fractional parts of the square roots of the 9th through 16th primes 23..53
		$this->h = array(
			0xc1059ed8,
			0x367cd507,
			0x3070dd17,
			0xf70e5939,
			0xffc00b31,
			0x68581511,
			0x64f98fa7,
			0xbefa4fa4,
		);

		$this->sha2_calc32($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 4 , true );
		$h .= $this->CHR( $this->h[1] , 4 , true );
		$h .= $this->CHR( $this->h[2] , 4 , true );
		$h .= $this->CHR( $this->h[3] , 4 , true );
		$h .= $this->CHR( $this->h[4] , 4 , true );
		$h .= $this->CHR( $this->h[5] , 4 , true );
		$h .= $this->CHR( $this->h[6] , 4 , true );
		return $h;
	}
	//////////////////////////////
	private function sha2_calc64( &$str )
	{
		php_req_bytes(8);
		$this->mask = $this->MASK(64);
		$this->PADLEN_128($str, true);
		//echo debug($str);

		// K[80]
		// first 64 bits of the fractional parts of the cube roots of the first 80 primes 2..409
		$this->K = array(
			0x428a2f98d728ae22, 0x7137449123ef65cd, 0xb5c0fbcfec4d3b2f, 0xe9b5dba58189dbbc,
			0x3956c25bf348b538, 0x59f111f1b605d019, 0x923f82a4af194f9b, 0xab1c5ed5da6d8118,
			0xd807aa98a3030242, 0x12835b0145706fbe, 0x243185be4ee4b28c, 0x550c7dc3d5ffb4e2,
			0x72be5d74f27b896f, 0x80deb1fe3b1696b1, 0x9bdc06a725c71235, 0xc19bf174cf692694,
			0xe49b69c19ef14ad2, 0xefbe4786384f25e3, 0x0fc19dc68b8cd5b5, 0x240ca1cc77ac9c65,
			0x2de92c6f592b0275, 0x4a7484aa6ea6e483, 0x5cb0a9dcbd41fbd4, 0x76f988da831153b5,
			0x983e5152ee66dfab, 0xa831c66d2db43210, 0xb00327c898fb213f, 0xbf597fc7beef0ee4,
			0xc6e00bf33da88fc2, 0xd5a79147930aa725, 0x06ca6351e003826f, 0x142929670a0e6e70,
			0x27b70a8546d22ffc, 0x2e1b21385c26c926, 0x4d2c6dfc5ac42aed, 0x53380d139d95b3df,
			0x650a73548baf63de, 0x766a0abb3c77b2a8, 0x81c2c92e47edaee6, 0x92722c851482353b,
			0xa2bfe8a14cf10364, 0xa81a664bbc423001, 0xc24b8b70d0f89791, 0xc76c51a30654be30,
			0xd192e819d6ef5218, 0xd69906245565a910, 0xf40e35855771202a, 0x106aa07032bbd1b8,
			0x19a4c116b8d2d0c8, 0x1e376c085141ab53, 0x2748774cdf8eeb99, 0x34b0bcb5e19b48a8,
			0x391c0cb3c5c95a63, 0x4ed8aa4ae3418acb, 0x5b9cca4f7763e373, 0x682e6ff3d6b2b8a3,
			0x748f82ee5defb2fc, 0x78a5636f43172f60, 0x84c87814a1f0ab72, 0x8cc702081a6439ec,
			0x90befffa23631e28, 0xa4506cebde82bde9, 0xbef9a3f7b2c67915, 0xc67178f2e372532b,
			0xca273eceea26619c, 0xd186b8c721c0c207, 0xeada7dd6cde0eb1e, 0xf57d4f7fee6ed178,
			0x06f067aa72176fba, 0x0a637dc5a2c898a6, 0x113f9804bef90dae, 0x1b710b35131c471b,
			0x28db77f523047d84, 0x32caab7b40c72493, 0x3c9ebe0a15c9bebc, 0x431d67c49c100d4c,
			0x4cc5d4becb3e42b6, 0x597f299cfc657e2a, 0x5fcb6fab3ad6faec, 0x6c44198c4a475817,
		);

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x80 )
		{
			// break into 1024-bit/128-byte/16-int chunks
			$this->chunk = array();
			for ( $j=0; $j < 0x80; $j += 8 )
				$this->chunk[] = $this->ORD($str, $i+$j, 8, true);

			// Extend the first 16 words into the remaining 64 words w[16..79] of the message schedule array
			for ( $j = 16; $j < 80; $j++ )
			{
				// Wt = SSIG1(W(t-2)) + W(t-7) + SSIG0(W(t-15)) + W(t-16)
				// SSIG1(x) = ROTR^19(x) XOR ROTR^61(x) XOR SHR^6(x)
				// SSIG0(x) = ROTR^ 1(x) XOR ROTR^ 8(x) XOR SHR^7(x)
				$s1  = $this->RIGHT_ROTATE($this->chunk[$j- 2], 19, 64);
				$s1 ^= $this->RIGHT_ROTATE($this->chunk[$j- 2], 61, 64);
				$s1 ^= $this->RIGHT_SHIFT ($this->chunk[$j- 2],  6, 64);

				$s0  = $this->RIGHT_ROTATE($this->chunk[$j-15],  1, 64);
				$s0 ^= $this->RIGHT_ROTATE($this->chunk[$j-15],  8, 64);
				$s0 ^= $this->RIGHT_SHIFT ($this->chunk[$j-15],  7, 64);

				$this->chunk[$j] = $this->ADDU($s1, $this->chunk[$j-7], $s0, $this->chunk[$j-16]);
			} // for ( $j = 16; $j < 80; $j++ )

			// main compress loop
			list($A,$B,$C,$D,$E,$F,$G,$H) = $this->h;
			for ( $j=0; $j < 80; $j++ )
			{
				// T1 = h + BSIG1(e) + CH(e,f,g) + Kt + Wt
				// BSIG1(x) = ROTR^14(x) XOR ROTR^18(x) XOR ROTR^41(x)
				// CH( x, y, z) = (x AND y) XOR ( (NOT x) AND z)
				$s1  = $this->RIGHT_ROTATE($E, 14, 64);
				$s1 ^= $this->RIGHT_ROTATE($E, 18, 64);
				$s1 ^= $this->RIGHT_ROTATE($E, 41, 64);

				$ch  = ($E & $F) ^ (~$E & $G);
				$b1  = $this->ADDU($H, $s1, $ch, $this->K[$j], $this->chunk[$j]);

				// T2 = BSIG0(a) + MAJ(a,b,c)
				// MAJ( x, y, z) = (x AND y) XOR (x AND z) XOR (y AND z)
				// BSIG0(x) = ROTR^28(x) XOR ROTR^34(x) XOR ROTR^39(x)
				$s0  = $this->RIGHT_ROTATE($A, 28, 64);
				$s0 ^= $this->RIGHT_ROTATE($A, 34, 64);
				$s0 ^= $this->RIGHT_ROTATE($A, 39, 64);

				$maj = ($A & $B) ^ ($A & $C) ^ ($B & $C);
				$b2  = $this->ADDU($s0, $maj);

				$H = $G;
				$G = $F;
				$F = $E;
				$E = $this->ADDU($D, $b1);
				$D = $C;
				$C = $B;
				$B = $A;
				$A = $this->ADDU($b1, $b2);
			} // for ( $j=0; $j < 80; $j++ )

			$this->h[0] = $this->ADDU($this->h[0], $A);
			$this->h[1] = $this->ADDU($this->h[1], $B);
			$this->h[2] = $this->ADDU($this->h[2], $C);
			$this->h[3] = $this->ADDU($this->h[3], $D);
			$this->h[4] = $this->ADDU($this->h[4], $E);
			$this->h[5] = $this->ADDU($this->h[5], $F);
			$this->h[6] = $this->ADDU($this->h[6], $G);
			$this->h[7] = $this->ADDU($this->h[7], $H);
		} // for ( $i=0; $i < $len; $i += 0x80 )
		return;
	}

	function sha512( $str )
	{
		// h[8]
		// first 64 bits of the fractional parts of the square roots of the first 8 primes 2..19
		$this->h = array(
			0x6a09e667f3bcc908,
			0xbb67ae8584caa73b,
			0x3c6ef372fe94f82b,
			0xa54ff53a5f1d36f1,
			0x510e527fade682d1,
			0x9b05688c2b3e6c1f,
			0x1f83d9abfb41bd6b,
			0x5be0cd19137e2179,
		);

		$this->sha2_calc64($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 8 , true );
		$h .= $this->CHR( $this->h[1] , 8 , true );
		$h .= $this->CHR( $this->h[2] , 8 , true );
		$h .= $this->CHR( $this->h[3] , 8 , true );
		$h .= $this->CHR( $this->h[4] , 8 , true );
		$h .= $this->CHR( $this->h[5] , 8 , true );
		$h .= $this->CHR( $this->h[6] , 8 , true );
		$h .= $this->CHR( $this->h[7] , 8 , true );
		return $h;
	}

	function sha384( $str )
	{
		// h[8]
		// second 64 bits of the fractional parts of the square roots of the 9th through 16th primes 23..53
		$this->h = array(
			0xcbbb9d5dc1059ed8,
			0x629a292a367cd507,
			0x9159015a3070dd17,
			0x152fecd8f70e5939,
			0x67332667ffc00b31,
			0x8eb44a8768581511,
			0xdb0c2e0d64f98fa7,
			0x47b5481dbefa4fa4,
		);

		$this->sha2_calc64($str);
		$h  = '';
		$h .= $this->CHR( $this->h[0] , 8 , true );
		$h .= $this->CHR( $this->h[1] , 8 , true );
		$h .= $this->CHR( $this->h[2] , 8 , true );
		$h .= $this->CHR( $this->h[3] , 8 , true );
		$h .= $this->CHR( $this->h[4] , 8 , true );
		$h .= $this->CHR( $this->h[5] , 8 , true );
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

	function  md5  ( $key, $msg ) { return $this->HMACX( "md5"  , $key,  64, $msg); }
	function sha1  ( $key, $msg ) { return $this->HMACX("sha1"  , $key,  64, $msg); }
	function sha256( $key, $msg ) { return $this->HMACX("sha256", $key,  64, $msg); }
	function sha224( $key, $msg ) { return $this->HMACX("sha224", $key,  64, $msg); }
	function sha512( $key, $msg ) { return $this->HMACX("sha512", $key, 128, $msg); }
	function sha384( $key, $msg ) { return $this->HMACX("sha384", $key, 128, $msg); }
} // class HMAC
