<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "quad.inc";

$gp_json = array();

function sectquad( &$file, $off, $w, $h, &$sqd, &$dqd )
{
	$float =array();
	for ( $i=0; $i < 0x40; $i += 4 )
	{
		$p = $off + $i;
		$b = substr($file, $p, 4);
		$float[] = float32($b);
	}

	// dqd    sqd
	//  1  2   3  4  c1
	//  5  6   7  8  c2
	//  9 10  11 12  c3
	// 13 14  15 16  c4
	//   4 1    1-2   13-1-5-9
	//   | | =>   | , 15-3-7-11
	//   3-2    4-3
	$dqd = array(
		$float[13] , $float[14] ,
		$float[ 1] , $float[ 2] ,
		$float[ 5] , $float[ 6] ,
		$float[ 9] , $float[10] ,
	);

	$sqd = array(
		$float[15]*$w , $float[16]*$h ,
		$float[ 3]*$w , $float[ 4]*$h ,
		$float[ 7]*$w , $float[ 8]*$h ,
		$float[11]*$w , $float[12]*$h ,
	);
	return;
}
//////////////////////////////
function sect_anim( &$file, $off1, $off2 )
{
	$sub = substr($file, $off1, $off2-$off1);

	global $gp_json;
	$cnt = str2int($sub, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 4 + ($i * 4);
		$p1 = str2int($sub, $p+0, 4);
		if ( $i+1 == $cnt )
			$p2 = $off2 - $off1;
		else
			$p2 = str2int($sub, $p+4, 4);

		$len = $p2 - $p1;
		$dat = substr($sub, $p1, $len);

		$ent = array();
		for ( $i2=0; $i2 < $len; $i2 += 4 )
		{
			$id = str2int($dat, $i2+0, 2);
			$no = str2int($dat, $i2+2, 2);

			$ent[] = array($id,$no);
		}

		$gp_json['Animation']["anim_$i"][0] = $ent;

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}
//////////////////////////////
function gv_rgba16( &$pix )
{
	$dec = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$b1 = ord($pix[$i+0]);
		$b2 = ord($pix[$i+1]);

		$b = ($b1 & BIT4) * 0x11;
		$g = ($b1 >>   4) * 0x11;
		$r = ($b2 & BIT4) * 0x11;
		$a = ($b2 >>   4) * 0x11;

		$dec .= chr($r) . chr($g) . chr($b) . chr($a);
	} // for ( $i=0; $i < $len; $i += 2 )

	return $dec;
}

function save_tex( &$file, $pixd_off, $pfx, &$w, &$h )
{
	$sz = str2int($file, $pixd_off+ 0, 4);
	$w  = str2int($file, $pixd_off+ 4, 4);
	$h  = str2int($file, $pixd_off+ 8, 4);
	$ty = str2int($file, $pixd_off+12, 4);

	$pix = substr($file, $pixd_off+0x80, $sz);
	switch ( $ty )
	{
		case 4:
			printf("32-bit RGBA = %s\n", $pfx);
			break;
		case 5:
			printf("16-bit RGBA = %s\n", $pfx);
			$pix = gv_rgba16($pix);
			break;
		default:
			return php_error("UNKNOWN %x = %s", $ty, $pfx);
	} // switch ( $ty )

	$img = array(
		'w' => $w,
		'h' => $h,
		'pix' => $pix,
	);

	save_clutfile("$pfx.0.rgba", $img);
	return;
}
//////////////////////////////
function gunvolt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1  = str2int($file, 12, 4);
	$b2  = str2int($file, 16, 4);
	$len = strlen ($file);
	if ( ($b1+$b2) != $len )
		return;

	$file = substr($file, $b1);
	if ( substr($file,0,4) != 'IOBJ' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$anim_off = str2int($file,  4, 4);
	$ptgt_off = str2int($file,  8, 4);
	$pixd_off = str2int($file, 16, 4);

	if ( substr($file,$ptgt_off,4) != 'PTGT' )
		return;

	$w = 0;
	$h = 0;
	save_tex($file, $pixd_off, $pfx, $w, $h);

	global $gp_json;
	$gp_json = load_idtagfile('pc_gv');

	sect_anim($file, $anim_off, $ptgt_off);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );
