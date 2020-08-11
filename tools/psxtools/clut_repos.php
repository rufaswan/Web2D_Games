<?php
require "common.inc";
require "common-guest.inc";

define("CANV_S", 0x400);
$gp_adj = array(0,0);
//////////////////////////////
function trimrgba_rect( &$pix )
{
	$x1 = 0;
	$x2 = $pix['rgba']['w'];
	$y1 = 0;
	$y2 = $pix['rgba']['h'];
	$TRIM_SZ = 8;

	// trim height
	while (1)
	{
		$row = $pix['rgba']['w'] * 4;
		$b = "";

		$p = $y1 * $row;
		$b .= substr($pix['rgba']['pix'], $p, $row*$TRIM_SZ);

		$p = ($y2 - $TRIM_SZ) * $row;
		$b .= substr($pix['rgba']['pix'], $p, $row*$TRIM_SZ);

		if ( trim($b, ZERO) != '' )
			break;

		$y1 += $TRIM_SZ;
		$y2 -= $TRIM_SZ;
	}
	// trim width
	while (1)
	{
		$row = $pix['rgba']['w'] * 4;
		$b = "";

		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + ($x1 * 4);
			$b .= substr($pix['rgba']['pix'], $p, 4*$TRIM_SZ);
		}
		for ( $y=$y1; $y < $y2; $y++ )
		{
			$p = ($y * $row) + (($x2-$TRIM_SZ) * 4);
			$b .= substr($pix['rgba']['pix'], $p, 4*$TRIM_SZ);
		}

		if ( trim($b, ZERO) != '' )
			break;

		$x1 += $TRIM_SZ;
		$x2 -= $TRIM_SZ;
	}
	printf("TRIM rect %4d , %4d , %4d , %4d\n", $x1, $y1, $x2, $y2);

	$w = $x2 - $x1;
	$h = $y2 - $y1;
	$canv = "";
	for ( $y=$y1; $y < $y2; $y++ )
	{
		$p = $y * $pix['rgba']['w'] + $x1;
		$canv .= substr($pix['rgba']['pix'], $p*4, $w*4);
	}
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = $canv;
	return;
}

function savrgba_rect( $fn, &$pix )
{
	if ( defined("DRY_RUN") )
		return;

	if ( trim($pix['rgba']['pix'], ZERO) == '' )
		return trigger_error("ERROR $fn [pix] blank\n", E_USER_WARNING);

	trimrgba_rect($pix);
	$rgba = "RGBA";
	$rgba .= chrint( $pix['rgba']['w'], 4 );
	$rgba .= chrint( $pix['rgba']['h'], 4 );
	$rgba .= $pix['rgba']['pix'];
	save_file("$fn", $rgba);
	return;
}

function repos( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_adj;
	$mgc = substr($file, 0, 4);
	if ( $mgc == "CLUT" )
	{
		$cc = str2int($file,  4, 4);
		$w  = str2int($file,  8, 4);
		$h  = str2int($file, 12, 4);
		$pal = substr($file, 16,  $cc*4);
		$dat = substr($file, 16 + $cc*4, $w*$h);

		$pix = COPYPIX_DEF();
		$pix['rgba']['w'] = CANV_S;
		$pix['rgba']['h'] = CANV_S;
		$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

		$pix['dx'] = ($w/-2) + (CANV_S/2) + $gp_adj[0];
		$pix['dy'] = ($h/-2) + (CANV_S/2) + $gp_adj[1];
		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = $dat;
		$pix['src']['pal'] = $pal;

		copypix($pix);
		savrgba_rect($fname, $pix);
	}
	if ( $mgc == "RGBA" )
	{
		$w  = str2int($file,  4, 4);
		$h  = str2int($file,  8, 4);
		$dat = substr($file, 12, $w*$h*4);

		$pix = COPYPIX_DEF();
		$pix['rgba']['w'] = CANV_S;
		$pix['rgba']['h'] = CANV_S;
		$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

		$pix['dx'] = ($w/-2) + (CANV_S/2) + $gp_adj[0];
		$pix['dy'] = ($h/-2) + (CANV_S/2) + $gp_adj[1];
		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = $dat;

		copyrgba($pix);
		savrgba_rect($fname, $pix);
	}
	return;
}
//////////////////////////////
$ERROR = "{$argv[0]}  x,y  FILE...\n";
if ( $argc < 3 )
	exit($ERROR);
if ( strpos($argv[1], ',') === false )
	exit($ERROR);

$b1 = explode(',', $argv[1]);
$gp_adj = array( (int)$b1[0] , (int)$b1[1] );
printf("Reposition sprites by %d x %d\n", $gp_adj[0], $gp_adj[1]);

for ( $i=2; $i < $argc; $i++ )
	repos( $argv[$i] );
