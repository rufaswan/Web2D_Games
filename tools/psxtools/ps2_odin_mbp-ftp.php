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

function sectquad( &$mbp, $pos, $name, $SCALE )
{
	$float = array();
	for ( $i=0; $i < $mbp['k']; $i += 2 )
		$float[] = str2int($mbp, $pos+$i, 2, true) / 0x10;

	if ( $float[ 4] != $float[12] )
		php_notice("float[ 4] != float[12] [%x,%x]", $float[4], $float[12]);
	if ( $float[ 5] != $float[13] )
		php_notice("float[ 5] != float[13] [%x,%x]", $float[5], $float[13]);
	if ( $float[14] != 0 || $float[15] != 0 )
		php_notice("float[14],float[15] not zero [%x,%x]", $float[14], $float[15]);

	// float[2],float[3] is the center point of quad cdef
	//  float[2] == average(float[4],float[8])
	//  float[3] == average(float[5],float[9])

	for ( $i=4; $i < 12; $i++ )
		$float[$i] *= $SCALE;

	$cdef = array(
		array($float[ 4] , $float[ 5] , 1),
		array($float[ 6] , $float[ 7] , 1),
		array($float[ 8] , $float[ 9] , 1),
		array($float[10] , $float[11] , 1),
	);

	printf("== sectquad( %x , $name , %.2f )\n", $pos, $SCALE);
	printf("    a %7.2f,%7.2f  \n", $float[0], $float[1]);
	printf("    b %7.2f,%7.2f  \n", $float[2], $float[3]);
	quad_dump($cdef, "1423", "cdef");
	return $cdef;
}

function load_tm2( &$pix, $tid , $pfx )
{
	global $gp_pix;
	if ( defined("DRY_RUN") )
		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');

	if ( ! isset( $gp_pix[$tid] ) )
	{
		$fn = sprintf("%s.%d.tm2", $pfx, $tid);
		$ftp = load_clutfile($fn);
		if ( $ftp === 0 )
			return php_error("NOT FOUND %s", $fn);

		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');
		if ( isset( $ftp['cc'] ) )
		{
			$gp_pix[$tid]['w'] = $ftp['w'];
			$gp_pix[$tid]['h'] = $ftp['h'];
			$gp_pix[$tid]['d'] = clut2rgba($ftp['pal'], $ftp['pix'], false);
		}
		else
		{
			$gp_pix[$tid]['w'] = $ftp['w'];
			$gp_pix[$tid]['h'] = $ftp['h'];
			$gp_pix[$tid]['d'] = $ftp['pix'];
		}
	} // if ( ! isset( $gp_pix[$tid] ) )

	printf("== load_tm2( $tid , $pfx ) = %x x %x\n", $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	$pix['src']['w'] = $gp_pix[$tid]['w'];
	$pix['src']['h'] = $gp_pix[$tid]['h'];
	$pix['src']['pix'] = &$gp_pix[$tid]['d'];
	$pix['src']['pal'] = "";
	return;
}
//////////////////////////////
function sectpart( &$mbp, $pfx, $k6, $id6, $no6 )
{
	//return;
	printf("== sectpart( $pfx , %d , %x , %x )\n", $k6, $id6, $no6);

	// ERROR : computer run out of memory
	// required CANV_S is too large for *.mbp
	//   auto canvas size detection
	//   auto move center point 0,0 from middle-center to top-left
	//   auto trim is DISABLED
	$data = array();
	$CANV_S = 0;
	$is_mid = false;
	for ( $i4=0; $i4 < $no6; $i4++ )
	{
		$p4 = ($id6 + $i4) * $mbp[4]['k'];

		// 0 1 2 3  4   6  8    a    c    e     10   12   14   16
		// sub      s1  -  s0-0 s0-6 s0-c s0-2  s2-0 s2-6 s2-c s2-2
		$sub = substr ($mbp[4]['d'], $p4+ 0, 4);

		$s1 = str2int($mbp[4]['d'], $p4+ 4, 2); // sx,sy
		$s0 = str2int($mbp[4]['d'], $p4+ 8, 2);
		$s2 = str2int($mbp[4]['d'], $p4+16, 2); // dx,dy

		$sqd = sectquad($mbp[1]['d'], $s1*$mbp[1]['k'], "mbp 1 $s1", 1);
		$dqd = sectquad($mbp[2]['d'], $s2*$mbp[2]['k'], "mbp 2 $s2", SCALE);
		$dv = array($sub, $sqd, $dqd);
		$data[] = $dv;
		//array_unshift($data, $dv);
		printf("DATA  %x , %x\n", $s1, $s2);

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
	$pix = array();

	$origin = ( $is_mid ) ? $ceil / 2 : 0;
	printf("ORIGIN  %d\n", $origin);

	foreach ( $data as $dv )
	{
		list($sub, $sqd, $dqd) = $dv;

		echo debug($sub);
		$s1 = str2int($sub, 0, 2); // ??
		$s3 = ord( $sub[2] ); // mask
		$s4 = ord( $sub[3] ); // tid

		if ( ! isset( $pix[$s1][$s3] ) )
		{
			$pix[$s1][$s3] = COPYPIX_DEF();
			$pix[$s1][$s3]['rgba']['w'] = $ceil;
			$pix[$s1][$s3]['rgba']['h'] = $ceil;
			$pix[$s1][$s3]['rgba']['pix'] = canvpix($ceil,$ceil);
			$pix[$s1][$s3]['alpha'] = "alpha_over";
		}

		$pix[$s1][$s3]['src']['vector'] = $sqd;
		for ( $i=0; $i < 4; $i++ )
		{
			$dqd[$i][0] += $origin;
			$dqd[$i][1] += $origin;
		}
		$pix[$s1][$s3]['vector'] = $dqd;

		load_tm2($pix[$s1][$s3], $s4, $pfx);
		copyquad($pix[$s1][$s3], 4);
	} // foreach ( $data as $dv )

	foreach ( $pix as $s1 => $v1 )
	{
		foreach ( $v1 as $s2 => $v2 )
		{
			$fn = sprintf("$pfx/%x/%04d.%d", $s1, $k6, $s2);
			savepix("$fn", $v2, false);
		}
	}
	return;
}

function sectspr( &$mbp, $pfx )
{
	// s6-s4-s1,s2 [18-18-20,20]
	$len6 = strlen( $mbp[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbp[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2int($mbp[6]['d'], $i6+0x10, 2);
		$no6 = str2int($mbp[6]['d'], $i6+0x14, 1);
		if ( $no6 == 0 )
			continue;

		$k6 = $i6 / $mbp[6]['k'];
		sectpart($mbp, $pfx, $k6, $id6, $no6);

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbp[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$mbp, $pfx )
{
	$anim = "";
	// s9-sa-s8 [30-8-20]
	$len9 = strlen( $mbp[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbp[9]['k'] )
	{
		// 0 4 8 c  10
		// - - - -  name
		// 28 29  2a  2b 2c 2d 2e 2f
		// id     no  -  -  -  -  -
		$name = substr0($mbp[9]['d'], $i9+0x10);
		$id9  = str2int($mbp[9]['d'], $i9+0x28, 2);
		$no9  = str2int($mbp[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbp[10]['k'];

			// 0 1  2 3  4 5 6 7
			// id   no   - - - -
			$ida = str2int($mbp[10]['d'], $pa+0, 2);
			$noa = str2int($mbp[10]['d'], $pa+2, 2);

			$ent = array();
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbp[8]['k'];

				// 0   2 4  6   8 c 10 14 18 1c
				// id  - -  no  - - -  -  -  -
				$id8 = str2int($mbp[8]['d'], $p8+0, 2);
				$no8 = str2int($mbp[8]['d'], $p8+6, 2);

				$ent[] = "$id8-$no8";

			} // for ( $i8=0; $i8 < $noa; $i8++ )

			$anim .= sprintf("%s_%d = ", $name, $ia);
			$anim .= implode(' , ', $ent);
			$anim .= "\n";

		} // for ( $ia=0; $ia < $no9; $ia++ )

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbp[9]['k'] )

	save_file("$pfx/anim.txt", $anim);
	return;
}
//////////////////////////////
function mbpcoldbg( &$mbp, $id, $pos )
{
	$len = strlen( $mbp[$id]['d'] );
	$dbg = array();
	for ( $i=0; $i < $len; $i += $mbp[$id]['k'] )
	{
		$b1 = ord( $mbp[$id]['d'][$i+$pos] );
		if ( ! isset( $dbg[$b1] ) )
			$dbg[$b1] = 0;
		$dbg[$b1]++;
	}

	printf("== mbpcoldbg( %x , %x )\n", $id, $pos);
	foreach ( $dbg as $k => $v )
		printf("  %2x = %8x\n", $k, $v);
	return;
}

function mbpdbg( &$meta, $name, $blk )
{
	printf("== mbpdbg( $name , %x )\n", $blk);
	$buf = debug_block( $meta, $blk );
	//echo "$buf\n";
	save_file("$name.txt", $buf);
	return;
}

function loadmbp( &$mbp, $sect, $pfx )
{
	$offs = array();
	$offs[] = strrpos($mbp, "FEOC");
	foreach ( $sect as $k => $v )
	{
		$b1 = str2int($mbp, $v['p'], 4);
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
		$dat = substr($mbp, $v['o'], $sz);

		//save_file("$pfx/meta/$k.meta", $dat);
		mbpdbg($dat, "$pfx/meta/$k", $v['k']);

		$sect[$k]['d'] = $dat;
	} // foreach ( $sect as $k => $v )

	$mbp = $sect;
	return;
}
//////////////////////////////
function odin( $fname )
{
	$mbp = load_file($fname);
	if ( empty($mbp) )  return;

	if ( substr($mbp,0,4) != "FMBP" )
		return;

	if ( str2int($mbp, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	// $siz = str2int($mbp, 4, 3);
	// $hdz = str2int($mbp, 8, 3);
	// $len = 0x10 + $hdz + $siz;
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	global $gp_pix;
	$gp_pix = array();

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// staff_dummy.mbp
	//        a0 1a0 1e0 |      8*20 2*20 8*20
	//     - 550   - 2e0 |    - 2*18    - 2*18
	//   310 370 580 850 | 2*30 f*20 f*30 d*8
	// s9[+28] = c+1 => sa
	// sa[+ 0] = e+1 => s8
	// s8[]
	//
	// gwendlyn.mbp
	//            a0   6fa0   8260 |         378*20  96*20 9078*20
	// 129160 157080 19a490 12dee0 |  f8*50 2cd6*18 209*8   376*18
	// 1331f0 13b440 19b4d8 19da58 | 2b7*30  de2*20  c8*30  193*8
	// s9[+28] =  190+3  => sa
	// sa[+ 0] =  ddf+3  => s8
	// s8[+ 0] =  375    => s6 , [+ 4] = 2b6
	// s6[+10] = 2cb9+1d => s4 , [+12] = 208+1 => s5
	// s4[]
	// s5[+ 0] =   f7    => s3
	//
	// s9-sa-s8-s6-s4-?
	$sect = array(
		array('p' => 0x54 , 'k' => 0x20), // 0
		array('p' => 0x58 , 'k' => 0x20), // 1
		array('p' => 0x5c , 'k' => 0x20), // 2
		array('p' => 0x60 , 'k' => 0x50), // 3 area=0
		array('p' => 0x64 , 'k' => 0x18), // 4
		array('p' => 0x68 , 'k' => 0x08), // 5 area=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x30), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x08), // 10
	);
	loadmbp($mbp, $sect, $pfx);
	mbpcoldbg($mbp, 4, 0); //
	mbpcoldbg($mbp, 4, 1); // = 0
	mbpcoldbg($mbp, 4, 2); // 0 1 2

	sectanim($mbp, $pfx);
	sectspr ($mbp, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	odin( $argv[$i] );

/*
mbp 4-01 valids
	grim 1 3 5 7 9 d 11 29
	odin 0 1 2 3 4 5 6 7 9 b d f 10 11 13 15 19 21 28 29 2d 2f 31 35 39
mbp 4-2 valids
	0 1 2

	0   ---- ----
	1   ---- ---1
	2   ---- --1-
	3   ---- --11
	4   ---- -1--
	5   ---- -1-1
	6   ---- -11-
	7   ---- -111
	9   ---- 1--1
	b   ---- 1-11
	d   ---- 11-1
	f   ---- 1111
	10  ---1 ----
	11  ---1 ---1
	13  ---1 --11
	15  ---1 -1-1
	19  ---1 1--1
	21  --1- ---1
	28  --1- 1---
	29  --1- 1--1
	2d  --1- 11-1
	2f  --1- 1111
	31  --11 ---1
	35  --11 -1-1
	39  --11 1--1

gwendlyn
	3 = eyes
	9 = light
	1+29 = spear + tip light
	2d = effects


bg 88d3d4 + fg 26cbe6 = a3ffff
 */
