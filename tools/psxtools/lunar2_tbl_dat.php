<?php
require "common.inc";
require "common-quad.inc";
require "lunar2.inc";

define("CANV_S", 0x300);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = "";
$gp_clut = "";

function sectparts( &$meta, $dir )
{
	printf("== sectparts( $dir )\n");

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_pix, $gp_clut;
	$len = strlen($meta);
	$sw = 0x80;
	$sh = strlen($gp_pix) / 0x80;
	for ( $i=0; $i < $len; $i += 0x16 )
	{
		$b1 = ord( $meta[$i+0] );
		$b2 = ord( $meta[$i+1] );
		$sx = ord( $meta[$i+2] );
		$sy = ord( $meta[$i+3] );
		$w  = ord( $meta[$i+4] );
		$h  = ord( $meta[$i+5] );

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $sx, $b2*0x100+$sy, $w, $h, $sw, $sh);
		$pix['src']['pal'] = $gp_clut;
		$pix['bgzero'] = 0;

		sectquad($pix, $meta, $i, $ceil/2);
		printf("parts() %02x , %02x\n", $b1, $b2);

		copyquad($pix, 1);
	} // for ( $i=0; $i < $len; $i += 0x16 )

	savpix($dir, $pix, true);
	return;
}

function sectmeta( &$meta, $dir )
{
	printf("== sectmeta( $dir )\n");
	//save_file("$dir/meta", $meta);

	$ed = str2int($meta, 4, 3);
	$st = str2int($meta, 0, 3);
	if ( $st < 8 )
		return;
	for ( $i=$st; $i < $ed; $i += 4 )
	{
		$num = str2int($meta, $i+0, 2);
		$off = str2int($meta, $i+2, 2);

		$pos = $ed + ($off * 0x16);
		$sub = substr ($meta, $pos, $num*0x16);
		printf("meta() %x , %x\n", $pos, $num*0x16);

		$fn = sprintf("$dir/%04d", ($i-$st)/4);
		sectparts($sub, $fn);
	} // for ( $i=$st; $i < $ed; $i += 4 )

	// tileset
		global $gp_pix, $gp_clut;
		$w = 0x80;
		$h = strlen($gp_pix) / 0x80;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gp_clut;
		$clut .= $gp_pix;
		save_file("$dir/pix.clut", $clut);
	return;
}
//////////////////////////////
function lunar2( $fname )
{
	// for pp*.dat only
	if ( ! preg_match('|pp.*\.dat|i', $fname) )
		return;

	$pfx = str_ireplace('.dat', '', $fname);
	$dat = load_file("$pfx.dat");
	$pal = load_file("$pfx.pal");
	if ( empty($dat) || empty($pal) )
		return;

	$b1 = str2int($dat,  8, 4);
	$b2 = str2int($dat, 12, 4);
	$meta = substr($dat, $b1, $b2-$b1);

	global $gp_pix, $gp_clut;
	$gp_pix  = substr($dat, $b2);
	$gp_clut = $pal;
	sectmeta($meta, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
