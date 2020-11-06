<?php
require "common.inc";
require "common-guest.inc";

define("CANV_S", 0x200);
define("SCALE", 1);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function load_texx( $pfx, $id )
{
	printf("== load_texx( $pfx , $id )\n");
	global $gp_pix, $gp_clut;
	if ( ! isset( $gp_pix[$id] ) )
	{
		$ftx = load_file("$pfx.$id.tpl");
		if ( empty($ftx) )
			return;
		$b1 = substr($ftx, 0, 4);
		if ( $b1 == "CLUT" )
		{
			$cc = str2int($ftx,  4, 4);
			$w  = str2int($ftx,  8, 4);
			$h  = str2int($ftx, 12, 4);
			$gp_clut[$id] = substr($ftx, 16, $cc*4);
			$gp_pix [$id] = substr($ftx, 16 + $cc*4, $w*$h);
		}
		if ( $b1 == "RGBA" )
		{
			$w  = str2int($ftx, 4, 4);
			$h  = str2int($ftx, 8, 4);
			$gp_clut[$id] = "";
			$gp_pix [$id] = substr($ftx, 12, $w*$h*4);
		}
	}
	return;
}
//////////////////////////////
function sects1( $pfx, &$s1 )
{
	$len = strlen($s1);
	printf("== sects1( $pfx ) = %x\n", $len);
	for ( $i=0; $i < $len; $i += 0x30 )
	{
		if ( ! isset($s1[$i+0x2f]) )
			continue;
		//debug( substr($s1, $i, 0x30) );

		$qax = float32( substrrev($s1, $i+ 0, 4) );
		$qay = float32( substrrev($s1, $i+ 4, 4) );
		$qbx = float32( substrrev($s1, $i+ 8, 4) );
		$qby = float32( substrrev($s1, $i+12, 4) );
		$qcx = float32( substrrev($s1, $i+16, 4) );
		$qcy = float32( substrrev($s1, $i+20, 4) );
		$qdx = float32( substrrev($s1, $i+24, 4) );
		$qdy = float32( substrrev($s1, $i+28, 4) );
		$qex = float32( substrrev($s1, $i+32, 4) );
		$qey = float32( substrrev($s1, $i+36, 4) );
		$qfx = float32( substrrev($s1, $i+40, 4) );
		$qfy = float32( substrrev($s1, $i+44, 4) );

		if ( $qbx != $qfx )
			trigger_error("qbx != qfx [$qbx,$qfx]\n", E_USER_NOTICE);
		if ( $qby != $qfy )
			trigger_error("qby != qfy [$qby,$qfy]\n", E_USER_NOTICE);

		printf("s1 %x\n", $i / 0x30);
		printf("  bcde %7.2f,%7.2f  %7.2f,%7.2f\n", $qbx, $qby, $qcx, $qcy);
		printf("       %7.2f,%7.2f  %7.2f,%7.2f\n", $qex, $qey, $qdx, $qdy);
		printf("    af %7.2f,%7.2f  %7.2f,%7.2f\n", $qax, $qay, $qfx, $qfy);

	} // for ( $i=0; $i < $len; $i += 0x30 )
	return;
}
//////////////////////////////
function sects8( $pfx, &$s8 )
{
	$len = strlen($s8);
	printf("== sects8( $pfx ) = %x\n", $len);
	for ( $i=0; $i < $len; $i += 0x20 )
	{
		if ( ! isset($s8[$i+0x1f]) )
			continue;
		debug( substr($s8, $i, 0x20) );

	}
	return;
}

function sectsa( $pfx, &$sa, &$s8 )
{
	$len = strlen($sa);
	printf("== sectsa() = %x\n", $len);
	for ( $i=0; $i < $len; $i += 0x10 )
	{
		if ( ! isset($sa[$i+0x0f]) )
			continue;
		debug( substr($sa, $i, 0x10) );

		$b1 = str2big($sa, $i+0, 2);
		$b2 = str2big($sa, $i+2, 2);
		$b3 = str2big($sa, $i+4, 4);
		printf("sa %x %x %x\n", $b1, $b2, $b3);

		$dir = sprintf("$pfx/%d", $i/0x10);
		$ss8 = substr($s8, $b1*0x20, $b2*0x20);
		sects8($dir, $ss8);
	}
	return;
}

