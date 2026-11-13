<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-mips');

define('RAM_SIZE', 2 << 20); // 2 mb
define('RAM_MASK', RAM_SIZE-1);

$gp_mips  = new mips_asm('mips-1');
$gp_asm   = [];
$gp_label = [];
$gp_queue = [];

class psxwork {
	public $name = '';
	public $type = '';
	public $pos  = 0;
	public $case = 0;
}

// addiu  sp  sp  u16
//   27bd ffe8  func start
//   27bd   18  func end


function ram2asm( string $fname, string $name, string $pos ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )
		return;
	if ( strlen($file) !== RAM_SIZE )
		tool::error('invalid RAM size', strlen($file), RAM_SIZE);

	global $gp_queue;
	$w = new psxwork;
	$w->name = $name;
	$w->type = 'func';
	$w->pos  = (int)hexdec($pos);
	$gp_queue[] = $w;


	//global $gp_mips;
	//$asm = [];

}

echo "@usage: RAM_FILE  NAME  POS\n";

if ( $argc < 4 )  exit();
ram2asm($argv[1], $argv[2], $argv[3]);

/*
$mips = new mips_asm('mips-1');

$asm = "\x90\x82\x00\x70";
$dec = $mips->decode($asm, false);
printf("%s = %s\n", implode(' ',$dec[0]), bin2hex($asm));

$asm =
	"\x90\x82\x00\x70"."\x30\x43\x00\x7f".
	"\x00\x03\x10\x40"."\x03\xe0\x00\x08".
	"\x00\x43\x10\x21".
	"\xfc\x00\x00\x00";
$dec = $mips->decode($asm, false);
//$dec = $mips->jit($asm, 0, false);
foreach ( $dec as $k => $v )
	printf("%x : %s\n", $k, implode('  ',$v));
*/
