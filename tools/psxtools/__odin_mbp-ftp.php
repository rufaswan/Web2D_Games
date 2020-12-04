<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-quad.inc";

define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix = array();

function sectquad( &$mbp, $pos, $name, $SCALE )
{
	$qax = str2int($mbp, $pos+ 0, 2);
	$qay = str2int($mbp, $pos+ 2, 2);
	$qbx = str2int($mbp, $pos+ 4, 2);
	$qby = str2int($mbp, $pos+ 6, 2);
	$qcx = str2int($mbp, $pos+ 8, 2);
	$qcy = str2int($mbp, $pos+10, 2);
	$qdx = str2int($mbp, $pos+12, 2);
	$qdy = str2int($mbp, $pos+14, 2);
	$qex = str2int($mbp, $pos+16, 2);
	$qey = str2int($mbp, $pos+18, 2);
	$qfx = str2int($mbp, $pos+20, 2);
	$qfy = str2int($mbp, $pos+22, 2);
	$qgx = str2int($mbp, $pos+24, 2);
	$qgy = str2int($mbp, $pos+26, 2);
	$qhx = str2int($mbp, $pos+28, 2);
	$qhy = str2int($mbp, $pos+30, 2);

	if ( $qcx != $qgx )
		php_notice("qcx != qgx [%x,%x]", $qcx, $qgx);
	if ( $qcy != $qgy )
		php_notice("qcy != qgy [%x,%x]", $qcy, $qgy);

	$qcx *= $SCALE;
	$qcy *= $SCALE;
	$qdx *= $SCALE;
	$qdy *= $SCALE;
	$qex *= $SCALE;
	$qey *= $SCALE;
	$qfx *= $SCALE;
	$qfy *= $SCALE;

	$cdef = array(
		array($qcx,$qcy,1),
		array($qdx,$qdy,1),
		array($qex,$qey,1),
		array($qfx,$qfy,1),
	);

	printf("== sectquad( %x , $name , %.2f )\n", $pos, $SCALE);
	printf("    ab %7d,%7d  %7d,%7d\n", $qax, $qay, $qbx, $qby);
	printf("    gh %7d,%7d  %7d,%7d\n", $qgx, $qgy, $qhx, $qhy);
	quad_dump($cdef, "1423", "cdef");
	return $cdef;
}

function load_tm2( &$pix, $tid , $pfx )
{
	global $gp_pix;
	if ( ! isset( $gp_pix[$tid] ) )
	{
		$ftp = load_file("$pfx.$tid.tm2");
		if ( empty($ftp) )
			return;

		$b1 = substr($ftp, 0, 4);
		if ( $b1 == "CLUT" )
		{
			$cc = str2int($ftp,  4, 4);
			$w  = str2int($ftp,  8, 4);
			$h  = str2int($ftp, 12, 4);

			$pal = substr($ftp, 16, $cc*4);
			$dat = substr($ftp, 16 + $cc*4, $w*$h);
			$gp_pix[$tid] = array(
				'w' => $w,
				'h' => $h,
				'd' => clut2rgba($pal, $dat, false),
			);
		} // if ( $b1 == "CLUT" )
		else
		if ( $b1 == "RGBA" )
		{
			$w  = str2int($ftp, 4, 4);
			$h  = str2int($ftp, 8, 4);

			$gp_pix[$tid] = array(
				'w' => $w,
				'h' => $h,
				'd' => substr($ftp, 12, $w*$h*4),
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
function sectspr( &$mbp, $pfx )
{
	return;
}
//////////////////////////////
function sectanim( &$mbp, $pfx )
{
	return;
}
//////////////////////////////
function mbpdbg( &$meta, $name, $blk )
{
	$len = strlen($meta);
	printf("== mbpdbg( $name , %x ) = %x\n", $blk, $len);

	ob_start();
	for ( $i=0; $i < $len; $i += $blk )
	{
		$n = sprintf("%4x", $i/$blk);
		debug( substr($meta, $i, $blk), $n );
	}
	$buf = ob_get_clean();
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

	sectanim($mbp, $pfx);
	sectspr ($mbp, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	odin( $argv[$i] );

/*
 */
