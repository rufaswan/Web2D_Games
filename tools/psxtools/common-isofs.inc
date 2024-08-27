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

class filesystem_handler
{
	private $block_size;
	private  $data_size;

	public function __construct( $block_size, $data_size=-1, $head_size=0 )
	{
		$this->block_size = 0;
		$this->data_size  = 0;
		$this->head_size  = 0;
		if ( $block_size < 1 )
			return self::error(__METHOD__, 'block size = %x', $block_size);

		if ( $data_size > $block_size )
			$data_size = $block_size;
		if ( $data_size < 1 )
			$data_size = $block_size;

		$this->block_size = $block_size;
		$this->data_size  = $data_size;

		if ( $head_size > 0 )
			$this->head_size = $head_size;
		return;
	}

	private function is_callback( $callback )
	{
		if ( empty($callback) )
			return false;
		if ( ! function_exists($callback) )
			return false;
		return true;
	}

	private function lba2pos( $lba )
	{
		$lba *= $this->block_size;
		$lba += $this->head_size;
		return $lba;
	}

	private function data_size_block( $data, $lba=0 )
	{
		while ( $data > 0 )
		{
			$data -= $this->data_size;
			$lba++;
		}
		return $lba;
	}
	//////////////////////////////
	private $alloc_list;
	private $alloc_len;

	public function new_alloc()
	{
		$this->alloc_list = array();
		$this->alloc_len  = 0;
		return;
	}

	public function append_alloc( $byte, $note )
	{
		if ( $byte < 0 )
			return;
		$this->write_alloc($this->alloc_len, $byte, $note);
		return;
	}

	public function add_alloc( $lba, $byte, $note )
	{
		if ( $byte < 0 )
			return;
		$blk = $this->data_size_block($byte);
		$end = $lba + $blk;

		$last = $end;
		foreach( $this->alloc_list as $ak => $av )
		{
			if ( $last < $av['ed'] )
				$last = $av['ed'];

			if ( $lba      >= $av['ed'] )  continue;
			if ( $av['st'] >= $end      )  continue;
			return;
		} // foreach( $this->alloc_list as $ak =>$av )

		$this->alloc_list[] = array(
			'st'   => $lba,
			'ed'   => $end,
			'byte' => $byte,
			'note' => $note,
		);
		$this->alloc_len = $last;
		return;
	}

	public function list_alloc()
	{
		return $this->alloc_list;
	}
	//////////////////////////////
	public function str_read_block( &$str, $lba, $block, $ecc_callback='' )
	{
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $block, $ecc_callback);
		$byte = $block * $this->data_size;
		return $this->str_read($str, $lba, $byte, $ecc_callback);
	}

	public function fp_read_block( $fp, $lba, $block, $ecc_callback='' )
	{
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $block, $ecc_callback);
		$byte = $block * $this->data_size;
		return $this->fp_read($fp, $lba, $byte, $ecc_callback);
	}

	public function str_read( &$str, $lba, $byte, $ecc_callback='' )
	{
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $byte, $ecc_callback);
		$end = $this->data_size_block($byte, $lba);
		$end = $this->lba2pos($end);
		if ( strlen($str) < $end )
			return self::error(__METHOD__, '%s : not enough data');

		$pos = $this->lba2pos($lba);
		$bin = '';
		while ( $byte > 0 )
		{
			$sub = substr($str, $pos, $this->block_size);

			// callback to remove ECC/EDC from data
			$rem = ( $byte > $this->data_size ) ? $this->data_size : $byte;
			if ( $this->is_callback($ecc_callback) )
				$sub = $ecc_callback($sub, $rem);

			$pos  += $this->block_size;
			$byte -= $this->data_size;
			$bin  .= $sub;
		} // while ( $byte > 0 )
		return $bin;
	}

	public function fp_read( $fp, $lba, $byte, $ecc_callback='' )
	{
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $byte, $ecc_callback);
		$end = $this->data_size_block($byte, $lba);
		$end = $this->lba2pos($end);
		fseek($fp, 0, SEEK_END);
		if ( ftell($fp) < $end )
			return self::error(__METHOD__, '%s : not enough data');

		$pos = $this->lba2pos($lba);
		$bin = '';
		while ( $byte > 0 )
		{
			fseek($fp, $pos, SEEK_SET);
			$sub = fread($fp, $this->block_size);

			// callback to remove ECC/EDC from data
			$rem = ( $byte > $this->data_size ) ? $this->data_size : $byte;
			if ( $this->is_callback($ecc_callback) )
				$sub = $ecc_callback($sub, $rem);

			$pos  += $this->block_size;
			$byte -= $this->data_size;
			$bin  .= $sub;
		} // while ( $byte > 0 )
		return $bin;
	}

	public function str_write( &$str, $lba, &$bin, $ecc_callback='' )
	{
		$byte = strlen($bin);
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $byte, $ecc_callback);
		$end  = $this->data_size_block($byte, $lba);
		$end  = $this->lba2pos($end);
		while ( strlen($str) < $end )
			$str .= "\x00";

		$pos = $this->lba2pos($lba);
		for ( $i=0; $i < $byte; $i += $this->data_size )
		{
			$sub = substr($bin, $i, $this->data_size);

			// callback to add ECC/EDC to data
			if ( $this->is_callback($ecc_callback) )
				$sub = $ecc_callback($sub);

			while ( strlen($sub) < $this->block_size )
				$sub .= "\x00";
			for ( $j=0; $j < $this->block_size; $j++ )
				$str[$pos+$j] = $sub[$j];

			$pos += $this->block_size;
		} // for ( $i=0; $i < $byte; $i += $this->data_size )
		return;
	}

	public function fp_write( $fp, $lba, &$bin, $ecc_callback='' )
	{
		$byte = strlen($bin);
		trace("== %s( %x , %x , %s )\n", __METHOD__, $lba, $byte, $ecc_callback);
		$pos = $this->lba2pos($lba);
		fseek($fp, 0, SEEK_END);
		$pad = $pos - ftell($fp);
		if ( $pad > 0 )
			fwrite($fp, str_repeat("\x00", $pad));

		for ( $i=0; $i < $byte; $i += $this->data_size )
		{
			$sub = substr($bin, $i, $this->data_size);

			// callback to add ECC/EDC to data
			if ( $this->is_callback($ecc_callback) )
				$sub = $ecc_callback($sub);

			while ( strlen($sub) < $this->block_size )
				$sub .= "\x00";
			fseek ($fp, $pos, SEEK_SET);
			fwrite($fp, $sub);

			$pos += $this->block_size;
		} // for ( $i=0; $i < $byte; $i += $this->data_size )
		return;
	}
	//////////////////////////////
	static public function array_repeat( $cnt, $val=0, $inc=0 )
	{
		$arr = array();
		while ( $cmt > 0 )
		{
			$cnt--;
			$arr[]  = $val;
			$val   += $inc;
		}
		return $arr;
	}

	static public function warn()
	{
		$args = func_get_args();
		$met = array_shift($args);
		$fmt = $met . '() : ' . array_shift($args);
		$err = vsprintf($fmt, $args);
		return trigger_error($err, E_USER_WARNING);
	}
	static public function error()
	{
		$args = func_get_args();
		$met = array_shift($args);
		$fmt = $met . '() : ' . array_shift($args);
		$err = vsprintf($fmt, $args);
		return trigger_error($err, E_USER_ERROR);
	}
} // class filesystem_handler

//////////////////////////////
// ps2 memory card
//
class ps2mc_ecc
{
	private $table;

	private function rex_sma02_table()
	{
		$key = array(
			0x00 , 0x11 , 0x22 , 0x33 ,
			0x44 , 0x55 , 0x66 , 0x77 ,
			0x87 , 0x96 , 0xa5 , 0xb4 ,
			0xc3 , 0xd2 , 0xe1 , 0xf0 ,
		);
		//  0  8  9  1
		// 10  2  3 11
		// 12  4  5 13
		//  6 14 15  7
		$idx = array(
			 0, 8, 9, 1 , 10, 2, 3,11 , 11, 3, 2,10 ,  1, 9, 8, 0 , // 00
			12, 4, 5,13 ,  6,14,15, 7 ,  7,15,14, 6 , 13, 5, 4,12 , // 10
			13, 5, 4,12 ,  7,15,14, 6 ,  6,14,15, 7 , 12, 4, 5,13 , // 20
			 1, 9, 8, 0 , 11, 3, 2,10 , 10, 2, 3,11 ,  0, 8, 9, 1 , // 30

			14, 6, 7,15 ,  4,12,13, 5 ,  5,13,12, 4 , 15, 7, 6,14 , // 40
			 2,10,11, 3 ,  8, 0, 1, 9 ,  9, 1, 0, 8 ,  3,11,10, 2 , // 50
			 3,11,10, 2 ,  9, 1, 0, 8 ,  8, 0, 1, 9 ,  2,10,11, 3 , // 60
			15, 7, 6,14 ,  5,13,12, 4 ,  4,12,13, 5 , 14, 6, 7,15 , // 70

			15, 7, 6,14 ,  5,13,12, 4 ,  4,12,13, 5 , 14, 6, 7,15 , // 80
			 3,11,10, 2 ,  9, 1, 0, 8 ,  8, 0, 1, 9 ,  2,10,11, 3 , // 90
			 2,10,11, 3 ,  8, 0, 1, 9 ,  9, 1, 0, 8 ,  3,11,10, 2 , // a0
			14, 6, 7,15 ,  4,12,13, 5 ,  5,13,12, 4 , 15, 7, 6,14 , // b0

			 1, 9, 8, 0 , 11, 3, 2,10 , 10, 2, 3,11 ,  0, 8, 9, 1 , // c0
			13, 5, 4,12 ,  7,15,14, 6 ,  6,14,15, 7 , 12, 4, 5,13 , // d0
			12, 4, 5,13 ,  6,14,15, 7 ,  7,15,14, 6 , 13, 5, 4,12 , // e0
			 0, 8, 9, 1 , 10, 2, 3,11 , 11, 3, 2,10 ,  1, 9, 8, 0 , // f0
		);

		$table = array();
		foreach ( $idx as $k => $v )
			$table[$k] = $key[$v];
		return $table;
	}

	private function calc_ecc( &$data )
	{
		if ( strlen($data) !== 0x80 )
			return '';

		$ecc = array(0,0,0);
		for ( $i=0; $i < 0x80; $i++ )
		{
			$b1 = ord($data[$i]);
			$b2 = $this->table[$b1];
			$ecc[0] ^= $b2;
			if ( $b2 & 0x80 )
			{
				$ecc[1] ^= ~$i;
				$ecc[2] ^=  $i;
			}
		} // for ( $i=0; $i < 0x80; $i++ )

		$c = '';
		$c .= chr(~$ecc[0] & 0x77);
		$c .= chr(~$ecc[1] & 0x7f);
		$c .= chr(~$ecc[2] & 0x7f);
		return $c;
	}
	//////////////////////////////
	public function __construct()
	{
		// PS2 MC erase = set the block to all 1s
		//   while programming change 1s to 0s
		$this->table = $this->rex_sma02_table();
		return;
	}

	public function read( &$enc )
	{
		$len = strlen($enc);
		if ( ($len % 0x210) !== 0 )
			return filesystem_handler::error(__METHOD__, 'data not 210 aligned [%x]', strlen($enc));

		$dec = '';
		$err = 0;
		$dmy = str_repeat("\xff", 0x80);
		for ( $i=0; $i < $len; $i += 0x210 )
		{
			$sub = substr($enc, $i, 0x200);
			$ecc = substr($enc, $i + 0x200, 12);

			for ( $j=0; $j < 4; $j++ )
			{
				$jsub = substr($sub, $j * 0x80, 0x80);
				$jecc = substr($ecc, $j *    3,    3);

				$calc = $this->calc_ecc($jsub);
				if ( $jecc === $calc )
					$dec .= $jsub;
				else
				{
					$dec .= $dmy;
					$err++;
				}
			} // for ( $j=0; $j < 4; $j++ )
		} // for ( $i=0; $i < $len; $i += 0x200 )

		if ( $err > 0 )
			filesystem_handler::error(__METHOD__, 'data has %x error', $err);
		return $dec;
	}

