<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-bitarray');

$asm =
	"\x70\x00\x82\x90"."\x7f\x00\x43\x30".
	"\x40\x10\x03\x00"."\x08\x00\xe0\x03".
	"\x21\x10\x43\x00";

$len = strlen($asm) & ~3;
for ( $i=0; $i < $len; $i += 4 )
{
	$p = $i * 8;
	$op = bitarray::get2($asm, $p + 26, 6);
	switch ( $op )
	{
		case 0:
			$rs = bitarray::get2($asm, $p + 21,  5);
			$rt = bitarray::get2($asm, $p + 16,  5);
			$rd = bitarray::get2($asm, $p + 11,  5);
			$sa = bitarray::get2($asm, $p +  6,  5);
			$fn = bitarray::get2($asm, $p +  0,  6);
			tool::trace($op, $rs, $rt, $rd, $sa, $fn);
			break;
		case 2:
		case 3:
			$im = bitarray::get2($asm, $p + 0, 26);
			tool::trace($op, $im);
			break;
		default:
			$rs = bitarray::get2($asm, $p + 21,  5);
			$rt = bitarray::get2($asm, $p + 16,  5);
			$im = bitarray::get2($asm, $p +  0, 16);
			tool::trace($op, $rs, $rt, $im);
			break;
	} // switch ( $op )
} // for ( $i=0; $i < $len; $i += 4 )

/*
88c1d44  lbu   v0, 70(a0) = 70 -- 82 90
88c1d48  andi  v1, v0, 7f = 7f -- 43 30
88c1d4c  sll   v0, v1, 1  = 40 10 03 --
88c1d50  jr    ra         = 08 -- e0 03
88c1d54  addu  v0, v0, v1 = 21 10 43 --
*/
