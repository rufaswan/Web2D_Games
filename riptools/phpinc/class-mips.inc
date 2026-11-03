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

define('BIT5' , (1 <<  5)-1);
define('BIT6' , (1 <<  6)-1);
define('BIT10', (1 << 10)-1);
define('BIT25', (1 << 25)-1);
define('BIT26', (1 << 26)-1);

class mips_asm
{
	// mixed $str
	public static function is_none( $str ) : bool
	{
		if ( empty($str)     )  return true;
		if ( $str === '-'    )  return true;
		if ( $str === 'none' )  return true;
		return false;
	}

	public static function is_branch( string $str ) : bool
	{
		$s = substr($str, 0, 3);
		switch ( $s )
		{
			case 'beq':  case 'bne':
			case 'blt':  case 'ble':
			case 'bgt':  case 'bge':
				return true;
		} // switch ( $s )
		return false;
	}
	//////////////////////////////
	// reg is 5-bit = 00-1f
	private $reg  = [
		'zr' , 'at' , 'v0' , 'v1' , // 00
		'a0' , 'a1' , 'a2' , 'a3' , // 04
		't0' , 't1' , 't2' , 't3' , // 08
		't4' , 't5' , 't6' , 't7' , // 0c
		's0' , 's1' , 's2' , 's3' , // 10
		's4' , 's5' , 's6' , 's7' , // 14
		't8' , 't9' , 'k0' , 'k1' , // 18
		'gp' , 'sp' , 'fp' , 'ra' , // 1c
	];

	// opcode is 6-bit = 00-3f
	private $opcode = [
		'get'  => 'opcode' ,
		'list' => [
			// 00-03
			[
				'get'  => 'func' ,
				'list' => []
			] ,
			[
				'get'  => 'rt' ,
				'list' => []
			] ,
			0,0 ,

			// 04-3f
			0,0,0,0 , 0,0,0,0 , 0,0,0,0 ,
			0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0 ,
			0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0 ,
			0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0 ,
		]
	];

	private $hi = 0;
	private $lo = 0;

	// mixed $mvar
	private function opjson( &$mvar, array &$json ) : void
	{
		// mvar   json
		// none   entry = copy ent
		// none   list  = copy list
		// entry  entry = copy ent
		// entry  list  = copy list
		// list   entry = copy ent
		// list   list  = update list
		$none = [__CLASS__, 'is_none'];
		if ( isset($json['list']) )
		{
			// if update or copy
			if ( isset($mvar['list']) )
			{
				$mvar['get'] = $json['get'];
				goto update_list;
			}
			else
				goto init_list;
		}
		else
			goto copy_entry;

copy_entry:
		$asm = [ $json[0] ];
		if ( ! $none($json[1]) )
		{
			$tmp = preg_split('|[^0-9a-zA-Z]+|', $json[1]);
			while ( ! empty($tmp) )
			{
				$s = array_shift($tmp);
				if ( ! empty($s) )
					$asm[] = $s;
			} // while ( ! empty($tmp) );
		}
		$mvar = $asm;
		return;

init_list:
		$mvar = [
			'get'  => $json['get'],
			'list' => [],
		];

		$cnt = 0;
		switch ( $json['get'] )
		{
			case 'opcode':
			case 'func':
				$cnt = 1 << 6;
				break;
			case 'rs':
			case 'rt':
			case 'rd':
			case 'sa':
				$cnt = 1 << 5;
				break;
			default:
				tool::error('unknown json get type', $json['get']);
				return;
		} // switch ( $json['get'] )

		while ( $cnt > 0 )
		{
			$mvar['list'][] = 0;
			$cnt--;
		} // while ( $cnt > 0 )

		goto update_list;
		return;

update_list:
		$func = [$this, __FUNCTION__];
		foreach ( $json['list'] as $jk => $jv )
		{
			if ( $none($jk) )
				continue;
			$jkk = hexdec($jk);
			$func($mvar['list'][$jkk], $jv);
		} // foreach ( $dst['list'] as $dk => $dv )

		return;
	}

	private function loadjson( string $mips ) : bool
	{
		$path = sprintf('%s/file/%s.json', TOOL_BASE, $mips);
		$file = file_get_contents($path);
		if ( empty($file) )
			return false;
		$json = json_decode($file, true);
		if ( empty($json) )
			return false;

		tool::trace(__FUNCTION__, $mips);
		$func = [$this, __FUNCTION__];
		if ( isset($json['req']) && ! empty($json['req']) )
			$func( $json['req'] );

		$this->opjson($this->opcode, $json);
		return true;
	}

	public function __construct( string $mips )
	{
		$r = $this->loadjson($mips);
		if ( ! $r )
			tool::error('failed to loadjson()', $mips);

		//print_r($this->opcode);
	}
	//////////////////////////////
	private function bin_get( string $t ) : int
	{
		//   1111 11 | 11 111 | 1 1111 |        |        |         |
		//   fedc ba | 98 765 | 4 3210 | fedc b | a98 76 | 54 3210 |
		// R op      | rs     | rt     | rd     | sa     | func    |
		// I op      | rs     | rt     | i16                       |
		// J op      | i26                                         |
		switch ( $t )
		{
			case 'opcode':  return ($this->hi >> 10) & BIT6;
			case 'rs'    :  return ($this->hi >>  5) & BIT5;
			case 'rt'    :  return ($this->hi >>  0) & BIT5;
			case 'rd'    :  return ($this->lo >> 11) & BIT5;
			case 'sa'    :  return ($this->lo >>  6) & BIT5;
			case 'func'  :  return ($this->lo >>  0) & BIT6;

			case 'u16':
				return $this->lo;
			case 'i16':
				return tool::sign($this->lo, 16);
			case 'i26':
				$i32 = ($this->hi << 16) | $this->lo;
				return $i32 & BIT26;
			case 'i25':
				$i32 = ($this->hi << 16) | $this->lo;
				return $i32 & BIT25;
		} // switch ( $t )

		return -1;
	}

	private function asm_decode( string &$bin ) : array
	{
		$this->hi = (ord($bin[0]) << 8) | ord($bin[1]);
		$this->lo = (ord($bin[2]) << 8) | ord($bin[3]);

		$cur = &$this->opcode;
		while (1)
		{
			if ( isset($cur['get']) )
			{
				$op = $this->bin_get($cur['get']);
				$cur = &$cur['list'][$op];
			}
			else
				break;
		} // while (1)

		if ( empty($cur) )
			return [ sprintf('%04x%04x', $this->hi, $this->lo) ];

		$asm = [ $cur[0] ];
		$len = count($cur);
		for ( $i=1; $i < $len; $i++ )
		{
			$op = $this->bin_get($cur[$i]);

			// rs rt rd => v0 v1 a0 a1 a2 ... sp
			if ( $cur[$i][0] === 'r' )
			{
				$op = $this->reg[$op];
				$asm[] = $op;
				continue;
			}

			// sa imm = +/- hex number
			if ( $op < 0 )
				$asm[] = sprintf('-%x', -$op);
			else
				$asm[] = sprintf( '%x',  $op);
		} // for ( $i=1; $i < $len; $i++ )
		return $asm;
	}