	public function write( &$dec )
	{
		$rem = strlen($dec) & 0x1ff;
		if ( $rem > 0 )
			$dec .= str_repeat("\x00", 0x200-$rem);

		$len = strlen($dec);
		$enc = '';
		for ( $i=0; $i < $len; $i += 0x200 )
		{
			$sub = substr($dec, $i, 0x200);
			$ecc = '';
			for ( $j=0; $j < 4; $j++ )
			{
				$jsub = substr($sub, $j * 0x80, 0x80);
				$ecc .= $this->calc_ecc($jsub);
			} // for ( $j=0; $j < 4; $j++ )

			$ecc .= "\x00\x00\x00\x00";
			$enc .= $sub . $ecc;
		} // for ( $i=0; $i < $len; $i += 0x200 )
		return $enc;
	}
} // class ps2mc_ecc

//////////////////////////////
// iso/bin
//
// Special Thanks
//   cmdpack - ecm
//     Neill Corlett
//   The PlayStation 1 Video (STR) Format v1.00
//     Michael Sabin
class isobin_ecc
{
	private $cdsync;
	private $edc_table;
	private $ecc_f_table;
	private $ecc_b_table;

	public function __construct()
	{
		$this->cdsync = str_repeat("\xff", 12);
		$this->cdsync[ 0] = "\x00";
		$this->cdsync[11] = "\x00";

		$this->edc_table = filesystem_handler::array_repeat(0x100);
		for ( $i=0; $i < 0x100; $i++ )
		{
			$edc = $i;
			for ( $k=0; $k < 8; $k++ )
			{
				$a   = ($edc &  1) ? 0xd8018001 : 0;
				$edc = ($edc >> 1) ^ $a;
			}
			$this->edc_table[$i] = $edc;
		} // for ( $i=0; $i < 0x100; $i++ )

		$this->ecc_f_table = filesystem_handler::array_repeat(0x100);
		$this->ecc_b_table = filesystem_handler::array_repeat(0x100);
		for ( $i=0; $i < 0x100; $i++ )
		{
			$a = ($i & 0x80) ? 0x11d : 0;
			$j = ($i << 1) ^ $a;

			$this->ecc_f_table[$i     ] = $j;
			$this->ecc_b_table[$i ^ $j] = $i;
		} // for ( $i=0; $i < 0x100; $i++ )
		return;
	}
	//////////////////////////////
	private function edc_calc( &$sub )
	{
		$edc = 0;
		$len = strlen($sub);
		for ( $i=0; $i < $len; $i++ )
		{
			$ord = ord( $sub[$i] );
			$a   = ($edc ^ $ord) & 0xff;
			$lut = $this->edc_table[$a];
			$edc = ($edc >> 8) ^ $lut;
		}

		$bin = '';
		for ( $i=0; $i < 4; $i++ )
		{
			$ord = $edc & 0xff;
				$edc >>= 8;
			$bin .= chr($ord);
		}
		return $bin;
	}
	//////////////////////////////
	public function cdread_edc( &$str, $sectsize )
	{
		$len = strlen($str);
		$dec = '';
		for ( $i=0; $i < $len; $i += $sectsize )
		{
			$sync = substr($str, $i, 12);
			if ( $sync !== $this->cdsync )
				return 0;

			switch ( $str[$i+15] )
			{
				case "\x01":
					$sub = substr($str, $i, 0x810); // 0-810
					$edc1 = $this->edc_calc($sub);
					$edc2 = substr($str, 0x810, 4); // 810-814
					if ( $edc1 !== $edc2 )
						return 0;
					$dec .= substr($str, $i + 0x10, 0x800);
					break;

				case "\x02":
					$head1 = substr($str, $i + 0x10, 4);
					$head2 = substr($str, $i + 0x14, 4);
					if ( $head1 !== $head2 )
						return 0;

					$mode = ord( $head1[2] );
					if ( $mode & 8 ) // data
					{
						$sub  = substr($str, $i + 0x10, 0x808); // 10-818
						$edc1 = $this->edc_calc($sub);
						$edc2 = substr($str, 0x818, 4); // 818-81c
						if ( $edc1 !== $edc2 )
							return 0;
						$dec .= substr($str, $i + 0x18, 0x800);
					}
					else // 4=audio , 2=video
					{
						$sub  = substr($str, $i + 0x10, 0x91c); // 10-92c
						$edc1 = $this->edc_calc($sub);
						$edc2 = substr($str, 0x92c, 4); // 92c-930
						if ( $edc1 !== $edc2 )
							return 0;
						$dec .= substr($str, $i + 0x18, 0x914);
					}
					break;

				default:
					return 0;
			} // switch ( $str[$i+15] )
		} // for ( $i=0; $i < $len; $i +- $sectsize )
		return $dec;
	}
} // class isobin_ecc

