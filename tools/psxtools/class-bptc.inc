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
// https://docs.microsoft.com/en-us/windows/win32/direct3d11/bc7-format
// https://docs.microsoft.com/en-us/windows/win32/direct3d11/bc7-format-mode-reference
// https://www.khronos.org/opengl/wiki/BPTC_Texture_Compression
// https://www.khronos.org/registry/OpenGL/extensions/ARB/ARB_texture_compression_bptc.txt
// https://www.khronos.org/registry/DataFormat/specs/1.1/dataformat.1.1.html#BPTC

// Unlike S3 Texture Compression, the blocks are taken as byte streams, and thus they are endian-independent.
class BPTC_Texture
{
	// 64 pre-defined partition set for 2 region tile
	private $m_p2_table = array(
		0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,
		0,0,0,1,0,0,0,1,0,0,0,1,0,0,0,1,
		0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,
		0,0,0,1,0,0,1,1,0,0,1,1,0,1,1,1,
		0,0,0,0,0,0,0,1,0,0,0,1,0,0,1,1,
		0,0,1,1,0,1,1,1,0,1,1,1,1,1,1,1,
		0,0,0,1,0,0,1,1,0,1,1,1,1,1,1,1,
		0,0,0,0,0,0,0,1,0,0,1,1,0,1,1,1,
		0,0,0,0,0,0,0,0,0,0,0,1,0,0,1,1,
		0,0,1,1,0,1,1,1,1,1,1,1,1,1,1,1,
		0,0,0,0,0,0,0,1,0,1,1,1,1,1,1,1,
		0,0,0,0,0,0,0,0,0,0,0,1,0,1,1,1,
		0,0,0,1,0,1,1,1,1,1,1,1,1,1,1,1,
		0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,
		0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,
		0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,
		0,0,0,0,1,0,0,0,1,1,1,0,1,1,1,1,
		0,1,1,1,0,0,0,1,0,0,0,0,0,0,0,0,
		0,0,0,0,0,0,0,0,1,0,0,0,1,1,1,0,
		0,1,1,1,0,0,1,1,0,0,0,1,0,0,0,0,
		0,0,1,1,0,0,0,1,0,0,0,0,0,0,0,0,
		0,0,0,0,1,0,0,0,1,1,0,0,1,1,1,0,
		0,0,0,0,0,0,0,0,1,0,0,0,1,1,0,0,
		0,1,1,1,0,0,1,1,0,0,1,1,0,0,0,1,
		0,0,1,1,0,0,0,1,0,0,0,1,0,0,0,0,
		0,0,0,0,1,0,0,0,1,0,0,0,1,1,0,0,
		0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,
		0,0,1,1,0,1,1,0,0,1,1,0,1,1,0,0,
		0,0,0,1,0,1,1,1,1,1,1,0,1,0,0,0,
		0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0,
		0,1,1,1,0,0,0,1,1,0,0,0,1,1,1,0,
		0,0,1,1,1,0,0,1,1,0,0,1,1,1,0,0,
		0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,
		0,0,0,0,1,1,1,1,0,0,0,0,1,1,1,1,
		0,1,0,1,1,0,1,0,0,1,0,1,1,0,1,0,
		0,0,1,1,0,0,1,1,1,1,0,0,1,1,0,0,
		0,0,1,1,1,1,0,0,0,0,1,1,1,1,0,0,
		0,1,0,1,0,1,0,1,1,0,1,0,1,0,1,0,
		0,1,1,0,1,0,0,1,0,1,1,0,1,0,0,1,
		0,1,0,1,1,0,1,0,1,0,1,0,0,1,0,1,
		0,1,1,1,0,0,1,1,1,1,0,0,1,1,1,0,
		0,0,0,1,0,0,1,1,1,1,0,0,1,0,0,0,
		0,0,1,1,0,0,1,0,0,1,0,0,1,1,0,0,
		0,0,1,1,1,0,1,1,1,1,0,1,1,1,0,0,
		0,1,1,0,1,0,0,1,1,0,0,1,0,1,1,0,
		0,0,1,1,1,1,0,0,1,1,0,0,0,0,1,1,
		0,1,1,0,0,1,1,0,1,0,0,1,1,0,0,1,
		0,0,0,0,0,1,1,0,0,1,1,0,0,0,0,0,
		0,1,0,0,1,1,1,0,0,1,0,0,0,0,0,0,
		0,0,1,0,0,1,1,1,0,0,1,0,0,0,0,0,
		0,0,0,0,0,0,1,0,0,1,1,1,0,0,1,0,
		0,0,0,0,0,1,0,0,1,1,1,0,0,1,0,0,
		0,1,1,0,1,1,0,0,1,0,0,1,0,0,1,1,
		0,0,1,1,0,1,1,0,1,1,0,0,1,0,0,1,
		0,1,1,0,0,0,1,1,1,0,0,1,1,1,0,0,
		0,0,1,1,1,0,0,1,1,1,0,0,0,1,1,0,
		0,1,1,0,1,1,0,0,1,1,0,0,1,0,0,1,
		0,1,1,0,0,0,1,1,0,0,1,1,1,0,0,1,
		0,1,1,1,1,1,1,0,1,0,0,0,0,0,0,1,
		0,0,0,1,1,0,0,0,1,1,1,0,0,1,1,1,
		0,0,0,0,1,1,1,1,0,0,1,1,0,0,1,1,
		0,0,1,1,0,0,1,1,1,1,1,1,0,0,0,0,
		0,0,1,0,0,0,1,0,1,1,1,0,1,1,1,0,
		0,1,0,0,0,1,0,0,0,1,1,1,0,1,1,1,
	);
	// 64 pre-defined partition set for 3 region tile
	private $m_p3_table = array(
		0,0,1,1,0,0,1,1,0,2,2,1,2,2,2,2,
		0,0,0,1,0,0,1,1,2,2,1,1,2,2,2,1,
		0,0,0,0,2,0,0,1,2,2,1,1,2,2,1,1,
		0,2,2,2,0,0,2,2,0,0,1,1,0,1,1,1,
		0,0,0,0,0,0,0,0,1,1,2,2,1,1,2,2,
		0,0,1,1,0,0,1,1,0,0,2,2,0,0,2,2,
		0,0,2,2,0,0,2,2,1,1,1,1,1,1,1,1,
		0,0,1,1,0,0,1,1,2,2,1,1,2,2,1,1,
		0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,
		0,0,0,0,1,1,1,1,1,1,1,1,2,2,2,2,
		0,0,0,0,1,1,1,1,2,2,2,2,2,2,2,2,
		0,0,1,2,0,0,1,2,0,0,1,2,0,0,1,2,
		0,1,1,2,0,1,1,2,0,1,1,2,0,1,1,2,
		0,1,2,2,0,1,2,2,0,1,2,2,0,1,2,2,
		0,0,1,1,0,1,1,2,1,1,2,2,1,2,2,2,
		0,0,1,1,2,0,0,1,2,2,0,0,2,2,2,0,
		0,0,0,1,0,0,1,1,0,1,1,2,1,1,2,2,
		0,1,1,1,0,0,1,1,2,0,0,1,2,2,0,0,
		0,0,0,0,1,1,2,2,1,1,2,2,1,1,2,2,
		0,0,2,2,0,0,2,2,0,0,2,2,1,1,1,1,
		0,1,1,1,0,1,1,1,0,2,2,2,0,2,2,2,
		0,0,0,1,0,0,0,1,2,2,2,1,2,2,2,1,
		0,0,0,0,0,0,1,1,0,1,2,2,0,1,2,2,
		0,0,0,0,1,1,0,0,2,2,1,0,2,2,1,0,
		0,1,2,2,0,1,2,2,0,0,1,1,0,0,0,0,
		0,0,1,2,0,0,1,2,1,1,2,2,2,2,2,2,
		0,1,1,0,1,2,2,1,1,2,2,1,0,1,1,0,
		0,0,0,0,0,1,1,0,1,2,2,1,1,2,2,1,
		0,0,2,2,1,1,0,2,1,1,0,2,0,0,2,2,
		0,1,1,0,0,1,1,0,2,0,0,2,2,2,2,2,
		0,0,1,1,0,1,2,2,0,1,2,2,0,0,1,1,
		0,0,0,0,2,0,0,0,2,2,1,1,2,2,2,1,
		0,0,0,0,0,0,0,2,1,1,2,2,1,2,2,2,
		0,2,2,2,0,0,2,2,0,0,1,2,0,0,1,1,
		0,0,1,1,0,0,1,2,0,0,2,2,0,2,2,2,
		0,1,2,0,0,1,2,0,0,1,2,0,0,1,2,0,
		0,0,0,0,1,1,1,1,2,2,2,2,0,0,0,0,
		0,1,2,0,1,2,0,1,2,0,1,2,0,1,2,0,
		0,1,2,0,2,0,1,2,1,2,0,1,0,1,2,0,
		0,0,1,1,2,2,0,0,1,1,2,2,0,0,1,1,
		0,0,1,1,1,1,2,2,2,2,0,0,0,0,1,1,
		0,1,0,1,0,1,0,1,2,2,2,2,2,2,2,2,
		0,0,0,0,0,0,0,0,2,1,2,1,2,1,2,1,
		0,0,2,2,1,1,2,2,0,0,2,2,1,1,2,2,
		0,0,2,2,0,0,1,1,0,0,2,2,0,0,1,1,
		0,2,2,0,1,2,2,1,0,2,2,0,1,2,2,1,
		0,1,0,1,2,2,2,2,2,2,2,2,0,1,0,1,
		0,0,0,0,2,1,2,1,2,1,2,1,2,1,2,1,
		0,1,0,1,0,1,0,1,0,1,0,1,2,2,2,2,
		0,2,2,2,0,1,1,1,0,2,2,2,0,1,1,1,
		0,0,0,2,1,1,1,2,0,0,0,2,1,1,1,2,
		0,0,0,0,2,1,1,2,2,1,1,2,2,1,1,2,
		0,2,2,2,0,1,1,1,0,1,1,1,0,2,2,2,
		0,0,0,2,1,1,1,2,1,1,1,2,0,0,0,2,
		0,1,1,0,0,1,1,0,0,1,1,0,2,2,2,2,
		0,0,0,0,0,0,0,0,2,1,1,2,2,1,1,2,
		0,1,1,0,0,1,1,0,2,2,2,2,2,2,2,2,
		0,0,2,2,0,0,1,1,0,0,1,1,0,0,2,2,
		0,0,2,2,1,1,2,2,1,1,2,2,0,0,2,2,
		0,0,0,0,0,0,0,0,0,0,0,0,2,1,1,2,
		0,0,0,2,0,0,0,1,0,0,0,2,0,0,0,1,
		0,2,2,2,1,2,2,2,0,2,2,2,1,2,2,2,
		0,1,0,1,2,2,2,2,2,2,2,2,2,2,2,2,
		0,1,1,1,2,0,1,1,2,2,0,1,2,2,2,0,
	);

