<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-mips');

$gp_mips = new mips_asm('mips-1');


/*
.mips
.arch psx
.endian little
.text
	slti  $at, $a0, 4        // Check if the case index ($a0) is less than 4
	beqz  $at, default_case  // If not, skip to default case
	sll   $v0, $a0, 2        // Multiply index by 4 (byte offset)
	lui   $v1, 0x8001        // Base address of the function pointer array
	addu  $v1, $v1, $v0      // Add offset to base address
	lw    $v0, 0x5000($v1)   // Load the specific case function pointer
	jalr  $v0                // Jump to the case block subroutine!
*/
