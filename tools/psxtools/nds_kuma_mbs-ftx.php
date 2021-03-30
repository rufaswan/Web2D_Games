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
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$b = substr($mbs['d'], $pos+$i, 4);
		$float[] = float32($b) * $SCALE;
	}

	if ( $float[2] != $float[10] )
		php_notice("float[2] != float[10] [%.2f,%.2f]", $float[2], $float[10]);
	if ( $float[3] != $float[11] )
		php_notice("float[3] != float[11] [%.2f,%.2f]", $float[3], $float[11]);

	$bcde = array(
		array($float[2] , $float[3] , 1),
		array($float[4] , $float[5] , 1),
		array($float[6] , $float[7] , 1),
		array($float[8] , $float[9] , 1),
	);

	printf("== sectquad( %x , $name , %.2f )\n", $pos, $SCALE);
	printf("    af %7.2f,%7.2f\n", $float[0], $float[1]);
	quad_dump($bcde, "1423", "bcde");
	return $bcde;
}

function load_tpl( &$pix, $tid , $pfx )
{
	global $gp_pix;
	if ( defined("DRY_RUN") )
		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');

	if ( ! isset( $gp_pix[$tid] ) )
	{
		$fn = sprintf("%s.%d.tpl", $pfx, $tid);
		$ftx = load_clutfile($fn);
		if ( $ftx === 0 )
			return php_error("NOT FOUND %s", $fn);

		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');
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
function sectpart( &$mbs, $pfx, $k6, $id6, $no6 )
{
	//return;
	printf("== sectpart( $pfx , %d, %x , %x )\n", $k6, $id6, $no6);

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

		// 0 1 2 3  4 5  6 7  8 9  a b
		// sub      s1   - -  s0   s2
		$sub = substr ($mbs[4]['d'], $p4+ 0, 4);
		$s0  = str2int($mbs[4]['d'], $p4+ 8, 2);

		$s1  = str2int($mbs[4]['d'], $p4+ 4, 2); // sx,sy
		$s2  = str2int($mbs[4]['d'], $p4+10, 2); // dx,dy
		$sqd = sectquad($mbs[1], $s1*$mbs[1]['k'], "mbs 1 $s1", 1);
		$dqd = sectquad($mbs[2], $s2*$mbs[2]['k'], "mbs 2 $s2", SCALE);

		$dv = array($sub, $s0, $sqd, $dqd);
		$data[] = $dv;
		//array_unshift($data, $dv);
		printf("DATA  %x , %x , %x\n", $s0, $s1, $s2);

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
		list($sub, $s0, $sqd, $dqd) = $dv;

		echo debug($sub);
		$s1 = str2int($sub, 0, 2); // ?type?
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

		load_tpl($pix[$s1][$s3], $s4, $pfx);
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

function sectspr( &$mbs, $pfx )
{
	// s6-s4-s1,s2 [18-c-30,30]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2int($mbs[6]['d'], $i6+0x10, 2);
		$no6 = str2int($mbs[6]['d'], $i6+0x14, 1);
		if ( $no6 == 0 )
			continue;

		$k6 = $i6 / $mbs[6]['k'];
		sectpart($mbs, $pfx, $k6, $id6, $no6);

	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$mbs, $pfx )
{
	$anim = "";
	// s9-sa-s8 [30-8-20]
	$len9 = strlen( $mbs[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )
	{
		// 0 4 8 c  10
		// - - - -  name
		// 28 29  2a  2b 2c 2d 2e 2f
		// id     no  -  -  -  -  -
		$name = substr0($mbs[9]['d'], $i9+0x10);
		$id9  = str2int($mbs[9]['d'], $i9+0x28, 2);
		$no9  = str2int($mbs[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbs[10]['k'];

			// 0 1  2 3  4 5 6 7
			// id   no   - - - -
			$ida = str2int($mbs[10]['d'], $pa+0, 2);
			$noa = str2int($mbs[10]['d'], $pa+2, 2);

			$ent = array();
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbs[8]['k'];

				// 0   2 4  6   8 c 10 14 18 1c
				// id  - -  no  - - -  -  -  -
				$id8 = str2int($mbs[8]['d'], $p8+0, 2);
				$no8 = str2int($mbs[8]['d'], $p8+6, 2);

				$ent[] = "$id8-$no8";

			} // for ( $i8=0; $i8 < $noa; $i8++ )

			$anim .= sprintf("%s_%d = ", $name, $ia);
			$anim .= implode(' , ', $ent);
			$anim .= "\n";

		} // for ( $ia=0; $ia < $no9; $ia++ )

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )

	save_file("$pfx/anim.txt", $anim);
}
//////////////////////////////
function kuma( $fname )
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

	//   0 1 2 |
	// 3 4 5 6 |
	// 7 8 9 a |
	// reform01b.mbs
	//        a0  d0 100 |  -   2*18 1*30 2*30
	//     - 1bc   - 160 |  -   2*c   -   1*18
	//   178 19c 1d4 204 | 1*24 1*20 1*30 1*10
	//
	// k_item00.mbs
	//            a0   358  5ff8 |    -     1d*18 1ee*30 857*30
	//   1f048 6954c 768e4 1f098 |   4*14 11a2*c    1*20 30b*18
	//   239a0 2832c 76904 7bee4 | 20b*24 2091*20 1ca*30 310*10
	// s9[+28] =  30c+4 => sa
	// sa[+ 0] = 208d+4 => s8
	// s8[+ 0] =  30a   => s6
	// s6[+10] = 11a0+2 => s4 , [+12] =  3+1 => s3
	// s4[+ 4] =  1ed   => s1 , [+ 8] = 1c   => s0 , [+ a] = 856 => s2
	// s2
	// s0
	// s1
	// s3
	$sect = array(
		array('p' => 0x54 , 'k' => 0x18), // 0
		array('p' => 0x58 , 'k' => 0x30), // 1
		array('p' => 0x5c , 'k' => 0x30), // 2
		array('p' => 0x60 , 'k' => 0x14), // 3 reform=0
		array('p' => 0x64 , 'k' => 0xc ), // 4
		array('p' => 0x68 , 'k' => 0x20), // 5 reform=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x24), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x10), // a
	);
	file2sect($mbs, $sect, $pfx, array('str2int', 4), strrpos($mbs, "FEOC"), true);

	sectanim($mbs, $pfx);
	sectspr ($mbs, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );
