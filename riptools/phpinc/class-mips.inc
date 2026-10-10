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
		0  0  mip  sll      rd  rt  sa
		0  1  m4   movc1    -
		0  2  mip  srl      =sll
		0  3  mip  sra      =sll
		0  4  mip  sllv     rd  rt  rs
		//0  5  -
		0  6  mip  srlv     =sllv
		0  7  mip  srav     =sllv

		0  8  mip  jr       rs
		0  9  mip  jalr     rd  rs
		0  a  ps2  movz     rd  rs  rt
		0  b  ps2  movn     =movz
		0  c  mip  syscall  -
		0  d  mip  break    -
		//0  e  -
		0  f  ps2  sync     -

		0  10  mip  mfhi    rd
		0  11  mip  mthi    rs
		0  12  mip  mflo    =mfhi
		0  13  mip  mtlo    =mthi
		0  14  ps2  dsllv   =sllv
		//0  15  -
		0  16  ps2  dsrlv   =sllv
		0  17  ps2  dsrav   =sllv

		0  18  mip  mult    rs  rt
		0  19  mip  multu   =mult
		0  1a  mip  div     =mult
		0  1b  mip  divu    =mult
		0  1c  ps2  dmult   =mult
		0  1d  ps2  dmultu  =mult
		0  1e  ps2  ddiv    =mult
		0  1f  ps2  ddivu   =mult

		0  20  mip  add     rd  rs  rt
		0  21  mip  addu    =add
		0  22  mip  sub     =add
		0  23  mip  subu    =add
		0  24  mip  and     =add
		0  25  mip  or      =add
		0  26  mip  xor     =add
		0  27  mip  nor     =add

		0  28  ps2  mfsa    -
		0  29  ps2  mtsa    -
		0  2a  mip  slt     =add
		0  2b  mip  sltu    =add
		0  2c  ps2  dadd    =add
		0  2d  ps2  daddu   =add
		0  2e  ps2  dsub    =add
		0  2f  ps2  dsubu   =add

		0  30  ps2  tge     rs  rt
		0  31  ps2  tgeu    =tge
		0  32  ps2  tle     =tge
		0  33  ps2  tltu    =tge
		0  34  ps2  teq     =tge
		//0  35  -
		0  36  ps2  tne     =tge
		//0  37  -

		0  38  ps2  dsll    =sll
		//0  39  -
		0  3a  ps2  dsrl    =sll
		0  3b  ps2  dsra    =sll
		0  3c  ps2  dsll32  =sll
		//0  3d  -
		0  3e  ps2  dsrl32  =sll
		0  3f  ps2  dsra32  =sll

		1  0  mip  bltz      rs  imm
		1  1  mip  bgez      =bltz
		1  2  ps2  bltzl     =bltz
		1  3  ps2  bgezl     =bltz
		//1  4  -
		//1  5  -
		//1  6  -
		//1  7  -

		1  8  ps2  tgei      rs  imm
		1  9  ps2  tgeiu     =tgei
		1  a  ps2  tlti      =tgei
		1  b  ps2  tltiu     =tgei
		1  c  ps2  teqi      =tgei
		//1  d  -
		1  e  ps2  tnei      =tgei
		//1  f  -

		1  10  mip  bltzal   =bltz
		1  11  mip  bgezal   =bltz
		1  12  ps2  bltzall  =bltz
		1  13  ps2  bgezall  =bltz
		//1  14  -
		//1  15  -
		//1  16  -
		//1  17  -

		1  18  ps2  mtsab    -
		1  19  ps2  mtsah    -
		//1  1a  -
		//1  1b  -
		//1  1c  -
		//1  1d  -
		//1  1e  -
		//1  1f  -

		2  mip  j        imm
		3  mip  jal      =j
		4  mip  beq      rs  rt  imm
		5  mip  bne      =beq
		6  mip  blez     rs  imm
		7  mip  bgtz     =blez

		8  mip  addi     rt  rs  imm
		9  mip  addiu    =addi
		a  mip  slti     =addi
		b  mip  sltiu    =addi
		c  mip  andi     =addi
		d  mip  ori      =addi
		e  mip  xori     =addi
		f  mip  lui      rt  imm

		//10  mip  cop0
		//11  mip  cop1
		//12  mip  cop2
		//13  CONFLICT  mip=cop3  m4=cop1x
		14  ps2  beql    =beq
		15  ps2  bnel    =beq
		16  ps2  blezl   =blez
		17  ps2  bgtzl   =blez

		18  ps2  daddi   =addi
		19  ps2  daddiu  =addi
		1a  ps2  ldl     =lb
		1b  ps2  ldr     =lb
		//1c  ps2  mm1     -
		//1d  -
		1e  ps2  lq      =lb
		1f  ps2  sq      =lb

		20  mip  lb      rt  imm  rs
		21  mip  lh      =lb
		22  mip  lwl     =lb
		23  mip  lw      =lb
		24  mip  lbu     =lb
		25  mip  lhu     =lb
		26  mip  lwr     =lb
		27  mip  lwu     =lb

		28  mip  sb      rt  imm  rs
		29  mip  sh      =sb
		2a  mip  swl     =sb
		2b  mip  sw      =sb
		2c  m4   sdl     =sb
		2d  m4   sdr     =sb
		2e  mip  swr     =sb
		2f  ps2  cache   -

		//30  CONFLICT  psx=lwc0  m4=ll
		31  mip  lwc1    =lb
		32  mip  lwc2    =lb
		//33  CONFLICT  mip=lwc3  ps2/m4=pref
		34  m4   lld     =lb
		35  m4   ldc1    =lb
		//36  CONFLICT  m4=ldc2  ps2=lqc2
		37  ps2  ld      =lb

		//38  CONFLICT  psx=swc0  m4=sc
		39  mip  swc1    =sb
		3a  mip  swc2    =sb
		3b  psx  swc3    =sb
		3c  m4   scd     =sb
		3d  m4   sdc1    =sb
		//3e  CONFLICT  m4=sdc2  ps2=sqc2
		3f  ps2  sd      =sb
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
	13  c3    c1x
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