	public function decode( string $bin, bool $le=false ) : array
	{
		// in  = \x70\x00\x82\x90\x7f\x00\x43\x30
		// out = [
		//   [ lbu  , v0 , 70 , a0 ] ,
		//   [ andi , v1 , v0 , 7f ] ,
		// ]
		$len = strlen($bin) & ~3;
		$asm = [];
		for ( $i=0; $i < $len; $i += 4 )
		{
			$s = substr($bin, $i, 4);
			if ( $le )
				$s = strrev($s);
			$asm[] = $this->asm_decode($s);
		} // for ( $i=0; $i < $len; $i += 4 )
		return $asm;
	}
	//////////////////////////////
	private function bin_set( array &$mips ) : void
	{
		//   1111 11 | 11 111 | 1 1111 |        |        |         |
		//   fedc ba | 98 765 | 4 3210 | fedc b | a98 76 | 54 3210 |
		// R op      | rs     | rt     | rd     | sa     | func    |
		// I op      | rs     | rt     | i16                       |
		// J op      | i26                                         |
		$this->hi = 0;
		$this->lo = 0;
		foreach ( $mips as $mk => $mv )
		{
			switch ( $mk )
			{
				case 'opcode': $this->hi |= ($mv & BIT6) << 10; break;
				case 'rs'    : $this->hi |= ($mv & BIT5) <<  5; break;
				case 'rt'    : $this->hi |= ($mv & BIT5) <<  0; break;
				case 'rd'    : $this->lo |= ($mv & BIT5) << 11; break;
				case 'sa'    : $this->lo |= ($mv & BIT5) <<  6; break;
				case 'func'  : $this->lo |= ($mv & BIT6) <<  0; break;

				case 'u16':
				case 'i16':
					while ( $mv < 0 )
						$mv += (1 << 16);
					$this->lo = $mv & BIT16;
					break;
				case 'i26':
					$mv &= BIT26;
					$this->lo = $mv & BIT16;
					$this->hi = $mv >> 16;
					break;
				case 'i25':
					$mv &= BIT25;
					$this->lo = $mv & BIT16;
					$this->hi = $mv >> 16;
					break;
			} // switch ( $mk )
		} // foreach ( $mips as $mk => $mv )
	}

	private function op_get( string $op, array &$cur, array $mips ) : array
	{
		if ( isset($cur['get']) )
		{
			$func = [$this, __FUNCTION__];
			$key  = $cur['get'];
			foreach ( $cur['list'] as $ck => $cv )
			{
				if ( empty($cv) )
					continue;
				$r = $func($op, $cv, $mips);
				if ( ! empty($r) )
				{
					$r[$key] = $ck;
					return $r;
				}
			} // foreach ( $cur['list'] as $ck => $cv )
			return [];
		}

		if ( $op === $cur[0] )
		{
			$mips['asm'] = $cur;
			return $mips;
		}
		else
			return [];
	}

	private function asm_encode( array &$token ) : string
	{
		$op = array_shift($token);
		$mips = $this->op_get($op, $this->opcode, []);
		if ( empty($mips) )
			return '';

		$cnt = count($mips['asm']);
		for ( $i=1; $i < $cnt; $i++ )
		{
			$key = $mips['asm'][$i];
			$val = array_shift($token);
			if ( $key[0] === 'r' )
				$val = array_search($val, $this->reg);
			else
				$val = hexdec($val);

			$mips[$key] = $val;
		} // for ( $i=0; $i < $cnt; $i++ )

		$this->bin_set($mips);
		$bin = '';
		$bin .= chr( $this->hi >> 8 );
		$bin .= chr( $this->hi & BIT8 );
		$bin .= chr( $this->lo >> 8 );
		$bin .= chr( $this->lo & BIT8 );
		return $bin;
	}

