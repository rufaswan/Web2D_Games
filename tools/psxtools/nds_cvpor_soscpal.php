<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = "";

function loadsc( $pfx, $id, $sx, $sy, $w, $h )
{
	global $gp_pix;
	if ( ! isset( $gp_pix[$id] ) )
	{
		$scfn = "$pfx.$id.sc";
		$file = load_file($scfn);
		if ( empty($file) )
			return php_error("NOT FOUND %s", $scfn);

		$len = strlen($file);
		switch ( $len )
		{
			case 0x2000: // 128x128 , 4-bit
			case 0x8000: // 256x256 , 4-bit
				$gp_pix[$id] = "";
				for ( $i=0; $i < $len; $i++ )
				{
					$b = ord($file[$i]);
					$b1 = ($b >> 0) & BIT4;
					$b2 = ($b >> 4) & BIT4;
					$gp_pix[$id] .= chr($b1) . chr($b2);
				}
				break;
			case 0x4000: // 128x128 , 8-bit
				$gp_pix[$id] = $file;
				break;
			default:
				return php_error("UNKNOWN LEN %s = %x", $scfn, $len);
		} // switch ( $len )
		echo "add TEXX $id\n";
	}

	$len = strlen( $gp_pix[$id] );
	if ( $len == 0x4000 ) // 128x128
		return rippix8($gp_pix[$id], $sx, $sy, $w, $h, 0x80, 0x80);
	else
	if ( $len == 0x10000 ) // 256x256
		return rippix8($gp_pix[$id], $sx, $sy, $w, $h, 0x100, 0x100);
	return "";
}
//////////////////////////////
function abs_canv( &$CANV_S, $int )
{
	$int = abs($int);
	if ( $int > $CANV_S )
		$CANV_S = $int + 1;
	return;
}

function sectpart( &$meta, $pfx, $id, $blk )
{
	printf("=== sectpart( $pfx , $id , %x )\n", $blk);

	$data = array();
	$CANV_S = 0;
	$is_mid = false;
	$pos = strlen($meta);
	while ( $pos > 0 )
	{
		// 0 1  2 3  4 5  6 7  8 9  10 11  12 13 14 15
		// dx-  dy-  sx-  sy-  w--  h----  t  f  c  -
		$pos -= $blk;
		zero_watch("v15", $meta[$pos+15]);

		$dx = str2int($meta, $pos+ 0, 2, true);
		$dy = str2int($meta, $pos+ 2, 2, true);
		$sx = str2int($meta, $pos+ 4, 2);
		$sy = str2int($meta, $pos+ 6, 2);
		$w  = str2int($meta, $pos+ 8, 2);
		$h  = str2int($meta, $pos+10, 2);
		$p12 = ord( $meta[$pos+12] );
		$p13 = ord( $meta[$pos+13] );
		$p14 = ord( $meta[$pos+14] );

		$data[] = array($dx,$dy,$sx,$sy,$w,$h,$p12,$p13,$p14);

		// detect origin and canvas size
		abs_canv($CANV_S, $dx);
		abs_canv($CANV_S, $dy);
		abs_canv($CANV_S, $dx + $w);
		abs_canv($CANV_S, $dy + $h);
		if ( $dx < 0 || $dy < 0 )
			$is_mid = true;

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , %2x , %2x , %2x\n", $p12, $p13, $p14);
	} // for ( $i=0; $i < $num; $i++ )
	if ( empty($data) )
		return;

	$ceil = ( $is_mid ) ? int_ceil($CANV_S*2, 16) : int_ceil($CANV_S, 16);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	$origin = ( $is_mid ) ? $ceil / 2 : 0;
	printf("CANV_S  %d\n", $CANV_S);
	printf("ORIGIN  %d\n", $origin);

	global $gp_clut;
	foreach ( $data as $v )
	{
		list($dx,$dy,$sx,$sy,$w,$h,$p12,$p13,$p14) = $v;

		$pix['dx'] = $dx + $origin;
		$pix['dy'] = $dy + $origin;

		$tid = $p12;
		$cid = $p14;
		$loadsc = loadsc($pfx, $tid, $sx, $sy, $w, $h);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = $loadsc;
		$pix['src']['pal'] = substr($gp_clut, $cid*0x40, 0x40);
		$pix['src']['pal'][3] = ZERO;
		//$pix['bgzero'] = 0;

		// 7654 3210
		// ---- --hv
		$pix['vflip'] = $p13 & 1;
		$pix['hflip'] = $p13 & 2;
		flag_watch("p13", $p13 & 0xfc);

		copypix_fast($pix);
	} // for ( $i=0; $i < $num; $i++ )

	$fn = sprintf("$pfx/%04d", $id);
	savepix($fn, $pix, false);
	return;
}

//////////////////////////////
function sectspr( &$so, $pfx )
{
	$len2 = strlen( $so[2]['d'] );
	$id2 = 0;
	for ( $i2=0; $i2 < $len2; $i2 += $so[2]['k'] )
	{
		$num = str2int($so[2]['d'], $i2+3, 1);
		$off = str2int($so[2]['d'], $i2+8, 2);
		$id2++;
		if ( $num == 0 )
			continue;

		$sub = substr($so[0]['d'], $off, $num*$so[0]['k']);
		sectpart($sub, $pfx, $id2-1, $so[0]['k']);
	} // for ( $i2=0; $i2 < $len2; $i2 += $so[2]['k'] )

	return;
}
function sectanim( &$so, $pfx )
{
	if ( ! isset( $so[3]['o'] ) || ! isset( $so[4]['o'] ) )
		return php_warning("no anim data on %s.so", $pfx);

	$anim = '';
	$len4 = strlen( $so[4]['d'] );
	$id4 = 0;
	for ( $i4=0; $i4 < $len4; $i4 += $so[4]['k'] )
	{
		$num = str2int($so[4]['d'], $i4+0, 2);
		$off = str2int($so[4]['d'], $i4+4, 2);
		$id4++;
		if ( $num == 0 )
			continue;

		$dat = array();
		for ( $n3=0; $n3 < $num; $n3++ )
		{
			$p = $off + ($n3 * $so[3]['k']);
			$b1 = str2int($so[3]['d'], $p+0, 2);
			$b2 = str2int($so[3]['d'], $p+2, 2);
			$dat[] = "$b1-$b2";
		}
		$dat = implode(' , ', $dat);
		$anim .= sprintf("anim_%d = %s\n", $id4-1, $dat);

	} // for ( $i4=0; $i4 < $len4; $i4 += $so[4]['k'] )

	save_file("$pfx/anim.txt", $anim);
	return;
}
//////////////////////////////
function cvpor( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$so  = load_file("$pfx.so");
	$pal = load_file("$pfx.pal");
	if ( empty($so) || empty($pal) )
		return;

	global $gp_clut, $gp_pix;
	$gp_clut = $pal;
	$gp_pix  = array();

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4     | 4-3 5-4
	// 5       | s-5
	// p_imo.dat
	//          40 3c70 3e20 |      3c3*10 36*8 7f*c
	//   4414 48e4           | 9a*8  22*8
	//   49f4                |  2*c
	// s3[+ 0] = 7e => s2
	// s2
	$sect = array(
		array('p' => 0x04 , 'k' => 16), // 0
		array('p' => 0x08 , 'k' =>  8), // 1
		array('p' => 0x0c , 'k' => 12), // 2
		array('p' => 0x10 , 'k' =>  8), // 3 jnt=0
		array('p' => 0x14 , 'k' =>  8), // 4 jnt=0
		array('p' => 0x20 , 'k' => 12), // 5
	);
	file2sect($so, $sect, $pfx, array('str2int', 4), 0, true);

	sectanim($so, $pfx);
	sectspr ($so, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );
