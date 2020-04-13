<?php
require "common.inc";

define("CANV_S", 0x180);

function sint16( $s )
{
	$int = ordint($s);
	if ( $int >> 15 )
		return $int - BIT16 - 1;
	return $int;
}

function loadtex( $pfx, $id )
{
	$fn = sprintf("%s_%04d.f", $pfx, $id);
	$file = file_get_contents($fn);

	$pix = "";
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$p = ord( $file[$st] );
		$p1 = $p & 0x0f;
		$p2 = $p >> 4;

		$pix .= chr($p1) . chr($p2);
		$st++;
	} // while ( $st < $ed )
	return $pix;
}
//////////////////////////////
function sectpart( &$meta, $pfx, $id, $num, $off )
{
	printf("=== sectpart( $pfx , $id , $num , %x )\n", $off);

	$pix = COPYPIX_DEF;
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	$clut = grayclut(16);
	$texx = array();

	while ( $num > 0 )
	{
		// 0 1  2 3  4 5  6 7  8 9  10 11  12 13 14 15
		// dx-  dy-  sx-  sy-  w--  h----  t  f  p  -
		$num--;
		$p = $off + ($num * 0x10);

		zero_watch("v13", $meta[$p+13]);
		//zero_watch("v14", $meta[$p+14]); //parent
		zero_watch("v15", $meta[$p+15]);

		$dx = sint16( $meta[$p+0] . $meta[$p+1] );
		$dy = sint16( $meta[$p+2] . $meta[$p+3] );
		$pix['dx'] = $dx + (CANV_S / 2);
		$pix['dy'] = $dy + (CANV_S / 2);

		$sx = str2int($meta, $p+ 4, 2);
		$sy = str2int($meta, $p+ 6, 2);
		$w  = str2int($meta, $p+ 8, 2);
		$h  = str2int($meta, $p+10, 2);
		$tid = ord( $meta[$p+12] );

		if ( ! isset( $texx[$tid] ) )
			$texx[$tid] = loadtex($pfx, $tid);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($texx[$tid], $sx, $sy, $w, $h, 0x80, 0x100);
		$pix['src']['pal'] = $clut;

		$p13 = ord( $meta[$p+13] );
		$pix['vflip'] = $p13 & 1;
		$pix['hflip'] = $p13 & 2;

		printf("%4d , %4d , %4d , %4d , %4d , %4d , $tid , %02x\n",
			$dx, $dy, $sx, $sy, $w, $h, $p13);
		copypix($pix);
	} // for ( $i=0; $i < $num; $i++ )

	$fn = sprintf("%04d", $id);
	savpix($fn, $pix);
	return;
}

function sectanim( &$meta, $id, $num, $off )
{
	if ( $num < 1 )
		return "";

	$ret = array();
	for ( $i=0; $i < $num; $i++ )
	{
		$p = $off + ($i*8);
		$b1 = str2int($meta, $p+0, 2);
		$b2 = str2int($meta, $p+2, 2);
		$ret[] = "$b1-$b2";
	}

	$buf = "anim_{$id} = ";
	$buf .= implode(' , ', $ret);
	return "$buf\n";
}
//////////////////////////////
function cvds( $pfx )
{
	$file = file_get_contents( "$pfx.p" );
	if ( empty($file) )  return;

	$o1 = str2int($file, 0x04, 4);
	$o2 = str2int($file, 0x08, 4);
	$o3 = str2int($file, 0x0c, 4);
	$o4 = str2int($file, 0x10, 4);
	$o5 = str2int($file, 0x14, 4);
	$o6 = str2int($file, 0x20, 4);
	// 0x24 = total sprite
	// 0x28 = total animation

	// sprite parts data
	$meta = substr($file, $o1, $o2-$o1);
	$grps = substr($file, $o3, $o4-$o3);

	$ed = strlen($grps);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$num = ord( $grps[$st+3] );
		$off = str2int($grps, $st+8, 2);
		sectpart($meta, $pfx, $id, $num, $off);

		$id++;
		$st += 12;
	} // while ( $st < $ed )

	// sprite animation sequence
	$meta = substr($file, $o4, $o5-$o4);
	$grps = substr($file, $o5, $o6-$o5);

	$ed = strlen($grps);
	$st = 0;
	$id = 0;
	$buf = "";
	while ( $st < $ed )
	{
		$num = ord( $grps[$st+0] );
		$off = str2int($grps, $st+4, 2);
		$buf .= sectanim($meta, $id, $num, $off);

		$id++;
		$st += 8;
	} // while ( $st < $ed )
	file_put_contents("anim.txt", $buf);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

/*
tpm_p.dat
 155409c = /OVERLAY/OVERLAY_0008.BIN + 609c
  171c80 = /ARM9.BIN + be480
 153def8 = /OVERLAY/OVERLAY_0007.BIN + 9ef8
  173130 = /ARM9.BIN + bf930
 1554058 = /OVERLAY/OVERLAY_0008.BIN + 6058
 153de34 = /OVERLAY/OVERLAY_0007.BIN + 9e34
 */
