<?php
/*
[license]
[/license]
 */
require "common.inc";

define("CANV_S", 0x200);
define("SCALE", 1.0);
$gp_tim = array();

function loadtim( $tim_fn )
{
	$file = load_file($tim_fn);
	if ( empty($file) )  return;

	global $gp_tim;
	$gp_tim = psxtim($file);
	return;
}

// callback for copypix()
function wm_alp3( $fg, $bg )
{
	$nfg = "";
	for ( $i=0; $i < 4; $i++ )
	{
		$p1 = ord( $fg[$i] );
		$p1 = (int)($p1 / 5);
		$nfg .= chr($p1);
	}
	return alpha_add( $nfg, $bg );
}

// callback for copypix()
function wm_alp1( $fg, $bg )
{
	return alpha_add( $fg, $bg );
}
//////////////////////////////
function sect1( &$file, $off, $fn )
{
	$num = ord( $file[$off] );
		$off++;
	printf("=== sect1( %x , %s ) = $num\n", $off, $fn);
	if ( $num == 0 || $num & 0x80 )
		return;

	$data = array();
	while ( $num > 0 )
	{
		$num--;
		if ( $file[$off+0] != BYTE || $file[$off+1] != BYTE )
		{
			$s = substr($file, $off, 12);
			array_unshift($data, $s);
		}
		$off += 12;
	}
	if ( empty($data) )  return;

	$ceil = int_ceil( CANV_S * SCALE, 2 );
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_tim;
	foreach ( $data as $v )
	{
		zero_watch( "v7", $v[7] );

		// 0   1   2  3  4 5 6  7 8 9 a   b
		// dx1 dy1 sx sy w h cn - r - dx2 dy2
		if ( $v[10] == BYTE )
			$dx = sint16( $v[0] . $v[10] );
		else
			$dx = sint8 ( $v[0] );

		if ( $v[11] == BYTE )
			$dy = sint16( $v[1] . $v[11] );
		else
			$dy = sint8 ( $v[1] );

		$dx = (int)($dx * SCALE);
		$dy = (int)($dy * SCALE);
		$pix['dx'] = $dx + $ceil/2;
		$pix['dy'] = $dy + $ceil/2;

		$sx = ord($v[2]);
		$sy = ord($v[3]);
		$w  = ord($v[4]);
		$h  = ord($v[5]);

		$p6 = ord($v[6]);
		// /wm/weff/roah1.dat = 0xc0
		$cn = $p6 & 0x0f;

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_tim['pix'], $sx, $sy, $w, $h, $gp_tim['w'], $gp_tim['h']);
		$pix['src']['pal'] = $gp_tim['pal'][$cn];
		$pix['bgzero'] = 0;
		scalepix($pix, SCALE, SCALE);

		$pix['rotate'] = array(ord($v[8]), 0, 0);

		$p9 = ord($v[9]);
		$pix['alpha'] = "";
		if ( $p9 == 1 ) // mask / 1 + image
			$pix['alpha'] = "wm_alp1";
		if ( $p9 == 3 ) // mask / 5 + image
			$pix['alpha'] = "wm_alp3";

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , $cn , %d , $p9\n", $pix['rotate'][0]);
		copypix($pix);
	} // foreach ( $data as $v )

	savepix($fn, $pix, true);
	return;
}

function mana( $fname )
{
	// for /wm/wm*/*.dat and /wm/wm*/*.tim pair
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$file = load_file("$pfx.dat");
	if ( empty($file) )  return;
	echo "\n=== $pfx.dat/tim pair ===\n";

	loadtim("$pfx.tim");
	$dir = "{$pfx}_dattim";

	$prv = 0;
	$st = 0x40;
	$id = 1;
	while (1)
	{
		$off = str2int($file, $st, 2);
		if ( $off == 0 )
			return;
		if ( ! isset($file[$off]) )
			return;
		if ( $off < $prv )
			return;

		$fn = sprintf("$dir/%04d", $id);
		sect1( $file, $off, $fn );

		$prv = $off;
		$st += 2;
		$id++;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
v7 != ZERO
	npckan00
	npckan01
v8 != ZERO
	drl2_etc
	esl_af
	hel_af
	jul_lan
	min_af/s
	roah1
v9 != ZERO
	dom_af
	fig_af
	manal
	mek_af/bom/lan
	noruaf
	roah1/2
 */
