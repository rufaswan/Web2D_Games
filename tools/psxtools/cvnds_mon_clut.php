<?php
require "common.inc";

function arm9_ldr( &$ram, $pc, $dep )
{
	$tab = str_pad('', $dep*4, ' ');
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
	// arm9 == bl  (pc-1ed4)
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
				$b1 = str2int($ram, $bak+8+$off, 4);
				printf("%sloc_%6x  r%d , Lxx_%x\n", $tab, $bak, $reg, $b1);
				break;
			case 0xe92d: // stmfd
				$stack = true;
				break;
			case 0xe8bd: // ldmfd
				if ( $goto != 0 && $goto > $bak )
					continue;
				$sp = ord( $ram[$bak+1] ) >> 3;
				if ( $stack && $sp )
					return;
				break;
		} // switch ( $arm )

		$arm = ord( $ram[$bak+3] ) & 0x0f;
		if ( $arm == 0x0a )
		{
			$b1 = substr($ram, $bak, 3);
			$b1 = $bak + 8 + sint24($b1);
			if ( $b1 > $goto )
				$goto = $b1;
		}
	}
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

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;
	$ram = nds_ram($dir);
	nds_game( $ram, $dir, $pat['arm9.bin']['game'] );
	save_file("$dir/nds.ram", $ram);

	arrayhex( $pat['arm9.bin']['mon_ov'] );
	list($st,$ed) = $pat['arm9.bin']['mon_ov'];
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

	arrayhex( $pat['arm9.bin']['mon_data'] );
	list($st,$ed,$bk) = $pat['arm9.bin']['mon_data'];
	$id = 0;
	while ( $st < $ed )
	{
		echo "== mon $id ==\n";
		if ( isset( $mon[$id] ) )
			nds_overlay( $ram, $dir, $mon[$id] );

		$func = str2int($ram, $st+0, 3);
			arm9_ldr( $ram, $func, 1 );
		$func = str2int($ram, $st+4, 3);
			arm9_ldr( $ram, $func, 1 );

		$id++;
		$st += $bk;
	}
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
