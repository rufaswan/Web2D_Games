<?php
require "common.inc";

//$file = file_get_contents("f_tpm00.dat");
$file = file_get_contents("arlau_0000.f");
//$file = file_get_contents("f_stor00.dat");

$pix = "";
$len = strlen($file);
for ( $i=0; $i < $len; $i++ )
{
	$p = ord( $file[$i] );
	$p1 = $p & 0x0f;
	$p2 = $p >> 4;
	$pix .= chr($p1) . chr($p2);
}

// start for vmem ////////////
$file = file_get_contents("por.2.dec.VMEM");
$cn = strlen($file) / 0x20;
$clut = mclut2str($file, 0, 16, $cn);

foreach ( $clut as $k => $v )
{
	if ( trim($v, ZERO.BYTE) == "" )
		continue;

	$clut = "CLUT";
	$clut .= chrint(0x10, 4);
	$clut .= chrint(0x80, 4);
	$clut .= chrint(0x80, 4);
	$clut .= $v;
	$clut .= $pix;
	file_put_contents("arlau-$k.clut", $clut);
}
// end for vmem //////////////

/*
// start for ov7 /////////////
$id = 0;
while (1)
{
	if ( ! file_exists("ov7.bin.$id") )
		break;
	$file = file_get_contents("ov7.bin.$id");
	$cn = strlen($file) / 0x20;
	$clut = mclut2str($file, 0, 16, $cn);

	foreach ( $clut as $k => $v )
	{
		if ( trim($v, ZERO.BYTE) == "" )
			continue;

		$clut = "CLUT";
		$clut .= chrint(0x10, 4);
		$clut .= chrint(0x80, 4);
		$clut .= chrint(0x80, 4);
		$clut .= $v;
		$clut .= $pix;
		//file_put_contents("tpm-$id-$k.clut", $clut);
		file_put_contents("arlau-$id-$k.clut", $clut);
		//file_put_contents("stor-$id-$k.clut", $clut);
	}
	$id++;
}
// end for ov7 ///////////////
*/

/*
tpm.f close
  268 <- lighter
  326 <- lighter
  334-339
  355
  358 <- lighter
  397 <- lighter
  402
arlau close
  223 = 2c23dc
  (0x10+)
  27
  95
  184
  188
  228
  325

  62
stor close
  63 = 2ba57c

arlau data
  bf1f6 = hp
  bf1f8 = exp
  -> be568 + (id * 0x20)
	00  4  func
	04  4  func
	08  2  drop 1
	0a  2  drop 2
	0c  1
	0d  1  sp
	0e  2  hp
	10  3  exp
	13  1  atk
	14  1  phy.def
	15  1  mgc.def
	16  1  drop 1 rate (get++)
	17  1  drop 2 rate (get++)
	18  2  weakness (all=ff 07)
	1a  2
	1c  2  strong   (all=ff 07)
	1e  2

1000 1100 1000 0000
1000 1100 1000 0100

same pix diff palette
	be568  1   zombie
	be788  18  wight
	befe8  85  ghoul
	bf428  119 ghoul king

loading overlay
	load y9.bin
	load the overlay to target ram address
	start from start init address
	ww hh 00 00 (ww *= (0x10*2) , hh *= 0xc , in 16x16 tiles)
	00 01 40 00 = CLUT
*/
