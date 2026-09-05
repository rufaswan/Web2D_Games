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
 *
 * Special Thanks
 * https://github.com/rufaswan/cmdpack/blob/master/src/ecm.c
 *   Neill Corlett
 */
declare( strict_types=1 );

class ecc
{
	private $ecc_f_lut = [];
	private $ecc_b_lut = [];
	private $edc_lut   = [];

	public function __construct()
	{
		for ( $i=0; $i < 0x100; $i++ )
		{
			$this->ecc_f_lut[$i] = 0;
			$this->ecc_b_lut[$i] = 0;
			$this->edc_lut  [$i] = 0;
		} // for ( $i=0; $i < 0x100; $i++ )

		// 32-bit php
		// 0xd8018001 is over PHP_INT_MAX and convert to float
		//            and lower bits precision loss bug
		// force as negative int
		//      d8018001
		// not -27fe7ffe
		// +1  -27fe7fff
		$mxor = (0xd801 << 16) | 0x8001;

		for ( $i=0; $i < 0x100; $i++ )
		{
			$a = ($i & 0x80) ? 0x11d : 0;
			$j = ($i << 1) ^ $a;
			$this->ecc_f_lut[$i]      = $j;
			$this->ecc_b_lut[$i ^ $j] = $i;

			$edc = $i;
			for ( $j=0; $j < 8; $j++ )
			{
				$a   = ($edc &  1) ? $mxor : 0;
				$edc = ($edc >> 1) ^ $a;
			} // for ( $j=0; $j < 8; $j++ )
			$this->edc_lut[$i] = $edc;
		} // for ( $i=0; $i < 0x100; $i++ )
	}
	//////////////////////////////
	private function eccpq( string &$bin, int $c_major, int $c_minor, int $i_major, int $i_minor ) : string
	{
		$ecc = [];
		$size = $c_major << 1;
		while ( $size > 0 )
		{
			$ecc[] = 0;
			$size--;
		}

		$c_max = $c_major * $c_minor;
		for ( $major = 0; $major < $c_major; $major++ )
		{
			$c_index = ($major >> 1) * $i_major + ($major & 1);
			$ecc_a = 0;
			$ecc_b = 0;

			for ( $minor = 0; $minor < $c_minor; $minor++ )
			{
				$tmp = ord( $bin[$c_index] );

				$c_index += $i_minor;
				if ( $c_index >= $c_max )
					$c_index -= $c_max;

				$ecc_a ^= $tmp;
				$ecc_b ^= $tmp;
				$ecc_a  = $this->ecc_f_lut[$ecc_a];
			} // for ( $minor = 0; $minor < $c_minor; $minor++ )

			$tmp   = $this->ecc_f_lut[$ecc_a] ^ $ecc_b;
			$ecc_a = $this->ecc_b_lut[$tmp];

			$ecc[$major           ] = $ecc_a;
			$ecc[$major + $c_major] = $ecc_a ^ $ecc_b;
		} // for ( $major = 0; $major < $c_major; $major++ )

		$data = '';
		foreach ( $ecc as $v )
			$data .= chr($v);
		return $data;
	}

	private function ecc( string &$bin ) : array
	{
		//   p   56*2 + q 34*2
		// = p   ac   + q 68
		// = pq 114
		$p = $this->eccpq($bin, 86, 24,  2, 86);
		$q = $this->eccpq($bin, 52, 43, 86, 88);

		// p 56*18 = 810 -> 10-81c = [subhead] + data + edc
		// q 34*2b = 8bc -> 10-8c8 = [subhead] + data + edc + ecc p
		return array($p,$q);
	}

	private function edc( string &$bin ) : string
	{
		$len = strlen($bin);
		$edc = 0;
		for ( $i=0; $i < $len; $i++ )
		{
			$src = ord( $bin[$i] );
			$a   = ($edc ^ $src) & BIT8;
			$b   = ($edc >> 8  ) & 0xffffff;
			$edc = $b ^ $this->edc_lut[$a];
		} // for ( $i=0; $i < $len; $i++ )
tool::trace($edc);

		$bin = '';
		for ( $i=0; $i < 4; $i++ )
		{
			$a = $edc & BIT8;
				$edc >>= 8;
			$bin .= chr($a);
		}
		return $bin;
	}
	//////////////////////////////
	// mode1/930 , ecc(msf)
	//   cdsync    0+  c=  c  edc  -     -
	//   msf       c+  3=  f  edc  eccp  eccq
	//   mode      f+  1= 10  edc  eccp  eccq
	//   data     10+800=810  edc  eccp  eccq
	//   edc     810+  4=814  =    eccp  eccq
	//   pad     814+  8=81c       eccp  eccq
	//   eccp    81c+ ac=8c8       =     eccq
	//   eccq    8c8+ 68=930             =
	public function mode1( string &$bin ) : string
	{
		if ( strlen($bin) !== 0x930 )
			tool::error('not enough data != 930', strlen($bin));

		// calc edc
		$sub = tool::substr($bin, 0, 0x810);
		$edc = $this->edc($sub);
		$sub = tool::substr($bin, 0x810, 4);
		if ( $edc !== $sub )
		{
			tool::trace('edc not match', bin2hex($edc), bin2hex($sub));
			return '';
		}

		// calc ecc
		$bin  = substr($bin, 12);
		$eccp = tool::substr($bin, 0x81c-12, 0xac);
		$eccq = tool::substr($bin, 0x8c8-12, 0x68);
		$ecc  = $this->ecc($bin);
		if ( $ecc[0] !== $eccp )
		{
			tool::trace('eccp not match', bin2hex($ecc[0]), bin2hex($eccp));
			return '';
		}
		if ( $ecc[1] !== $eccq )
		{
			tool::trace('eccq not match', bin2hex($ecc[1]), bin2hex($eccq));
			return '';
		}

		return tool::substr($bin, 0, 0x800);
	}

	// mode2/920 , ecc(0)
	//   subh      0+  8=  8  edc  eccp  eccq
	//   data      8+800=808  edc  eccp  eccq
	//   edc     808+  4=80c  =    eccp  eccq
	//   eccp    80c+ ac=8b8       =     eccq
	//   eccq    8b8+ 68=920             =
	public function mode2( string &$bin ) : string
	{
		if ( strlen($bin) !== 0x920 )
			tool::error('not enough data != 920', strlen($bin));

		// extra check for mode2
		$subh1 = substr($bin, 0, 4);
		$subh2 = substr($bin, 4, 4);
		if ( $subh1 !== $subh2 )
			tool::error('subheader not match', bin2hex($subh1), bin2hex($subh2));

		// sub-header
		//   0  interleave
		//   1  channel number
		//   2  submode
		//      08  Data  Sector *
		//      04  Audio Sector *
		//      02  Video Sector *
		//          * mutual exclusive
		//   3  coding info
		//      for submode audio sector only
		$mode = ord( $subh1[2] );
		if ( ($mode & 8) === 0 )
		{
			tool::trace('not data sector', bin2hex($subh1));
			return '';
		}

		// calc edc
		$sub = tool::substr($bin, 0, 0x808);
		$edc = $this->edc($sub);
		$sub = tool::substr($bin, 0x808, 4);
		if ( $edc !== $sub )
		{
			tool::trace('edc not match', bin2hex($edc), bin2hex($sub));
			return '';
		}

		// calc ecc
		$eccp = tool::substr($bin, 0x80c, 0xac);
		$eccq = tool::substr($bin, 0x8b8, 0x68);
		$ecc  = $this->ecc($bin);
		if ( $ecc[0] !== $eccp )
		{
			tool::trace('eccp not match', bin2hex($ecc[0]), bin2hex($eccp));
			return '';
		}
		if ( $ecc[1] !== $eccq )
		{
			tool::trace('eccq not match', bin2hex($ecc[1]), bin2hex($eccq));
			return '';
		}

		return tool::substr($bin, 8, 0x800);
	}
} // class ecc