/*
class error_correction_code
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
			return php_error('XA subheader not identical');

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
				return php_error('sector is not 800 or 930');
		} // switch ( $len )

		return $this->fix930($h.$sect.$f , $lba);
	}
} // class error_correction_code
*/
//////////////////////////////
function isofile_r( $isop, &$sub, &$list, $par, $skip=0 )
{
	$func = __FUNCTION__;
	$size = strlen($sub);
	for ( $i=0; $i < $size; $i += 0x800 )
	{
		$j = 0;
		while ( $j < 0x800 )
		{
			$p = $i + $j;
			$len1 = ord( $sub[$p+ 0] );
			if ( $len1 == 0 ) // entry on next sector
				goto endj;

			// file = min 3 = 'A;1'
			// dir  = min 1 = 'A'
			$len2 = ord( $sub[$p+32] );
			$lfn  = substr($sub, $p+33, $len2);
			if ( $lfn === "\x00" || $lfn === "\x01" ) // for  . and ..
				goto endp;

			$lfn = strtolower($lfn);
			$lba = str2int($sub, $p+ 2, 4);
			$lsz = str2int($sub, $p+10, 4);

			$lfg = ord( $sub[$p+25] );
			if ( $lfg & 2 ) // is_dir
			{
				$s = fp2str($isop, $skip+$lba*0x800, $lsz);
				$func($isop, $s, $list, "$par/$lfn", $skip);
				goto endp;
			}
			else // is_file
			{
				$s = substr($lfn, 0, strrpos($lfn, ';'));
				$ent = array(
					'lba'  => $lba,
					'size' => $lsz,
					'file' => "$par/$s",
				);

				$pxa = $p + 0x21 + $len2;
				if ( $pxa & 1 )
					$pxa++;
				if ( ($p+$len1) > ($pxa+8) && substr($sub,$pxa+6,2) === 'XA' )
				{
					// 0d 55 XA = file
					// 8d 55 XA = dir
					// 25 55 XA = XA/STR file
					$ent['xa'] = ordint( $sub[$pxa+5] . $sub[$pxa+4] );
				}
				$list[] = $ent;
				goto endp;
			}

endj:
			$j += 0x800;
			continue;
endp:
			$j += $len1;
			continue;

		} // while ( $j < 0x800 )
	} // for ( $i=0; $i < $size; $i += 0x800 )
	return;
}