function sects9( $pfx, &$s9, &$sa, &$s8 )
{
	$len = strlen($s9);
	printf("== sects9() = %x\n", $len);
	for ( $i=0; $i < $len; $i += 0x30 )
	{
		if ( ! isset($s9[$i+0x2f]) )
			continue;
		debug( substr($s9, $i, 0x30) );

		$str = substr0($s9, $i+0x10);
		$b1 = str2big($s9, $i+0x28, 2);
		$b2 = str2big($s9, $i+0x2a, 1);
		printf("s9 %x %x %s\n", $b1, $b2, $str);

		$dir = sprintf("$pfx/%s", $str);
		$ssa = substr($sa, $b1*0x10, $b2*0x10);
		sectsa($dir, $ssa, $s8);
	}
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
	$feoc = strpos($mbs, 'FEOC');

	global $gp_pix, $gp_clut;
	$gp_pix  = array();
	$gp_clut = array();

	//$num1 = str2big($mbs, 0x3c, 2); // no1 * 0x18
	//$num2 = str2big($mbs, 0x3e, 2); // no quad * 0x30
	//$num3 = str2big($mbs, 0x40, 2); // no3 distort * 0x30
	//$num4 = str2big($mbs, 0x42, 2); // no5 distort set * 12
	//$num5 = str2big($mbs, 0x44, 2); // no4 * 0x50
	//$num6 = str2big($mbs, 0x46, 2); // no6 * 8
	//$num7 = str2big($mbs, 0x48, 2); // no8 * 0x24
	//$num8 = str2big($mbs, 0x4a, 2); // no9 * 0x18
	//$num9 = str2big($mbs, 0x4c, 2); // no7 anim
	//$num10 = str2big($mbs, 0x4e, 2); // no10 anim name
	//$num11 = str2big($mbs, 0x50, 2); // no11 anim set
	//$num12 = str2big($mbs, 0x52, 2);

	$off0 = str2big($mbs, 0x54, 4);
	$off1 = str2big($mbs, 0x58, 4);
	$off2 = str2big($mbs, 0x5c, 4);
	$off3 = str2big($mbs, 0x60, 4);
	$off4 = str2big($mbs, 0x64, 4);
	$off5 = str2big($mbs, 0x68, 4);
	$off6 = str2big($mbs, 0x6c, 4);
	$off7 = str2big($mbs, 0x70, 4);
	$off8 = str2big($mbs, 0x74, 4);
	$off9 = str2big($mbs, 0x78, 4);
	$offa = str2big($mbs, 0x7c, 4);

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
	$s0 = substr($mbs, $off0, $off1-$off0); // def * 0x18
	$s1 = substr($mbs, $off1, $off2-$off1); // quad def * 0x30
	$s2 = substr($mbs, $off2, $off3-$off2); // distort def * 0x30 , dummy_npc=0
	$s3 = substr($mbs, $off3, $off6-$off3); // def * 0x50 , bg=0
	$s4 = substr($mbs, $off4, $off5-$off4); // distort set def * 12
	$s5 = substr($mbs, $off5, $off9-$off5); // def * 8 , bg=0
	$s6 = substr($mbs, $off6, $off7-$off6); // def * 0x18
	$s7 = substr($mbs, $off7, $off8-$off7); // def * 0x24
	$s8 = substr($mbs, $off8, $off4-$off8); // anim def * 0x20
	$s9 = substr($mbs, $off9, $offa-$off9); // anim name def * 0x30
	$sa = substr($mbs, $offa, $feoc-$offa); // anim set def * 16
		save_file("$pfx/s0.meta", $s0);
		save_file("$pfx/s1.meta", $s1);
		save_file("$pfx/s2.meta", $s2);
		save_file("$pfx/s3.meta", $s3);
		save_file("$pfx/s4.meta", $s4);
		save_file("$pfx/s5.meta", $s5);
		save_file("$pfx/s6.meta", $s6);
		save_file("$pfx/s7.meta", $s7);
		save_file("$pfx/s8.meta", $s8);
		save_file("$pfx/s9.meta", $s9);
		save_file("$pfx/sa.meta", $sa);

	//sects9($pfx, $s9, $sa, $s8);
	sects1($pfx, $s1);
	sects1($pfx, $s2);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
