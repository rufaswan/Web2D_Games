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
	private function ord( string $str ) : int
	{
		$b0 = ord( $str[0] );
		$b1 = ord( $str[1] );
		$b2 = ord( $str[2] );
		$b3 = ord( $str[3] );
		return $b0 | ($b1 << 8) | ($b1 << 16) | ($b1 << 24);
	}
	private function chr( int $int ) : string
	{
		$b0 = ($int >>  0) & BIT8;
		$b1 = ($int >>  8) & BIT8;
		$b2 = ($int >> 16) & BIT8;
		$b3 = ($int >> 24) & BIT8;
		return chr($b0) . chr($b1) . chr($b2) . chr($b3);
	}

	private function int2op( int $int ) : array
	{
		//   1111 11 | 11 111 | 1 1111 |        |        |         |
		//   fedc ba | 98 765 | 4 3210 | fedc b | a98 76 | 54 3210 |
		// R op      | rs     | rt     | rd     | sa     | func    |
		// I op      | rs     | rt     | imme                      |
		// J op      | imme                                        |
		$op = ($int >> 0x1a) & 3f;
		if ( $op === 0 )
		{
			$rs   = ($int >> 0x15) & 0x1f;
			$rt   = ($int >> 0x10) & 0x1f;
			$rd   = ($int >> 0xb ) & 0x1f;
			$sa   = ($int >> 6   ) & 0x1f;
			$func = ($int >> 0   ) & 0x3f;
			return [$op,$rs,$rt,$rd,$sa,$func];
		}
		if ( $op === 2 || $op === 3 )
		{
			$imm = $int & 0x03ffffff;
			return [$op,$imm];
		}
		$rs  = ($int >> 0x15) & 0x1f;
 		$rt  = ($int >> 0x10) & 0x1f;
 		$imm = ($int >> 0   ) & 0xffff;
 		return [$op,$rs,$rt,$imm];

	}

	private function op2int( array $op ) : int
	{
		$cnt = count($op);
		$int = 0;
		if ( $cnt === 6 )
		{
			$int |= ($op[0] << 0x1a);
			$int |= ($op[1] << 0x15);
			$int |= ($op[2] << 0x10);
			$int |= ($op[3] << 0xb );
			$int |= ($op[4] << 6   );
			$int |=  $op[5];
			return $int;
		}
		if ( $cnt === 4 )
		{
			$int |= ($op[0] << 0x1a);
			$int |= ($op[1] << 0x15);
			$int |= ($op[2] << 0x10);
			$int |=  $op[3];
			return $int;
		}
		if ( $cnt === 2 )
		{
			$int |= ($op[0] << 0x1a);
			$int |=  $op[1];
			return $int;
		}
		return 0;
	}
	//////////////////////////////
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


		////////////////////////////////////////
		// A-180 A 8.7
		// 6-bit = 00-3f , 0-63
		// bit   = 26 to 31
		//       >> 1a & 3f
		// all default value (rs/rt/rd/sa) is zero(0)
		$op = '
		';
		$this->op = preg_split('|[\s]+|',  trim($op));
		////////////////////////////////////////
		// 6-bit = 00-3f , 0-63
		// bit   = 0 to 5
		//       & 3f
		// all default value (rs/rt/rd/sa) is zero(0)
		$func = '
		';
		$this->func = preg_split('|[\s]+|',  trim($func));
		////////////////////////////////////////
		// 5-bit = 00-1f , 0-31
		// bit   = 16 to 20 (same as rt)
		//       >> 10 & 1f
		$rt = '
		';
		$this->rt = preg_split('|[\s]+|',  trim($rt));
		////////////////////////////////////////
	}
	//////////////////////////////
	public function dec( int $int ) : array
	{
		// check for R I J type
		$op = ($int >> 0x1a) & 0x3f;
		if ( $op > 3 )
		{
			//  6  1a-20  I type
			//  5  15-19  rs
			//  5  10-14  rt
			// 10   0-f   immediate
			$rs  = ($int >> 0x15) & 0x1f;
			$rt  = ($int >> 0x10) & 0x1f;
			$imm = $int & 0xffff;
			return [$this->op[$op],
				$this->reg[$rs],
				$this->reg[$rt],
				$imm
			];
		}
		if ( $op > 1 )
		{
			//  6  1a-20  J type
			// 1a   0-19  immediate
			$imm = $int & 0x3ffffff;
			return [$this->op[$op],
				$imm << 2
			];
		}

		// 6  1a-20  R type
		// 5  15-19  rs
		// 5  10-14  rt
		// 5   b-f   rd
		// 5   6-a   sa
		// 6   0-5   func
		$rs   = ($int >> 0x15) & 0x1f;
		$rt   = ($int >> 0x10) & 0x1f;
		$rd   = ($int >> 0xb ) & 0x1f;
		$sa   = ($int >> 6   ) & 0x1f;
		$func = ($int >> 0   ) & 0x3f;
		if ( $op === 1 )
		{
			return [$this->rt[$rt],
				$this->reg[$rs],
				$this->reg[$rd],
				$sa
			];
		}
		else // $op === 0
		{
			return [$this->func[$func],
				$this->reg[$rs],
				$this->reg[$rt],
				$this->reg[$rd],
				$sa
			];
		}
	}

	public function enc( array $asm, int &$pos=0 ) : int
	{
		return 0;
	}
	//////////////////////////////
	public function decode( string $asm ) : array
	{
		$len = strlen($asm) & ~3;
		$dec = [];
		for ( $i=0; $i < $len; $i += 4 )
		{
			$s = substr($asm, $i, 4);
			$s = $this->ord($s);
			$dec[] = $this->dec($s);
		}
		return $dec;
	}

	public function encode( string $asm, bool $is_chr=false ) : array
	{
		$token = preg_split('|[^a-z0-9]+|', trim($asm));
		$cnt   = count($token);
		$pos   = 0;
		$enc   = [];
		while ( $pos < $cnt )
			$enc[] = $this->enc($token, $pos);
		return $enc;
	}
} // class mips_asm

// MIPS opcode decoding and encoding
/*
 *   3332 22 | 22 222 | 2 2111 | 1111 1 | 11     |         |
 *   2109 87 | 65 432 | 1 0987 | 6543 2 | 109 87 | 65 4321 |
 * R op      | rs     | rt     | rd     | sa     | func    |
 * I op      | rs     | rt     | imme                      |
 * J op      | imme                                        |
 */
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
