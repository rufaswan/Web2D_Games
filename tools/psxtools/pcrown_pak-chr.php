<?php
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
		//debug($dat);

		// fedc ba98  7654 3210
		// cctt tttt  tttt tttt
		$b1 = str2big($dat, 0, 2);
		$tid = $b1 & 0x3fff;
		$cid = $b1 >> 14;
		flag_warn("cid-x", $b1 & 0xb000);

		// obaa.pak has both cat + 4 books having the same value
		// probably the palette is referred by opcode
		if ( $cid != 0 ) // OR $b1 & 0x4000
			return;

		$pix['src']['w'] = $gp_pix[$tid][1];
		$pix['src']['h'] = $gp_pix[$tid][2];
		$pix['src']['pix'] = $gp_pix[$tid][0];
		$pix['src']['pal'] = ( empty($gp_clut) ) ? $gray : substr($gp_clut, $cid*0x40, 0x40);
		$pix['bgzero'] = 0;

		sectquad($pix, $dat, $ceil/2);
		printf("$tid , $cid\n");

		copyquad($pix, 1);
	} // for ( $i=0; $i < $no; $i++ )

	savpix($dir, $pix, true);
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
		if ( $pak[$bak+3] == BYTE && $pak[$bak+2] == BYTE )
			continue;
		if ( $pak[$bak+7] != ZERO )
			return implode(' , ', $anim);

		$b1 = ordint( $pak[$bak+1] . $pak[$bak+0] );
		$b2 = ordint( $pak[$bak+5] . $pak[$bak+4] );
		$anim[] = sprintf("%d-%d", $b1 & 0x0fff, $b2);
	}
	return implode(' , ', $anim);
}

function load_texx( &$pak, $pfx )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	global $gp_clut;
	$pal = ( empty($gp_clut) ) ? grayclut(16) : substr($gp_clut, 0, 0x40);
		// first color is alpha
		$pal[3] = ZERO;

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
		$gp_pix[$d] = array($pix, $w, $h);

		//////////////////////////////
			$clut = "CLUT";
			$clut .= chrint(16, 4);
			$clut .= chrint($w, 4);
			$clut .= chrint($h, 4);
			$clut .= $pal;
			$clut .= $pix;

			$fn = sprintf("$pfx/src/%04d.clut", $d);
			save_file($fn, $clut);
		//////////////////////////////

		// aligned to 8x8 tile
		$pos = int_ceil($pos + $siz, 0x20);
	} // for ( $i=0; $i < $no; $i++ )

	return;
}
//////////////////////////////
function pakdbg( &$meta, $name, $blk )
{
	$len = strlen($meta);
	printf("== pakdbg( $name , %x ) = %x\n", $blk, $len);
	for ( $i=0; $i < $len; $i += $blk )
	{
		$n = sprintf("%4x", $i/$blk);
		debug( substr($meta, $i, $blk), $n );
	}
	return;
}

function loadpak( &$pak, $sect, $pfx )
{
	$feof = strlen($pak);
	$offs = array();
	foreach ( $sect as $k => $v )
	{
		$b1 = str2big($pak, $v['p'], 4);
		if ( $b1 == 0 )
			continue;
		$offs[] = $b1;
		$sect[$k]['o'] = $b1;
	}
	sort($offs);

	foreach ( $sect as $k => $v )
	{
		$id = array_search($v['o'], $offs);
		if ( isset( $offs[$id+1] ) )
			$sz = $offs[$id+1] - $v['o'];
		else
			$sz = $feof - $v['o'];

		$sz  = int_floor($sz, $v['k']);
		$dat = substr($pak, $v['o'], $sz);

		save_file("$pfx/meta/$k.meta", $dat);
		//pakdbg($dat, "meta $k", $v['k']);

		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$pak = $sect;

	// check for palette flag
	$len = strlen( $sect[1]['d'] );
	$done = array();
	for ( $i=0; $i < $len; $i += $sect[1]['k'] )
	{
		$b1 = str2big( $sect[1]['d'], $i+0, 2);
		if ( ($b1 & 0xf000) == 0 )
			continue;
		if ( in_array($b1, $done) )
			continue;
		printf("%4x -> %4d.png\n", $b1, $b1 & 0xfff);
		$done[] = $b1;
	}
	return;
}

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
	loadpak($pak, $sect, $pfx);
	load_texx($pak[0]['d'], $pfx);

	$anim = "";
	$len = strlen($pak[4]['d']);
	for ( $i=0; $i < $len; $i += 12 )
	{
		$st = str2big($pak[4]['d'], $i+0, 4);
			$st -= $pak[3]['o'];

		$n = $i / 12;
		$buf  = sprintf("anim_%d = ", $n);
		$buf .= sectanim($pak[3]['d'], $st);
		echo "$buf\n";
		$anim .= "$buf\n";
	}
	save_file("$pfx/anim.txt", $anim);


	$len = strlen($pak[2]['d']);
	for ( $i=0; $i < $len; $i += 12 )
	{
		// distort set def
		// 0 1 2 3 4 5 6 7  8 9  a b
		// - - - - - - - -  st   no
		$st = str2big($pak[2]['d'], $i+ 8, 2);
		$no = str2big($pak[2]['d'], $i+10, 2);

		printf("SPR %d = %x , %x\n", $i/12, $st, $no);
		$dir = sprintf("$pfx/%04d", $i/12);
		sectpart($pak, $dir, $st*12, $no);
	}
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

	global $gp_clut;
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

prg
	0 dohdoh  10 ghost    20 polt  30 larv
	1 slime   11 card     21 iced
	2 myco    12 barb     22 hind
	3 zombie  13 d/grad   23 blud
	4 goblin  14 vorg     24 blud2
	5         15 eeriel   25
	6 frog    16 ryon     26
	7 kage    17 necro    27
	8 basil   18          28
	9 dragon  19 sirene   29
	a kumo    1a cent     2a
	b nise    1b wgod     2b
	c grifon  1c pirates  2c puppet
	d demon   1d skul     2d
	e knight  1e          2e pros
	f egrad   1f          2f ceye
*/
