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

	public static function hexdec( string $hex ) : int
	{
		// skip last 4 bits
		// 8001abcd => 1abcd
		$pos = strlen($hex) - 7;
		if ( $pos > 0 )
			$hex = substr($hex, $pos, 7);

		// in 0 4 8 c
		$int = hexdec($hex) & ~3;
		return $int;
	}

	public static function print( array $asm ) : string
	{
		$cnt = count($asm);
		if ( $cnt === 0 )
			return '';

		// break
		if ( $cnt === 1 )
			return $asm[0];

		// op left-aligned
		$str = sprintf('%-8s  ', array_shift($asm));
		$arg = [];
		while ( ! empty($asm) )
		{
			$a = array_shift($asm);
			if ( $a === (int)$a )
			{
				if ( $a < 0 )
					$a = sprintf('-%x', -$a);
				else
					$a = sprintf( '%x',  $a);

				// if lbu  10(a0)
				if ( isset($asm[0]) )
				{
					$b = array_shift($asm);
					$arg[] = "$a($b)";
				}
				else
					$arg[] = $a;
			}
			else
				$arg[] = $a;
		} // while ( ! empty($asm) )

		$str .= implode(',', $arg);
		return $str;
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

			// sa imm
			$asm[] = $op;
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
	//////////////////////////////
	public static function jump_type( string $str ) : string
	{
		// to detect block ending opcode
		switch ( $str )
		{
			case 'j' :  case 'jal' : // i26
			case 'jr':  case 'jalr': // rs
				return 'jump';
			case 'syscall':
				return 'syscall';
			case 'break':
				return 'break';
		} // switch ( $str )

		$s = substr($str, 0, 3);
		switch ( $s )
		{
			case 'beq':  case 'bne': // ==  !=
			case 'blt':  case 'ble': // <   <=
			case 'bgt':  case 'bge': // >   >=
				return 'branch';
			case 'teq':  case 'tne': // ==  !=
			case 'tlt':  case 'tle': // <   <=
			case 'tgt':  case 'tge': // >   >=
				return 'trap';
		} // switch ( $s )

		return '';
	}

	public function jit( string &$bin, int $pos=0, bool $le=false ) : array
	{
		$len = strlen($bin) & ~3;
		$pos &= ~3;
		if ( $pos <  0    )  return [];
		if ( $pos >= $len )  return [];

		$jump = [__CLASS__, 'jump_type'];
		$asm  = [];
		while ( $pos < $len )
		{
			$s = substr($bin, $pos, 4);
				$pos += 4;
			if ( $le )
				$s = strrev($s);

			$op = $this->asm_decode($s);
			if ( ! empty( $jump($op[0]) ) )
				$len = $pos + 4; // delay slot

			$asm[] = $op;
		} // while ( $pos < $len )
		return $asm;
	}
	//////////////////////////////
	public function is_reg( string $str ) : bool
	{
		$r = array_search($str, $this->reg);
		return $r !== false;
	}
} // class mips_asm