	private $m_a2_table = array(
		15,15,15,15,15,15,15,15,
		15,15,15,15,15,15,15,15,
		15, 2, 8, 2, 2, 8, 8,15,
		 2, 8, 2, 2, 8, 8, 2, 2,
		15,15, 6, 8, 2, 8,15,15,
		 2, 8, 2, 2, 2,15,15, 6,
		 6, 2, 6, 8,15,15, 2, 2,
		15,15,15,15,15, 2, 2,15,
	);
	private $m_a3a_table = array(
		 3, 3,15,15, 8, 3,15,15,
		 8, 8, 6, 6, 6, 5, 3, 3,
		 3, 3, 8,15, 3, 3, 6,10,
		 5, 8, 8, 6, 8, 5,15,15,
		 8,15, 3, 5, 6,10, 8,15,
		15, 3,15, 5,15,15,15,15,
		 3,15, 5, 5, 5, 8, 5,10,
		 5,10, 8,13,15,12, 3, 3,
	);
	private $m_a3b_table = array(
		15, 8, 8, 3,15,15, 3, 8,
		15,15,15,15,15,15,15, 8,
		15, 8,15, 3,15, 8,15, 8,
		 3,15, 6,10,15,15,10, 8,
		15, 3,15,10,10, 8, 9,10,
		 6,15, 8,15, 3, 6, 6, 8,
		15, 3,15,15,15,15,15,15,
		15,15,15,15, 3,15,15, 8,
	);

