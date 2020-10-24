<?php
require "common.inc";
require "common-quad.inc";

define("CANV_S", 0x100);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = "";
$gp_clut = "";

function sectmeta( &$meta, $dir )
{
	printf("== sectmeta( $dir )\n");
	save_file("$dir/meta", $meta);

	$ed = str2int($meta, 4, 3);
	$st = str2int($meta, 0, 3);
	for ( $i=$st; $i < $ed; $i += 4 )
	{
		$num = str2int($meta, $i+0, 2);
		$off = str2int($meta, $i+2, 2);
		$pos = $ed + ($off * 0x16);
		for ( $j=0; $j < $num; $j++ )
		{
			$p = $pos + ($j * 0x16);

			$b1 = ord( $meta[$p+0] );
			$b2 = ord( $meta[$p+1] );
			$sx = ord( $meta[$p+2] );
			$sy = ord( $meta[$p+3] );
			$w  = ord( $meta[$p+4] );
			$h  = ord( $meta[$p+5] );

			$x1 = sint16( $meta[$p+ 6] . $meta[$p+ 7] );
			$y1 = sint16( $meta[$p+ 8] . $meta[$p+ 9] );
			$x2 = sint16( $meta[$p+10] . $meta[$p+11] );
			$y2 = sint16( $meta[$p+12] . $meta[$p+13] );
			$x3 = sint16( $meta[$p+14] . $meta[$p+15] );
			$y3 = sint16( $meta[$p+16] . $meta[$p+17] );
			$x4 = sint16( $meta[$p+18] . $meta[$p+19] );
			$y4 = sint16( $meta[$p+20] . $meta[$p+21] );
		}
	} // for ( $i=$st; $i < $ed; $i += 4 )
	return;
}
//////////////////////////////
function loadpck( &$file, $dir )
{
	$num = str2int($file, 0, 4);
	printf("== loadpck( $dir ) = $num\n");

	$ed = strlen($file);
	$st = 4;
	$pck = array();
	while ( $st < $ed )
	{
		$siz = str2int($file, $st, 4);
			$st += 4;
		if ( $siz == 0 )
			break;
		printf("%3d  %6x  %6x\n", count($pck), $st, $siz);
		$pck[] = substr($file, $st, $siz);
			$st += $siz;
	}

	$file = $pck;
	return;
}

function lunar2( $fname )
{
	$dir = str_replace('.', '_', $fname);
	$cha = "";
	$spr = "";
	if ( preg_match('|cha[0-9]+\.pck|i', $fname) )
	{
		$cha = load_file($fname);
		$dir   = str_ireplace('cha', ''   , $dir);
		$fname = str_ireplace('cha', 'spr', $fname);
		$spr = load_file($fname);
	}
	else
	if ( preg_match('|spr[0-9]+\.pck|i', $fname) )
	{
		$spr = load_file($fname);
		$dir   = str_ireplace('spr', ''   , $dir);
		$fname = str_ireplace('spr', 'cha', $fname);
		$cha = load_file($fname);
	}
	else
		return;

	if ( empty($cha) || empty($spr) )
		return;
	loadpck($cha, "$dir/cha");
	loadpck($spr, "$dir/spr");

	global $gp_pix, $gp_clut;
	if ( count($cha) == 2 )
	{
		$gp_pix  = $cha[0];
		$gp_clut = pal555( $cha[1] );
		$cha = '';
		sectmeta($spr[1], $dir);
	}
	else
	{
		$gp_pix  = $cha[0];
		$gp_clut = pal555( $spr[2] );
		$cha = '';
		sectmeta($spr[6], $dir);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
