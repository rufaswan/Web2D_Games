<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "quad.inc";

$gp_json = array();
$gp_tag  = '';

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
	//  0  1   2  3  c1
	//  4  5   6  7  c2
	//  8  9  10 11  c3
	// 12 13  14 15  c4
	//   4 1    1-2   12-0-4-8
	//   | | =>   | , 14-2-6-10
	//   3-2    4-3
	$dqd = array(
		$float[12] , $float[13] ,
		$float[ 0] , $float[ 1] ,
		$float[ 4] , $float[ 5] ,
		$float[ 8] , $float[ 9] ,
	);

	$sqd = array(
		$float[14]*$w , $float[15]*$h ,
		$float[ 2]*$w , $float[ 3]*$h ,
		$float[ 6]*$w , $float[ 7]*$h ,
		$float[10]*$w , $float[11]*$h ,
	);

	return;
}

function sect_spr( &$file, $ptgt_off, $w, $h )
{
	global $gp_json;
	$cnt = str2int($file, $ptgt_off+8, 4);
	$off1 = $ptgt_off + 12;
	$off2 = $ptgt_off + 12 + ($cnt * 8);

	for ( $i1=0; $i1 < $cnt; $i1++ )
	{
		// 0 1 2 3  4 5  6 7
		// off?     no   - -
		$no = str2int($file, $off1+4, 2);
			$off1 += 8;

		$data = array();
		for ( $i2=0; $i2 < $no; $i2++ )
		{
			$sqd = array();
			$dqd = array();
			sectquad($file, $off2, $w, $h, $sqd, $dqd);
				$off2 += 0x40;

			$data[$i2] = array(
				'SrcQuad' => $sqd,
				'DstQuad' => $dqd,
				'TexID'   => 0,
			);
		} // for ( $i2=0; $i2 < $no; $i2++ )

		$gp_json['Frame'][$i1] = $data;
	} // for ( $i1=0; $i1 < $cnt; $i1++ )

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
		if ( ($i+1) < $cnt )
			$p2 = str2int($sub, $p+4, 4);
		else
			$p2 = $off2 - $off1;

		$len = $p2 - $p1;
		$dat = substr($sub, $p1, $len);

		$ent = array();
		for ( $i2=0; $i2 < $len; $i2 += 4 )
		{
			$id = str2int($dat, $i2+0, 2);
			$no = str2int($dat, $i2+2, 1, true);
			if ( $no < 0 )
			{
				php_notice("anim_%d[%x] = %x , %d\n", $i, $i2, $id, $no);
				continue;
			}
			$ent[] = array($id,$no);
		}

		$gp_json['Animation']["anim_$i"][0] = $ent;

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}
//////////////////////////////
function gv_rgba32( &$pix )
{
	$dec = '';
	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$dec .= $pix[$i+2];
		$dec .= $pix[$i+1];
		$dec .= $pix[$i+0];
		$dec .= $pix[$i+3];
	} // for ( $i=0; $i < $len; $i += 4 )

	$pix = $dec;
	return;
}

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

	$pix = $dec;
	return;
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
			gv_rgba32($pix);
			break;
		case 5:
			printf("16-bit RGBA = %s\n", $pfx);
			gv_rgba16($pix);
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

	global $gp_json, $gp_tag;
	if ( $gp_tag == '' )
		return php_error('NO TAG %s', $fname);
	$gp_json = load_idtagfile($gp_tag);

	sect_anim($file, $anim_off, $ptgt_off);
	sect_spr ($file, $ptgt_off, $w, $h);

	save_quadfile($pfx, $gp_json);
	return;
}

echo "{$argv[0]}  -gv/-gv2/-gva/-mgv  FILE...\n";
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-gv' :  $gp_tag = 'pc_gv' ; break;
		case '-gv2':  $gp_tag = 'pc_gv2'; break;
		case '-gva':  $gp_tag = 'pc_gva'; break;
		case '-mgv':  $gp_tag = 'pc_mgv'; break;
		default:
			gunvolt( $argv[$i] );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )
