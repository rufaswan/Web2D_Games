<?php
require 'common.inc';

function sect3( &$file, $pos, $dir )
{
	$cnt = str2int($file, $pos+0, 1);
	$b0  = str2int($file, $pos+1, 3);
		$pos += 4;

	if ( $cnt === 0 )
		return;
	printf("    == sect3( %x , %s ) = %x , %x\n", $pos-4, $dir, $cnt, $b0);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$b0  = str2int($file, $pos+0, 1);
		$b1  = str2int($file, $pos+1, 1);
		$b23 = str2int($file, $pos+2, 2);
		$b45 = str2int($file, $pos+4, 2);
			$pos += 6;

		$gpu = [];

		// fedc ba98 7654 3210
		// 44-f ffff fffe eeee
		$gpu[14] =  ($b23 >>  0) & 0x1f; // sx
		$gpu[15] =  ($b23 >>  5) & 0xff; // sy
		$gpu[ 4] = (($b23 >> 14) << 8) | $b0; // dx
		//if ( $gpu[4] & 0x200 )
			//$gpu[4] |= (~0x3ff);

		// fedc ba98 7654 3210
		// 66-a aaa8 888- ----
		$gpu[ 8] =  ($b45 >>  5) & 0x0f; // width
		$gpu[10] =  ($b45 >>  9) & 0x0f; // height
		$gpu[ 6] = (($b45 >> 14) << 8) | $b1; // dy
		//if ( $gpu[6] & 0x200 )
			//$gpu[6] |= (~0x3ff);

		//printf("%x\n", $i);
		//printf("  4  %2x  %4x\n", $gpu[ 4],  $gpu[ 4]     );
		//printf("  6  %2x  %4x\n", $gpu[ 6],  $gpu[ 6]     );
		//printf("  8  %2x  %4x\n", $gpu[ 8], ($gpu[ 8]+1)*8);
		//printf("  a  %2x  %4x\n", $gpu[10], ($gpu[10]+1)*8);
		//printf("  e  %2x  %4x\n", $gpu[14],  $gpu[14]   *8);
		//printf("  f  %2x  %4x\n", $gpu[15],  $gpu[15]   *8);

		printf('      %2x  %4x  %4x  %4x  %4x  %4x  %4x', $i,
			 $gpu[ 4]     ,  $gpu[ 6],
			($gpu[ 8]+1)*8, ($gpu[10]+1)*8,
			 $gpu[14]   *8,  $gpu[15]   *8
		);
		$t = sprintf('  %04x  %04x',
			$b23 & 0x2000,
			$b45 & 0x201f
		);
		$t = str_replace('0', '-', $t);
		echo "$t\n";

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

function sect2( &$file, $st, $dir )
{
	$ed = str2int($file, $st, 2);
	$multi = false;
	if ( ($st+2) !== $ed )
		$multi = true;
	printf("  == sect2( %x , %x , %s )\n", $st, $ed, $dir);
	$id = 0;

	// 3a6 = aa 03 ad 03
	// 3d3 = e1 03 -- 5c 18 -- 8a 18 -- b8 18 -- e6 18
	while ( $st < $ed )
	{
		if ( $file[$st] === ZERO )
		{
			if ( $multi )
				echo "  + 3\n";
			$b1 = str2int($file, $st+1, 2);
				$st += 3;
		}
		else
		{
			if ( $multi )
				echo "  + 2\n";
			$b0 = str2int($file, $st, 2);
				$st += 2;
			if ( $b0 < $ed )
				$ed = $b0;
			$b1 = str2int($file, $b0+1, 2);
		}
		sect3($file, $b1, "$dir/$id");
			$id++;
	} // while ( $st < $ed )
	return;
}

function sect1( &$file, $st, $ed, $dir )
{
	printf("== sect1( %x , %x , %s )\n", $st, $ed, $dir);
	$id = 0;
	while ( $st < $ed )
	{
		$b0 = str2int($file, $st, 2);
			$st += 2;
		sect2($file, $b0, "$dir/$id");
			$id++;
	} // while ( $st < $ed )
	return;
}

function merge_1123( &$mrg )
{
	$pos  = 0x1123 * 0x800;
	$file = substr($mrg, $pos, 0x8000);

	//$ed = str2int($file, 0, 2) - 2;
	$id = 0;
	for ( $i=0; $i < 0x0c; $i += 2 )
	{
		$b1 = str2int($file, $i+0, 2);
		$b2 = str2int($file, $i+2, 2);
		sect1($file, $b1, $b2, "merge_1123/$id");
			$id++;
	}
	return;
}
//////////////////////////////
function merge_a9( &$mrg )
{
	$pos  = 0xa9 * 0x800;
	$file = substr($mrg, $pos, 0x8000);

	//$ed = str2int($file, 0, 2) - 2;
	$id = 0;
	for ( $i=0; $i < 12; $i += 2 )
	{
		$b1 = str2int($file, $i+0, 2);
		$b2 = str2int($file, $i+2, 2);
		sect1($file, $b1, $b2, "merge_a9/$id");
			$id++;
	}
	return;
}

function merge_1fbe( &$mrg )
{
	//for ( $g=0; $g < 5; $g++ )
	//{
	$g = 0;
		$pos  = (0x1fbe + $g * 0xae) * 0x800;
		$file = substr($mrg, $pos+0x23800, 0x3800);

		//$ed = str2int($file, 0, 2) - 2;
		//for ( $i=0; $i < 6; $i += 2 )
		//{
		$i = 6;
			$b1 = str2int($file, $i+0, 2);
			$b2 = str2int($file, $i+4, 2);

			$dir = sprintf('merge_%x/%04d-%d', $pos, $g, $i/2);
			sect1($file, $b1, $b2, $dir);
		//} // for ( $i=0; $i < 6; $i += 2 )
	//} // for ( $i=0; $i < 5; $i++ )
	return;
}
//////////////////////////////
function mkrmerge( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	//merge_1123($file);
	//merge_a9  ($file);
	merge_1fbe($file);
	return;
}

//for ( $i=1; $i < $argc; $i++ )
	//mkrmerge( $argv[$i] );
mkrmerge('merge.mrg');

/*
0/14=bg/base  8005f6dc
	t0 = lbu  50(a2[800bc328])
	v0 = lbu   0(a1[801dc000])
	v1 = lbu   1(a1[801dc000])
	v0 = v0 + (t0 << 1) + (v1 << 8) + 801dc000

2/50=upper/lower/shoe  80018e44
	v1 = 801dc000 + (v1 << 1)
	v0 = lbu   1(v1)
	v1 = lbu   0(v1)
	v0 = 801dc000 + (v0 << 8) | v1
	v1 = lbu  56(a3[800bc6d0])
	v0 = v0 + (v1 << 1)

4/10c=upper/lower/shoe  80018e44
6/138=upper/lower/shoe  80018e44
8/16e=shield  80018e44
a/186=weapon  80018e44

c/1c2=talk  8005ddac
e/1cc=talk  8005ddac
10/288=talk  8005ddac
12/2b4=talk  8005ddac
	v0 = sp + a1
	a0 = lw   18(v0)
	v1 = lbu   1(v0[801dc00e])
	v0 = lbu   0(v0[801dc00e])
	v0 = v0 + (a0 << 1) + 801dc000 + (v1 << 8)

end/3e62


item id
	0/14
		 0-29  *refer weapon*
	2/50
		uniform
			 0- 5  70-75  sophia
			 6-11  76-7b  hanna
			12-17  7c-81  rise  (12+3)
			18-23  82-87  lezlie
			24-29  88-8d  linda
		30-65  8e-b1  full set
		66-93  bf-db  upper
	4/10c
		 0-21   e0-f5   lower
	6/138
		 0-26  100-119  shoes
	a/186  weapon
		 0- 5  120-125  sword/sophia
		 6-11  126-12b  boomerang/hanna
		12-17  12c-131  rapier/rise
		18-23  132-137  claw/lezlie
		24-29  138-13d  whip/linda
	8/16e
		 0-11  140-14b  shield
	-  150-15b  bracelet
	-  160-16b  ring
	-  170-17a  pendant

photo = RAM 80144a08 , mrg 54800/a9

??? 1fbe + 23800 = RAM 801d4800 + 801d8000
 */
