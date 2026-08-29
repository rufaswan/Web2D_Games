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

class mips_asm
{
	public $reg  = <<<_REG
		zr  at  v0  v1
		a0  a1  a2  a3
		t0  t1  t2  t3
		t4  t5  t6  t7

		s0  s1  s2  s3
		s4  s5  s6  s7
		t8  t9  k0  k1
		gp  sp  fp  ra
_REG;

	public $opcode =<<<_OPCODE
		0  0  a  sll      rd  rt  sa
		//0  1  d  movc1
		0  2  a  srl      =sll
		0  3  a  sra      =sll
		0  4  a  sllv     rd  rt  rs
		//0  5  -
		0  6  a  srlv     =sllv
		0  7  a  srav     =sllv

		0  8  a  jr       rs
		0  9  a  jalr     rd  rs
		0  a  d  movz     rd  rs  rt
		0  b  d  movn     =movz
		0  c  a  syscall  -
		0  d  a  break    -
		//0  e  -
		0  f  b  sync     -

		0  10  a  mfhi    rd
		0  11  a  mthi    rs
		0  12  a  mflo    =mfhi
		0  13  a  mtlo    =mthi
		0  14  c  dsllv   =sllv
		//0  15  -
		0  16  c  dsrlv   =sllv
		0  17  c  dsrav   =sllv

		0  18  a  mult    rs  rt
		0  19  a  multu   =mult
		0  1a  a  div     =mult
		0  1b  a  divu    =mult
		0  1c  c  dmult   =mult
		0  1d  c  dmultu  =mult
		0  1e  c  ddiv    =mult
		0  1f  c  ddivu   =mult

		0  20  a  add     rd  rs  rt
		0  21  a  addu    =add
		0  22  a  sub     =add
		0  23  a  subu    =add
		0  24  a  and     =add
		0  25  a  or      =add
		0  26  a  xor     =add
		0  27  a  nor     =add
		//0  28  -
		//0  29  -
		0  2a  a  slt     =add
		0  2b  a  sltu    =add
		0  2c  c  dadd    =add
		0  2d  c  daddu   =add
		0  2e  c  dsub    =add
		0  2f  c  dsubu   =add

		0  30  b  tge     rs  rt
		0  31  b  tgeu    =tge
		0  32  b  tle     =tge
		0  33  b  tltu    =tge
		0  34  b  teq     =tge
		//0  35  -
		0  36  b  tne     =tge
		//0  37  -
		0  38  c  dsll    =sll
		//0  39  -
		0  3a  c  dsrl    =sll
		0  3b  c  dsra    =sll
		0  3c  c  dsll32  =sll
		//0  3d  -
		0  3e  c  dsrl32  =sll
		0  3f  c  dsra32  =sll

		1  0  a  bltz      rs  imm
		1  1  a  bgez      =bltz
		1  2  b  bltzl     =bltz
		1  3  b  bgezl     =bltz
		//1-4 to 1-7

		1  8  b  tgei      rs  imm
		1  9  b  tgeiu     =tgei
		1  a  b  tlti      =tgei
		1  b  b  tltiu     =tgei
		1  c  b  teqi      =tgei
		//1  d  -
		1  e  b  tnei      =tgei
		//1  f  -

		1  10  a  bltzal   =bltz
		1  11  a  bgezal   =bltz
		1  12  b  bltzall  =bltz
		1  13  b  bgezall  =bltz
		// 1-14 to 1-1f

		2  a  j        imm
		3  a  jal      =j
		4  a  beq      rs  rt  imm
		5  a  bne      =beq
		6  a  blez     rs  imm
		7  a  bgtz     =blez

		8  a  addi     rt  rs  imm
		9  a  addiu    =addi
		a  a  slti     =addi
		b  a  sltiu    =addi
		c  a  andi     =addi
		d  a  ori      =addi
		e  a  xori     =addi
		f  a  lui      rt  imm

		//10  cop0
		//11  cop1
		//12  cop2
		//13  d  cop3
		14  b  beql    =beq
		15  b  bnel    =beq
		16  b  blezl   =blez
		17  b  bgtzl   =blez

		18  c  daddi   =addi
		19  c  daddiu  =addi
		1a  c  ldl   =lb
		1b  c  ldr   =lb
		// 1c to 1f

		20  a  lb    rt  imm  rs
		21  a  lh    =lb
		22  a  lwl   =lb
		23  a  lw    =lb
		24  a  lbu   =lb
		25  a  lhu   =lb
		26  a  lwr   =lb
		27  c  lwu   =lb

		28  a  sb    rt  imm  rs
		29  a  sh    =sb
		2a  a  swl   =sb
		2b  a  sw    =sb
		2c  c  sdl   =sb
		2d  c  sdr   =sb
		2e  a  swr   =sb
		//2f  *

		30  b  ll    =lb
		31  a  lwc1  =lb
		32  a  lwc2  =lb
		33  d  pref  rt  imm  rs
		34  c  lld   =lb
		35  b  ldc1  =lb
		36  b  ldc2  =lb
		37  c  ld    =lb

		38  b  sc    =sb
		39  a  swc1  =sb
		3a  a  swc2  =sb
		//3b  -
		3c  c  scd   =sb
		3d  b  sdc1  =sb
		3e  b  sdc2  =sb
		3f  c  sd    =sb
_OPCODE;

	public function __construct()
	{
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
	}
	//////////////////////////////
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

		$op = ($upper >> 10) & 0x3f;
		if ( $op === 0 )
		{
			$asm = [
				$op,
				($upper >>  5) & 0x1f, // rs
				($upper >>  0) & 0x1f, // rt
				($lower >> 11) & 0x1f, // rd
				($lower >>  6) & 0x1f, // sa
				($lower >>  0) & 0x3f, // func
			];
			$this->asm_dec($asm);
			return $asm;
		}
		if ( $op === 2 || $op === 3 )
		{
			$asm = [
				$op,
				(($upper & 0x3ff) << 16) | $lower, // imm
			];
			$this->asm_dec($asm);
			return $asm;
		}
		$asm = [
			$op,
			($upper >> 5) & 0x1f, // rs
			($upper >> 0) & 0x1f, // rt
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
			$lower = $asm[1] & 0xffff; // imm
		}

		$op = [
			$upper >> 8,
			$upper & 0xff,
			$lower >> 8,
			$lower & 0xff,
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
} // class mips_asm

// MIPS opcode decoding and encoding
////////////////////////////////////////
// if op == 0      , use R-type
// if op == 2 or 3 , use J-type
//
// rs, rt, line  beq,bne
// rs, line      bltz,blez,bgtz
// rt, rs, imme  addi,slti,andi,ori,xori
// rt, imme      lui
// rt, imme(rs)  lb,lh,lw,sb,sh,sw
//
// bltz + rt = 0/bltz,1/bgez,16/bltzal,17/bgezal,~/beq
////////////////////////////////////////
// rs          jr,mthi,mtlo
// rs, rt      mult,div
// rd          mfhi,mflo
// rd, rs      jalr
// rd, rs, rt  add,sub,and,or,xor,nor,slt
// rd, rt, rs  sllv,srlv,srav
// rd, rt, sa  sll,srl,sra
////////////////////////////////////////
