<?php
/*
[license]
[/license]
 */
require "common.inc";

// 800004a  0
// 8000042  +97,4b800
// 800004a  +3ba8,1dd4000 (other ending)
// 8000002  +3cd4,1e6a000 (girl ending)
// 800004a  +45d4,22ea000 (credits roll)

// picevt.mrg
// loc 67a58         loc 67bc8
// sll  s0, 1 =   2  v0 = s1 - 1
// addu s0    =   3  sll  v0, 1 =   2
// sll  3     =  24  addu v0    =   3
// subu s0    =  23  sll  7     = 192
// sll  3     = 184
// = 97 + i*b8       = 3cd4 + i*c0

//////////////////////////////
function loopsect( &$mrg, $st, $ed, $bk, $callback )
{
	printf("== loopsect( %x , %x , %x )\n", $st, $ed, $bk);
	if ( $bk == 0 || ! function_exists($callback) )
		return;

	// 0x800 === 1 << 11
	$id = 0;
	while ( $st < $ed )
	{
		$meta = substr($mrg, $st<<11, $bk<<11);
		$callback($meta);

		$fn = sprintf("mrg/%s/%04d.meta", $callback, $id);
		save_file($fn, $meta);

		$st += $bk;
		$id++;
	}
	return;
}

function mkr( $fname )
{
	$mrg = load_file("picevt.mrg");
	if ( empty($mrg) )  return;

	$len = strlen($mrg);
	$ed = $len >> 11;
	loopsect($mrg, 0     , 0x97  , 0xb8, "picevt_0");
	loopsect($mrg, 0x97  , 0x3ba8, 0xb8, "picevt_97");
	loopsect($mrg, 0x3ba8, 0x3cd4, 0x0 , "picevt_3ba8");
	loopsect($mrg, 0x3cd4, 0x45d4, 0xc0, "picevt_3cd4");
	loopsect($mrg, 0x45d4, $ed   , 0x0 , "picevt_45d4");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mkr( $argv[$i] );

/*
sub 8003099c
	(s2 >> 16) &  400 = mapdat.mrg
	(s2 >> 16) & 4000 = wa_mrg.mrg
	(s2 >> 16) & 2000 = merge.mrg
	(s2 >> 16) & 1000 = batdat.mrg
	(s2 >> 16) &  800 = picevt.mrg
	s2 & 8
	s2 |= 8000
*/
