<?php
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";

define("CANV_S", 0x400);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix = array();

function sectquad( &$mbs, $pos, &$pix, $ceil, $SCALE )
{
	$qax = float32( substrrev($mbs, $pos+ 0, 4) );
	$qay = float32( substrrev($mbs, $pos+ 4, 4) );
	$qbx = float32( substrrev($mbs, $pos+ 8, 4) );
	$qby = float32( substrrev($mbs, $pos+12, 4) );
	$qcx = float32( substrrev($mbs, $pos+16, 4) );
	$qcy = float32( substrrev($mbs, $pos+20, 4) );
	$qdx = float32( substrrev($mbs, $pos+24, 4) );
	$qdy = float32( substrrev($mbs, $pos+28, 4) );
	$qex = float32( substrrev($mbs, $pos+32, 4) );
	$qey = float32( substrrev($mbs, $pos+36, 4) );
	$qfx = float32( substrrev($mbs, $pos+40, 4) );
	$qfy = float32( substrrev($mbs, $pos+44, 4) );

	if ( $qbx != $qfx )
		trigger_error("qbx != qfx [$qbx,$qfx]\n", E_USER_NOTICE);
	if ( $qby != $qfy )
		trigger_error("qby != qfy [$qby,$qfy]\n", E_USER_NOTICE);

	$qbx *= $SCALE;
	$qby *= $SCALE;
	$qcx *= $SCALE;
	$qcy *= $SCALE;
	$qdx *= $SCALE;
	$qdy *= $SCALE;
	$qex *= $SCALE;
	$qey *= $SCALE;

	$pix = array(
		array( $qbx+$ceil , $qby+$ceil , 1 ),
		array( $qcx+$ceil , $qcy+$ceil , 1 ),
		array( $qdx+$ceil , $qdy+$ceil , 1 ),
		array( $qex+$ceil , $qey+$ceil , 1 ),
	);

	$bcde = array(
		array($qbx,$qby,1),
		array($qcx,$qcy,1),
		array($qdx,$qdy,1),
		array($qex,$qey,1),
	);

	printf("== sectquad( %x , %d , %.2f )\n", $pos, $ceil, $SCALE);
	printf("    af %7.2f,%7.2f  %7.2f,%7.2f\n", $qax, $qay, $qfx, $qfy);
	quad_dump($bcde, "1423", "bcde");
	return;
}

function load_tpl( &$pix, $tid , $pfx )
{
	global $gp_pix;
	if ( ! isset( $gp_pix[$tid] ) )
	{
		$ftx = load_file("$pfx.$tid.tpl");
		if ( empty($ftx) )
			return;

		$b1 = substr($ftx, 0, 4);
		if ( $b1 == "CLUT" )
		{
			$cc = str2int($ftx,  4, 4);
			$w  = str2int($ftx,  8, 4);
			$h  = str2int($ftx, 12, 4);

			$pal = substr($ftx, 16, $cc*4);
			$dat = substr($ftx, 16 + $cc*4, $w*$h);
			$gp_pix[$tid] = array(
				'w' => $w,
				'h' => $h,
				'd' => clut2rgba($pal, $dat, false),
			);
		} // if ( $b1 == "CLUT" )
		else
		if ( $b1 == "RGBA" )
		{
			$w  = str2int($ftx, 4, 4);
			$h  = str2int($ftx, 8, 4);

			$gp_pix[$tid] = array(
				'w' => $w,
				'h' => $h,
				'd' => substr($ftx, 12, $w*$h*4),
			);
		} // if ( $b1 == "RGBA" )
	} // if ( ! isset( $gp_pix[$tid] ) )

	printf("== load_texx( $tid , $pfx ) = %x x %x\n", $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	$pix['src']['w'] = $gp_pix[$tid]['w'];
	$pix['src']['h'] = $gp_pix[$tid]['h'];
	$pix['src']['pix'] = &$gp_pix[$tid]['d'];
	$pix['src']['pal'] = "";
	return;
}
//////////////////////////////
function sectpart( &$mbs, $dir, $pfx, $id6, $no6 )
{
	printf("== sectpart( $dir , $pfx , %x , %x )\n", $id6, $no6);

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);
	$pix['alpha'] = "alpha_over";

	for ( $i4=0; $i4 < $no6; $i4++ )
	{
		$p4 = ($id6 + $i4) * $mbs[4]['k'];

		// 0 1 2 3    4 5  6 7 8 9  a b
		// - - - tid  s1   - - - -  s2
		$tid = str2big($mbs[4]['d'], $p4+ 3, 1);
		$s1  = str2big($mbs[4]['d'], $p4+ 4, 2); // sx,sy
		$s2  = str2big($mbs[4]['d'], $p4+10, 2); // dx,dy
			$tid *= 10; // tpl can have multiple images

		load_tpl($pix, $tid, $pfx);
		sectquad($mbs[1]['d'], $s1*$mbs[1]['k'], $pix['src']['vector'], 0, 1);
		sectquad($mbs[2]['d'], $s2*$mbs[2]['k'], $pix['vector'], $ceil/2, SCALE);

		debug( substr($mbs[6]['d'], $p4, 12) );
		copyquad($pix, 4);
	} // for ( $i4=0; $i4 < $no6; $i4++ )

	savpix($dir, $pix, true);
	return;
}

