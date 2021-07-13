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
 *   cmdpack - ecm
 *     Neill Corlett
 *   The PlayStation 1 Video (STR) Format v1.00
 *     Michael Sabin
 */
class ECC
{
	private $ecc_f_lut;
	private $ecc_b_lut;
	private $edc_lut;

	function __construct()
	{
		$this->ecc_f_lut = array();
		$this->ecc_b_lut = array();
		$this->edc_lut   = array();

		for ( $i=0; $i < 0x100; $i++ )
		{
			$a = ($i & 0x80) ? 0x11d : 0;
			$j = ($i << 1) ^ $a;

			$edc = $i;
			for ( $k=0; $k < 8; $k++ )
			{
				$a = ($edc & 1) ? 0xd8018001 : 0;
				$edc = ($edc >> 1) ^ $a;
			}

			$this->ecc_f_lut[$i]      = $j;
			$this->ecc_b_lut[$i ^ $j] = $i;
			$this->edc_lut  [$i]      = $edc;
		}
		return;
	}

	function ecc_pq( &$sect, $c_major, $c_minor, $i_major, $i_minor, $off )
	{
		$size = $c_major * $c_minor;
		for ( $major = 0; $major < $c_major; $major++ )
		{
			$index = ($major >> 1) * $i_major + ($major & 1);
			$ecc_a = 0;
			$ecc_b = 0;

			for ( $minor = 0; $minor < $c_minor; $minor++ )
			{
				$b = ( $index < 4 ) ? 0 : ord( $sect[$index-4] );

				$index += $i_minor;
				if ( $index >= $size )
					$index -= $size;

				$ecc_a ^= $b;
				$ecc_b ^= $b;
				$ecc_a  = $this->ecc_f_lut[$ecc_a];
			} // for ( $minor = 0; $minor < $c_minor; $minor++ )

			$b     = $this->ecc_f_lut[$ecc_a] ^ $ecc_b;
			$ecc_a = $this->ecc_b_lut[$b];

			$sect[$off + $major           ] = chr($ecc_a         );
			$sect[$off + $major + $c_major] = chr($ecc_a ^ $ecc_b);
		} // for ( $major = 0; $major < $c_major; $major++ )
		return;
	}

	function edc_update( &$sect, $off )
	{
		$edc = 0;
		for ( $i = 0x10; $i < $off; $i++ )
		{
			$a = (ord( $sect[$i] ) ^ $edc) ^ BIT8;
			$b = $this->edc_lut[$a];
			$edc = ($edc >> 8) ^ $b;
		}
		str_update($sect, $off, chrint($edc,4));
		return;
	}
	//////////////////////////////
	function hex2bin( $s )
	{
		$s = str_replace(' ', '', $s);
		return hex2bin($s);
	}

	function lba2str( $lba )
	{
		// add 2 seconds
		$lba += (75 * 2);

		$fra = $lba % 75; $lba = (int)($lba / 75);
		$sec = $lba % 60; $lba = (int)($lba / 60);
		$min = $lba;

		$s = '';
		$s .= ( $min < 10 ) ? "0$min" : "$min";
		$s .= ( $sec < 10 ) ? "0$sec" : "$sec";
		$s .= ( $fra < 10 ) ? "0$fra" : "$fra";
		$s .= '02';
		return hex2bin($s);
	}

	function fix930( $sect )
	{
		if ( substr($sect,0x10,4) !== substr($sect,0x14,4) )
			return php_error("XA subheader not identical");

		// 00 00 08 00  PS-X EXE
		// 00 00 09 00  \x01CD001
		// 00 00 20 00
		// 00 00 89 00  \xffCD001
		// 01 01 42 80  STR [60 01 01 80]
		// 01 01 64 05
		$sh3= ord( $sect[0x12] );
		if ( ($sh & 0x0e) == 0 ) // padding sector
			return $sect;
		else
		if ( $sh & 0x08 ) // data sector
		{
			$this->edc_update($sect, 0x818);
			$this->ecc_pq($sect, 86, 24,  2, 86, 0x81c); // p
			$this->ecc_pq($sect, 52, 43, 86, 88, 0x8c8); // q
		}
		else // video or audio sector
			$this->edc_update($sect, 0x92c);
		return $sect;
	}
	//////////////////////////////
	function sect930( $sect, $lba=0 )
	{
		$len = strlen($sect);
		$h = '';
		$f = '';
		switch ( $len )
		{
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

			case 0x800:
				$h .= $this->hex2bin('00 ff ff ff  ff ff ff ff  ff ff ff 00');
				$h .= $this->lba2str($lba);
				$h .= $this->hex2bin('00 00 08 00  00 00 08 00');
				$f  = str_repeat(ZERO, 0x118);
				break;
			case 0x808:
				$h .= $this->hex2bin('00 ff ff ff  ff ff ff ff  ff ff ff 00');
				$h .= $this->lba2str($lba);
				$f  = str_repeat(ZERO, 0x118);
				break;
			case 0x918:
				$h .= $this->hex2bin('00 ff ff ff  ff ff ff ff  ff ff ff 00');
				$h .= $this->lba2str($lba);
				$h .= $this->hex2bin('01 01 42 80  01 01 42 80');
				$f  = str_repeat(ZERO, 4);
				break;
			case 0x91c:
				$h .= $this->hex2bin('00 ff ff ff  ff ff ff ff  ff ff ff 00');
				$h .= $this->lba2str($lba);
				$f  = str_repeat(ZERO, 4);
				break;
			case 0x930:
				break;
			default:
				return php_error("sector is not 800 or 930");
		} // switch ( $len )

		return $this->fix930($h.$sect.$f , $lba);
	}
}
