<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-mips');

$mips = new mips_asm;

$asm = '90820070';
$dec = $mips->decode($asm);
printf("%s = %s\n", implode(' ',$dec[0]), $asm);

$asm =
	"\x90\x82\x00\x70"."\x30\x43\x00\x7f".
	"\x00\x03\x10\x40"."\x03\xe0\x00\x08".
	"\x00\x43\x10\x21";
$dec = $mips->decode($asm);
foreach ( $dec as $k => $v )
	printf("%x : %s\n", $k, implode(' ',$v));
//////////////////////////////
$asm = 'lbu  v0, 70(a0)';
$enc = $mips->encode($asm);
printf("%s = %s\n", $asm, bin2hex($enc[0]));

$asm ='
	lbu   v0, 70(a0)
	andi  v1, v0, 7f
	sll   v0, v1, 1
	jr    ra
	addu  v0, v0, v1
';
$enc = $mips->encode($asm);
foreach ( $enc as $k => $v )
	printf("%x : %s\n", $k, bin2hex($v));

/*
88c1d44  lbu   v0, 70(a0) = 70 -- 82 90
88c1d48  andi  v1, v0, 7f = 7f -- 43 30
88c1d4c  sll   v0, v1, 1  = 40 10 03 --
88c1d50  jr    ra         = 08 -- e0 03
88c1d54  addu  v0, v0, v1 = 21 10 43 --
*/
