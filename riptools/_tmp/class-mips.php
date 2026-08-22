<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-mips');

$mips = new mips_asm;

$asm = ['lbu', 'v0', '70', 'a0'];
$enc = $mips->enc($asm);
printf("%s = %x\n", implode(' ',$asm), $enc);

$asm = 0x90820070;
$dec = $mips->dec($asm);
printf("%x = %s\n", $dec, implode(' ',$asm));
//////////////////////////////
$asm ='
	lbu   v0, 70(a0) = 70 -- 82 90
	andi  v1, v0, 7f = 7f -- 43 30
	sll   v0, v1, 1  = 40 10 03 --
	jr    ra
	addu  v0, v0, v1 = 21 10 43 --
';
$enc = $mips->encode($asm, false);
foreach ( $enc as $k => $v )
{
	$e = $mips->dec($v);
	printf("%x : %8x = %s\n", $k, $v, implode(' ',$e));
}
//////////////////////////////
$asm =
	"\x70\x00\x82\x90"."\x7f\x00\x43\x30".
	"\x40\x10\x03\x00"."\x08\x00\xe0\x03"
	"\x21\x10\x43\x00";
$dec = $mips->decode($asm);
foreach ( $dec as $k => $v )
{
	$e = $mips->enc($v);
	printf("%x : %8x = %s\n", $k, $e, implode(' ',$v));
}

/*
88c1d44  lbu   v0, 70(a0) = 70 -- 82 90
88c1d48  andi  v1, v0, 7f = 7f -- 43 30
88c1d4c  sll   v0, v1, 1  = 40 10 03 --
88c1d50  jr    ra
88c1d54  addu  v0, v0, v1 = 21 10 43 --
*/