function sectspr( &$mbs, $pfx )
{
	// s6-s4-s1,s2 [18-c-30,30]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2big($mbs[6]['d'], $i6+0x10, 2);
		$no6 = str2big($mbs[6]['d'], $i6+0x14, 1);
		if ( $no6 == 0 )
			continue;

		$dir = sprintf("$pfx/%04d", $i6/$mbs[6]['k']);
		sectpart($mbs, $dir, $pfx, $id6, $no6);

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$mbs, $pfx )
{
	$anim = "";
	// s9-sa-s8 [30-10-20]
	$len9 = strlen( $mbs[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )
	{
		// 0 4 8 c  10
		// - - - -  name
		// 28 29  2a  2b 2c 2d 2e 2f
		// id     no  -  -  -  -  -
		$name = substr0($mbs[9]['d'], $i9+0x10);
		$id9  = str2big($mbs[9]['d'], $i9+0x28, 2);
		$no9  = str2big($mbs[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbs[10]['k'];

			// 0 1  2 3  4 5 6 7  8 9 a b  c d e f
			// id   no   sum      -        -
			$ida = str2big($mbs[10]['d'], $pa+0, 2);
			$noa = str2big($mbs[10]['d'], $pa+2, 2);

			$ent = array();
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbs[8]['k'];

				// 0   2 4  6   8 c 10 14 18 1c
				// id  - -  no  - - -  -  -  -
				$id8 = str2big($mbs[8]['d'], $p8+0, 2);
				$no8 = str2big($mbs[8]['d'], $p8+6, 2);

				$ent[] = "$id8-$no8";

			} // for ( $i8=0; $i8 < $noa; $i8++ )

			$anim .= sprintf("%s_%d = ", $name, $ia);
			$anim .= implode(' , ', $ent);
			$anim .= "\n";

		} // for ( $ia=0; $ia < $no9; $ia++ )

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )

	save_file("$pfx/anim.txt", $anim);
	return;
}
//////////////////////////////
function mbsdbg( &$meta, $name, $blk )
{
	$len = strlen($meta);
	printf("== mbsdbg( $name , %x ) = %x\n", $blk, $len);
	for ( $i=0; $i < $len; $i += $blk )
	{
		$n = sprintf("%4x", $i/$blk);
		debug( substr($meta, $i, $blk), $n );
	}
	return;
}

function loadmbs( &$mbs, $sect, $pfx )
{
	$feof = strrpos($mbs, "FEOC");
	$offs = array();
	foreach ( $sect as $k => $v )
	{
		$b1 = str2big($mbs, $v['p'], 4);
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
		$dat = substr($mbs, $v['o'], $sz);

		save_file("$pfx/meta/$k.meta", $dat);
		//mbsdbg($dat, "meta $k", $v['k']);

		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$mbs = $sect;
	return;
}
//////////////////////////////
function mura( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs,0,4) != "FMBS" )
		return;

	if ( str2int($mbs, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	// $siz = str2int($mbs, 4, 3);
	// $hdz = str2int($mbs, 8, 3);
	// $len = 0x10 + $hdz + $siz;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// dummy_npc.mbs
	//        a0  b8  e8 |      1*18 1*30 1*30
	//   118 244 250 168 | 1*50 1*c  1*8  1*18
	//   180 1a4 258 348 | 1*24 5*20 5*30 5*10
	// momohime_battle_drm.mbs
	//            a0   6b8  2cc8 |        41*18 cb*30 7bd*30
	//   1a038 26484 31a7c 1a7b8 | 18*50 f2a*c  f2*8  127*18
	//   1c360 1c5c4 3220c 32e3c | 11*24 4f6*20 41*30  87*10
	// s9[+28] =  85+2 => sa
	// sa[+ 0] = 4ee+8 => s8
	// s8[+ 0] = 126   => s6
	// s6[+10] = f29+1 => s4
	// s4[+ 4] =  ca   => s1 , [+ a] = 7bc => s2
	//
	// s9-sa-s8-s6-s4-s1,s2
	$sect = array(
		array('p' => 0x54 ,'k' => 0x18), // 0
		array('p' => 0x58 ,'k' => 0x30), // 1
		array('p' => 0x5c ,'k' => 0x30), // 2 dummy_npc=0
		array('p' => 0x60 ,'k' => 0x50), // 3 bg=0
		array('p' => 0x64 ,'k' => 0x0c), // 4
		array('p' => 0x68 ,'k' => 0x08), // 5 bg=0
		array('p' => 0x6c ,'k' => 0x18), // 6
		array('p' => 0x70 ,'k' => 0x24), // 7
		array('p' => 0x74 ,'k' => 0x20), // 8
		array('p' => 0x78 ,'k' => 0x30), // 9
		array('p' => 0x7c ,'k' => 0x10), // 10
	);
	loadmbs($mbs, $sect, $pfx);

	sectanim($mbs, $pfx);
	sectspr($mbs, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
