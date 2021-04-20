<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";

//////////////////////////////
function sect4( &$pak, $pfx )
{
	$len4 = strlen($pak[4]['d']);
	$buf = '';
	for ( $i4=0; $i4 < $len4; $i4 += $pak[4]['k'] )
	{
		$p1 = str2big($pak[4]['d'], $i4+0, 4);
		if ( ($i4 + $pak[4]['k']) >= $len4 )
			$p2 = $pak[4]['o'];
		else
			$p2 = str2big($pak[4]['d'], $i4+$pak[4]['k'], 4);

		$sz = $p2 - $p1;
		$ps = $p1 - $pak[3]['o'];

		$buf .= sprintf("4/%4x = 3/%4x [%6x]\n", $i4/$pak[4]['k'], $ps/$pak[3]['k'], $sz);

	} // for ( $i4=0; $i4 < $len4; $i4 += $pak[4]['k'] )

	save_file("$pfx/meta/4-3.txt", $buf);
	return;
}
//////////////////////////////
function pakchr( &$pak, $pfx )
{
	echo "== pakchr( $pfx )\n";

	//     0 1 |         1-0 2-1
	// 2 3 4   | 3-2 4-3 6-4
	//       5 |             s-5
	// 6       | 5-6
	// flower.pak
	//    -  - 40 48 |  -   -  1*8 1*c
	//   54 60 70  - | 1*c 2*8 1*c  -
	//    -  -  - 84 |  -   -   -  1*4
	//   7c          | 1*8
	//
	// grad.pak
	//       -     -    40   958 |     -     - 123*8 2544*c
	//   1c888 1ec10 22a30     - | 2f6*c 7c4*8  b9*c      -
	//       -     -     - 234bc |     -     -     -  2f6*4
	//   232dc                   |  3c*8
	// s4[+ 0] - 1ec10   => s3
	// s3[+ 0] =   2f5   => s2
	// s2[+ 8] =  2541+3 => s1
	// s1[+ 0] =   122   => s0
	// s0[]
	//
	// s4-s3-s2-s1-s0
	$sect = array(
		array('p' => 0x08 , 'k' =>  8), // 0
		array('p' => 0x0c , 'k' => 12), // 1
		array('p' => 0x10 , 'k' => 12), // 2
		array('p' => 0x14 , 'k' =>  8), // 3
		array('p' => 0x18 , 'k' => 12), // 4
		array('p' => 0x2c , 'k' =>  4), // 5
		array('p' => 0x30 , 'k' =>  8), // 6
	);
	file2sect($pak, $sect, $pfx, array('str2big', 4), 0, true);

	sect4($pak, $pfx);
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$pak = load_file("$pfx.pak");
	if ( empty($pak) )  return;

	if ( substr($pak,0,4) != "unkn" )
		return;

	pakchr($pak, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );
