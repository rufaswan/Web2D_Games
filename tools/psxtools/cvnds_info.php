<?php
require "common.inc";

$gp_patch = array();

function arm9_ldr( &$ram, $pc, $dep )
{
	$tab = str_repeat(' ', $dep*4);
	printf("%s== bl  %x ==\n", $tab, $pc);
	//=========================================
	// e    5    9    f     0    1    0    8
	// fedc ba98 7654 3210  fedc ba98 7654 3210
	// cond                                     = always
	//      01IP                                = 01 , immediate , pre-index
	//           UBWL                           = add , word , no writeback , load
	//                Rn
	//                      Rd
	//                           offset
	// arm9 == ldr  r0, [r15/pc + 0x108]
	//=========================================
	// fedc ba98 7654 3210  fedc ba98 7654 3210
	// cond
	//      100P
	//           USWL
	//                Rn
	//                      register list
	//=========================================
	// e    9    2    d     4    0    1    0
	//         1                               = 100 , Rn excluded
	//           0010                          = downward , - , base++ , store
	// arm9 == stmfd  r13/sp!,{r4,r14/ra}
	//=========================================
	// e    8    b    d     8    0    1    0
	//         0                               = 100 , Rn included
	//           1011                          = upward , - , base++ , load
	// arm9 == ldmfd  r13/sp!,{r4,r15/pc}
	//=========================================
	// e    b    f    f     f    8    4    b
	// fedc ba98 7654 3210  fedc ba98 7654 3210
	// cond                                    = always
	//      101L                               = 101 , link
	//           offset                        = fff84b = -7b5 -> -1ed4
	// arm9 == bl  (pc+-1ed4)
	//=========================================
	$func = __FUNCTION__;
	$stack = false;
	$goto = 0;
	while (1)
	{
		$bak = $pc;
		$pc += 4;
		if ( ! isset( $ram[$pc] ) )
			return;

		$arm = str2int($ram, $bak+2, 2);
		switch ( $arm )
		{
			case 0xe59f: // ldr
				$b1 = str2int($ram, $bak+0, 2);
				$reg = $b1 >> 12;
				$off = $b1 & 0xfff;
				$b1 = str2int($ram, $bak+8+$off, 3);
				printf("%sloc_%6s  ldr  r%d , [%6x]\n", $tab, dechex($bak), $reg, $b1);
				break;
			//case 0xe92d: // stmfd
				//$stack = true;
				//break;
			case 0xe8bd: // ldmfd
				if ( $goto != 0 && $goto > $bak )
					continue;
				$sp = ord( $ram[$bak+1] ) >> 3;
				if ( $sp )
					return;
				break;
		} // switch ( $arm )

		$arm = ord( $ram[$bak+3] ) & 0x0f;
		switch ( $arm )
		{
			case 0x0a: // branch
				$b1 = substr($ram, $bak, 3);
				$b1 = $bak + 8 + (sint24($b1) << 2);
				if ( $b1 > $goto )
					$goto = $b1;
				break;
			case 0x0b: // branch and link
				$b1 = substr($ram, $bak, 3);
				$b1 = $bak + 8 + (sint24($b1) << 2);
				printf("%sloc_%6s  bl   sub_%x\n", $tab, dechex($bak), $b1);
				break;
		} // switch ( $arm )
	}
	return;
}

