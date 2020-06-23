<?php
require "common.inc";

function arm9_ldr( &$ram, $pc, $dep )
{
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
	$func = __FUNCTION__;
	$stack = false;
	$tab = str_pad('', $dep*4, ' ');
	printf("%s== bl  %x ==\n", $tab, $pc);
	while (1)
	{
		$bak = $pc;
		$pc += 4;
		$arm = str2int($ram, $bak+2, 2);
		switch ( $arm )
		{
			case 0xe59f:
				$b1 = str2int($ram, $bak+0, 2);
				$reg = $b1 >> 12;
				$off = $b1 & 0xfff;
				$b1 = str2int($ram, $bak+8+$off, 4);
				printf("%sloc_%6x  r%d , Lxx_%x\n", $tab, $bak, $reg, $b1);
				break;
			case 0xe92d:
				$stack = true;
				break;
			case 0xe8bd:
				$sp = ord( $ram[$bak+1] ) >> 3;
				if ( $stack && $sp )
					return;
				break;
		} // switch ( $arm )
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

	arrayhex( $pat['arm9.bin']['mon_data'] );
	arrayhex( $pat['arm9.bin']['mon_clut'] );
	list($st,$ed,$bk) = $pat['arm9.bin']['mon_data'];
	$id = 0;
	while ( $st < $ed )
	{
		echo "== mon $id ==\n";
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