	public function encode( string $asm, bool $le=false ) : string
	{
		// in  = lbu v0 70(a0)  andi v1 v0 7f
		// out = \x70\x00\x82\x90\x7f\x00\x43\x30
		$tmp = preg_split('|[^0-9a-zA-Z]+|', strtolower($asm));
		$token = [];
		while ( ! empty($tmp) )
		{
			$s = array_shift($tmp);
			if ( ! empty($s) )
				$token[] = $s;
		} // while ( ! empty($tmp) )

		$bin = '';
		while ( ! empty($token) )
		{
			$s = $this->asm_encode($token);
			if ( $le )
				$s = strrev($s);
			$bin .= $s;
		}
		return $bin;
	}
/*
	private function asm_dec( array &$op ) : void
	{
		// 0 4 5 2 0 20 => add v0 a0 a1
		$cnt = count($op);
		$asm = 0;
		switch ( $cnt )
		{
			case 2:
				foreach ( $this->opcode as $opv )
				{
					if ( $opv['bin'][0] === $op[0] )
					{
						$asm = $opv['asm'];
						break;
					}
				} // foreach ( $this->opcode as $opv )
				if ( empty($asm) )
					return;
				$asm[1] = dechex($op[1]);

				$op = $asm;
				return;

			case 4:
				foreach ( $this->opcode as $opv )
				{
					if ( $op[0] === 1 )
					{
						// 1  rt
						if ( $opv['bin'][0] === $op[0] && $opv['bin'][1] === $op[2] )
						{
							$asm = $opv['asm'];
							break;
						}
					}
					else
					{
						// 03-3f
						if ( $opv['bin'][0] === $op[0] )
						{
							$asm = $opv['asm'];
							break;
						}
					}
				} // foreach ( $this->opcode as $opv )
				if ( empty($asm) )
					return;

				$c = count($asm);
				for ( $i=1; $i < $c; $i++ )
				{
					if ( $asm[$i] === 'rs' )
						$asm[$i] = $this->reg[ $op[1] ];
					if ( $asm[$i] === 'rt' )
						$asm[$i] = $this->reg[ $op[2] ];
					if ( $asm[$i] === 'imm' )
						$asm[$i] = dechex($op[3]);
				} // for ( $i=1; $i < $c; $i++ )
				$op = $asm;
				return;

			case 6:
				foreach ( $this->opcode as $opv )
				{
					if ( $opv['bin'][0] !== 0 )
						continue;

					if ( $opv['bin'][1] === $op[5] )
					{
						$asm = $opv['asm'];
						break;
					}
				} // foreach ( $this->opcode as $opv )
				if ( empty($asm) )
					return;

				$c = count($asm);
				for ( $i=1; $i < $c; $i++ )
				{
					if ( $asm[$i] === 'rs' )
						$asm[$i] = $this->reg[ $op[1] ];
					if ( $asm[$i] === 'rt' )
						$asm[$i] = $this->reg[ $op[2] ];
					if ( $asm[$i] === 'rd' )
						$asm[$i] = $this->reg[ $op[3] ];
					if ( $asm[$i] === 'sa' )
						$asm[$i] = dechex($op[4]);
				} // for ( $i=1; $i < $c; $i++ )
				$op = $asm;
				return;
		} // switch ( $cnt )
	}

	private function op_dec( string $s ) : array
	{
		//   1111 11 | 11 111 | 1 1111 |        |        |         |
		//   fedc ba | 98 765 | 4 3210 | fedc b | a98 76 | 54 3210 |
		// R op      | rs     | rt     | rd     | sa     | func    |
		// I op      | rs     | rt     | imme                      |
		// J op      | imme                                        |
		$upper = (ord($s[0]) << 8) | ord($s[1]);
		$lower = (ord($s[2]) << 8) | ord($s[3]);

		$op = ($upper >> 10) & BIT6;
		if ( $op === 0 )
		{
			$asm = [
				$op,
				($upper >>  5) & BIT5, // rs
				($upper >>  0) & BIT5, // rt
				($lower >> 11) & BIT5, // rd
				($lower >>  6) & BIT5, // sa
				($lower >>  0) & BIT6, // func
			];
			$this->asm_dec($asm);
			return $asm;
		}
		if ( $op === 2 || $op === 3 )
		{
			$asm = [
				$op,
				(($upper & BIT10) << 16) | $lower, // imm
			];
			$this->asm_dec($asm);
			return $asm;
		}
		$asm = [
			$op,
			($upper >> 5) & BIT5, // rs
			($upper >> 0) & BIT5, // rt
			$lower, // imm
		];
		$this->asm_dec($asm);
 		return $asm;

	}

	public function decode( string $asm, bool $le=false ) : array
	{
		if ( preg_match('|[^0-9a-fA-F]+|', $asm) < 1 )
			$asm = hex2bin($asm);

		$len = strlen($asm) & ~3;
		$res = [];
		for ( $i=0; $i < $len; $i += 4 )
		{
			$sub = substr($asm, $i, 4);
			if ( $le )
				$sub = strrev($sub);
			$res[] = $this->op_dec($sub);
		}
		return $res;
	}
	//////////////////////////////
	private function asm_reg_enc( string &$reg ) : int
	{
		foreach ( $this->reg as $k => $v )
		{
			if ( $reg === $v )
				return $k;
		}
		return -1;
	}

	private function asm_enc( array &$asm ) : void
	{
		// add v0 a0 a1 => 0 4 5 2 0 20
		$bin = 0;
		foreach ( $this->opcode as $opv )
		{
			if ( $opv['asm'][0] === $asm[0] )
			{
				$bin = $opv;
				break;
			}
		} // foreach ( $this->opcode as $opv )
		if ( empty($bin) )
			return;
		$cnt = count($asm);
		if ( $cnt !== count($bin['asm']) )
			return;

		switch ( $bin['bin'][0] )
		{
			case 0:
				$op = [0,0,0,0,0,0];
				$op[5] = $bin['bin'][1]; // func

				for ( $i=1; $i < $cnt; $i++ )
				{
					if ( $bin['asm'][$i] === 'rs' )
						$op[1] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'rt' )
						$op[2] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'rd' )
						$op[3] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'sa' )
						$op[4] = hexdec($asm[$i]);
				} // for ( $i=1; $i < $cnt; $i++ )
				$asm = $op;
				return;
			case 1:
				$op = [1,0,0,0];
				$op[2] = $bin['bin'][1]; // rt

				for ( $i=1; $i < $cnt; $i++ )
				{
					if ( $bin['asm'][$i] === 'rs' )
						$op[1] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'imm' )
						$op[3] = hexdec($asm[$i]);
				} // for ( $i=1; $i < $cnt; $i++ )
				$asm = $op;
				return;
			case 2:
			case 3:
				$op = [0,0];
				$op[0] = $bin['bin'][0];
				$op[1] = hexdec($asm[1]); // imm
				$asm = $op;
				return;
			default:
				$op = [0,0,0,0];
				$op[0] = $bin['bin'][0];

				for ( $i=1; $i < $cnt; $i++ )
				{
					if ( $bin['asm'][$i] === 'rs' )
						$op[1] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'rt' )
						$op[2] = self::asm_reg_enc($asm[$i]);
					if ( $bin['asm'][$i] === 'imm' )
						$op[3] = hexdec($asm[$i]);
				} // for ( $i=1; $i < $cnt; $i++ )
				$asm = $op;
				return;
		} // switch ( $bin['bin'][0] )
	}

	private function op_enc( array $asm, bool $le ) : string
	{
		$this->asm_enc($asm);
		//   1111 11 | 11 111 | 1 1111 |        |        |         |
		//   fedc ba | 98 765 | 4 3210 | fedc b | a98 76 | 54 3210 |
		// R op      | rs     | rt     | rd     | sa     | func    |
		// I op      | rs     | rt     | imme                      |
		// J op      | imme                                        |
		$cnt = count($asm);
		$upper = 0;
		$lower = 0;
		if ( $cnt === 6 )
		{
			$upper |= ($asm[0] << 10); // op
			$upper |= ($asm[1] <<  5); // rs
			$upper |= ($asm[2] <<  0); // rt
			$lower |= ($asm[3] << 11); // rd
			$lower |= ($asm[4] <<  6); // sa
			$lower |= ($asm[5] <<  0); // func
		}
		if ( $cnt === 4 )
		{
			$upper |= ($asm[0] << 10); // op
			$upper |= ($asm[1] <<  5); // rs
			$upper |= ($asm[2] <<  0); // rt
			$lower = $asm[3]; // imm
		}
		if ( $cnt === 2 )
		{
			$upper |= ($asm[0] << 10); // op
			$upper |= ($asm[1] >> 16); // imm
			$lower = $asm[1] & BIT16; // imm
		}

		$op = [
			$upper >> 8,
			$upper & BIT8,
			$lower >> 8,
			$lower & BIT8,
		];
		if ( $le )
			return chr($op[3]) . chr($op[2]) . chr($op[1]) . chr($op[0]);
		else
			return chr($op[0]) . chr($op[1]) . chr($op[2]) . chr($op[3]);
	}
	public function encode( string $asm, bool $le=false ) : array
	{
		$token = preg_split('|[^0-9a-zA-Z]+|', trim($asm));
		$asm = [];
		while ( ! empty($token) )
		{
			// lbu  v0, 4(a0) => lbu v0 4 a0 ''
			if ( empty($token[0]) )
			{
				array_shift($token);
				continue;
			}

			$res = 0;
			foreach ( $this->opcode as $opv )
			{
				if ( $opv['asm'][0] === $token[0] )
					$res = $opv['asm'];
			}
			if ( empty($res) )
				return [];

			$asm[] = array_splice($token, 0, count($res));
		} // while ( ! empty($token) )

		foreach ( $asm as $k => $v )
			$asm[$k] = $this->op_enc($v, $le);
		return $asm;
	}
*/
} // class mips_asm

