<?php
require "common.inc";

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 12, 4);
	$func = "cvnds_" . strtolower($mgc);

	if ( ! function_exists($func) )
		return;

	$ram = ndsram($dir);
	$func($ram, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
overlays - 1caec0 219040 225a60 2a5880 2a58e0 2aa460 2c3ce0 2ca2a0 2ca6a0 2d2840 2d2d00 2da780
	forgotten city , dark academy
		1caec0 = 0   , 0   <- ALL SAME
		219040 = 1   , 1   <- ALL SAME
		225a60 = 4   , 4   <- ALL SAME
		2a5880 = 5   , 5   <- ALL SAME
		2a58e0 = 6   , 6   <- ALL SAME
		2aa460 = 7   , 7   <- ALL SAME
		2c3ce0 = 8   , 8   <- ALL SAME
		2ca2a0 = 9   , 9   <- DIFF
		2ca6a0 = 25  , 25  <- ALL SAME
		2d2840 = 38  , 38  <- ALL SAME
		2d2d00 = [^ + 0x4c0]
		2da780 = 102 , 110 <- DIFF
	2da780
		24,91  = sandy grave
		24,93  = city of haze
		24,96  = nation of fools
		24,99  = forest of doom
		9 ,102 = forgotten city
		9 ,104 = 13th street
		9 ,107 = burnt paradise
		9 ,110 = dark academy
		24,113 = nest of evil

	dracula castle entrance / jonathan , richter , sisters
		1caec0 = 0  , 0  , 0  <- ALL SAME
		219040 = 1  , 1  , 1  <- ALL SAME
		225a60 = 4  , 4  , 4  <- ALL SAME
		2a5880 = 5  , 5  , 5  <- ALL SAME
		2a58e0 = 6  , 6  , 6  <- ALL SAME
		2aa460 = 7  , 7  , 7  <- ALL SAME
		2c3ce0 = 8  , 8  , 8  <- ALL SAME
		2ca2a0 = 9  , 9  , 9  <- ALL SAME
		2ca6a0 = 25 , 25 , 25 <- ALL SAME
		2d2840 = 30 , 38 , 38 <- DIFF
		2d2d00 = [^ + 0x4c0]
		2da780 = 78 , 78 , 78 <- ALL SAME

same pix diff palette
	be568  1   zombie
	be788  18  wight
	befe8  85  ghoul
	bf428  119 ghoul king

map 0-0 room 0 mon_data
	= 2eaf00

arlau por jp (00 01 64 00)
	overlay 110/map , 73/arlau
	map 22e43d6
		21d8e80
			r3 = 22e43d0[6]
			r2 = 22e43d0[5]
			r7 = (r2 << 16) | r3
		21d8b00
			r3 = 22e43d0[6]
				r5 = 20b27ac  / mon_data
				r12 = r5 + (r3 << 5)
		-> 20f60a0 + 132 = 20f61d2
		-> 20fd160 + 132 = 20fd292
		-> 20fd2c0 + 132 = 20fd3f2

overlay loading
map 4e -> 20b3c44 -> 202fb04  ldr r0,[r0,r1, lsl 2]
 */
