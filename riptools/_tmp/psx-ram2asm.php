<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-mips');

define('RAM_SIZE', 2 << 20); // 2 mb
define('RAM_MASK', RAM_SIZE-1);

class psxlabel {
	public $type = '';
	public $pos  = 0;
	public $name = '';
	public $case = 0;
	public function __construct( string $typeint $pos, string $name='', int $case=0 )
	{
		$this->type = $type;
		$this->pos  = $pos;
		$this->name = $name;
		$this->case = $case;
	}
}

function is_psx_func( string &$ram, int &$pos ) : bool
{
	tool::trace(__FUNCTION__, $pos);
	$jal = [];
	for ( $i=0; $i < RAM_SIZE; $i += 4 )
	{
		$bin = tool::ordstr($ram, $i, 4, true);
		$op  = ($bin >> 26) & BIT6;
		if ( $op !== 3 )
			continue;
		$imm = ($bin & BIT26) << 2;
		if ( $imm < RAM_SIZE )
			$jal[$imm] = 1;
	}
	tool::trace('found jal', count($jal));
	//foreach ( $jal as $jk => $jv )
		//tool::trace($jk, $jv);

	if ( isset($jal[$pos]) )
		return true;

	$bak = $pos;
	while ( $pos > 0x10000 ) // 80000000-80010000 is bios area
	{
		if ( isset($jal[$pos]) )
		{
			tool::notice('not a func', $bak, 'try', $pos);
			return true;
		}
		$pos -= 4;
	}

	tool::error('not a func', $bak);
	return false;
}

// addiu  sp  sp  u16
//   27bd ffe8  func start
//   27bd   18  func end
//////////////////////////////
function process_asm( string &$ram, array &$psxasm, array &$psxlabel, array &$queue ) : void
{
	$mips = new mips_asm('mips-1');

	while ( ! empty($queue) )
	{
		$cur = array_shift($queue);
		$p = $cur->pos;
		if ( isset($label[$p]) )
		{
			if ( $cur->type === 'loop' )
				$label[$p]->type = 'loop';
			continue;
		}
		$label[$p] = $cur;

		$jit = $mips->jit($ram, $p, true);
		$cnt = count($jit);

		// must at least 2 opcode
		// jump/branch + delay slot
		if ( $cnt < 2 )
			continue;

		$jump = $jit[$cnt-2];
		$type = mips_asm::jump_type($jump[0]);
		switch ( $type )
		{
			case 'syscall':
			case 'break':
				break;
			case 'trap':
				$p = $cur->pos + $cnt * 4;
				$w = new psxlabel('goto', $p);
				array_unshift($queue, $w);
				break;
			case 'branch':
				$p = $cur->pos + $cnt * 4;
				$w = new psxlabel('goto', $p);
				array_unshift($queue, $w);

				$bcnt = count($jump);
				$r = $jump[$bcnt-1] * 4;
				$p = $cur->pos + $cnt * 4 + $r;
				if ( $r < 0 )
					$w = new psxlabel('loop', $p);
				else
					$w = new psxlabel('goto', $p);
				array_unshift($queue, $w);
				break;
			case 'jump':
				// j     pc = i26 * 4
				// jr    pc = rs
				// jal   ra = pc + 8 , pc = i26 * 4
				// jalr  rd = pc + 8 , pc = rs
				switch ( $jump[0] )
				{
					case 'j':
						$p = $jump[1] * 4;
						$w = new psxlabel('jump', $p);
						array_unshift($queue, $w);
						break;
					case 'jal':
						$p = $cur->pos + $cnt * 4;
						$w = new psxlabel('goto', $p);
						array_unshift($queue, $w);

						$p = $jump[1] * 4;
						$w = new psxlabel('func', $p);
						array_unshift($queue, $w);
						break;
					case 'jr':
						//$w = detect_case();
						break;
					case 'jalr':
						$p = $cur->pos + $cnt * 4;
						$w = new psxlabel('goto', $p);
						array_unshift($queue, $w);

						//$w = detect_case();
						break;
				} // switch ( $jump[0] )
				break;
			default:
				tool::error('unknown jump type', $type, $jump[0]);
				return;
		} // switch ( mips_asm::jump_type($jump[0]) )

		foreach ( $jit as $jk => $jv )
		{
			$p = $cur->pos + ($jk << 2);
			$psxasm[$p] = $jv;
		} // foreach ( $jit as $jk => $jv )
	} // while ( ! empty($queue) )
}

function ram2asm( string $fname, string $pos, string $name ) : void
{
	$ram = file_get_contents($fname);
	if ( empty($ram) )
		return;
	if ( strlen($ram) !== RAM_SIZE )
		tool::error('invalid RAM size', strlen($ram), RAM_SIZE);

	$pos = mips_asm::hexdec($pos);
	if ( ! is_psx_func($ram, $pos) )
		return;

	$psxasm   = [];
	$psxlabel = [];
	$queue    = [];

	$w = new psxlabel('func', $pos, $name);
	$queue[] = $w;
	//process_asm($ram, $psxasm, $psxlabel, $queue);

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