function asm_mon_clut( &$ram, &$mon_ov, $dir )
{
	global $gp_patch;
	list($st,$ed,$bk) = $gp_patch['ndsram']['mon_data'];
	$id = 0;
	while ( $st < $ed )
	{
		echo "== mon $id ==\n";
		if ( isset( $mon_ov[$id] ) )
			nds_overlay( $ram, $dir, $mon_ov[$id] );

		$func = str2int($ram, $st+0, 3);
			arm9_ldr( $ram, $func, 1 );
		//$func = str2int($ram, $st+4, 3);
			//arm9_ldr( $ram, $func, 1 );

		$id++;
		$st += $bk;
	} // while ( $st < $ed )
	return;
}
//////////////////////////////
function find_sc_clut( &$ram, $ldr )
{
	if ( $ldr == 0 )
		return;
	$TWO = chr(2);
	$siz = strlen($ram) - 8;
	$ldr = chrint($ldr, 3);
	for ( $i=0; $i < $siz; $i += 4 )
	{
		if ( $ram[$i+ 3] != $TWO )  continue;
		if ( $ram[$i+ 7] != $TWO )  continue;
		if ( $ram[$i+11] != $TWO )  continue;

		if ( $ram[$i+0] != $ldr[0] )  continue;
		if ( $ram[$i+1] != $ldr[1] )  continue;
		if ( $ram[$i+2] != $ldr[2] )  continue;

		$b1 = str2int($ram, $i+8, 3);
		printf("%4sclut , %6x , %6x\n", ' ', $i, $b1);
	}
	return;
}
//////////////////////////////
function list_mon_obj_sc( &$ram , $mon_ov, $dir, $type )
{
	global $gp_patch;
	list($st,$ed) = $gp_patch['ndsram'][$type];
	$files = $gp_patch['ndsram']['files'];
	$id = 0;
	while ( $st < $ed )
	{
		if ( isset( $mon_ov[$id] ) )
			nds_overlay( $ram, $dir, $mon_ov[$id] );
		echo "== $type $id ==\n";
			$id++;
		$pos = str2int($ram, $st, 3);
			$st += 4;
		while (1)
		{
			$b1 = str2int($ram, $pos, 3);
			$b2 = $ram[$pos+3];
				$pos += 8;
			if ( $b1 == BIT24 )
				break;
			if ( $b2 == chr(2) )
				continue;
			$ps = $files[0] + ($b1 * $files[2]);
			$b2 = str2int($ram, $ps+0, 3);
			$fn = substr0($ram, $ps+6);
			printf("%4s%4x , %6x , %s\n", ' ', $b1, $b2, $fn);
			if ( ord( $ram[$pos-4] ) == 2 )
				find_sc_clut( $ram, $b2 );
		}
	} // while ( $st < $ed )
	return;
}

function list_mon_ov( &$ram, &$mon )
{
	global $gp_patch;
	list($st,$ed) = $gp_patch['ndsram']['mon_ov'];
	$mon = array();
	$ovs = array();
	while ( $st < $ed )
	{
		$b1 = str2int($ram, $st+0, 2);
		$b2 = str2int($ram, $st+4, 2);
		$mon[$b1] = $b2;
		if ( ! isset( $ovs[$b2] ) )
			$ovs[$b2] = array();
		$ovs[$b2][] = $b1;
		$st += 8;
	}
	ksort($mon);
	echo "== monster @ overlay ==\n";
	foreach ( $mon as $k => $v )
		echo "mon $k = overlay $v\n";
	ksort($ovs);
	echo "== overlay @ monster ==\n";
	foreach ( $ovs as $k => $v )
		printf("overlay $k = %s\n", implode('  ', $v));
	return;
}
//////////////////////////////
function nds_game( &$ram, $dir, $game )
{
	foreach ( $game as $g )
	{
		if ( strpos($g, 'ov-') === false )
			continue;
		nds_overlay( $ram, $dir, $g );
	}
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	global $gp_patch;
	$gp_patch = nds_patch($dir, 'cvnds');
	if ( empty($gp_patch) )
		return;
	$ram = nds_ram($dir);
	nds_game( $ram, $dir, $gp_patch['ndsram']['game'] );
	save_file("$dir/nds.ram", $ram);

	$mon_ov = array();
	arrayhex( $gp_patch['ndsram']['mon_ov'] );
	list_mon_ov( $ram, $mon_ov );

	arrayhex( $gp_patch['ndsram']['mon_data'] );
	asm_mon_clut( $ram, $mon_ov, $dir );

	arrayhex( $gp_patch['ndsram']['files'] );
	arrayhex( $gp_patch['ndsram']['mon_sc'] );
	arrayhex( $gp_patch['ndsram']['obj_sc'] );
	list_mon_obj_sc( $ram, $mon_ov, $dir, 'mon_sc' );
	list_mon_obj_sc( $ram, array(), $dir, 'obj_sc' );
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
shared overlays
	DOS
		24 = 37  97 (great armor + final guard)
		27 = 18  81 (manticore + mushufushu)
		28 = 34  14  100 (treant + golem + iron golem)
		31 = 47  91  93 (devil + flame demon + arc demon)
		32 = 25  77 (catoblepas + gorgon)
	POR
		44 = 33  82 (great armor + final guard)
		45 = 69  120 (treant + iron golem)
		47 = 78  122 (flame demon + demon)
		48 = 34  92 (catoblepas + gorgon)
		63 = 145  146 (stella + loretta)
		64 = 144  153 (death + dracula)
		69 = 42  114 (sand worm + poison worm)
		70 = 135  136  137 (fake trio)
	OOE
		37 = 60  65  78  94  22 (owl + owl knight + draculina + spectral sword + creature)
*/