/*
MIPS 1 compare to MIPS 4 PSX PS2 PSP

op list
	# mips 1
	00  *func  *rt    j     jal    beq   bne  blez  bgez
	08  addi   addiu  slti  sltiu  andi  ori  xori  lui
	10  *c0    *c1    *c2   *c3    -     -    -     -
	18  -      -      -     -      -     -    -     -
	20  lb     lh     lwl   lw     lbu   lhu  lwr   lwu
	28  sb     sh     swl   sw     -     -    swr   -
	30  -      lwc1   lwc2  lwc3   -     -    -     -
	38  -      swc1   swc2  swc3   -     -    -     -

	# mips 4
	00  =      =       =    =     =     =     =      =
	08  =      =       =    =     =     =     =      =
	10  =      =       =    *c1x  beql  bnel  blezl  bgezl
	18  daddi  daddiu  ldl  ldr   =     =     =      =
	20  =      =       =    =     =     =     =      =
	28  =      =       =    =     sdl   sdr   =      =
	30  ll     =       =    pref  lld   ldc1  ldc2   ld
	38  sc     =       =    -     scd   sdc1  sdc2   sd

	# psx
	00  =     =  =  =  =  =  =  =
	08  =     =  =  =  =  =  =  =
	10  =     =  =  =  =  =  =  =
	18  =     =  =  =  =  =  =  =
	20  =     =  =  =  =  =  =  -
	28  =     =  =  =  =  =  =  =
	30  lwc0  =  =  =  =  =  =  =
	38  swc0  =  =  =  =  =  =  =

	# ps2
	00  =      =       =    =     =     =     =      =
	08  =      =       =    =     =     =     =      =
	10  =      =       =    -     beql  bnel  blezl  bgezl
	18  daddi  daddiu  ldl  ldr   *mmi  =     lq     sq
	20  =      =       =    =     =     =     =      =
	28  =      =       =    =     =     =     =      cache
	30  =      =       -    pref  =     =     lqc2   ld
	38  =      =       -    -     =     =     sqc2   sd

	CONFLICTS
	13  cop3  cop1x
	30  lwc0  ll
	38  swc0  sc
	33  lwc3  pref
	36  ldc2  lqc2
	3e  sdc2  sqc2


op=00 , func list
	# mips 1
	00  sll   -      srl   sra   sllv    -      srlv  srav
	08  jr    jalr   -     -     system  break  -     -
	10  mfhi  mthi   mflo  mtlo  -       -      -     -
	18  mult  multu  div   divu  -       -      -     -
	20  add   addu   sub   subu  and     or     xor   nor
	28  -     -      slt   sltu  -       -      -     -
	30  -     -      -     -     -       -      -     -
	38  -     -      -     -     -       -      -     -

	# mips 4
	00  =     movc1  =     =     =       =       =       =
	08  =     =      movz  movn  =       =       =       sync
	10  =     =      =     =     dsllv   =       dsrlv   dsrav
	18  =     =      =     =     dmult   dmultu  ddiv    ddivu
	20  =     =      =     =     =       =       =       =
	28  =     =      =     =     dadd    daddu   dsub    dsubu
	30  tge   tgeu   tlt   tltu  teq     =       tne     =
	38  dsll  =      dsrl  dsra  dsll32  =       dsrl32  dsra32

	# psx = mips 1

	# ps2
	00  =     =      =     =     =       =       =       =
	08  =     =      movz  movn  =       =       =       sync
	10  =     =      =     =     dsllv   =       dsrlv   dsrav
	18  =     =      =     =     =       =       =       =
	20  =     =      =     =     =       =       =       =
	28  mfsa  mtsa   =     =     dadd    daddu   dsub    dsubu
	30  tge   tgeu   tlt   tltu  teq     =       tne     =
	38  dsll  =      dsrl  dsra  dsll32  =       dsrl32  dsra32

	CONFLICTS
	-

op=01 ,  rt list
	# mips 1
	00  bltz    bgez    -  -  -  -  -  -
	08  -       -       -  -  -  -  -  -
	10  bltzal  bgezal  -  -  -  -  -  -
	18  -       -       -  -  -  -  -  -

	# mips 4
	00  =     =      bltzl    bgezl    =     =  =     =
	08  tgei  tgeiu  tlti     tltiu    teqi  =  tnei  =
	10  =     =      bltzall  bgezall  =     =  =     =
	18  =     =      =        =        =     =  =     =

	# psx = mips 1

	# ps2
	00  =      =      bltzl    bgezl    =     =  =     =
	08  tgei   tgeiu  tlti     tltiu    teqi  =  tnei  =
	10  =      =      bltzall  bgezall  =     =  =     =
	18  mtsab  mtsah  =        =        =     =  =     =

	CONFLICTS
	-

op   = (asm >> 1a) & 3f
rs   = (asm >> 15) & 1f
rt   = (asm >> 10) & 1f
rd   = (asm >>  b) & 1f
sa   = (asm >>  6) & 1f
func = (asm >>  0) & 3f

ps2  op=10 , rs list              = cop0
ps2  op=10 , rs=08    , rt   list = bc0
ps2  op=10 , rs=10    , func list = tbl/exception
ps2  op=11 , rs list              = cop1
ps2  op=11 , rs=08    , rt   list = bc1
ps2  op=11 , rs=10    , func list = fpu.s
ps2  op=11 , rs=14    , func list = fpu.w
ps2  op=12 , rs list              = cop2
ps2  op=12 , rs=08    , rt   list = bc2
ps2  op=12 , rs=10-1f , func list = special
ps2  op=12 , rs=10-1f , func=3c-3f
	flo = func & 3
	fhi = (asm >> 6) & 3f
	(fhi << 2) | flo list
ps2  op=1c , func list                         = mmi
ps2  op=1c , func=08    , (asm >> 6) & 3f list = mmi0
ps2  op=1c , func=09    , (asm >> 6) & 3f list = mmi1
ps2  op=1c , func=28    , (asm >> 6) & 3f list = mmi2
ps2  op=1c , func=29    , (asm >> 6) & 3f list = mmi3
*/
/*
		// 5-bit = 00-1f , 0-31
		$this->reg = preg_split('|[\s]+|',  trim($this->reg));

		$opcode = explode("\n", trim($this->opcode));
		$op = [];
		while ( ! empty($opcode) )
		{
			$s = array_shift($opcode);
			$p = strpos($s, '//');
			if ( $p !== false )
				$s = substr($s, 0, $p);

			$s = trim($s);
			if ( empty($s) )
				continue;
			$a = preg_split('|[\s]+|', $s);
			$a[0] = hexdec($a[0]);
			if ( $a[0] === 0 || $a[0] === 1 )
			{
				$a[1] = hexdec($a[1]);
				$t = [
					'bin' => array_splice($a, 0, 3),
					'asm' => $a,
				];
			}
			else
			{
				$t = [
					'bin' => array_splice($a, 0, 2),
					'asm' => $a,
				];
			}

			// resolve =asm
			$op_arg = $t['asm'][1];
			if ( $op_arg[0] === '=' )
			{
				$op_arg = substr($op_arg, 1);
				$op_cmd = $t['asm'][0];
				foreach ( $op as $opv )
				{
					if ( $opv['asm'][0] !== $op_arg )
						continue;
					$op_arg = $opv['asm'];
					$op_arg[0] = $op_cmd;
					$t['asm'] = $op_arg;
					break;
				} // foreach ( $op as $opv )
			}
			$op[] = $t;
		} // while ( ! empty($opcode) )

		$this->opcode = $op;
*/
