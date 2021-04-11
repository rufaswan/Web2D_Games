<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";

define("CANV_S", 0x200);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function sectquad( &$pix, $dat, $ceil )
{
	//return;
	// 0 1  2  3   4  5   6  7   8  9   a  b
	// tid  x1 y1  x2 y2  x3 y3  x4 y4  -  sign
	$b = array();
	for ( $i=0; $i < 12; $i++ )
		$b[] = ord( $dat[$i] );
	$dat = $b;
	//zero_watch("dat", $dat[10] );

	$qax = ( $dat[11] & 0x01 ) ? -$dat[2] : $dat[2];
	$qay = ( $dat[11] & 0x02 ) ? -$dat[3] : $dat[3];
	$qbx = ( $dat[11] & 0x04 ) ? -$dat[4] : $dat[4];
	$qby = ( $dat[11] & 0x08 ) ? -$dat[5] : $dat[5];
	$qcx = ( $dat[11] & 0x10 ) ? -$dat[6] : $dat[6];
	$qcy = ( $dat[11] & 0x20 ) ? -$dat[7] : $dat[7];
	$qdx = ( $dat[11] & 0x40 ) ? -$dat[8] : $dat[8];
	$qdy = ( $dat[11] & 0x80 ) ? -$dat[9] : $dat[9];
		$qax = (int)($qax * SCALE);
		$qay = (int)($qay * SCALE);
		$qbx = (int)($qbx * SCALE);
		$qby = (int)($qby * SCALE);
		$qcx = (int)($qcx * SCALE);
		$qcy = (int)($qcy * SCALE);
		$qdx = (int)($qdx * SCALE);
		$qdy = (int)($qdy * SCALE);

	$pix['vector'] = array(
		array( $qax+$ceil , $qay+$ceil , 1 ),
		array( $qbx+$ceil , $qby+$ceil , 1 ),
		array( $qcx+$ceil , $qcy+$ceil , 1 ),
		array( $qdx+$ceil , $qdy+$ceil , 1 ),
	);

	$pix['src']['vector'] = array(
		array(                 0,                 0, 1),
		array($pix['src']['w']-1,                 0, 1),
		array($pix['src']['w']-1,$pix['src']['h']-1, 1),
		array(                 0,$pix['src']['h']-1, 1),
	);

	$des = array(
		array($qax,$qay,1),
		array($qbx,$qby,1),
		array($qcx,$qcy,1),
		array($qdx,$qdy,1),
	);

	printf("sign : %08b\n", $dat[11]);
	quad_dump($pix['src']['vector'] , "1243", "src quad");
	quad_dump($des                  , "1243", "des quad");
	return;
}

function sectpart( &$pak, $dir, $off, $no )
{
	printf("== sectpart( $dir , %x , $no )\n", $off);

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_pix, $gp_clut;
	$gray = grayclut(16);
	for ( $i=0; $i < $no; $i++ )
	{
		$p = $off + ($i * 12);
		$dat = substr($pak[1]['d'], $p, 12);
		//echo debug($dat);

		// fedc ba98  7654 3210
		// cctt tttt  tttt tttt
		$b1 = str2big($dat, 0, 2);
		$tid = $b1 & 0x3fff;
		$cid = $b1 >> 14;
		flag_watch("cid-x", $b1 & 0xb000);

		// obaa.pak has both cat + 4 books having the same value
		// probably the palette is referred by opcode
		//if ( $cid != 0 ) // OR $b1 & 0x4000
			//return;
		$pal = ( empty($gp_clut) ) ? $gray : substr($gp_clut, $cid*0x40, 0x40);
		$gp_pix[$tid]['pal'] = $pal;

		$pix['src']['w'] = $gp_pix[$tid]['w'];
		$pix['src']['h'] = $gp_pix[$tid]['h'];
		$pix['src']['pix'] = $gp_pix[$tid]['pix'];
		$pix['src']['pal'] = $pal;
		$pix['bgzero'] = 0;

		sectquad($pix, $dat, $ceil/2);
		printf("$tid , $cid\n");

		copyquad($pix, 1);
	} // for ( $i=0; $i < $no; $i++ )

	savepix($dir, $pix, true);
	return;
}
//////////////////////////////
function save_texx( $pfx )
{
	global $gp_pix;
	foreach ( $gp_pix as $k => $v )
	{
		if ( ! isset($v['pal']) )
			$v['pal'] = grayclut(16);

		// first color is alpha
		$v['pal'][3] = ZERO;

		$fn = sprintf("$pfx/src/%04d.clut", $k);
		save_clutfile($fn, $v);
	} // foreach ( $gp_pix as $k => $v )
	return;
}

function load_texx( &$pak, $pfx )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	$pos = 0;
	$len = strlen($pak);
	for ( $i=0; $i < $len; $i += 8 )
	{
		$d = $i/8;
		// 0  1 2 3  4  5  6  7
		// -  chr    w  h  id
		$id = ordint( $pak[$i+7] . $pak[$i+6] );
		$w = ord( $pak[$i+4] );
		$h = ord( $pak[$i+5] );
		$siz = ($w/2 * $h);
		printf("%4x , %6x , %3d x %3d = %4x\n", $d, $pos, $w, $h, $siz);

		$b1 = substr($chr, $pos, $siz);
		$pix = "";
		for ( $s=0; $s < $siz; $s++ )
		{
			$b2 = ord( $b1[$s] );
			$b3 = ($b2 >> 4) & BIT4;
			$b4 = ($b2 >> 0) & BIT4;
			$pix .= chr($b3) . chr($b4);
		}
		$gp_pix[$d] = array(
			'pix' => $pix,
			'cc' => 16,
			'w' => $w,
			'h' => $h,
		);

		// aligned to 8x8 tile
		$pos = int_ceil($pos + $siz, 0x20);
	} // for ( $i=0; $i < $no; $i++ )

	return;
}
//////////////////////////////
function sectanim( &$pak, $off )
{
	// anim def
	// 0 1  2  3  4 5  6  7
	// sid  -  -  ms   -  rep
	$anim = array();
	while (1)
	{
		$bak = $off;
			$off += 8;

		$b2 = str2big($pak, $bak+2, 2, true);
		if ( $b2 == -1 )
			continue;

		$b7 = str2big($pak, $bak+7, 1);
		if ( $b7 != 0 )
			return implode(' , ', $anim);

		$b0 = str2big($pak, $bak+0, 2, true);
		$b4 = str2big($pak, $bak+4, 2, true);
		#$b6 = str2big($pak, $bak+6, 1);

		$anim[] = sprintf("%d-%d", $b0 & 0x0fff, $b4);
	}
	return implode(' , ', $anim);
}
//////////////////////////////
function pakchr( &$pak, $pfx )
{
	echo "== pakchr( $pfx )\n";

	//     0 1 |         1-0 2-1
	// 2 3 4   | 3-2 4-3 6-4
	//       5 |             s-5
	// 6       | 5-6
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
	sect_sum($pak[1], 'pak[1][0]', 0); // byte code ref palette check

	load_texx($pak[0]['d'], $pfx);

	$anim = "";
	$len = strlen($pak[4]['d']);
	for ( $i=0; $i < $len; $i += $pak[4]['k'] )
	{
		$st = str2big($pak[4]['d'], $i+0, 4);
			$st -= $pak[3]['o'];

		$n = $i / $pak[4]['k'];
		$buf  = sprintf("anim_%d = ", $n);
		$buf .= sectanim($pak[3]['d'], $st);
		echo "$buf\n";
		$anim .= "$buf\n";
	}
	save_file("$pfx/anim.txt", $anim);


	$len = strlen($pak[2]['d']);
	for ( $i=0; $i < $len; $i += $pak[2]['k'] )
	{
		// distort set def
		// 0 1 2 3 4 5 6 7  8 9  a b
		// - - - - - - - -  st   no
		$st = str2big($pak[2]['d'], $i+ 8, 2);
		$no = str2big($pak[2]['d'], $i+10, 2);
		$id = $i / $pak[2]['k'];

		printf("SPR %d = %x , %x\n", $id, $st, $no);
		$dir = sprintf("$pfx/%04d", $id);
		sectpart($pak, $dir, $st*12, $no);
	}
	save_texx($pfx);
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

	global $gp_clut, $gp_pix;
	$gp_pix = array();
	$pal = load_file("$pfx.pal");
	if ( ! empty($pal) )
		$gp_clut = $pal;

	pakchr($pak, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
book select
	VORE  item.pak
	VORE  comm.pak
	VORE  arel.pak
	VORE  slct.pak
	VORE  chap.pak
	VORE  obaa.pak
*/
