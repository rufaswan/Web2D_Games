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
class merkel_damgard
{
	private $m_len;

	private function c_rotate_left( $a, $bit )
	{
		$l = $this->c_shift_left ($a, $bit);
		$r = $this->c_shift_right($a, $this->m_len*8-$bit);
		return $this->c_or($l, $r);
	}

	private function c_rotate_right( $a, $bit )
	{
		$l = $this->c_shift_left ($a, $this->m_len*8-$bit);
		$r = $this->c_shift_right($a, $bit);
		return $this->c_or($l, $r);
	}

	private function c_shift_left( $a, $bit )
	{
		while ( $bit >= 8 )
		{
			$bit -= 8;
			array_unshift($a, 0);
		}
		foreach ( $a as $k => $v )
			$a[$k] <<= $bit;

		$this->c_norm($a);
		return $a;
	}

	private function c_shift_right( $a, $bit )
	{
		while ( $bit >= 8 )
		{
			$bit -= 8;
			array_shift($a);
		}
		$mask = (1 << $bit) - 1;
		foreach ( $a as $k => $v )
		{
			$carry = $v & $mask;
			$a[$k] >>= $bit;
			if ( $k > 0 )
				$a[$k-1] |= ($carry << (8-$bit));
		}

		$this->c_norm($a);
		return $a;
	}

	private function c_add()
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			for ( $i=0; $i < $this->m_len; $i++ )
				$a[$i] += $arg[$i];
		}
		$this->c_norm($a);
		return $a;
	}

	private function c_norm( &$array )
	{
		while ( count($array) < $this->m_len )
			$array[] = 0;

		$carry = 0;
		foreach ( $array as $ak => $av )
		{
			$array[$ak] += $carry;
			$carry = $array[$ak] >> 8;
			$array[$ak] &= BIT8;
		}
		if ( $carry !== 0 )
			$array[] = $carry;

		array_splice($array, $this->m_len);
		return;
	}

	private function c_not( $a )
	{
		foreach ( $a as $k => $v )
			$a[$k] = (~$v) & BIT8;
		return $a;
	}

	private function c_and( $a, $b )
	{
		foreach ( $a as $k => $v )
			$a[$k] &= $b[$k];
		return $a;
	}

	private function c_xor()
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			for ( $i=0; $i < $this->m_len; $i++ )
				$a[$i] ^= $arg[$i];
		}
		return $a;
	}

	private function c_or()
	{
		$all = func_get_args();
		$a   = array_shift($all);

		foreach ( $all as $arg )
		{
			for ( $i=0; $i < $this->m_len; $i++ )
				$a[$i] |= $arg[$i];
		}
		return $a;
	}
	//////////////////////////////
	private $m_big;

	private function ord_const( $const )
	{
		foreach ( $const as $k => $v )
		{
			$v = hex2bin($v);
			$v = strrev($v);

			$a = array();
			for ( $i=0; $i < $this->m_len; $i++ )
				$a[$i] = ord($v[$i]);
			$const[$k] = $a;
		}
		return $const;
	}

	private function ord_chunk( &$str, $pos )
	{
		$s = substr($str, $pos, $this->m_len);
		if ( $this->m_big )
			$s = strrev($s);

		$a = array();
		for ( $i=0; $i < $this->m_len; $i++ )
			$a[$i] = ord( $s[$i] );
		return $a;
	}

	private function chr_chunk( &$array )
	{
		$s = '';
		foreach ( $array as $v )
			$s .= chr($v);
		if ( $this->m_big )
			$s = strrev($s);
		return $s;
	}

	private function padchunk( &$str, $plen, $clen )
	{
		$len = strlen($str) * 8; // in bits
		$siz = '';
		for ( $i=0; $i < $plen; $i++ )
		{
			$s = $len & BIT8;
			$siz .= chr($s);
			$len >>= 8;
		}
		if ( $this->m_big )
			$siz = strrev($siz);

		$str .= "\x80";
		while ( (strlen($str)+$plen) % $clen )
			$str .= ZERO;

		$str .= $siz;
		return;
	}
	//////////////////////////////
	private function md5_calc( &$str, &$h )
	{
		// K[64]
		// Use binary integer part of the sines of integers (Radians) as constants
		// K[i] := floor( 2^32 * abs( sin(i+1) ) )
		$key = $this->ord_const(array(
			'd76aa478', 'e8c7b756', '242070db', 'c1bdceee', 'f57c0faf', '4787c62a', 'a8304613', 'fd469501',
			'698098d8', '8b44f7af', 'ffff5bb1', '895cd7be', '6b901122', 'fd987193', 'a679438e', '49b40821',
			'f61e2562', 'c040b340', '265e5a51', 'e9b6c7aa', 'd62f105d', '02441453', 'd8a1e681', 'e7d3fbc8',
			'21e1cde6', 'c33707d6', 'f4d50d87', '455a14ed', 'a9e3e905', 'fcefa3f8', '676f02d9', '8d2a4c8a',
			'fffa3942', '8771f681', '6d9d6122', 'fde5380c', 'a4beea44', '4bdecfa9', 'f6bb4b60', 'bebfbc70',
			'289b7ec6', 'eaa127fa', 'd4ef3085', '04881d05', 'd9d4d039', 'e6db99e5', '1fa27cf8', 'c4ac5665',
			'f4292244', '432aff97', 'ab9423a7', 'fc93a039', '655b59c3', '8f0ccc92', 'ffeff47d', '85845dd1',
			'6fa87e4f', 'fe2ce6e0', 'a3014314', '4e0811a1', 'f7537e82', 'bd3af235', '2ad7d2bb', 'eb86d391',
		));

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
			$chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$chunk[] = $this->ord_chunk($str, $i+$j);

			// main compress loop
			list($h_a,$h_b,$h_c,$h_d) = $h;
			for ( $j=0; $j < 64; $j++ )
			{
				switch ( $j >> 4 )
				{
					case 0: // 0-15
						//$h_f = ($h_b & $h_c) | (~$h_b & $h_d);
						$h_xb  = $this->c_not($h_b);
						$h_bc  = $this->c_and($h_b, $h_c);
						$h_xbd = $this->c_and($h_xb, $h_d);

						$h_f = $this->c_or($h_bc, $h_xbd);
						$h_g = $j & BIT4;
						break;
					case 1: // 16-31
						//$h_f = ($h_d & $h_b) | (~$h_d & $h_c);
						$h_xd  = $this->c_not($h_d);
						$h_db  = $this->c_and($h_d, $h_b);
						$h_xdc = $this->c_and($h_xd, $h_c);

						$h_f = $this->c_or($h_db, $h_xdc);
						$h_g = ($j * 5 + 1) & BIT4;
						break;
					case 2: // 32-47
						//$h_f = ($h_b ^ $h_c ^ $h_d);
						$h_f = $this->c_xor($h_b, $h_c, $h_d);
						$h_g = ($j * 3 + 5) & BIT4;
						break;
					case 3: // 48-63
						//$h_f = $h_c ^ ($h_b | ~$h_d);
						$h_xd  = $this->c_not($h_d);
						$h_bxd = $this->c_or($h_b, $h_xd);
						$h_f = $this->c_xor($h_c, $h_bxd);
						$h_g = ($j * 7) & BIT4;
						break;
				} // switch ( $j >> 4 )

				$h_f = $this->c_add($h_f, $h_a, $key[$j], $chunk[$h_g]);
				$b1  = $this->c_rotate_left($h_f, $sh[$j]);

				$h_a = $h_d;
				$h_d = $h_c;
				$h_c = $h_b;
				$h_b = $this->c_add($h_b, $b1);
			} // for ( $j=0; $j < 64; $j++ )

			$h[0] = $this->c_add($h[0], $h_a);
			$h[1] = $this->c_add($h[1], $h_b);
			$h[2] = $this->c_add($h[2], $h_c);
			$h[3] = $this->c_add($h[3], $h_d);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}

	static public function md5( $str )
	{
		$class = new self;
		$class->m_len = 4; // 32-bit
		$class->m_big = false; // little-endian

		// len   =  64-bit/ 8-byte
		// chunk = 512-bit/64-byte
		$class->padchunk($str, 8, 64);

		// h[4]
		$h = $class->ord_const(array(
			'67452301',
			'efcdab89',
			'98badcfe',
			'10325476',
		));

		$class->md5_calc($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		return $sum;
	}
	//////////////////////////////
	private function sha1_calc( &$str, &$h )
	{
		// K[4]
		$key = $this->ord_const(array(
			'5a827999',
			'6ed9eba1',
			'8f1bbcdc',
			'ca62c1d6',
		));

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			// break into 512-bit/64-byte/16-int chunks
			$chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$chunk[] = $this->ord_chunk($str, $i+$j);

			// Message schedule: extend the sixteen 32-bit words into eighty 32-bit words
			for ( $j = 16; $j < 80; $j++ )
			{
				$b = $this->c_xor($chunk[$j-3], $chunk[$j- 8], $chunk[$j-14], $chunk[$j-16]);
				$chunk[$j] = $this->c_rotate_left($b, 1);
			} // for ( $j = 16; $j < 80; $j++ )

			// main compress loop
			list($h_a,$h_b,$h_c,$h_d,$h_e) = $h;
			for ( $j=0; $j < 80; $j++ )
			{
				$ah = (int)($j / 20);
				switch ( $ah )
				{
					case 0:
						//$h_f = ($h_b & $h_c) | (~$h_b & $h_d);
						$h_xb  = $this->c_not($h_b);
						$h_bc  = $this->c_and($h_b, $h_c);
						$h_xbd = $this->c_and($h_xb, $h_d);
						$h_f   = $this->c_or($h_bc, $h_xbd);
						break;
					case 1:
					case 3:
						//$h_f = ($h_b ^ $h_c ^ $h_d);
						$h_f = $this->c_xor($h_b, $h_c, $h_d);
						break;
					case 2:
						//$h_f = ($h_b & $h_c) | ($h_b & $h_d) | ($h_c & $h_d);
						$h_bc = $this->c_and($h_b, $h_c);
						$h_bd = $this->c_and($h_b, $h_d);
						$h_cd = $this->c_and($h_c, $h_d);
						$h_f  = $this->c_or($h_bc, $h_bd, $h_cd);
						break;
				} // switch ( $ah )

				$b1 = $this->c_rotate_left($h_a, 5);
				$b1 = $this->c_add($b1, $h_f, $h_e, $key[$ah], $chunk[$j]);

				$h_e = $h_d;
				$h_d = $h_c;
				$h_c = $this->c_rotate_left($h_b, 30);
				$h_b = $h_a;
				$h_a = $b1;
			} // for ( $j=0; $j < 80; $j++ )

			$h[0] = $this->c_add($h[0], $h_a);
			$h[1] = $this->c_add($h[1], $h_b);
			$h[2] = $this->c_add($h[2], $h_c);
			$h[3] = $this->c_add($h[3], $h_d);
			$h[4] = $this->c_add($h[4], $h_e);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}

	static public function sha1( $str )
	{
		$class = new self;
		$class->m_len = 4; // 32-bit
		$class->m_big = true; // big-endian

		// len   =  64-bit/ 8-byte
		// chunk = 512-bit/64-byte
		$class->padchunk($str, 8, 64);

		// h[5]
		$h = $class->ord_const(array(
			'67452301',
			'efcdab89',
			'98badcfe',
			'10325476',
			'c3d2e1f0',
		));

		$class->sha1_calc($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		$sum .= $class->chr_chunk( $h[4] );
		return $sum;
	}
	//////////////////////////////
	private function sha2_calc32( &$str, &$h )
	{
		// K[64]
		// first 32 bits of the fractional parts of the cube roots of the first 64 primes 2..311
		$key = $this->ord_const(array(
			'428a2f98', '71374491', 'b5c0fbcf', 'e9b5dba5', '3956c25b', '59f111f1', '923f82a4', 'ab1c5ed5',
			'd807aa98', '12835b01', '243185be', '550c7dc3', '72be5d74', '80deb1fe', '9bdc06a7', 'c19bf174',
			'e49b69c1', 'efbe4786', '0fc19dc6', '240ca1cc', '2de92c6f', '4a7484aa', '5cb0a9dc', '76f988da',
			'983e5152', 'a831c66d', 'b00327c8', 'bf597fc7', 'c6e00bf3', 'd5a79147', '06ca6351', '14292967',
			'27b70a85', '2e1b2138', '4d2c6dfc', '53380d13', '650a7354', '766a0abb', '81c2c92e', '92722c85',
			'a2bfe8a1', 'a81a664b', 'c24b8b70', 'c76c51a3', 'd192e819', 'd6990624', 'f40e3585', '106aa070',
			'19a4c116', '1e376c08', '2748774c', '34b0bcb5', '391c0cb3', '4ed8aa4a', '5b9cca4f', '682e6ff3',
			'748f82ee', '78a5636f', '84c87814', '8cc70208', '90befffa', 'a4506ceb', 'bef9a3f7', 'c67178f2',
		));

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x40 )
		{
			// break into 512-bit/64-byte/16-int chunks
			$chunk = array();
			for ( $j=0; $j < 0x40; $j += 4 )
				$chunk[] = $this->ord_chunk($str, $i+$j);

			// Extend the first 16 words into the remaining 48 words w[16..63] of the message schedule array
			for ( $j = 16; $j < 64; $j++ )
			{
				// Wt = SSIG1(W(t-2)) + W(t-7) + SSIG0(w(t-15)) + W(t-16)
				// SSIG1(x) = ROTR^17(x) XOR ROTR^19(x) XOR SHR^10(x)
				// SSIG0(x) = ROTR^ 7(x) XOR ROTR^18(x) XOR SHR^ 3(x)
				$s1a = $this->c_rotate_right($chunk[$j- 2], 17);
				$s1b = $this->c_rotate_right($chunk[$j- 2], 19);
				$s1c = $this->c_shift_right ($chunk[$j- 2], 10);

				$s0a = $this->c_rotate_right($chunk[$j-15],  7);
				$s0b = $this->c_rotate_right($chunk[$j-15], 18);
				$s0c = $this->c_shift_right ($chunk[$j-15],  3);

				$s1 = $this->c_xor($s1a, $s1b, $s1c);
				$s0 = $this->c_xor($s0a, $s0b, $s0c);

				$chunk[$j] = $this->c_add($s1, $chunk[$j-7], $s0, $chunk[$j-16]);
			} // for ( $j = 16; $j < 64; $j++ )

			// main compress loop
			list($h_a,$h_b,$h_c,$h_d,$h_e,$h_f,$h_g,$h_h) = $h;
			for ( $j=0; $j < 64; $j++ )
			{
				// T1 = h + BSIG1(e) + CH(e,f,g) + Kt + Wt
				// BSIG1(x) = ROTR^6(x) XOR ROTR^11(x) XOR ROTR^25(x)
				// CH( x, y, z) = (x AND y) XOR ( (NOT x) AND z)
				$s1a = $this->c_rotate_right($h_e,  6);
				$s1b = $this->c_rotate_right($h_e, 11);
				$s1c = $this->c_rotate_right($h_e, 25);

				$h_xe  = $this->c_not($h_e);
				$h_ef  = $this->c_and($h_e, $h_f);
				$h_xeg = $this->c_and($h_xe, $h_g);

				$s1 = $this->c_xor($s1a, $s1b, $s1c);
				$ch = $this->c_xor($h_ef, $h_xeg);

				$b1 = $this->c_add($h_h, $s1, $ch, $key[$j], $chunk[$j]);

				// T2 = BSIG0(a) + MAJ(a,b,c)
				// BSIG0(x) = ROTR^2(x) XOR ROTR^13(x) XOR ROTR^22(x)
				// MAJ( x, y, z) = (x AND y) XOR (x AND z) XOR (y AND z)
				$s0a = $this->c_rotate_right($h_a,  2);
				$s0b = $this->c_rotate_right($h_a, 13);
				$s0c = $this->c_rotate_right($h_a, 22);

				$h_ab = $this->c_and($h_a, $h_b);
				$h_ac = $this->c_and($h_a, $h_c);
				$h_bc = $this->c_and($h_b, $h_c);

				$s0  = $this->c_xor($s0a, $s0b, $s0c);
				$maj = $this->c_xor($h_ab , $h_ac , $h_bc);

				$b2 = $this->c_add($s0, $maj);


				$h_h = $h_g;
				$h_g = $h_f;
				$h_f = $h_e;
				$h_e = $this->c_add($h_d, $b1);
				$h_d = $h_c;
				$h_c = $h_b;
				$h_b = $h_a;
				$h_a = $this->c_add($b1, $b2);
			} // for ( $j=0; $j < 64; $j++ )

			$h[0] = $this->c_add($h[0], $h_a);
			$h[1] = $this->c_add($h[1], $h_b);
			$h[2] = $this->c_add($h[2], $h_c);
			$h[3] = $this->c_add($h[3], $h_d);
			$h[4] = $this->c_add($h[4], $h_e);
			$h[5] = $this->c_add($h[5], $h_f);
			$h[6] = $this->c_add($h[6], $h_g);
			$h[7] = $this->c_add($h[7], $h_h);
		} // for ( $i=0; $i < $len; $i += 0x40 )
		return;
	}

	static public function sha256( $str )
	{
		$class = new self;
		$class->m_len = 4; // 32-bit
		$class->m_big = true; // big-endian

		// len   =  64-bit/ 8-byte
		// chunk = 512-bit/64-byte
		$class->padchunk($str, 8, 64);

		// h[8]
		// first 32 bits of the fractional parts of the square roots of the first 8 primes 2..19
		$h = $class->ord_const(array(
			'6a09e667',
			'bb67ae85',
			'3c6ef372',
			'a54ff53a',
			'510e527f',
			'9b05688c',
			'1f83d9ab',
			'5be0cd19',
		));

		$class->sha2_calc32($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		$sum .= $class->chr_chunk( $h[4] );
		$sum .= $class->chr_chunk( $h[5] );
		$sum .= $class->chr_chunk( $h[6] );
		$sum .= $class->chr_chunk( $h[7] );
		return $sum;
	}

	static public function sha224( $str )
	{
		$class = new self;
		$class->m_len = 4; // 32-bit
		$class->m_big = true; // big-endian

		// len   =  64-bit/ 8-byte
		// chunk = 512-bit/64-byte
		$class->padchunk($str, 8, 64);

		// h[8]
		// second 32 bits of the fractional parts of the square roots of the 9th through 16th primes 23..53
		$h = $class->ord_const(array(
			'c1059ed8',
			'367cd507',
			'3070dd17',
			'f70e5939',
			'ffc00b31',
			'68581511',
			'64f98fa7',
			'befa4fa4',
		));

		$class->sha2_calc32($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		$sum .= $class->chr_chunk( $h[4] );
		$sum .= $class->chr_chunk( $h[5] );
		$sum .= $class->chr_chunk( $h[6] );
		return $sum;
	}
	//////////////////////////////
	private function sha2_calc64( &$str, &$h )
	{
		// K[80]
		// first 64 bits of the fractional parts of the cube roots of the first 80 primes 2..409
		$key = $this->ord_const(array(
			'428a2f98d728ae22', '7137449123ef65cd', 'b5c0fbcfec4d3b2f', 'e9b5dba58189dbbc',
			'3956c25bf348b538', '59f111f1b605d019', '923f82a4af194f9b', 'ab1c5ed5da6d8118',
			'd807aa98a3030242', '12835b0145706fbe', '243185be4ee4b28c', '550c7dc3d5ffb4e2',
			'72be5d74f27b896f', '80deb1fe3b1696b1', '9bdc06a725c71235', 'c19bf174cf692694',
			'e49b69c19ef14ad2', 'efbe4786384f25e3', '0fc19dc68b8cd5b5', '240ca1cc77ac9c65',
			'2de92c6f592b0275', '4a7484aa6ea6e483', '5cb0a9dcbd41fbd4', '76f988da831153b5',
			'983e5152ee66dfab', 'a831c66d2db43210', 'b00327c898fb213f', 'bf597fc7beef0ee4',
			'c6e00bf33da88fc2', 'd5a79147930aa725', '06ca6351e003826f', '142929670a0e6e70',
			'27b70a8546d22ffc', '2e1b21385c26c926', '4d2c6dfc5ac42aed', '53380d139d95b3df',
			'650a73548baf63de', '766a0abb3c77b2a8', '81c2c92e47edaee6', '92722c851482353b',
			'a2bfe8a14cf10364', 'a81a664bbc423001', 'c24b8b70d0f89791', 'c76c51a30654be30',
			'd192e819d6ef5218', 'd69906245565a910', 'f40e35855771202a', '106aa07032bbd1b8',
			'19a4c116b8d2d0c8', '1e376c085141ab53', '2748774cdf8eeb99', '34b0bcb5e19b48a8',
			'391c0cb3c5c95a63', '4ed8aa4ae3418acb', '5b9cca4f7763e373', '682e6ff3d6b2b8a3',
			'748f82ee5defb2fc', '78a5636f43172f60', '84c87814a1f0ab72', '8cc702081a6439ec',
			'90befffa23631e28', 'a4506cebde82bde9', 'bef9a3f7b2c67915', 'c67178f2e372532b',
			'ca273eceea26619c', 'd186b8c721c0c207', 'eada7dd6cde0eb1e', 'f57d4f7fee6ed178',
			'06f067aa72176fba', '0a637dc5a2c898a6', '113f9804bef90dae', '1b710b35131c471b',
			'28db77f523047d84', '32caab7b40c72493', '3c9ebe0a15c9bebc', '431d67c49c100d4c',
			'4cc5d4becb3e42b6', '597f299cfc657e2a', '5fcb6fab3ad6faec', '6c44198c4a475817',
		));

		$len = strlen($str);
		for ( $i=0; $i < $len; $i += 0x80 )
		{
			// break into 1024-bit/128-byte/16-int chunks
			$chunk = array();
			for ( $j=0; $j < 0x80; $j += 8 )
				$chunk[] = $this->ord_chunk($str, $i+$j);

			// Extend the first 16 words into the remaining 64 words w[16..79] of the message schedule array
			for ( $j = 16; $j < 80; $j++ )
			{
				// Wt = SSIG1(W(t-2)) + W(t-7) + SSIG0(W(t-15)) + W(t-16)
				// SSIG1(x) = ROTR^19(x) XOR ROTR^61(x) XOR SHR^6(x)
				// SSIG0(x) = ROTR^ 1(x) XOR ROTR^ 8(x) XOR SHR^7(x)
				$s1a = $this->c_rotate_right($chunk[$j- 2], 19);
				$s1b = $this->c_rotate_right($chunk[$j- 2], 61);
				$s1c = $this->c_shift_right ($chunk[$j- 2],  6);

				$s0a = $this->c_rotate_right($chunk[$j-15],  1);
				$s0b = $this->c_rotate_right($chunk[$j-15],  8);
				$s0c = $this->c_shift_right ($chunk[$j-15],  7);

				$s1 = $this->c_xor($s1a, $s1b, $s1c);
				$s0 = $this->c_xor($s0a, $s0b, $s0c);

				$chunk[$j] = $this->c_add($s1, $chunk[$j-7], $s0, $chunk[$j-16]);
			} // for ( $j = 16; $j < 80; $j++ )

			// main compress loop
			list($h_a,$h_b,$h_c,$h_d,$h_e,$h_f,$h_g,$h_h) = $h;
			for ( $j=0; $j < 80; $j++ )
			{
				// T1 = h + BSIG1(e) + CH(e,f,g) + Kt + Wt
				// BSIG1(x) = ROTR^14(x) XOR ROTR^18(x) XOR ROTR^41(x)
				// CH( x, y, z) = (x AND y) XOR ( (NOT x) AND z)
				$s1a = $this->c_rotate_right($h_e, 14);
				$s1b = $this->c_rotate_right($h_e, 18);
				$s1c = $this->c_rotate_right($h_e, 41);

				$h_xe  = $this->c_not($h_e);
				$h_ef  = $this->c_and($h_e, $h_f);
				$h_xeg = $this->c_and($h_xe, $h_g);

				$s1 = $this->c_xor($s1a, $s1b, $s1c);
				$ch = $this->c_xor($h_ef, $h_xeg);
				$b1 = $this->c_add($h_h, $s1, $ch, $key[$j], $chunk[$j]);

				// T2 = BSIG0(a) + MAJ(a,b,c)
				// BSIG0(x) = ROTR^28(x) XOR ROTR^34(x) XOR ROTR^39(x)
				// MAJ( x, y, z) = (x AND y) XOR (x AND z) XOR (y AND z)
				$s0a = $this->c_rotate_right($h_a, 28);
				$s0b = $this->c_rotate_right($h_a, 34);
				$s0c = $this->c_rotate_right($h_a, 39);

				$h_ab = $this->c_and($h_a, $h_b);
				$h_ac = $this->c_and($h_a, $h_c);
				$h_bc = $this->c_and($h_b, $h_c);

				$s0  = $this->c_xor($s0a, $s0b, $s0c);
				$maj = $this->c_xor($h_ab , $h_ac , $h_bc );
				$b2  = $this->c_add($s0, $maj);


				$h_h = $h_g;
				$h_g = $h_f;
				$h_f = $h_e;
				$h_e = $this->c_add($h_d, $b1);
				$h_d = $h_c;
				$h_c = $h_b;
				$h_b = $h_a;
				$h_a = $this->c_add($b1, $b2);
			} // for ( $j=0; $j < 80; $j++ )

			$h[0] = $this->c_add($h[0], $h_a);
			$h[1] = $this->c_add($h[1], $h_b);
			$h[2] = $this->c_add($h[2], $h_c);
			$h[3] = $this->c_add($h[3], $h_d);
			$h[4] = $this->c_add($h[4], $h_e);
			$h[5] = $this->c_add($h[5], $h_f);
			$h[6] = $this->c_add($h[6], $h_g);
			$h[7] = $this->c_add($h[7], $h_h);
		} // for ( $i=0; $i < $len; $i += 0x80 )
		return;
	}

	static public function sha512( $str )
	{
		$class = new self;
		$class->m_len = 8; // 64-bit
		$class->m_big = true; // big-endian

		// len   =  128-bit/ 16-byte
		// chunk = 1024-bit/128-byte
		$class->padchunk($str, 16, 128);

		// h[8]
		// first 64 bits of the fractional parts of the square roots of the first 8 primes 2..19
		$h = $class->ord_const(array(
			'6a09e667f3bcc908',
			'bb67ae8584caa73b',
			'3c6ef372fe94f82b',
			'a54ff53a5f1d36f1',
			'510e527fade682d1',
			'9b05688c2b3e6c1f',
			'1f83d9abfb41bd6b',
			'5be0cd19137e2179',
		));

		$class->sha2_calc64($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		$sum .= $class->chr_chunk( $h[4] );
		$sum .= $class->chr_chunk( $h[5] );
		$sum .= $class->chr_chunk( $h[6] );
		$sum .= $class->chr_chunk( $h[7] );
		return $sum;
	}

	static public function sha384( $str )
	{
		$class = new self;
		$class->m_len = 8; // 64-bit
		$class->m_big = true; // big-endian

		// len   =  128-bit/ 16-byte
		// chunk = 1024-bit/128-byte
		$class->padchunk($str, 16, 128);

		// h[8]
		// second 64 bits of the fractional parts of the square roots of the 9th through 16th primes 23..53
		$h = $class->ord_const(array(
			'cbbb9d5dc1059ed8',
			'629a292a367cd507',
			'9159015a3070dd17',
			'152fecd8f70e5939',
			'67332667ffc00b31',
			'8eb44a8768581511',
			'db0c2e0d64f98fa7',
			'47b5481dbefa4fa4',
		));

		$class->sha2_calc64($str, $h);
		$sum  = '';
		$sum .= $class->chr_chunk( $h[0] );
		$sum .= $class->chr_chunk( $h[1] );
		$sum .= $class->chr_chunk( $h[2] );
		$sum .= $class->chr_chunk( $h[3] );
		$sum .= $class->chr_chunk( $h[4] );
		$sum .= $class->chr_chunk( $h[5] );
		return $sum;
	}
	//////////////////////////////
} // class merkel_damgard

class hmac
{
	private function hmacx( $hash, $key, $max, $msg )
	{
		if    ( strlen($key) > $max )  $key  = merkel_damgard::$hash($key);
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

		$dg1 = merkel_damgard::$hash( $inner.$msg );
		$dg2 = merkel_damgard::$hash( $outer.$dg1 );
		return $dg2;
	}

	static public function md5( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('md5', $key, 64, $msg);
	}
	static public function sha1( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('sha1', $key, 64, $msg);
	}
	static public function sha256( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('sha256', $key, 64, $msg);
	}
	static public function sha224( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('sha224', $key, 64, $msg);
	}
	static public function sha512( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('sha512', $key, 128, $msg);
	}
	static public function sha384( $key, $msg )
	{
		$class = new self;
		return $class->hmacx('sha384', $key, 128, $msg);
	}
} // class hmac