	// wieght 2 = 64 /  3 = 21.333 per step , round()
	// wieght 3 = 64 /  7 =  9.142 per step , round()
	// wieght 4 = 64 / 15 =  4.266 per step , round()
	private $m_weight2 = array(0,          21,            43,            64);
	private $m_weight3 = array(0,  9,   18,   27,      37,   46,   55,   64);
	private $m_weight4 = array(0,4,9,13,17,21,26,30,34,38,43,47,51,55,60,64);
	//////////////////////////////
	private function bc7_setbits( $data )
	{
		// 128 bit == 16 byte
		$bits = array();
		for ( $i=0; $i < 16; $i++ )
		{
			$b = ord( $data[$i] );
			for ( $j=0; $j < 8; $j++ )
			{
				$bits[] = ($b & 1);
				$b >>= 1;
			} // for ( $b=0; $b < 8; $b++ )
		} // for ( $i=0; $i < 16; $i++ )
		return $bits;
	}

	private function bc7_getbits( &$bits, $cnt )
	{
		$int = 0;
		for ( $i=0; $i < $cnt; $i++ )
		{
			$b = array_shift($bits);
			$int |= ($b << $i);
		} // for ( $i=0; $i < $cnt; $i++ )
		return $int;
	}

	private function bc7_rotation( &$pix, $rot )
	{
		if ( $rot == 0 ) // RGBA , no swap
			return;
		$len = strlen($pix);
		for ( $i=0; $i < $len; $i += 4 )
		{
			$a = $pix[$i+3];
			switch ( $rot )
			{
				case 1: // AGBR , swap(A,R)
					$pix[$i+3] = $pix[$i+0];
					$pix[$i+0] = $a;
					break;
				case 2: // RABG , swap(A,G)
					$pix[$i+3] = $pix[$i+1];
					$pix[$i+1] = $a;
					break;
				case 3: // RGAB , swap(A,B)
					$pix[$i+3] = $pix[$i+2];
					$pix[$i+2] = $a;
					break;
			} // switch ( $rot )
		} // for ( $i=0; $i < $len; $i += 4 )
		return;
	}

