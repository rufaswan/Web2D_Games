<?php
require "common.inc";
require "common-quad.inc";
require "lunar2.inc";

define("CANV_S", 0x300);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = "";
$gp_clut = "";

function pix_row2col( &$pix )
{
	// for width > 128
	// rearranged from 0x80 width to 0x100 height
	$len = strlen($pix);
	$w = 0x80;
	$h = (int)($len / $w);
	$row = (int)($h / 0x100);
	printf("len %x h $h row $row\n", $len);

	$canvas = "";
	for ( $y=0; $y < 0x100; $y++ )
	{
		for ( $r=0; $r < $row; $r++ )
		{
			$pos = ($y * 0x80) + ($r * 0x8000);
			$canvas .= substr($pix, $pos, 0x80);
		} // for ( $r=0; $r < $row; $r++ )
	} // for ( $y=0; $y < 0x100; $y++ )

	$pix = $canvas;
	return;
}
//////////////////////////////
function dat_pp( $datn )
{
	// tbl/pp*.dat reuse palette from map/mp*.dat
	echo "== dat_pp( $datn )\n";
	$pfx = substr($datn, 0, strrpos($datn, '.dat'));
	$dat = load_file("$pfx.dat");
	$pal = load_file("$pfx.pal");
	if ( empty($dat) )  return;

	$dir = str_replace('.', '_', $datn);

	$b1 = str2int($dat,  8, 4);
	$b2 = str2int($dat, 12, 4);
	$meta = substr($dat, $b1, $b2-$b1);

	global $gp_pix, $gp_clut;
	$gp_pix = substr($dat, $b2);
	pix_row2col($gp_pix);

	// use sysspr.pck palette for NPC
	// use pp****.pal palette for map objects
	if ( ! empty($gp_clut) )
		sectmeta($meta, "$dir/sys");

	$t = $gp_clut;
	if ( ! empty($pal) )
	{
		$gp_clut = $pal;
		sectmeta($meta, "$dir/mp");
	}
	$gp_clut = $t;
	return;
}

function dat_cde( $datn )
{
	echo "== dat_cde( $datn )\n";
	$dat = file_get_contents($datn);
	if ( empty($dat) )  return;

	$dir = str_replace('.', '_', $datn);

	$b1 = str2int($dat, 0x10, 4);
	$b2 = str2int($dat, 0x14, 4);
	$meta = substr($dat, $b1, $b2-$b1);

	global $gp_pix;
	$gp_pix = substr($dat, $b2);
	pix_row2col($gp_pix);

	sectmeta($meta, "$dir/sys");
	return;
}
//////////////////////////////
function lunar2( $fname )
{
	// 256 color palettes from somewhere...
	if ( stripos($fname, '.pal') !== false )
	{
		global $gp_clut;
		$gp_clut = file_get_contents($fname);
		return;
	}

	// for data/tbl/pp*.dat only
	if ( preg_match('|pp.*\.dat|i', $fname) )
		return dat_pp( $fname );

	// for data/scn/cde*.dat only
	if ( preg_match('|cde.*\.dat|i', $fname) )
		return dat_cde( $fname );
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