function lsiso_r( $isop, $skip=0 )
{
	$s = fp2str($isop, $skip+0x8000, 0x800);
	if ( substr($s,0,6) !== "\x01CD001" )
		return '';

	$s = substr($s, 0x9c); // root dir
	$lba = str2int($s,  2, 4);
	$siz = str2int($s, 10, 4);
	$s = fp2str($isop, $skip+$lba*0x800, $siz);

	$list = array();
	isofile_r($isop, $s, $list, '', $skip);
	return $list;
}
//////////////////////////////
// cd mage
// -                m1/2048  m1/2352  m2/2336  m2/2352
// \x01CD001        8000     9310     9208     9318
// sector           800      930      920      930
// header    size
// -cdsync   +  c   -        0        -        0
// -min      +  3   -        c        -        c
// -mode     +  1   -        f        -        f
// -subhead  +  8   -        -        0        10
// -data     +800   0        10       8        18
// -edc      +  4   -        810      808      818
// -pad      +  8   -        814      -        -
// -ecc      +114   -        81c      80c      81c
//
//////////////////////////////
// frame = min:sec:frame binary format
//       = 3 byte BIN , as 00 02 00
//
// min   = frame in string format
//       = 6 char STR , as "000200"
//       = 8 char STR , as "00:02:00"
//
// lba   = int format
//       = 3 byte BIN , as 17 00 00
//       - INT        , as 0x17
//
// NOTE : 4 byte lba starts from offset 0x800000000
//        or (1 << (11+24))
//        or 32 GB (not used in CD/DVD)
//
// mkisofs
//   -full-iso9660-filenames = 31 char
//   -max-iso9660-filenames  = 37 char (violates ISO9660)
//
// https://opensource.apple.com/source/xnu/xnu-344.32/bsd/isofs/cd9660/iso.h.auto.html
// https://problemkaputt.de/psxspx-cdrom-iso-file-and-directory-descriptors.htm
// XA flags
//   0001  Owner Read    (usually 1)
//   0004  Owner Execute (usually 1)
//   0010  Group Read    (usually 1)
//   0040  Group Execute (usually 1)
//   0100  World Read    (usually 1)
//   0400  World Execute (usually 1)
//   0800  IS_MODE2        (0=MODE1 or CD-DA, 1=MODE2)
//   1000  IS_MODE2_FORM2  (0=FORM1, 1=FORM2)
//   2000  IS_INTERLEAVED  (0=No, 1=Yes...?) (by file and/or channel?)
//   4000  IS_CDDA         (0=Data or ADPCM, 1=CD-DA Audio Track)
//   8000  IS_DIRECTORY    (0=File or CD-DA, 1=Directory Record)

function lba2frame( $lba )
{
	// mfhi( v * 0x1b4e81b5 ) == v * 0.106666 =  8 / 75
	// mfhi( v * 0x88888889 ) == v * 0.533333 = 32 / 60
	// mfhi( v * 0x66666667 ) == v * 0.4      =  4 / 10

	// frames starts at 00:02:00
	$lba   += (75 * 2);
	$size  = array(60 , 60 , 75);
	$frame = ZERO . ZERO . ZERO;
	$i = 3;
	while ( $i > 0 )
	{
		$i--;
		if ( $lba < 1 )
			continue;

		$div = (int)($lba / $size[$i]);
		$rem = $lba - ($div * $size[$i]);

		// in 4-bit hex
		$hi = (int)($rem * 0.1);
		$lo = $rem - ($hi * 10);
		$hex = ($hi << 4) | $lo;

		$lba = $div;
		$frame[$i] = chr($hex);
	} // while ( $i > 0 )

	return $frame;
}

function frame2lba( $frame )
{
	$m = ord( $frame[0] );
	$s = ord( $frame[1] );
	$f = ord( $frame[2] );

	$mm = ($m >> 4) * 0xa + ($m & 0xf);
	$ss = ($s >> 4) * 0xa + ($s & 0xf);
	$ff = ($f >> 4) * 0xa + ($f & 0xf);

	// frames starts at 00:02:00
	$lba = (($mm * 0x3c) + $ss) * 0x4b + $ff;
	return $lba - 0x96;
}

function printframe( $frame )
{
	$m = ord( $frame[0] );
	$s = ord( $frame[1] );
	$f = ord( $frame[2] );
	return sprintf('%2x:%2x:%2x', $m, $s, $f);
}

function isosearch( &$list, $fname )
{
	foreach ( $list as $v )
	{
		if ( stripos($v['file'], $fname) !== false )
			return $v;
	}
	return -1;
}

function isolba( &$list, $lba )
{
	foreach ( $list as $v )
	{
		if ( $v['lba'] === $lba )
			return $v;
	}
	return -1;
}