	private function bc7_endpoints( &$bits, $cnt, $rgb_bit, $a_bit, $p_bit )
	{
		$rgba = array();
		for ( $i=0; $i < $cnt; $i++ )
			$rgba[] = array(BIT8,BIT8,BIT8,BIT8);

		// read RGBA bits
		for ( $i=0; $i < $cnt; $i++ ) // R
			$rgba[$i][0] = $this->bc7_getbits($bits, $rgb_bit);
		for ( $i=0; $i < $cnt; $i++ ) // G
			$rgba[$i][1] = $this->bc7_getbits($bits, $rgb_bit);
		for ( $i=0; $i < $cnt; $i++ ) // B
			$rgba[$i][2] = $this->bc7_getbits($bits, $rgb_bit);

		if ( $a_bit != 0 )
		{
			for ( $i=0; $i < $cnt; $i++ ) // A
				$rgba[$i][3] = $this->bc7_getbits($bits, $a_bit);
		}

		// read P bits
		$p = array();
		if ( $p_bit === 1 )
		{
			for ( $i=0; $i < $cnt; $i++ ) // P
				$p[] = $this->bc7_getbits($bits, 1);
		}
		else if ( $p_bit === 0.5 )
		{
			for ( $i=0; $i < ($cnt/2); $i++ ) // P
			{
				$b = $this->bc7_getbits($bits, 1);
				$p[] = $b;
				$p[] = $b;
			} // for ( $i=0; $i < ($cnt/2); $i++ ) // P
		}

		// apply P bits
		if ( ! empty($p) )
		{
			for ( $i=0; $i < $cnt; $i++ )
			{
				$rgba[$i][0] = (($rgba[$i][0] << 1) | $p[$i]);
				$rgba[$i][1] = (($rgba[$i][1] << 1) | $p[$i]);
				$rgba[$i][2] = (($rgba[$i][2] << 1) | $p[$i]);
				if ( $a_bit != 0 )
					$rgba[$i][3] = (($rgba[$i][3] << 1) | $p[$i]);
			} // for ( $i=0; $i < $cnt; $i++ )
			$rgb_bit++;
			if ( $a_bit != 0 )
				$a_bit++;
		}

		// move MSB to 8 bit , and duplicate MSB to LSB
		for ( $i=0; $i < $cnt; $i++ )
		{
			$rgba[$i][0] <<= (8 - $rgb_bit);
			$rgba[$i][1] <<= (8 - $rgb_bit);
			$rgba[$i][2] <<= (8 - $rgb_bit);

			$rgba[$i][0] |= ($rgba[$i][0] >> $rgb_bit);
			$rgba[$i][1] |= ($rgba[$i][1] >> $rgb_bit);
			$rgba[$i][2] |= ($rgba[$i][2] >> $rgb_bit);

			if ( $a_bit != 0 )
			{
				$rgba[$i][3] <<= (8 - $a_bit);
				$rgba[$i][3] |= ($rgba[$i][3] >> $a_bit);
			}
		} // for ( $i=0; $i < $cnt; $i++ )
		return $rgba;
	}

	private function bc7_interpolation( $e0, $e1, $index, $bit )
	{
		$w = 0;
		switch ( $bit )
		{
			case 2:  $w = $this->m_weight2[$index]; break;
			case 3:  $w = $this->m_weight3[$index]; break;
			case 4:  $w = $this->m_weight4[$index]; break;
		}
		$c = ((64-$w) * $e0) + ($w * $e1) + 32;
		$c >>= 6;
		$c = int_clamp($c, 0, BIT8);
		return chr($c);
	}

	private function bc7_pix_rgba( &$bits, &$set, &$anch, &$end, $rgb_bit, $a_bit )
	{
		$pix = '';
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			$ai = $anch[$si];
			if ( $i == $ai ) // MSB is zero
				$ci = $this->bc7_getbits($bits, $rgb_bit-1);
			else
				$ci = $this->bc7_getbits($bits, $rgb_bit);

			$e0 = $end[ $si * 2 + 0 ];
			$e1 = $end[ $si * 2 + 1 ];
			$pix .= $this->bc7_interpolation($e0[0], $e1[0], $ci, $rgb_bit);
			$pix .= $this->bc7_interpolation($e0[1], $e1[1], $ci, $rgb_bit);
			$pix .= $this->bc7_interpolation($e0[2], $e1[2], $ci, $rgb_bit);
			if ( $a_bit == 0 )
				$pix .= BYTE;
			else
				$pix .= $this->bc7_interpolation($e0[3], $e1[3], $ci, $a_bit);
		} // for ( $i=0; $i < 16; $i++ )
		return $pix;
	}

	private function bc7_subset_index( $subset, $partition_id )
	{
		$index = array();
		switch ( $subset )
		{
			case 1:
				return array_pad($index, 16, 0);
			case 2:
				$n = $partition_id * 16;
				for ( $i=0; $i < 16; $i++ )
				{
					$index[] = $this->m_p2_table[$n];
					$n++;
				}
				return $index;
			case 3:
				$n = $partition_id * 16;
				for ( $i=0; $i < 16; $i++ )
				{
					$index[] = $this->m_p3_table[$n];
					$n++;
				}
				return $index;
		} // switch ( $subset )
		return $index;
	}

	private function bc7_anchor_index( $subset, $partition_id )
	{
		$index = array();
		switch ( $subset )
		{
			case 1:
				$index[] = 0;
				return $index;
			case 2:
				$index[] = 0;
				$index[] = $this->m_a2_table[$partition_id];
				return $index;
			case 3:
				$index[] = 0;
				$index[] = $this->m_a3a_table[$partition_id];
				$index[] = $this->m_a3b_table[$partition_id];
				return $index;
		} // switch ( $subset )
		return $index;
	}
	//////////////////////////////
	// Mode  H + P + R + I        RGB   + A   + P          indices       - sub
	// 0     1 + 4         =  5   4*6*3       + 1*6 = 78   4*4*3         - 3   = 45
	// 1     2 + 6         =  8   6*4*3       + 1*2 = 74   4*4*3         - 2   = 46
	// 2     3 + 6         =  9   5*6*3             = 90   4*4*2         - 3   = 29
	// 3     4 + 6         = 10   7*4*3       + 1*4 = 88   4*4*2         - 2   = 30
	// 4     5     + 2 + 1 =  8   5*2*3 + 6*2       = 42   4*4*2 + 4*4*3 - 1*2 = 78
	// 5     6     + 2     =  8   7*2*3 + 8*2       = 58   4*4*2 + 4*4*2 - 1*2 = 62
	// 6     7             =  7   7*2*3 + 7*2 + 1*2 = 58   4*4*4         - 1   = 63
	// 7     8 + 6         = 14   5*4*3 + 5*4 + 1*4 = 84   4*4*2         - 2   = 30

	private function bc7_mode0( &$bits )
	{
		//  4 = 4 bit partition
		// 72 = 4 bit * 6 color * RGB
		//  6 = 1 bit * 6 P
		// 45 = 3 bit * 16 index - 1*3 subset
		$sub  = 3;
		$part = $this->bc7_getbits($bits, 4);
		$end  = $this->bc7_endpoints($bits, $sub*2, 4, 0, 1);

		$set  = $this->bc7_subset_index($sub, $part);
		$anch = $this->bc7_anchor_index($sub, $part);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 3, 0);
		return $pix;
	}

	private function bc7_mode1( &$bits )
	{
		//  6 = 6 bit partition
		// 72 = 6 bit * 4 color * RGB
		//  2 = 1 bit * 2 P
		// 46 = 3 bit * 16 index - 1*2 subset
		$sub  = 2;
		$part = $this->bc7_getbits($bits, 6);
		$end  = $this->bc7_endpoints($bits, $sub*2, 6, 0, 0.5);

		$set  = $this->bc7_subset_index($sub, $part);
		$anch = $this->bc7_anchor_index($sub, $part);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 3, 0);
		return $pix;
	}

	private function bc7_mode2( &$bits )
	{
		//  6 = 6 bit partition
		// 90 = 5 bit * 6 color * RGB
		// 29 = 2 bit * 16 index - 1*3 subset
		$sub  = 3;
		$part = $this->bc7_getbits($bits, 6);
		$end  = $this->bc7_endpoints($bits, $sub*2, 5, 0, 0);

		$set  = $this->bc7_subset_index($sub, $part);
		$anch = $this->bc7_anchor_index($sub, $part);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 2, 0);
		return $pix;
	}

	private function bc7_mode3( &$bits )
	{
		//  6 = 6 bit partition
		// 84 = 7 bit * 4 color * RGB
		//  4 = 1 bit * 4 P
		// 30 = 2 bit * 16 index - 1*2 subset
		$sub  = 2;
		$part = $this->bc7_getbits($bits, 6);
		$end  = $this->bc7_endpoints($bits, $sub*2, 7, 0, 1);

		$set  = $this->bc7_subset_index($sub, $part);
		$anch = $this->bc7_anchor_index($sub, $part);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 2, 0);
		return $pix;
	}

	private function bc7_mode4( &$bits )
	{
		//  2 = 2 bit rotation
		//  1 = 1 bit index mode
		// 30 = 5 bit * 2 color * RGB
		// 12 = 6 bit * 2 color * A
		// 31 = 2 * 16 index - 1*1 subset
		// 47 = 3 * 16 index - 1*1 subset
		$sub  = 1;
		$rot  = $this->bc7_getbits($bits, 2);
		$idx  = $this->bc7_getbits($bits, 1);
		$end  = $this->bc7_endpoints($bits, $sub*2, 5, 6, 0);

		$set  = $this->bc7_subset_index($sub, 0);
		$anch = $this->bc7_anchor_index($sub, 0);

		$cb = ( $idx ) ? 3 : 2;
		$ab = ( $idx ) ? 2 : 3;
		$ci = array();
		$ai = array();
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			if ( $i == $anch[$si] ) // MSB is zero
			{
				if ( $idx )
					$ai[] = $this->bc7_getbits($bits, $ab-1);
				else
					$ci[] = $this->bc7_getbits($bits, $cb-1);
			}
			else
			{
				if ( $idx )
					$ai[] = $this->bc7_getbits($bits, $ab);
				else
					$ci[] = $this->bc7_getbits($bits, $cb);
			}
		} // for ( $i=0; $i < 16; $i++ )
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			if ( $i == $anch[$si] ) // MSB is zero
			{
				if ( $idx )
					$ci[] = $this->bc7_getbits($bits, $cb-1);
				else
					$ai[] = $this->bc7_getbits($bits, $ab-1);
			}
			else
			{
				if ( $idx )
					$ci[] = $this->bc7_getbits($bits, $cb);
				else
					$ai[] = $this->bc7_getbits($bits, $ab);
			}
		} // for ( $i=0; $i < 16; $i++ )

		$pix = '';
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			$e0 = $end[ $si * 2 + 0 ];
			$e1 = $end[ $si * 2 + 1 ];
			$pix .= $this->bc7_interpolation($e0[0], $e1[0], $ci[$i], $cb);
			$pix .= $this->bc7_interpolation($e0[1], $e1[1], $ci[$i], $cb);
			$pix .= $this->bc7_interpolation($e0[2], $e1[2], $ci[$i], $cb);
			$pix .= $this->bc7_interpolation($e0[3], $e1[3], $ai[$i], $ab);
		} // for ( $i=0; $i < 16; $i++ )

		$this->bc7_rotation($pix, $rot);
		return $pix;
	}

	private function bc7_mode5( &$bits )
	{
		//  2 = 2 bit rotation
		// 42 = 7 bit * 2 color * RGB
		// 16 = 8 bit * 2 color * A
		// 31 = 2 bit * 16 color index - 1*1 subset
		// 31 = 2 bit * 16 alpha index - 1*1 subset
		$sub  = 1;
		$rot  = $this->bc7_getbits($bits, 2);
		$end  = $this->bc7_endpoints($bits, $sub*2, 7, 8, 0);

		$set  = $this->bc7_subset_index($sub, 0);
		$anch = $this->bc7_anchor_index($sub, 0);

		$ci = array();
		$ai = array();
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			if ( $i == $anch[$si] ) // MSB is zero
				$ci[] = $this->bc7_getbits($bits, 1);
			else
				$ci[] = $this->bc7_getbits($bits, 2);
		} // for ( $i=0; $i < 16; $i++ )
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			if ( $i == $anch[$si] ) // MSB is zero
				$ai[] = $this->bc7_getbits($bits, 1);
			else
				$ai[] = $this->bc7_getbits($bits, 2);
		} // for ( $i=0; $i < 16; $i++ )

		$pix = '';
		for ( $i=0; $i < 16; $i++ )
		{
			$si = $set[$i];
			$e0 = $end[ $si * 2 + 0 ];
			$e1 = $end[ $si * 2 + 1 ];
			$pix .= $this->bc7_interpolation($e0[0], $e1[0], $ci[$i], 2);
			$pix .= $this->bc7_interpolation($e0[1], $e1[1], $ci[$i], 2);
			$pix .= $this->bc7_interpolation($e0[2], $e1[2], $ci[$i], 2);
			$pix .= $this->bc7_interpolation($e0[3], $e1[3], $ai[$i], 2);
		} // for ( $i=0; $i < 16; $i++ )

		$this->bc7_rotation($pix, $rot);
		return $pix;
	}

	private function bc7_mode6( &$bits )
	{
		// 56 = 7 bit * 2 color * RGBA
		//  2 = 1 bit * 2 P
		// 63 = 4 bit * 16 index - 1*1 subset
		$sub  = 1;
		$end  = $this->bc7_endpoints($bits, $sub*2, 7, 7, 1);

		$set  = $this->bc7_subset_index($sub, 0);
		$anch = $this->bc7_anchor_index($sub, 0);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 4, 4);
		return $pix;
	}

	private function bc7_mode7( &$bits )
	{
		//  6 = 6 bit partition
		// 80 = 5 bit * 4 color * RGBA
		//  4 = 1 bit * 4 P
		// 30 = 2 bit * 16 index - 1*2 subset
		$sub  = 2;
		$part = $this->bc7_getbits($bits, 6);
		$end  = $this->bc7_endpoints($bits, $sub*2, 5, 5, 1);

		$set  = $this->bc7_subset_index($sub, $part);
		$anch = $this->bc7_anchor_index($sub, $part);

		$pix  = $this->bc7_pix_rgba($bits, $set, $anch, $end, 2, 2);
		return $pix;
	}

	private function bc7_block( &$bits )
	{
		if ( array_shift($bits) )  return $this->bc7_mode0($bits);
		if ( array_shift($bits) )  return $this->bc7_mode1($bits);
		if ( array_shift($bits) )  return $this->bc7_mode2($bits);
		if ( array_shift($bits) )  return $this->bc7_mode3($bits);
		if ( array_shift($bits) )  return $this->bc7_mode4($bits);
		if ( array_shift($bits) )  return $this->bc7_mode5($bits);
		if ( array_shift($bits) )  return $this->bc7_mode6($bits);
		if ( array_shift($bits) )  return $this->bc7_mode7($bits);
		return str_repeat(PIX_ALPHA, 4*4);
	}
	//////////////////////////////
	public function BPTC( &$data )  { return $this->BC7($data); }
	public function BC7( &$data )
	{
		$pix = '';
		$len = int_ceil( strlen($data), -16 );
		for ( $i=0; $i < $len; $i += 16 )
		{
			$bits = $this->bc7_setbits( substr($data,$i,16) );
			$pix .= $this->bc7_block($bits);
		} // for ( $i=0; $i < $len; $i += 16 )
		return $pix;
	}

	public function BPTC_debug( &$data, $w, $h )
	{
		trace("== BPTC_debug( %x , %x )\n", $w, $h);
		$b1 = strlen($data);
		$b2 = $w * $h * 4;
		if ( $b1 != $b2 )
			return php_error("data size [%x] != w x h [%x]", $b1, $b2);

		// BPTC blocks are always 4x4 pixels
		$canvas = str_repeat(ZERO, $b1);
		$pos    = 0;
		for ( $y=0; $y < $h; $y += 4 )
		{
			for ( $x=0; $x < $w; $x += 4 )
			{
				for ( $by=0; $by < 4; $by++ )
				{
					$src = substr($data, $pos, 16); // 4 RGBA pixels
						$pos += 16;
					$dyy = ($y   + $by) * $w;
					$dxx = ($dyy + $x ) * 4;
					str_update($canvas, $dxx, $src);
				}
			} // for ( $x=0; $x < $w; $x += 4 )
		} // for ( $y=0; $y < $h; $y += 4 )
		return $canvas;
	}
} // class BPTC
