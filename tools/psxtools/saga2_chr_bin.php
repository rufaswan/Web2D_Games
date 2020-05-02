<?php
/*
 * /battle/chr%03d.bin
 *   0     test
 *   1- 31 party chars
 *  32- 57 duel  chars
 *  94-138 party weapons
 * 158-202 duel  weapons
 * 257-344 party enemies
 * 388-447 duel  enemies
 * 452-460 boss
 * 466-471 final boss
 */
require "common.inc";

define("CANV_S", 0x100);
$gp_pix = "";
$gp_clut = "";
$gp_dir  = "";

function sectpart( &$file, $nid, $st, $ed )
{
	printf("=== sectpart( $nid, %x, %x )\n", $st, $ed);

	$cnt = str2int($file, $st, 4);
	$pos = $st + 0x14;

	$data = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p0 = ord( $file[$pos] );
		if ( $p0 & 0x80 )
			$n = 12;
		else
		if ( $p0 & 0x60 ) // 40 vflip , 20 hflip
			$n = 8;
		else
		{
			$data[] = substr($file, $pos, 8);
			$n = 8;
		}
		$pos += $n;
	}
	if ( empty($data) )
		return;
	if ( $pos < $ed )
	{
		foreach ( $data as $k => $v )
		{
			$p3 = ord( $v[3] );
			$data[$k][3] = $file[$pos+$p3];
		} // foreach ( $data as $k => $v )
	}

	$pix = COPYPIX_DEF;
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	global $gp_pix, $gp_clut;
	foreach ( $data as $k => $v )
	{
		$sx = ord( $v[2] );
		$sy = ord( $v[3] ) * 0x10;
		$w  = ord( $v[4] );
		$h  = ord( $v[5] );

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $sx, $sy, $w, $h, 0x100, 0x100);
		$pix['src']['pal'] = $gp_clut;

		$dx = ord( $v[6] );
		$dy = ord( $v[7] );
		$pix['dx'] = $dx;
		$pix['dy'] = $dy;

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf("\n");
		copypix($pix);
	} // foreach ( $data as $k => $v )

	savpix($nid, $pix, true);
	return;
}

function sect1( &$file, $dir )
{
	global $gp_pix;
	$pix_st = str2int($file, 0, 4);

	$gp_pix = substr($file, $pix_st);
	$file = substr($file, 0, $pix_st);

	$pos1 = str2int($file, 0x34 , 4);
	$pos2 = str2int($file, $pos1, 4);

	$ed = $pos2 - 4;
	$st = $pos1;
	$nid = 1;
	while ( $st < $ed )
	{
		$fn = sprintf("$dir-%04d", $nid);

		$off1 = str2int($file, $st+0, 4);
		$off2 = str2int($file, $st+4, 4);

		sectpart($file, $fn, $off1, $off2);

		$st += 4;
		$nid++;
	}
	$fn = sprintf("$dir/%04d", $nid);
	$off1 = str2int($file, $st+0, 4);

	sectpart($file, $fn, $off1, $pix_st);
}

function saga2( $fname )
{
	// only CHR???.BIN files
	if ( ! preg_match("|CHR[0-9]+\.BIN|i", $fname) )
		return;

	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	global $gp_clut;
	$clut_off = str2int($file, 0x1c, 4);
	$gp_clut = clut2str($file, $clut_off, 0x100);

	$cnt = str2int($file, 0x08, 4);
	$pos = str2int($file, 0x18, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = str2int($file, $pos+0, 4);
		$p2 = str2int($file, $pos+4, 4);
		$bin = substr($file, $p1, $p2-$p1);

		$fn = sprintf("$dir/%04d", $i);
		printf("=== sect1() , %x - %x\n", $p1, $p2);
		sect1($bin, $fn);
		$pos += 4;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );

/*
	/mout/battle.out is loaded to 801a0000
	data is loaded to 800ac000
		then append to 80102000
 */
