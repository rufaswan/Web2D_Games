<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";

define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix = array();

function sectquad( &$mbs, $pos, $name, $SCALE )
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
		php_notice("qbx != qfx [%.2f,%.2f]", $qbx, $qfx);
	if ( $qby != $qfy )
		php_notice("qby != qfy [%.2f,%.2f]", $qby, $qfy);

	$qbx *= $SCALE;
	$qby *= $SCALE;
	$qcx *= $SCALE;
	$qcy *= $SCALE;
	$qdx *= $SCALE;
	$qdy *= $SCALE;
	$qex *= $SCALE;
	$qey *= $SCALE;

	$bcde = array(
		array($qbx,$qby,1),
		array($qcx,$qcy,1),
		array($qdx,$qdy,1),
		array($qex,$qey,1),
	);

	printf("== sectquad( %x , $name , %.2f )\n", $pos, $SCALE);
	printf("    af %7.2f,%7.2f\n", $qax, $qay);
	quad_dump($bcde, "1423", "bcde");
	return $bcde;
}

function load_tpl( &$pix, $tid , $pfx )
{
	global $gp_pix;
	if ( ! isset( $gp_pix[$tid] ) )
	{
		$fn = sprintf("%s.%d.tpl", $pfx, $tid);
		$ftx = load_clutfile($fn);
		if ( $ftx === 0 )
			return php_error("NOT FOUND %s", $fn);

		$gp_pix[$tid] = array();
		if ( isset( $ftx['cc'] ) )
		{
			$gp_pix[$tid]['w'] = $ftx['w'];
			$gp_pix[$tid]['h'] = $ftx['h'];
			$gp_pix[$tid]['d'] = clut2rgba($ftx['pal'], $ftx['pix'], false);
		}
		else
		{
			$gp_pix[$tid]['w'] = $ftx['w'];
			$gp_pix[$tid]['h'] = $ftx['h'];
			$gp_pix[$tid]['d'] = $ftx['pix'];
		}
	} // if ( ! isset( $gp_pix[$tid] ) )

	printf("== load_tpl( $tid , $pfx ) = %x x %x\n", $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
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

	// ERROR : computer run out of memory
	// required CANV_S is too large for bg/*.mbs
	//   auto canvas size detection
	//   auto move center point 0,0 from middle-center to top-left
	//   auto trim is DISABLED
	$data = array();
	$CANV_S = 0;
	$is_mid = false;
	for ( $i4=0; $i4 < $no6; $i4++ )
	{
		$p4 = ($id6 + $i4) * $mbs[4]['k'];

		// 0  1    2    3    4 5  6 7  8 9  a b
		// -  typ  trn  tid  s1   - -  s0   s2
		$typ = str2big($mbs[4]['d'], $p4+ 1, 1);
		$trn = str2big($mbs[4]['d'], $p4+ 2, 1);
		$s0  = str2big($mbs[4]['d'], $p4+ 8, 2);

		$tid = str2big($mbs[4]['d'], $p4+ 3, 1);
		$s1  = str2big($mbs[4]['d'], $p4+ 4, 2); // sx,sy
		$s2  = str2big($mbs[4]['d'], $p4+10, 2); // dx,dy

		$sqd = sectquad($mbs[1]['d'], $s1*$mbs[1]['k'], "mbs 1 $s1", 1);
		$dqd = sectquad($mbs[2]['d'], $s2*$mbs[2]['k'], "mbs 2 $s2", SCALE);
		$dv = array($typ, $trn, $s0, $tid, $sqd, $dqd);
		$data[] = $dv;
		//array_unshift($data, $dv);
		printf("DATA  %d , %2x , %2x , %x\n", $tid, $typ, $trn, $s0);

		// detect origin and canvas size
		for ( $i=0; $i < 4; $i++ )
		{
			$s1 = abs( $dqd[$i][0] );
			$s2 = abs( $dqd[$i][1] );
			if ( $s1 > $CANV_S )  $CANV_S = $s1 + 1;
			if ( $s2 > $CANV_S )  $CANV_S = $s2 + 1;
			if ( $dqd[$i][0] < 0 || $dqd[$i][1] < 0 )
				$is_mid = true;
		} // for ( $i=0; $i < 4; $i++ )
		printf("CANV_S  %d\n", $CANV_S);

	} // for ( $i4=0; $i4 < $no6; $i4++ )
	if ( empty($data) )
		return;

	$ceil = ( $is_mid ) ? int_ceil($CANV_S*2, 16) : int_ceil($CANV_S, 16);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	$origin = ( $is_mid ) ? $ceil / 2 : 0;
	printf("ORIGIN  %d\n", $origin);

	foreach ( $data as $dv )
	{
		list($typ, $trn, $s0, $tid, $sqd, $dqd) = $dv;

		// 00  ok
		// +01 normal
		// +02 eyes , leaf
		// +04 censored / broken
		// +08 ok
		// +10 ok
		// +20 has trn == 1 or 2
		// +40 --
		// +80 --
		if ( $typ & 2 || $typ & 4 || $typ & 0x20 )
			continue;

		// TODO
		//   get the alpha-blending formula right
		//   make it blueish (save portal npc)
		//$s0 = substr($mbs[0]['d'], $s0*0x18, 4);
		if ( $trn == 1 || $trn == 2 )
			continue;

		$pix['alpha'] = "alpha_over";
		if ( $trn == 1 )  $pix['alpha'] = "alpha_add";
		if ( $trn == 2 )  $pix['alpha'] = "alpha_add";

		$pix['src']['vector'] = $sqd;
		for ( $i=0; $i < 4; $i++ )
		{
			$dqd[$i][0] += $origin;
			$dqd[$i][1] += $origin;
		}
		$pix['vector'] = $dqd;

		load_tpl($pix, $tid, $pfx);
		copyquad($pix, 4);
	} // foreach ( $data as $dv )

	savepix($dir, $pix, false);
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
function mbscoldbg( &$mbs, $id, $pos )
{
	$len = strlen( $mbs[$id]['d'] );
	$dbg = array();
	for ( $i=0; $i < $len; $i += $mbs[$id]['k'] )
	{
		$b1 = ord( $mbs[$id]['d'][$i+$pos] );
		if ( ! isset( $dbg[$b1] ) )
			$dbg[$b1] = 0;
		$dbg[$b1]++;
	}

	printf("== mbscoldbg( %x , %x )\n", $id, $pos);
	foreach ( $dbg as $k => $v )
		printf("  %2x = %8x\n", $k, $v);
	return;
}

function mbsdbg( &$meta, $name, $blk )
{
	printf("== mbsdbg( $name , %x )\n", $blk);
	$buf = debug_block( $meta, $blk );
	//echo "$buf\n";
	save_file("$name.txt", $buf);
	return;
}

function loadmbs( &$mbs, $sect, $pfx )
{
	$offs = array();
	$offs[] = strrpos($mbs, "FEOC");
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
		if ( ! isset( $v['o'] ) )
			continue;
		$id = array_search($v['o'], $offs);
		$sz = int_floor($offs[$id+1] - $v['o'], $v['k']);
		$dat = substr($mbs, $v['o'], $sz);

		//save_file("$pfx/meta/$k.meta", $dat);
		mbsdbg($dat, "$pfx/meta/$k", $v['k']);

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

	global $gp_pix;
	$gp_pix = array();

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// dummy_npc.mbs
	//        a0  b8  e8 |      1*18 1*30 1*30
	//   118 244 250 168 | 1*50 1*c  1*8  1*18
	//   180 1a4 258 348 | 1*24 5*20 5*30 5*10
	// s9[+28] = 4+1 => s8/sa
	// sa[+ 0] = 4+1 => s8
	// s8[]
	//
	// momohime_battle_drm.mbs
	//            a0   6b8  2cc8 |        41*18 cb*30 7bd*30
	//   1a038 26484 31a7c 1a7b8 | 18*50 f2a*c  f2*8  127*18
	//   1c360 1c5c4 3220c 32e3c | 11*24 4f6*20 41*30  87*10
	// s9[+28] =  85+2 => sa
	// sa[+ 0] = 4ee+8 => s8
	// s8[+ 0] = 126   => s6
	// s6[+10] = f29+1 => s4
	// s4[+ 4] =  ca   => s1 , [+ 8] = 40 => s0 , [+ a] = 7bc => s2
	// s1[]
	// s2[]
	//
	// s9-sa-s8-s6-s4-[s1,s2]
	$sect = array(
		array('p' => 0x54 , 'k' => 0x18), // 0
		array('p' => 0x58 , 'k' => 0x30), // 1
		array('p' => 0x5c , 'k' => 0x30), // 2 dummy_npc=0
		array('p' => 0x60 , 'k' => 0x50), // 3 bg=0
		array('p' => 0x64 , 'k' => 0x0c), // 4
		array('p' => 0x68 , 'k' => 0x08), // 5 bg=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x24), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x10), // 10
	);
	loadmbs($mbs, $sect, $pfx);
	mbscoldbg($mbs, 4, 1); // byte censored parts check
	mbscoldbg($mbs, 4, 2); // byte alpha add blending check

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );

/*
mbs files = fe6ea
	type
		00  ---- ----    de7
		01  ---- ---1  ed060  *normal*
		02  ---- --1-      2
		03  ---- --11   2432
		04  ---- -1--     8d
		05  ---- -1-1   39e1
		07  ---- -111     a4
		09  ---- 1--1      4
		11  ---1 ---1    495
		13  ---1 --11      1
		15  ---1 -1-1      4
		29  --1- 1--1   26cb
		2d  --1- 11-1   7df4
	trn
		00  e2008  *normal*
		01  1878a
		02   3f58

type != 1 3 5
	07  /bg/bg02/b_00.mbs
	07  /bg/bg04_02.mbs
	04  /bg/bg20_00.mbs
	04  /bg/bg21c/f_00.mbs
	11  /bg/bg23a_00.mbs
	04  /bg/bg30/b/c_00.mbs
	04  /bg/bg33_00.mbs
	04  /bg/bg37_00.mbs
	00  /bg/bg_share02a_00.mbs
	00  /bg/bg_test.mbs
	00  /bg/staffroll_kc.mbs

	09  /char/Karasutengu00/01.mbs
	11  /char/keukegen00.mbs
	11 29 2d  /char/Kongaradoji00.mbs
	11  /char/musya00.mbs
	11  /char/Seitakadoji00.mbs
	00 04  /char/tokugawa00/04.mbs
	00  /char/umibouzu00.mbs
	11  /char/Yukionna00.mbs

	29 2d  /drm_char/Kisuke_Battle_drm.mbs
	02  /drm_char/Kongiku_drm.mbs
	2d  /drm_char/Momohime_Battle_drm.mbs
	11  /drm_char/MomohimeE_Battle_drm.mbs
	2d  /drm_char/MomohimeH_Rest_drm.mbs
	11 13 15  /drm_char/Momokurousoul_drm.mbs
	00  /drm_char/Oooni_Stomach_drm00.mbs

trn != 0 1
	02  /char/Ashiba_A00.mbs
	02  /char/bourei00.mbs
	02  /char/dragon00.mbs
	02  /char/Fudoumyouou00.mbs
	02  /char/Fudoumyouou_A/B/C/D/E/F00.mbs
	02  /char/gaki00/01/02.mbs
	02  /char/Genin00/01/02.mbs
	02  /char/Gozu00.mbs
	02  /char/Karakasa00.mbs
	02  /char/Karasutengu00/01.mbs
	02  /char/keukegen00.mbs
	02  /char/Kisuke_Rest.mbs
	02  /char/Kongaradoji00.mbs
	02  /char/Mezu00.mbs
	02  /char/mukade00.mbs
	02  /char/musya00.mbs
	02  /char/nue00.mbs
	02  /char/Ochimusya00/01.mbs
	02  /char/Oni00.mbs

	02  /drm_char/EchigoyaB_drm00.mbs
	02  /drm_char/Fudoumyouou_drm00.mbs
	02  /drm_char/Kisuke_Battle_drm.mbs
	02  /drm_char/Morahime_drm00.mbs
	02  /drm_char/musya_drm00.mbs
	02  /drm_char/Ochimusya_drm00/01.mbs
	02  /drm_char/Oooni_Stomach_drm00.mbs
	02  /drm_char/tokugawa_drm00.mbs
	02  /drm_char/wanyuudou_drm00.mbs
 */
