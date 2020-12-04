<?php
/*
[license]
[/license]
 */
require "common.inc";

// 2000004a  0
// 2000004a  +85,42800 (mini photo)
// 2000004a  +ad,56800 (photo)
// 2000004a  +4e3,271800 (npc front)
// 2000004a  +6bf,35f800
// 20000002  +1123,891800 (char bg)
// 20000042  +1123,891800 (^)
// 20000042  +114d,8a6800 (char upper body + weapon)
// 2000000a  +118e,8c7000 (char lower body)
// 20000042  +1fab,fd5800
// 2000000a  +1fbe,fdf000/+1fd6,feb000/+1fee,ff7000 (char side/back/front)
// 2000004a  +1fee,ff7000 (^)
// 2000000a  +2311,1188800/+231b,118d800/+2325,1192800 (cloth side/back/front)
// 20000042  +2311,1188800 (^)
// 2000004a  +2331,1198800 (cloth full + upper)
// 2000004a  +40cf,2067800 (cloth lower)
// 2000004a  +42f5,217a800/+42fe,217f000 (shoe/shield)

// merge.mrg
// loc 54db0         loc 594cc         loc 59804        loc 59c68        loc 59d14        loc 59e7c         loc 59f60        loc 5a4e0        loc 63b78
// sll  v0, 3 =   8  sll  t0, 1 =   2  sll  t0, 2 =  4  sll  s4, 1 =  2  sll  s1, 1 =  2  sll  s0, 5 =  32  sll  s3, 2 =  4  sll  t0, 4 = 16  sll  s2, 1 =  2
// addu v0    =   9  addu t0    =   3  addu t0    =  5  addu s4    =  3  addu s1    =  3  subu s0    =  31  addu s3    =  5  addu t0    = 17  addu s2    =  3
// sll  2     =  36  sll  2     =  12  sll  4     = 80  sll  3     = 24  sll  2     = 12  sll  2     = 124  sll  4     = 80  sll  1     = 34  sll  4     = 48
// subu v0    =  35  subu t0    =  11                   addu s4    = 25  addu s1    = 13                    addu s3    = 81                   addu s2    = 49
// sll  2     = 140  sll  3     =  88                                    sll  1     = 26
//                   subu t0    =  87
//                   sll  1     = 174
// = 6bf + i*8c      = 1fee + i*ae     = 2325 + i*50    = 40cf + i*19    = 42f5 + i*1a    = 1123 + i*7c     = 2331 + i*51    = 4e3 + i*22     = ad + i*31

//////////////////////////////
function loopsect( &$mrg, $st, $ed, $bk, $callback )
{
	printf("== loopsect( %x , %x , %x )\n", $st, $ed, $bk);
	if ( $bk == 0 || $callback == "" )
		return;
	while ( $st < $ed )
	{
		$meta = substr($mrg, $st*0x800, $bk*0x800);
		//$callback($meta);
		$fn = sprintf("mrg/%s/%x.bin", $callback, $st);
		save_file($fn, $meta);
		$st += $bk;
		return;
	}
	return;
}

function mkr( $fname )
{
	$mrg = load_file("merge.mrg");
	if ( empty($mrg) )  return;

	$len = strlen($mrg);
	$ed = $len >> 11;
	loopsect($mrg, 0     , 0x85  , 0x85, "merge_0");
	loopsect($mrg, 0x85  , 0xad  , 0x0 , "merge_85");
	loopsect($mrg, 0xad  , 0x4e3 , 0x31, "merge_ad");
	loopsect($mrg, 0x4e3 , 0x6bf , 0x22, "merge_4e3");
	loopsect($mrg, 0x6bf , 0x1123, 0x8c, "merge_6bf");
	loopsect($mrg, 0x1123, 0x1fab, 0x7c, "merge_1123");
	loopsect($mrg, 0x114d, 0x1fab, 0x0 , "merge_114d");
	loopsect($mrg, 0x118e, 0x1fab, 0x0 , "merge_118e");
	loopsect($mrg, 0x1fab, 0x1fbe, 0x0 , "merge_1fab");
	loopsect($mrg, 0x1fbe, 0x2311, 0xae, "merge_1fbe");
	loopsect($mrg, 0x1fd6, 0x2311, 0xae, "merge_1fd6");
	loopsect($mrg, 0x1fee, 0x2311, 0xae, "merge_1fee");
	loopsect($mrg, 0x2311, 0x40cf, 0x50, "merge_2311");
	loopsect($mrg, 0x231b, 0x40cf, 0x50, "merge_231b");
	loopsect($mrg, 0x2325, 0x40cf, 0x50, "merge_2325");
	loopsect($mrg, 0x2331, 0x40cf, 0x51, "merge_2331");
	loopsect($mrg, 0x40cf, 0x42f5, 0x19, "merge_40cf");
	loopsect($mrg, 0x42f5, $ed   , 0x1a, "merge_42f5");
	loopsect($mrg, 0x42fe, $ed   , 0x1a, "merge_42fe");
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
