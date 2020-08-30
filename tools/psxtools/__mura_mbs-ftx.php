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

function mura( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs,0,4) != "FMBS" )
		return;

	if ( str2int($mbs, 8, 3) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	// $siz = str2int($mbs, 4, 3);
	// $hdz = str2int($mbs, 8, 3);
	// $len = 0x10 + $hdz + $siz;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	global $gp_pix, $gp_clut;
	$gp_pix  = array();
	$gp_clut = array();

	//$num1 = str2big($mbs, 0x3c, 2); // no1 * 0x18
	//$num2 = str2big($mbs, 0x3e, 2); // no2 * 0x30
	$num3 = str2big($mbs, 0x40, 2); // no3 distort * 0x30
	$num4 = str2big($mbs, 0x42, 2); // no5 distort set * 12
	//$num5 = str2big($mbs, 0x44, 2); // no4 * 0x50
	//$num6 = str2big($mbs, 0x46, 2); // no6 * 8
	//$num7 = str2big($mbs, 0x48, 2); // no8 * 0x24
	//$num8 = str2big($mbs, 0x4a, 2); // no9 * 0x18
	$num9 = str2big($mbs, 0x4c, 2); // no7 anim
	$num10 = str2big($mbs, 0x4e, 2); // no10 anim name
	$num11 = str2big($mbs, 0x50, 2); // no11 anim set
	//$num12 = str2big($mbs, 0x52, 2);

	//$off1 = str2big($mbs, 0x54, 3); // def * 0x18
	//$off2 = str2big($mbs, 0x58, 3); // def * 0x30
	$off3 = str2big($mbs, 0x5c, 3); // distort def * 0x30 , dummy_npc=0
	//$off4 = str2big($mbs, 0x60, 3); // def * 0x50 , bg=0
	$off5 = str2big($mbs, 0x64, 3); // distort set def * 12
	//$off6 = str2big($mbs, 0x68, 3); // def * 8 , bg=0
	//$off7 = str2big($mbs, 0x6c, 3); // def * 0x18
	//$off8 = str2big($mbs, 0x70, 3); // def * 0x24
	$off9 = str2big($mbs, 0x74, 3); // anim def * 0x20
	$off10 = str2big($mbs, 0x78, 3); // anim name def * 0x30
	$off11 = str2big($mbs, 0x7c, 3); // anim set def * 16



	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );
